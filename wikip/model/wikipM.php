<?php

require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/helpers.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/logger.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/control.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/model.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/view.php');

/**
 * wikip model
 * ________________________________________________________________
 */
class wikipM extends model
{
  public $wC = 'de';
  public $url = '';

  /**
   * Konstruktor
   * _________________________________________________________________
   */
  function __construct($loc = 'de')
  {
    $this->wC = $loc;
  }

  /**
   * Search
   * _________________________________________________________________
   */
  public function search($term)
  {
    $this->url = 'http://'.$this->wC.'.wikipedia.org/w/api.php?action=query&format=json&list=search&srsearch='.$this->sanitizeQuery($term);
    $data = $this->grabJson($this->url);

    if (isset($data['query']['search']) and (count($data['query']['search']) > 0))
    {
      $data = $data['query']['search'];
      return $data;
    }
    else
    {
      throw new Exception('Nothing found or empty search key.');
    }

  }

  /**
   * Summary - currently unused
   * _________________________________________________________________
   */
  public function summary($title)
  {
    $this->url = 'http://'.$this->wC.'.wikipedia.org/api/rest_v1/page/summary/'.$this->sanitizeQuery($title);
    $data = $this->grabJson($this->url);
    $data = $data['extract_html'];

    if (isset($data) and ($data != ''))
    {
      return $data;
    }
    else
    {
      throw new Exception('No summary found.');
    }
  }

  /**
   * grab the fulltext and rework it
   * basically we're creating View Code here because we replace some
   * characters with html
   * but we regard the whole text as one entity so we don't care
   * _________________________________________________________________
   */
  public function fulltext($title)
  {
    // no "sanitizeQuery" here - we use a different API...
    $this->url = 'https://'.$this->wC.'.wikipedia.org/w/api.php?action=query&format=json&titles='.urlencode($title).'&prop=extracts&exlimit=max&explaintext';

    $data = $this->grabJson($this->url);
    $data = array_values($data['query']['pages']);
    $fulltext = $data[0]['extract'];

    if ($fulltext != '')
    {
      $fulltext = preg_replace_callback('/^== (.*) ==$/m', function($matches)
      {
        $str = '<br><br><b>'.$matches[1].'</b><br>';
        return $str;
      },
      $fulltext);

      $fulltext = preg_replace_callback('/\<br\>\s*\<br\>\s*\<br\>/m', function($matches)
      {
        $str = '<br><br>';
        return $str;
      },
      $fulltext);

      $fulltext = preg_replace_callback('/^=== (.*) ===$/m', function($matches)
      {
        $str = '<br><br><i>'.$matches[1].'</i><br>';
        return $str;
      },
      $fulltext);

      $fulltext = preg_replace_callback('/\<br\>\s*\<br\>\s*\<br\>/m', function($matches)
      {
        $str = '<br><br>';
        return $str;
      },
      $fulltext);

      $fulltext = preg_replace_callback('/^==== (.*) ====$/m', function($matches)
      {
        $str = '<br><br><u>'.$matches[1].'</u><br>';
        return $str;
      },
      $fulltext);

      $fulltext = preg_replace_callback('/\<br\>\s*\<br\>\s*\<br\>/m', function($matches)
      {
        $str = '<br><br>';
        return $str;
      },
      $fulltext);

      $fulltext = preg_replace_callback('/^===== (.*) =====$/m', function($matches)
      {
        $str = '<br><br>*'.$matches[1].'*<br>';
        return $str;
      },
      $fulltext);

      $fulltext = preg_replace_callback('/\<br\>\s*\<br\>\s*\<br\>/m', function($matches)
      {
        $str = '<br><br>';
        return $str;
      },
      $fulltext);

      $fulltext = str_replace("\r", '', $fulltext);
      $fulltext = str_replace("\n", ' ', $fulltext);

      return $fulltext;
    }
    else
    {
      throw new Exception('No text found for item.');
    }
  }

  /**
   * grab the "media" - just images at the moment
   * _________________________________________________________________
   */
  public function media($title)
  {
    $images = array();
    $this->url = 'https://'.$this->wC.'.wikipedia.org/api/rest_v1/page/media-list/'.$this->sanitizeQuery($title);
    $media = $this->grabJson($this->url);
    $media = isset($media['items']) ? $media['items'] : array();

    if (count($media) > 0)
    {
      foreach($media as $item)
      {
        if ($item['type'] == 'image')
        {
          if (isset($item['srcset'][0]['src']))
          {
            $img = 'http:'.$item['srcset'][0]['src'];
            $caption = $item['caption']['text'];
            if (
                 ($this->getImgExt($img) == 'jpg') ||
                 ($this->getImgExt($img) == 'png') ||
                 ($this->getImgExt($img) == 'gif') ||
                 ($this->getImgExt($img) == 'jpeg')
               )
            {
              $images[] = array('url' => $img, 'caption' => $caption);
            }
          }
        }
      }
    }

    return $images; // always return an array, don't throw exceptions
  }

  /**
   * grabs a json structure and convert it to
   * ISO encoding (json is ALWAYS UTF-8)
   * _________________________________________________________________
   */
  public function grabJson($url)
  {
    $json = parent::grabJson($url);
    array_walk_recursive($json, function(&$val, $key)
    {
      $val = $this->Utf8ToIso($val);
    });

    return $json;
  }

  /**
   * sanitize a query - replace spaces with underscore and encode
   * the query term as UTF8 which is important for some APIs...
   * _________________________________________________________________
   */
  private function sanitizeQuery($term)
  {
    $term = str_replace(' ', '_', utf8_encode($term));
    return $term;
  }

  /**
   * get file extension lowercase
   * _________________________________________________________________
   */
  private function getImgExt($url)
  {
    $ext = mb_strtolower(mb_substr($url, mb_strrpos($url, '.')+1));
    return $ext;
  }
}
