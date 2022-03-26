<?php

class feedGenM extends model
{
  /**
   * fetchContent
   * ________________________________________________________________
   */
  public function fetchContent($url, $linkXpath, $descXpath = '')
  {
    $data = array();

    try
    {
      // load html
      $html = $this->grab($url);

      // create Document
      $doc = new DOMDocument();
      $doc->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG | LIBXML_NOXMLDECL);

      // fetch target nodes
      $xp = new DOMXPath($doc);
      $linkEls = $xp->query($linkXpath);

      // fetch description elements, if any
      if ($descXpath != '')
      {
        $xpd = new DOMXPath($doc);
        $descEls = $xpd->query($descXpath);
      }
      else
      {
        $descEls = $linkEls;
      }

      //extract links, assign title and description
      for($i = 0; $i < count($linkEls); $i++)
      {
        $href = $linkEls[$i]->getAttribute('href');
        $href = $this->Utf8ToIso(trim($href));
        $href = $this->ensureFullURL($url, $href);

        $title = $this->Utf8ToIso(trim($linkEls[$i]->textContent));
        $desc  = $this->Utf8ToIso(trim($descEls[$i]->textContent));

        if ($title == $desc)
        {
          $desc = '';
        }

        $data[] = array('title'       => $title,
                        'description' => $desc,
                        'link'        => $href);
      }

      return $data;
    }
    catch(Exception $e)
    {
      throw $e;
    }

  }

  /**
   * make sure we always have a full URL to link to
   * ________________________________________________________________
   */
  public function ensureFullURL($url, $href)
  {
    $path = '';
    $ch = '';

    // if the URL is relative, make it full
    if ((strpos($href, 'https://') === false) && (strpos($href, 'http://') === false))
    {
      // extract base url/path
      if (strlen(parse_url($url, PHP_URL_PATH)) > 0)
      {
        $path = mb_substr($url, 0, strrpos($url, '/'));
      }

      // has trailing slash?
      $ch = substr($href, 0, 1);

      // combine base path and href, with or without slash
      $href = ($ch != '/') ? $path.'/'.$href : $path.$href;
    }

    return $href;
  }

}

?>