<?php

class Scraper2M extends cachedRequestM
{
  public $doc = null;
  public $xp = null;

  public $pElems = [];
  public $pElemsScore = [];
  public $statsLog = [];

  public $upvoteScore = 25; // default value for upvoting
  public $upvoteTextLength = 15;
  public $upvoteNumCommas = 1;
  public $upvoteNumDots = 2;
  public $upvotePathSimilarityTreshold = 75; //percent

  public $dumbMode = false;
  public $textOnly = false;
  public $allowAmpReroute = false; // true = breaks Süddeutsche Zeitung - y tho?

  public $metaXPath = [
                        'title'       => '/html/head/meta[@property="og:title"]',
                        'url'         => '/html/head/meta[@property="og:url"]',
                        'type'        => '/html/head/meta[@property="og:type"]',
                        'description' => '/html/head/meta[@property="og:description"]',
                        'image'       => '/html/head/meta[@property="og:image"]'
                      ];

  //public $contentXPath = '/html/body//p';
  public $contentXPath = '/html/body//*[self::p or self::blockquote or self::pre]';
  //public $contentXPath = '/html/body//*[self::p or self::blockquote or self::pre or self::ol or self::ul]';
  //public $contentXPath = '/html/body//*[self::p or self::blockquote or self::h3 or self::h4]';
  //public $contentXPath = '/html/body//*[self::p or self::blockquote or self::ol or self::ul or self::h3 or self::h4]';
  public $allowedTags = '<strong><em><br>';

  /**
   * Konstruktor
   * ________________________________________________________________
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * fetchContent
   * ________________________________________________________________
   */
  public function fetchContent($url, $xpath = '')
  {
    $treshold = $this->getTreshold();
    $data = [
      'meta' => [],
      'text' => []
    ];

    $this->contentXPath = ($xpath != '') ? $xpath : $this->contentXPath;

    try
    {
      $this->loadDocument($url);

      $data['meta'] = $this->getMetadata();
      $this->pElems = $this->getPElems();

      if (
          (!$this->dumbMode) &&
          ($xpath == '')
         )
      {
        for ($i = 0; $i < count($this->pElems); $i++)
        {
          if (isset($this->pElems[$i]))
          {
            if (
                $this->rateHasContent($i) &&    // if node is empty, stop rating
                $this->rateAncestors($i)        // if node is side content, stop rating
               )
            {
              $this->rateLength($i);
              $this->ratePunctuation($i);
              $this->rateNeighbours($i);
              $this->ratePathStructure($i);
            }

            // for debugging
            if (STATS == true)
            {
              $this->statsLog[$i]['FINAL SCORE'] = (int) $this->pElemsScore[$i];
              logger::vh($i, $this->pElems[$i]->textContent, $this->statsLog[$i]);
            }
          }
        }

        if (STATS == true)
        {
          $this->stats();
        }

        $this->removeZeroRated();

        for ($i = 0; $i < count($this->pElems); $i++)
        {
          if ($this->pElemsScore[$i] >= $treshold)
          {
            $str = $this->cleanElement($this->pElems[$i]);
            $data['text'][] = $str;
          }
        }
      }
      else
      {
        for ($i = 0; $i < count($this->pElems); $i++)
        {
          $str = $this->cleanElement($this->pElems[$i]);
          $data['text'][] = $str;
        }
      }

      return $data;
    }
    catch(Exception $e)
    {
      throw $e;
    }
  }

  /**
   * rate Empty
   * kick out empty elements
   * return TRUE if not empty.
   * return FALSE if empty.
   * _________________________________________________________________
   */
  protected function rateHasContent($idx)
  {
    $str = $this->pElems[$idx]->nodeValue;
    $str = preg_replace('/\xc2\xa0/', ' ', $str);
    $str = trim($str);

    if ($str == '')
    {
      $this->pElemsScore[$idx] = 0;
      $this->statsLog[$idx]['is empty'] = '0!';
      return false;
    }

    return true;
  }

