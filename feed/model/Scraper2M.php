<?php

class Scraper2M extends cachedRequestM
{
  public $doc = null;
  public $xp = null;

  public $pElems = null;
  public $pElemsScore = null;
  public $statsLog = null;

  public $upvoteScore = 25; // default value for upvoting
  public $upvoteTextLength = 15;
  public $upvoteNumCommas = 1;
  public $upvoteNumDots = 2;
  public $upvotePathSimilarityTreshold = 75; //percent

  public $treshold = 75; // the final frontier

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

  public $koTags = ['aside', 'nav', 'header', 'footer', 'dialog',
                    'form', 'script', 'noscript', 'figure', 'figcaption', 'a', 'button',
                    'amp-sidebar', 'amp-consent', 'amp-analytics', 'amp-lightbox-gallery', 'amp-skimlinks', 'amp-geo'];

  public $koIDs = ['sidebar', 'comment', 'comments', 'nav', 'footer', 'header'];

  public $koClasses = ['comment', 'comments',
                       'popmake', 'modalwindow',
                       'navbar', 'navigation', 'lazytrigger',
                       'ad-container', 'ad_container',
                       'tagslist', 'tags-list', 'tags_list', 'tagbox',
                       'relatedtopics', 'related-topics', 'related_topics',
                       'relatedposts', 'related-posts', 'related_posts',
                       'articlesidebar', 'article-sidebar', 'article_sidebar',
                       'BorlabsCookie', 'teaser', 'hidden',
                       'wp-caption-text', 'comment-form'];

  public $koClassesFragments = ['adblock',
                                'cookie',
                                'comment-', '-comment'];

  public $koStyles = ['display: none;', 'display:none;',
                      'visibility: hidden;', 'visibility:hidden;',
                      'visibility: collapse;', 'visibility:collapse;'];

  public $koAttributes = ['role' => 'dialog'];

  public $mainTags = ['article', 'main'];
  public $mainIDs = ['content', 'article', 'main'];
  public $mainClasses = ['content', 'article', 'main'];
  public $mainAttributes = ['itemprop' => 'articleBody'];

  //public $contentXPath = '/html/body//p'; // works always
  public $contentXPath = '/html/body//*[self::p or self::blockquote or self::pre]'; // works
  //public $contentXPath = '/html/body//*[self::p or self::blockquote or self::pre or self::li]';
  //public $contentXPath = '/html/body//*[self::p or self::blockquote or self::pre or self::ol or self::ul]';
  //public $contentXPath = '/html/body//*[self::p or self::blockquote or self::h3 or self::h4]';
  //public $contentXPath = '/html/body//*[self::p or self::blockquote or self::ol or self::ul or self::h3 or self::h4]';

  public $allowedTags = '<strong><em><br>';
  //public $allowedTags = '<strong><em><br><li>';
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
      $this->setElements();

