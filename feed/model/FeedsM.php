<?php

class FeedsM extends cachedRequestM
{
  /**
   * Konstruktor
   * ________________________________________________________________
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * fetch and parse the rss file
   * ________________________________________________________________
   */
  public function fetchRSS($url)
  {
    try
    {
      $result = $this->grab($url);

      libxml_use_internal_errors(true);
      libxml_clear_errors();
      $xml = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOERROR | LIBXML_NOWARNING);

      if ($xml === false)
      {
        $msg = 'Laden des XML fehlgeschlagen.<br>';
        foreach(libxml_get_errors() as $error)
        {
          $msg .= $error->message.'<br>';
        }
        throw new Exception($msg);
      }

      $json = json_encode($xml);
      $feed = json_decode($json, true);

      return $this->extractData($feed);
    }
    catch (Exception $e)
    {
      throw $e;
    }
  }

  /**
   * extract the data
   * ________________________________________________________________
   */
  public function extractData($feed)
  {
    $type = null;
    $data = array();

    $type = $this->getFeedVersion($feed);

    if (
        ($type == 'rss091') ||
        ($type == 'rss092') ||
        ($type == 'rss200')
       )
    {
      $data['meta']['title']       = $this->cleanStringHTML($this->getPropFromItem($feed['channel'], 'title'));
      $data['meta']['link']        = $this->cleanStringHTML($this->getPropFromItem($feed['channel'], 'link'));
      $data['meta']['description'] = $this->cleanStringHTML($this->getPropFromItem($feed['channel'], 'description'));
      $data['meta']['logo']        = $this->rssGetFeedLogo($feed);

      if ($this->isFilledArray($feed['channel']['item']))
      {
        foreach($feed['channel']['item'] as $item)
        {
          $data['data'][] = array(
            'title'       => $this->cleanStringHTML($this->getPropFromItem($item, 'title')),
            'link'        => $this->cleanString($this->getPropFromItem($item, 'link')),
            'description' => $this->cleanStringHTML($this->getPropFromItem($item, 'description')),
            'date'        => $this->cleanString($this->getPropFromItem($item, 'pubDate')),
            'image'       => $this->rssGetImageFromEnclosure($item)
          );
        }
      }
      else
      {
        throw new Exception('This feed is empty.');
      }
    }
    elseif ($type == 'rss100')
    {
      $data['meta']['title']       = $this->cleanStringHTML($this->getPropFromItem($feed['channel'], 'title'));
      $data['meta']['link']        = $this->cleanStringHTML($this->getPropFromItem($feed['channel'], 'link'));
      $data['meta']['description'] = $this->cleanStringHTML($this->getPropFromItem($feed['channel'], 'description'));
      $data['meta']['logo']        = $this->rdfGetFeedLogo($feed);

      if ($this->isFilledArray($feed['item']))
      {
        foreach($feed['item'] as $item)
        {
          $data['data'][] = array(
            'title'       => $this->cleanStringHTML($this->getPropFromItem($item, 'title')),
            'link'        => $this->cleanString($this->getPropFromItem($item, 'link')),
            'description' => $this->cleanStringHTML($this->getPropFromItem($item, 'description')),
            'date'        => $this->cleanString($this->getPropFromItem($item, 'dc:date')),
            'image'       => $this->rdfGetImage($item)
          );
        }
      }
      else
      {
        throw new Exception('This feed is empty.');
      }
    }
    elseif($type == 'atom')
    {
      $data['meta']['title']       = $this->cleanStringHTML($this->getPropFromItem($feed, 'title'));
      $data['meta']['link']        = $this->cleanStringHTML($this->getPropFromItem($feed, 'link'));
      $data['meta']['description'] = $this->cleanStringHTML($this->getPropFromItem($feed, 'subtitle'));
      $data['meta']['logo']        = $this->atomGetFeedLogo($feed);

      if ($this->isFilledArray($feed['entry']))
      {
        foreach($feed['entry'] as $item)
        {
          $data['data'][] = array(
            'title'       => $this->cleanStringHTML($this->getPropFromItem($item, 'title')),
            'link'        => $this->cleanString($this->atomGetLinkInItem($item)),
            'description' => $this->cleanStringHTML($this->getPropFromItem($item, 'summary')),
            'date'        => $this->cleanString($this->getPropFromItem($item, 'published'))
          );
        }
      }
      else
      {
        throw new Exception('This feed is empty');
      }
    }

    return $data;
  }

  /**
   * fetch the rss version, or atom
   * ________________________________________________________________
   */
  protected function getFeedVersion($feedA)
  {
    $type = null;

    if (
          (isset($feedA['channel'])) &&
          (isset($feedA['@attributes']['version']))
        )
    {
      if ($feedA['@attributes']['version'] == '2.0')
      {
        $type = 'rss200';
      }
      elseif ($feedA['@attributes']['version'] == '0.91')
      {
        $type = 'rss091';
      }
      elseif ($feedA['@attributes']['version'] == '0.92')
      {
        $type = 'rss092';
      }
    }
    elseif (isset($feedA['item']))
    {
      $type = 'rss100';
    }
    elseif (isset($feedA['entry']))
    {
      $type = 'atom';
    }

    return $type;
  }

  /**
   * is element an array and filled?
   * _______________________________________________________________
   */
  protected function isFilledArray($el)
  {
    if (is_countable($el) && (count($el) > 0))
    {
      return true;
    }
    return false;
  }

  /**
   * get element from rss item, fail safe
   * ________________________________________________________________
   */
  protected function getPropFromItem($item, $propName)
  {
    if (isset($item[$propName]))
    {
      return $item[$propName];
    }

    return '';
  }

  /**
   * get link of an atom feed item element
   * single purpose function, bad.
   * but there is nothing we can do
   * ________________________________________________________________
   */
  protected function atomGetLinkInItem($item)
  {
    if (isset($item['link']['@attributes']['href']))
    {
      return $item['link']['@attributes']['href'];
    }

    return '';
  }

  /**
   * atom get feed logo
   * ________________________________________________________________
   */
  protected function atomGetFeedLogo($feed)
  {
    $logo = '';

    if ((string) $feed['icon'] != '')
    {
      $logo = (string) $feed['icon'];
    }

    if (($logo == '') && ($feed['logo'] != ''))
    {
      $logo = (string) $feed['logo'];
    }

    return $logo;
  }


  /**
   * rss get feed logo
   * ________________________________________________________________
   */
  protected function rssGetFeedLogo($feed)
  {
    if (isset($feed['channel']['image']))
    {
      return (string) $feed['channel']['image']['url'];
    }

    return '';
  }

  /**
   * rss get article image
   * ________________________________________________________________
   */
  protected function rssGetImageFromEnclosure($item)
  {
    if (isset($item['enclosure']))
    {
      $mime = $item['enclosure']['@attributes']['type'];
      if (
          ($mime == 'image/jpeg') ||
          ($mime == 'image/gif') ||
          ($mime == 'image/png')
         )
      {
        return $item['enclosure']['@attributes']['url'];
      }
    }

    return '';
  }

  /**
   * get RSS1/RDF feed logo
   * ________________________________________________________________
   */
  protected function rdfGetFeedLogo($feed)
  {
    if (isset($feed['channel']['image']))
    {
      return (string) $feed['channel']['image']['@attributes']['rdf:resource'];
    }

    return '';
  }

  /**
   * get RSS1/RDF image from MP tag
   * ________________________________________________________________
   */
  protected function rdfGetImage($item)
  {
    if (
        (isset($item['mp:image'])) &&
        (isset($item['mp:image'][0]['mp:data']))
       )
    {
      return $item['mp:image'][0]['mp:data'];
    }

    return '';
  }

  /**
   * clean a string and make it HTML instead of UTF8
   * don't apply this to links!
   * ________________________________________________________________
   */
  protected function cleanStringHTML($str)
  {
    if (is_array($str))
    {
      $str = implode($str, ' ');
    }

    return $this->Utf8ToIsoHtml(trim($this->stripTags((string) $str)));
  }

  /**
   * clean a string and convert it to ISO encoding
   * ________________________________________________________________
   */
  protected function cleanString($str)
  {
    if (is_array($str))
    {
      $str = implode($str, ' ');
    }

    return $this->Utf8ToIso(trim($this->stripTags((string) $str)));
  }

}

?>