  /**
   * exclude if eval ancestor
   * upvote if "main" element
   * return TRUE if MAIN content
   * return FALSE if SIDE content
   * _________________________________________________________________
   */
  protected function rateAncestors($idx)
  {
    $koTags = ['aside', 'nav', 'header', 'footer', 'form', 'noscript', 'figcaption', 'a',
               'amp-sidebar', 'amp-consent', 'amp-analytics', 'amp-lightbox-gallery', 'amp-skimlinks', 'amp-geo'];

    $koIDs = ['sidebar', 'comment', 'comments', 'nav', 'footer', 'header'];

    $koClasses = ['comment', 'comments',
                  'popmake', 'ad-container', 'ad_container',
                  'tagslist', 'tags-list', 'tags_list', 'tagbox',
                  'relatedtopics', 'related-topics', 'related_topics',
                  'relatedposts', 'related-posts', 'related_posts',
                  'articlesidebar', 'article-sidebar', 'article_sidebar',
                  'BorlabsCookie', 'teaser', 'hidden'];

    $koClassesFragments = ['adblock'];

    $koStyles = ['display: none;', 'display:none;',
                 'visibility: hidden;', 'visibility:hidden;',
                 'visibility: collapse;', 'visibility:collapse;'];

    $mainTags = ['body', 'article', 'main'];
    $mainIDs = ['content', 'article', 'main'];
    $mainClasses = ['content', 'article', 'main'];

    $isSideContent = false;
    $isMainContent = false;
    $parentNodes = $this->xp->query("ancestor::*" , $this->pElems[$idx]);

    // Downvotes - is side content?
    foreach ($parentNodes as $node)
    {
      $isSideContent = ($this->checkTag($idx, $node, $koTags)) ||
                       ($this->checkID($idx, $node, $koIDs)) ||
                       ($this->checkClasses($idx, $node, $koClasses) && (!$this->checkTag($idx, $node, $mainTags))) ||
                       ($this->checkClassesContain($idx, $node, $koClassesFragments) && (!$this->checkTag($idx, $node, $mainTags))) ||
                       ($this->checkStyles($idx, $node, $koStyles));

      if ($isSideContent)
      {
        $this->pElemsScore[$idx] = 0;
        $this->statsLog[$idx]['is apparently not in main content'] = '0!';
        break;
      }
    }

    // Upvotes - is main content?
    if (!$isSideContent)
    {
      foreach ($parentNodes as $node)
      {
        $isMainContent = false;
        $isMainContent = ($this->checkTag($idx, $node, $mainTags)) ||
                         ($this->checkID($idx, $node, $mainIDs)) ||
                         ($this->checkClasses($idx, $node, $mainClasses));

        if ($isMainContent)
        {
          $this->pElemsScore[$idx] += $this->upvoteScore;
          $this->statsLog[$idx]['is apparently in main content'][] = '+'.$this->upvoteScore;
        }
      }

      // return TRUE even if we can't be sure if we have main content -
      // because we are at least not side content and want to continue with the rating
      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * rate Length
   * _________________________________________________________________
   */
  protected function rateLength($idx)
  {
    if (str_word_count($this->pElems[$idx]->nodeValue) >= $this->upvoteTextLength)
    {
      $this->pElemsScore[$idx] += $this->upvoteScore;
      $this->statsLog[$idx]['length'] = '+'.$this->upvoteScore;
    }
  }

  /**
   * count commas
   * _________________________________________________________________
   */
  protected function ratePunctuation($idx)
  {
    if (substr_count($this->pElems[$idx]->nodeValue, ',') >= $this->upvoteNumCommas)
    {
      $this->pElemsScore[$idx] += $this->upvoteScore;
      $this->statsLog[$idx]['commas'] = '+'.$this->upvoteScore;
    }

    if (substr_count($this->pElems[$idx]->nodeValue, '.') >= $this->upvoteNumDots)
    {
      $this->pElemsScore[$idx] += $this->upvoteScore;
      $this->statsLog[$idx]['dots'] = '+'.$this->upvoteScore;
    }
  }

  /**
   * check if neighbour is also a "p"
   * _________________________________________________________________
   */
  protected function rateNeighbours($idx)
  {
    if (
        ($this->prevSibling($this->getNode($idx))->tagName == 'p') ||
        ($this->nextSibling($this->getNode($idx))->tagName == 'p')
       )
    {
      $this->pElemsScore[$idx] += $this->upvoteScore;
      $this->statsLog[$idx]['previous or next sibling is also p'] = '+'.$this->upvoteScore;
    }
  }

  /**
   * rate path structure similiarity
   * ________________________________________________________________
   */
  protected function ratePathStructure($idx)
  {
    $simPrevPercent = null;
    $simNextPercent = null;
    $inc = 0;
    $current = $this->pElems[$idx]->parentNode->getNodePath();
    $prev = $this->getNode($idx-1);
    $next = $this->getNode($idx+1);

    $inc = ($next === null) ? (2 * $this->upvoteScore) : $this->upvoteScore;
    $inc = ($prev === null) ? (2 * $this->upvoteScore) : $this->upvoteScore;

    if ($prev != null)
    {
      $prev = $prev->parentNode->getNodePath();
      if ($prev != '')
      {
        similar_text($current, $prev, $simPrevPercent);

        if ((int) $simPrevPercent >= $this->upvotePathSimilarityTreshold)
        {
          $this->pElemsScore[$idx] += $inc;
          $this->statsLog[$idx]['previous p has similiar path'] = '+'.$inc;
        }

        if ((int) $simPrevPercent == 100)
        {
          $this->pElemsScore[$idx] += $inc;
          $this->statsLog[$idx]['previous p has same path'] = '+'.$inc;
        }

        return;
      }
    }

    if ($next != null)
    {
      $next = $next->parentNode->getNodePath();
      if ($next != '')
      {
        similar_text($current, $next, $simNextPercent);

        if ((int) $simNextPercent >= $this->upvotePathSimilarityTreshold)
        {
          $this->pElemsScore[$idx] += $inc;
          $this->statsLog[$idx]['next p has similiar path'] = '+'.$inc;
        }

        if ((int) $simNextPercent == 100)
        {
          $this->pElemsScore[$idx] += $inc;
          $this->statsLog[$idx]['next p has same path'] = '+'.$inc;
        }
      }
    }
  }

  /**
   * remove 0 rated nodes
   * ________________________________________________________________
   */
  protected function removeZeroRated()
  {
    for ($i = 0; $i < count($this->pElems); $i++)
    {
      if ((int) $this->pElemsScore[$i] == 0)
      {
        //unset($this->pElems[$i]); // for some reason, this can't be removed - reference on the DOM object?
        unset($this->pElemsScore[$i]);
      }
    }
  }

  /**
   * load html into doc
   * ________________________________________________________________
   */
  protected function loadHTML($url)
  {
    try
    {
      $html = $this->grab($url);
      $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $html);

      //file_put_contents(makeFileName($url).'.html', $html);
      //logger::vh($url);

      $this->doc = new DOMDocument();
      $this->doc->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG | LIBXML_NOXMLDECL);
      $this->xp = new DOMXPath($this->doc);
    }
    catch(Exception $e)
    {
      throw $e;
    }
  }

