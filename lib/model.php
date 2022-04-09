<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/helpers.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/logger.php');

class model
{

  /**
   * fetch something
   * _________________________________________________________________
   */
  public function grab($url)
  {
    $ch = null;

    $url = $this->sanitizeURL($url);
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'mkGrab/0.99 (http://www.meyerk.com/)');
    curl_setopt($ch, CURLOPT_HTTPGET, true);

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    curl_setopt($ch, CURLOPT_COOKIESESSION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, './cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, './cookie.txt');

    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1000);

    $result = curl_exec($ch);

    if ($result === false)
    {
      throw new Exception('cURL Error: "'.trim(strip_tags(curl_error($ch))).'". URL was: "'.$url.'".');
      curl_close($ch);
    }
    else
    {
      curl_close($ch);
      $result = trim($result);
      return $result;
    }
  }

  /**
   * fetch json
   * _________________________________________________________________
   */
  public function grabJson($url)
  {
    $json = json_decode($this->grab($url), true);
    return $json;
  }

  /**
   * convert (multiple) UTF8 encodings to ISO
   * i should have had this years ago
   * _________________________________________________________________
   */
  protected function Utf8ToIso($str)
  {
    $max = 3;
    $i = 0;
    $fromCode = 'UTF-8';
    $toCode = "ISO-8859-1//TRANSLIT";

    while (mb_detect_encoding($str, $fromCode, true) == $fromCode) // try to catch faulty multiple utf8 encodings
    {
      $str = iconv($fromCode, $toCode, $str);
      if ($i == $max) break;
      $i++;
    }

    return $str;
  }

  /**
   * convert utf8 to iso and html entities
   * _________________________________________________________________
   */
  protected function Utf8ToIsoHtml($str)
  {
    $str = $this->Utf8ToIso($str);
    $str = htmlentities($str, ENT_SUBSTITUTE | ENT_HTML401, 'ISO-8859-1', false);

    return $str;
  }

  /**
   * sanitize URLs
   * ________________________________________________________________
   */
  protected function sanitizeURL($url)
  {
    $path = parse_url($url, PHP_URL_PATH);
    $encoded_path = array_map('urlencode', explode('/', $path));
    $url = str_replace($path, implode('/', $encoded_path), $url);

    return $url;
  }

  /**
   * strip tags but replace with space
   * ________________________________________________________________
   */
  function stripTags($string, $allowable_tags = null)
  {
    $string = str_replace('<', ' <', $string);
    $string = strip_tags($string, $allowable_tags);
    $string = preg_replace('/\s+/', ' ', $string); // replace multiple spaces with just one
    $string = trim($string);

    return $string;
  }

}

?>
