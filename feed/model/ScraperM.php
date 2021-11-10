<?php

class ScraperM extends cachedRequestM
{
  public $textOnly = false;
  public $allowedTags = '<br><b><i><u><s><em><ul><ol><li><hr><table><thead><tbody><tr><td>';

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
  public function fetchContent($url, $xpath)
  {
    $data = array();
    $parentRootXpaths = array("//article", "//main", "//*[@id='content']", "//body");
    //$parentRootXpaths = array("//body");

    try
    {
      $html = $this->grab($url);

      $doc = new DOMDocument();
      $doc->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG | LIBXML_NOXMLDECL);

      // fetch target nodes
      $xp = new DOMXPath($doc);

      if ($xpath == './/p')
      {
        foreach ($parentRootXpaths as $prx)
        {
          $elements = array();
          $elementsCount = array();

          $roots = $xp->query($prx);

          if (count($roots) != 0)
          {
            foreach($roots as $root)
            {
              $x = $xp->query($xpath, $root);
              $elements[] = $x;
              $elementsCount[] = count($x);
            }

            $maxPIdxs = array_keys($elementsCount, max($elementsCount));
            $maxPIdx = $maxPIdxs[0];

            $elements = $elements[$maxPIdx];

            if ((count($elements) > 0) and ($elements != NULL))
            {
              break;
            }
          }
        }
      }
      else
      {
        $elements = $xp->query($xpath);
      }

      // Clean up results
      if (!is_null($elements))
      {
        foreach ($elements as $element)
        {
          if ($this->textOnly == true)
          {
            // Strip everything
            $str = $this->Utf8ToIsoHtml(trim($element->nodeValue));
          }
          else
          {
            // Leave some tags intact - more fragile
            $str = $doc->saveXML($element);
            $str = trim($str);
            $str = strip_tags($str, $this->allowedTags);

            if (strlen($str) > 1)
            {
              $str = $this->removeAttributes($str);
              $str = $this->Utf8ToIso($str);
            }
          }

          if ($str != '')
          {
            $data[] = $str;
          }
        }

        return $data;
      }
      else
      {
        throw new Exception('ScaperM: No elements found.');
      }
    }
    catch(Exception $e)
    {
      throw $e;
    }
  }

  /**
   * try to remove attributes
   * ________________________________________________________________
   */
  function removeAttributes($html)
  {
    $str = '';
    $doc = new DOMDocument();
    $doc->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG | LIBXML_NOXMLDECL);

    // remove attributes
    $xp = new DOMXPath($doc);
    $nodes = $xp->query('//@*');

    foreach ($nodes as $node)
    {
      $attributes = $node->parentNode->attributes;
      while (($attributes->length > 0) && ($node->parentNode != null))
      {
        $node->parentNode->removeAttribute($attributes->item(0)->name);
      }
    }

    if ((is_array($doc->documentElement->childNodes)) and (count($doc->documentElement->childNodes) > 0))
    {
      foreach($doc->documentElement->childNodes as $child)
      {
        $str .= trim($doc->saveXML($child)).' ';
      }
    }
    else
    {
      $str = trim($doc->saveXML($doc->documentElement));
    }

    return $str;
  }
}

?>