  /**
   * load document, either plain or via amp
   * ________________________________________________________________
   */
  protected function loadDocument($url)
  {
    $ampXP = '/html/head/link[@rel="amphtml"]';
    $ampURL = '';

    try
    {
      $this->loadHTML($url);
      $ampLink = $this->xp->query($ampXP);

      if (($ampLink->length > 0) && $this->allowAmpReroute)
      {
        $ampURL = $ampLink[0]->getAttribute('href');

        if (strpos($ampURL, 'http') === false)
        {
          $parsed_url = parse_url($url);
          $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
          $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
          $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
          $slash    = (substr($ampURL, 0, 1) == '/') ? '' : '/';
          $ampURL   = $scheme.$host.$port.$slash.$ampURL;
        }

        $this->loadHTML($ampURL);
      }
    }
    catch(Exception $e)
    {
      throw $e;
    }
  }


  /**
   * get Meta Data
   * _________________________________________________________________
   */
  protected function getMetadata()
  {
    $meta = [];

    foreach($this->metaXPath as $key => $xp)
    {
      $meta[$key] = '';
      $elems = $this->xp->query($xp);
      if ($elems->length > 0)
      {
        $meta[$key] = $this->Utf8ToIso(trim($elems[0]->getAttribute('content')));
      }
    }

    return $meta;
  }

  /**
   * get PElems
   * _________________________________________________________________
   */
  protected function getPElems()
  {
    $elements = [];

    try
    {
      $elements = $this->xp->query($this->contentXPath);
      return $elements;
    }
    catch(Exception $e)
    {
      throw $e;
    }
  }

  /**
   * getNode
   * ________________________________________________________________
   */
  protected function getNode($idx)
  {
    $ret = null;
    $idx = ($idx < 0) ? null : $idx;
    $idx = ($idx >= count($this->pElems)) ? null : $idx;

    return ($idx !== null) ? $this->pElems[$idx] : null;
  }

  /**
   * nexSibling
   * ________________________________________________________________
   */
  protected function nextSibling($node)
  {
    while ($node && ($node = $node->nextSibling))
    {
      if ($node instanceof DOMElement)
      {
        break;
      }
    }
    return $node;
  }

  /**
   * prevSibling
   * ________________________________________________________________
   */
  protected function prevSibling($node)
  {
    while ($node && ($node = $node->previousSibling))
    {
      if ($node instanceof DOMElement)
      {
        break;
      }
    }
    return $node;
  }