      if (
          (!$this->dumbMode) &&
          ($xpath == '')
         )
      {
        for ($i = 0; $i < $this->getNodeCount(); $i++)
        {
          // for debugging
          if (STATS == true)
          {
            $this->statsLog[$i]['node_tagName'] = $this->getNode($i)->tagName;
            $this->statsLog[$i]['node_content'] = htmlspecialchars($this->getNode($i)->textContent, ENT_QUOTES, 'UTF-8', true);
            $this->statsLog[$i]['node_path'] = $this->getNode($i)->parentNode->getNodePath();
          }

          if (
              (!$this->nodeIsEmpty($i)) &&
              (!$this->isNodeSideContentByIdx($i)) &&
              (!$this->isNodeAncestorSideContent($i)) &&
              (!$this->nodeContentIsLinks($i))
             )
          {
            $this->rateAncestorsMainContent($i);
            $this->rateLength($i);
            $this->ratePunctuation($i);
            $this->rateNeighbours($i);
            $this->rateSamePath($i);
          }

          // for debugging
          if (STATS == true)
          {
            $this->statsLog[$i]['TOTAL SCORE'] = (int) $this->pElemsScore[$i];
          }
        }

        if (STATS == true)
        {
          $this->log();
          //$this->stats();
        }

        $this->removeZeroRated();

        for ($i = 0; $i < $this->getNodeCount(); $i++)
        {
          if ($this->pElemsScore[$i] >= $this->getTreshold())
          {
            $str = $this->cleanElement($this->getNode($i));
            $data['text'][] = $str;
          }
        }
      }
      else
      {
        for ($i = 0; $i < $this->getNodeCount(); $i++)
        {
          $str = $this->cleanElement($this->getNode($i));
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
  protected function nodeIsEmpty($idx)
  {
    $node = $this->getNode($idx);
    $str = $node->textContent;
    $str = preg_replace('/\xc2\xa0/', ' ', $str);
    $str = trim($str);

    if ($str == '')
    {
      $this->pElemsScore[$idx] = 0;
      $this->statsLog[$idx]['is empty'] = 'TRUE';

      return true;
    }

    $this->statsLog[$idx]['is empty'] = 'false';
    return false;
  }

  /**
   * check if node has any attributes that qualify for side content
   * ________________________________________________________________
   */
  protected function isNodeSideContent($idx, $node)
  {
    $isSideContent = false;

    // are we side content? - k.o.!
    $isSideContent = ($this->checkID($idx, $node, $this->koIDs)) ||
                     ($this->checkClasses($idx, $node, $this->koClasses)) ||
                     ($this->checkClassesContain($idx, $node, $this->koClassesFragments)) ||
                     ($this->checkStyles($idx, $node, $this->koStyles)) ||
                     ($this->checkAttributes($idx, $node, $this->koAttributes)) ||
                     ($this->checkTag($idx, $node, $this->koTags)); // FIXME: this doesn't make sense when checking the element itself and not the ancestors

    if ($isSideContent)
    {
      $this->pElemsScore[$idx] = 0;
    }

    return $isSideContent;
  }

  /**
   * api helper function
   * ________________________________________________________________
   */
  protected function isNodeSideContentByIdx($idx)
  {
    $node = $this->getNode($idx);
    return $this->isNodeSideContent($idx, $node);
  }

  /**
   * check if any of our ancestors has any attributes that qualify for
   * side content
   * ________________________________________________________________
   */
  protected function isNodeAncestorSideContent($idx)
  {
    $parentNodes = $this->xp->query("ancestor::*" , $this->getNode($idx));

    // is one of our ancestors side content? - k.o.!
    foreach ($parentNodes as $node)
    {
      if ($this->isNodeSideContent($idx, $node))
      {
        return true;
      }
    }

    return false;
  }

  /**
   * kill node if main content is just links
   * return TRUE if just links
   * return FALSE if not just links
   * ________________________________________________________________
   */
  protected function nodeContentIsLinks($idx)
  {
    $quotTreshold = 70;

    if (($node = $this->getNode($idx)) !== null)
    {
      if (  // FIXME: make the list of tags flexible
           //($node->tagName == 'li') ||
           ($node->tagName == 'p') ||
           ($node->tagName == 'blockquote') ||
           ($node->tagName == 'pre')
         )
      {
        $allText = $node->textContent;
        $links = $this->xp->query('.//*[self::a or self::button]', $node);

        if (count($links) > 0)
        {
          foreach($links as $link)
          {
            $linkText .= $link->textContent;
          }

          $quot = round((strlen($linkText) / strlen($allText)), 2) * 100;

          if ($quot >= $quotTreshold)
          {
            $this->pElemsScore[$idx] = 0;
            $this->statsLog[$idx]['node content is mainly links ('.$quot.'%)'] = 'TRUE';
            return true;
          }
          else
          {
            $this->statsLog[$idx]['node content is mainly links ('.$quot.'%)'] = 'false';
            return false;
          }
        }
      }
    }

    $this->statsLog[$idx]['node content is mainly links (no score)'] = 'false';
    return false;
  }

  /**
   * upvote if ancestor(s) seem(s) to be main content
   * ________________________________________________________________
   */
  protected function rateAncestorsMainContent($idx)
  {
    $isMainContent = false;
    $parentNodes = $this->xp->query("ancestor::*" , $this->getNode($idx));

    // Upvotes - is main content?
    foreach ($parentNodes as $node)
    {
      $isMainContent = false;
      $isMainContent = ($this->checkTag($idx, $node, $this->mainTags)) ||
                       ($this->checkID($idx, $node, $this->mainIDs)) ||
                       ($this->checkClasses($idx, $node, $this->mainClasses)) ||
                       ($this->checkAttributes($idx, $node, $this->mainAttributes));

      if ($isMainContent)
      {
        $this->pElemsScore[$idx] += $this->upvoteScore;
      }
    }
  }

  /**
   * rate Length
   * _________________________________________________________________
   */
  protected function rateLength($idx)
  {
    $node = $this->getNode($idx);
    if (str_word_count($node->nodeValue) >= $this->upvoteTextLength)
    {
      $this->pElemsScore[$idx] += $this->upvoteScore;
      $this->statsLog[$idx]['has length > '.$this->upvoteTextLength.' words'] = 'TRUE';
    }
    else
    {
      $this->statsLog[$idx]['has length > '.$this->upvoteTextLength.' words'] = 'false';
    }
  }

  /**
   * count commas
   * _________________________________________________________________
   */
  protected function ratePunctuation($idx)
  {
    $node = $this->getNode($idx);
    if (substr_count($node->nodeValue, ',') >= $this->upvoteNumCommas)
    {
      $this->pElemsScore[$idx] += $this->upvoteScore;
      $this->statsLog[$idx]['has more than '.$this->upvoteNumCommas.' commas'] = 'TRUE';
    }
    else
    {
      $this->statsLog[$idx]['has more than '.$this->upvoteNumCommas.' commas'] = 'false';
    }

    if (substr_count($node->nodeValue, '.') >= $this->upvoteNumDots)
    {
      $this->pElemsScore[$idx] += $this->upvoteScore;
      $this->statsLog[$idx]['has more than '.$this->upvoteNumDots.' dots'] = 'TRUE';
    }
    else
    {
      $this->statsLog[$idx]['has more than '.$this->upvoteNumDots.' dots'] = 'false';
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
      $this->statsLog[$idx]['previous or next sibling is also p'] = 'TRUE';
    }
    else
    {
      $this->statsLog[$idx]['previous or next sibling is also p'] = 'false';
    }
  }

  /**
   * check if neighboured elements have the same path
   * ________________________________________________________________
   */
  protected function rateSamePath($idx)
  {
    $node = $this->getNode($idx);
    $current = $node->parentNode->getNodePath();
    $prev = $this->getNode($idx-1);
    $next = $this->getNode($idx+1);
    $inc = 0;
    $inc = ($next === null) ? (2 * $this->upvoteScore) : $this->upvoteScore;
    $inc = ($prev === null) ? (2 * $this->upvoteScore) : $this->upvoteScore;

    if ($prev != null)
    {
      $prev = $prev->parentNode->getNodePath();
      if ($prev !== null)
      {
        if ($prev == $current)
        {
          $this->pElemsScore[$idx] += $inc;
          $this->statsLog[$idx]['previous node has same path'] = 'TRUE';
        }
        else
        {
          $this->statsLog[$idx]['previous node has same path'] = 'false';
        }
      }
    }

    if ($next != null)
    {
      $next = $next->parentNode->getNodePath();
      if ($next !== null)
      {
        if ($next == $current)
        {
          $this->pElemsScore[$idx] += $inc;
          $this->statsLog[$idx]['next node has same path'] = 'TRUE';
        }
        else
        {
          $this->statsLog[$idx]['next node has same path'] = 'false';
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
    for ($i = 0; $i < $this->getNodeCount(); $i++)
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
  protected function setElements()
  {
    try
    {
      $this->pElems = $this->xp->query($this->contentXPath);
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
  protected function getNode(int $idx)
  {
    $idx  = ($idx < 0) ? null : $idx;
    $idx  = ($idx >= $this->getNodeCount()) ? null : $idx;
    $node = ($idx !== null) ? $this->pElems->item($idx) : null;

    return $node;
  }

  /**
   * get node count
   * ________________________________________________________________
   */
  protected function getNodeCount()
  {
    return $this->pElems->length;
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
        $this->statsLog[$idx]['checking if tag name is "'.$tags[$i].'"'] = ($ret == true) ? 'TRUE' : 'false';
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
        $this->statsLog[$idx]['checking if ID is "'.$idName.'"'] = ($ret == true) ? 'TRUE' : 'false';
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

          $this->statsLog[$idx]['checking if a class with name "'.$className.'" exists'] = ($ret == true) ? 'TRUE' : 'false';
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
        $this->statsLog[$idx]['checking if classes contain the string "'.$className.'"'] = ($ret == true) ? 'TRUE' : 'false';
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
        $this->statsLog[$idx]['checking if style attribute contains the string "'.$styleString.'"'] = ($ret == true) ? 'TRUE' : 'false';
        if ($ret)
        {
          break;
        }
      }
    }

    return $ret;
  }

  /**
   * checking for certain attributes with certain values
   * ________________________________________________________________
   */
  protected function checkAttributes($idx, $node, $attributesToCheck)
  {
    $ret = false;

    foreach ($attributesToCheck as $attributeToCheckKey => $attributeToCheckVal)
    {
      if ($node->hasAttribute($attributeToCheckKey))
      {
        $attribVal = $node->getAttribute($attributeToCheckKey);
        $ret = ($attribVal == $attributeToCheckVal) ? true : false;
        $this->statsLog[$idx]['checking for attribute "'.$attributeToCheckKey.'" with value "'.$attributeToCheckVal.'"'] = ($ret == true) ? 'TRUE' : 'false';
        if ($ret == true)
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
      $str = $this->stripTags($str, $this->allowedTags);
      $str = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si",'<$1$2>', $str); // FIXME: remove attributes from allowed tags.
      $str = preg_replace("/(<br\ ?\/?>)+/", '<br><br>', $str); // replace multiple line breaks with ONE "empty line"
      $str = preg_replace("/^(<br\ ?\/?>)+/", '', $str); // remove leading line breaks - we put this in a p tag anyway
      $str = preg_replace("/(<br\ ?\/?>)+?/", '', $str); // remove trailing line breaks - we put this in a p tag anyway
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
    return $this->treshold;
  }

  /**
   * stats
   * ________________________________________________________________
   */
  public function stats()
  {
    $statF = 'stats.csv';
    @unlink($statF);

    for ($i = 0; $i < $this->getNodeCount(); $i++)
    {
      file_put_contents($statF,
                        (string) $i.';'.
                        '"'.substr(trim($this->getNode($i)->textContent), 0, 75).'...";'.
                        '"'.$this->getNode($i)->parentNode->getNodePath().'";'.
                        $this->statsLog[$i]['TOTAL SCORE']."\r\n",
                        FILE_APPEND);
    }
  }

  /**
   * Debugging function for scraper
   * ________________________________________________________________
   */
  public function log()
  {
    $file = 'scraping.html';

    $data = print_r($this->statsLog, true);

    $body  = '';
    $body .= '<html><head><title>Scraper Log</title></head><body>';
    $body .= '<pre>'.$data.'</pre>';
    $body .= '</body></html>';

    file_put_contents($file, $body);
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

  protected function ratePathStructure($idx)
  {
    $simPrevPercent = null;
    $simNextPercent = null;
    $inc = 0;
    $node = $this->getNode($idx);
    $current = $node->parentNode->getNodePath();
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
          $this->statsLog[$idx]['previous node has similiar path'] = 'true';
        }
        else
        {
          $this->statsLog[$idx]['previous node has similiar path'] = 'false';
        }

        if ((int) $simPrevPercent == 100)
        {
          $this->pElemsScore[$idx] += $inc;
          $this->statsLog[$idx]['previous node has same path'] = 'true';
        }
        else
        {
          $this->statsLog[$idx]['previous node has same path'] = 'false';
        }
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
          $this->statsLog[$idx]['next node has similiar path'] = 'true';
        }
        else
        {
          $this->statsLog[$idx]['next node has similiar path'] = 'false';
        }

        if ((int) $simNextPercent == 100)
        {
          $this->pElemsScore[$idx] += $inc;
          $this->statsLog[$idx]['next node has same path'] = 'true';
        }
        else
        {
          $this->statsLog[$idx]['next node has same path'] = 'false';
        }
      }
    }
  }

  */

}

?>