  /**
   * check if one of (multiple) tag names
   * ________________________________________________________________
   */
  protected function checkTag($idx, $node, $tags)
  {
    $ret = false;

    for ($i=0; $i < count($tags); $i++)
    {
      if ($node->tagName == $tags[$i])
      {
        $ret = true;
        $this->statsLog[$idx]['checking if tag name is "'.$tags[$i].'"'] = ($ret == true) ? '*** true ***' : 'false';
        break;
      }
    }

    return $ret;
  }

  /**
   * check ID
   * _________________________________________________________________
   */
  protected function checkID($idx, $node, $idNames)
  {
    $ret = false;

    if ($node->hasAttribute('id'))
    {
      $id = $node->getAttribute('id');
      foreach ($idNames as $idName)
      {
        $ret = ($id == $idName) ? true : false;
        $this->statsLog[$idx]['checking if ID is "'.$idName.'"'] = ($ret == true) ? '*** true ***' : 'false';
        if ($ret)
        {
          break;
        }
      }
    }

    return $ret;
  }

  /**
   * check classes
   * ________________________________________________________________
   */
  protected function checkClasses($idx, $node, $classNames)
  {
    $ret = false;

    if ($node->hasAttribute('class'))
    {
      $classes = $node->getAttribute('class');

      $classesArr = explode(' ', $classes);
      foreach($classesArr as $class)
      {
        $class = trim($class);
        foreach ($classNames as $className)
        {
          if ($class == $className)
          {
            $ret = true;
          }

          $this->statsLog[$idx]['checking if a class with name "'.$className.'" exists'] = ($ret == true) ? '*** true ***' : 'false';
          if ($ret)
          {
            break 2;
          }
        }
      }
    }

    return $ret;
  }

  /**
   * check classes contain
   * ________________________________________________________________
   */
  protected function checkClassesContain($idx, $node, $classNames)
  {
    $ret = false;

    if ($node->hasAttribute('class'))
    {
      $classes = $node->getAttribute('class');

      foreach ($classNames as $className)
      {
        $ret = (strpos($classes, $className) !== false) ? true : false;
        $this->statsLog[$idx]['checking if classes contain the string "'.$className.'"'] = ($ret == true) ? '*** true ***' : 'false';
        if ($ret)
        {
          break;
        }
      }
    }

    return $ret;
  }

  /**
   * check Styles
   * _________________________________________________________________
   */
  protected function checkStyles($idx, $node, $styleStrings)
  {
    $ret = false;

    if ($node->hasAttribute('style'))
    {
      $style = $node->getAttribute('style');

      foreach ($styleStrings as $styleString)
      {
        $ret = (strpos($style, $styleString) !== false) ? true : false;
        $this->statsLog[$idx]['checking if style attribute contains the string "'.$styleString.'"'] = ($ret == true) ? '*** true ***' : 'false';
        if ($ret)
        {
          break;
        }
      }
    }

    return $ret;
  }

  /**
   * clean Element
   * _________________________________________________________________
   */
  protected function cleanElement($element)
  {
    // https://stackoverflow.com/questions/3026096/remove-all-attributes-from-an-html-tag
    $str = '';

    if ($this->textOnly == true)
    {
      // Strip everything
      $str = trim($element->textContent);
    }
    else
    {
      // leave some tags intact
      $str = trim($element->ownerDocument->saveXML($element));
      $str = strip_tags($str, $this->allowedTags);
      $str = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si",'<$1$2>', $str); // FIXME: remove attributes from allowed tags.
      $str = preg_replace("/(<br\ ?\/?>)+/", '<br><br>', $str);
    }

    $str = $this->Utf8ToIso($str);
    return $str;
  }

  /**
   * get treshold
   * ________________________________________________________________
   */
  protected function getTreshold()
  {
    return 75;
  }

  /**
   * stats
   * ________________________________________________________________
   */
  public function stats()
  {
    $statF = 'stats.csv';
    @unlink($statF);

    for ($i = 0; $i < count($this->pElems); $i++)
    {
      file_put_contents($statF,
                        (string) $i.';'.
                        '"'.substr(trim($this->pElems[$i]->textContent), 0, 75).'...";'.
                        '"'.$this->pElems[$i]->parentNode->getNodePath().'";'.
                        $this->statsLog[$i]['FINAL SCORE']."\r\n",
                        FILE_APPEND);
    }
  }

  /**
   * Outtakes
   * ****************************************************************
   */

  /**
   * check if same direct parent
   * _________________________________________________________________

  protected function rateParent($idx)
  {
    if ($this->pElems[$idx-1] != null)
    {
      if ($this->pElems[$idx]->parentNode->isSameNode($this->pElems[$idx-1]->parentNode) == true)
      {
        $this->pElemsScore[$idx] += 25;
        $this->statsLog[$idx]['previous element same parent'] = '+25';
        return;
      }
    }

    if ($this->pElems[$idx+1] != null)
    {
      if ($this->pElems[$idx]->parentNode->isSameNode($this->pElems[$idx+1]->parentNode) == true)
      {
        $this->pElemsScore[$idx] += 25;
        $this->statsLog[$idx]['next element same parent'] = '+25';
      }
    }
  }


  /**
   * rate and update headline
   * _________________________________________________________________

  protected function rateHeadline($idx)
  {
    if (
        ($this->pElems[$idx]->tagName == 'h3') ||
        ($this->pElems[$idx]->tagName == 'h4')
       )
    {

      $this->pElems[$idx]->textContent = ($this->pElems[$idx]->tagName == 'h3') ? '+++ '.$this->pElems[$idx]->textContent.' +++' : $this->pElems[$idx]->textContent;
      $this->pElems[$idx]->textContent = ($this->pElems[$idx]->tagName == 'h4') ? '++++ '.$this->pElems[$idx]->textContent.' ++++' : $this->pElems[$idx]->textContent;

      if (
          ($this->prevSibling($this->getNode($idx))->tagName == 'p') ||
          ($this->nextSibling($this->getNode($idx))->tagName == 'p')
         )
      {
        $this->pElemsScore[$idx] += 25;
        $this->statsLog[$idx]['is headline'] = '+25';
      }
    }
  }

  /**
   * final - buggy

  protected function getFinalSelection()
  {
    $targets = array();
    for ($i = 0; $i < count($this->pElems); $i++)
    {
      $xpath = $this->pElems[$i]->parentNode->getNodePath();
      $score = pow($this->pElemsScore[$i], 2);
      $targets[$xpath] += $score;
    }

    rsort($targets);
    $tKeys = array_keys($targets);
    $bestKey = $tKeys[0];

    for ($i = 0; $i < count($this->pElems); $i++)
    {
      if ($this->pElems[$i]->parentNode->getNodePath() == $bestKey)
      {
        $str = $this->cleanElement($this->pElems[$i]);
        $data[] = $str;
      }
    }

    return $data;
  }

  /**
   * getSibling
   * ________________________________________________________________

  protected function getSibling($idx, $offset)
  {
    $node = $this->pElems[$idx];
    $parent = $node->parentNode;
    $children = $parent->childNodes;
    $targetIdx = 0;

    for ($i=0; $i < $children->length; $i++)
    {
      if ($children->item($i) == $node)
      {
        $targetIdx = $idx + $offset;
        if (($targetIdx >= 0) && ($targetIdx < $children->length))
        {
          return $children->item($targetIdx);
        }
        else
        {
          return false;
        }
      }
    }
  }


  /**
   * get treshold
   * ________________________________________________________________

  protected function getTreshold()
  {
    /*
    $treshold = null;
    $factor   = null;
    $rest     = null;
    $pointVal = 25;

    $treshold = $this->getMedian($this->pElemsScore);
    $factor = $treshold / $pointVal;
    $rest = $treshold % $pointVal;

    $factor = ($rest == 0) ? ($factor - 1) : floor($factor);

    $treshold = $factor * $pointVal;

    // don't go smaller than 50...
    $treshold = ($treshold < 50) ? 50 : $treshold;

    return $treshold;
  }

  /**
   * get median
   * stolen
   * ________________________________________________________________

  protected function getMedian($arr)
  {
    //Make sure it's an array.
    if(!is_array($arr))
    {
      throw new Exception('$arr must be an array!');
    }

    //If it's an empty array, return FALSE.
    if(empty($arr))
    {
        return false;
    }

    sort($arr);

    //Count how many elements are in the array.
    $num = count($arr);

    //Determine the middle value of the array.
    $middleVal = floor(($num - 1) / 2);

    //If the size of the array is an odd number,
    //then the middle value is the median.
    if($num % 2)
    {
      return $arr[$middleVal];
    }

    //If the size of the array is an even number, then we
    //have to get the two middle values and get their
    //average
    else
    {
      //The $middleVal var will be the low
      //end of the middle
      $lowMid = $arr[$middleVal];
      $highMid = $arr[$middleVal + 1];

      //Return the average of the low and high.
      return (($lowMid + $highMid) / 2);
    }
  }

  */

}

?>