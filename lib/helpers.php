<?php

define('NUMERIC', 0);
define('ALPHA', 1);
define('ALPHANUMERIC', 2);

/**
 * getReqVar
 * _________________________________________________________________
 */
function getReqVar($varName, $stripTags = true)
{
  $req = $_GET + $_POST;
  if (isset($req[$varName]))
  {
    $var = $req[$varName];
    $var = trim($var);
    $var = preg_replace("/^(content-type:|bcc:|cc:|to:|from:)/im", "", $var);
    if ($stripTags == true) {
      $var = strip_tags($var);
    }
    if (get_magic_quotes_gpc() == true) {
      $var = stripslashes($var);
    }
    return $var;
  }

  return false;
}

/**
 * Check a variable for a certain pattern
 * _________________________________________________________________
 */
function checkVar($var, $pregMode)
{
  switch($pregMode)
  {
    case 'nick':
      $pattern = '/^[\w\d\.\-\_]{2,255}$/';
    break;

    case 'password':
      $pattern = '/^[\w\d\.\-\_]{5,255}$/';
    break;

    case 'plz':
      $pattern = '/^[\d]{5}$/';
    break;

    case 'digits':
      $pattern = '/^[\d]+$/';
    break;

    case 'email':
      $pattern = '/^[\w.+-]{2,}\@[\w.-]{2,}\.{0,1}[a-z]{2,6}$/';
    break;

    case 'url':
      $pattern = '#(^|[^\"=]{1})(http://|ftp://|mailto:|news:)([^\s<>]+)([\s\n<>]|$)#sm';
    break;
  }

  if (preg_match($pattern, $var)) {
    return true;
  }

  return false;
}

/**
 * Debug a variable by putting its contents
 * on top of an html file
 * ________________________________________________________________
 */
function dv($var, $callers = false)
{
  $fname = 'debug.html';

  if (!defined('DEBUG_FLAG')) {
    @unlink($fname);
    define('DEBUG_FLAG', true);
  }

  $caller_info = debug_backtrace();
  $file = $caller_info[0]['file'];
  $line = $caller_info[0]['line'];
  $func = $caller_info[1]['function'];

  $srcFile = file($file);
  $debugLine = $srcFile[$line - 1];

  echo $debugLine;

  preg_match('/.*'.__FUNCTION__.'\((.*)\).*/', $debugLine, $treffer);
  $varMeta = '<strong>'.$treffer[1].'</strong> (in <strong>'.basename($file).'</strong> on line <strong>'.$line.'</strong> in function <strong>'.$func.'</strong>) is';

  $varInfo = print_r($var, true);

  if ($callers == true)
  {
    $varInfo .= getCallingStack();
  }

  $fc = @file_get_contents($fname);
  if ($fc != '') {
    $matches = array();
    preg_match('/<body>(.*)<\/body>/s', $fc, $matches);
    $body = $matches[1];
  }

  $body = '<html><head><title>Debug: '.basename($file).'</title></head><body>'
         .'<div>
            <p>'.$varMeta.'</p>
            <div style="border: 1px solid #DDD; padding: 5px;"><pre>'.$varInfo.'</pre></div>'
         .'</div>'
         .$body
         .'</body></html>';

  file_put_contents($fname, $body);
}

/**
 * Debug a request
 * ________________________________________________________________
 */
function debugRequest($tag = '')
{
  $sep = "---------------------------------------------------------------------\r\n\r\n";

  $lastErr = "LAST ERROR: \r\n".$php_errormsg.$sep;
  $get = "GET VARIABLES: \r\n".print_r($_GET, true).$sep;
  $post = "POST VARIABLES: \r\n".print_r($_POST, true).$sep;
  $files = "FILES: \r\n".print_r($_FILES, true).$sep;
  $server = "SERVER: \r\n".print_r($_SERVER, true).$sep;
  $env = "ENVIRONMENT: \r\n".print_r($_ENV, true).$sep;
  $session = "SESSION: \r\n".print_r($_SESSION, true).$sep;
  $cookie = "COOKIE: \r\n".print_r($_COOKIE, true);

  $data = $lastErr.$get.$post.$files.$server.$env.$session.$cookie;

  $fname = 'request_'.$tag.'_['.$_SERVER['REMOTE_ADDR'].']_'.time().'.txt';

  file_put_contents($fname, $data);
}

/**
 * Get calling stack
 * _________________________________________________________________
 */
function getCallingStack()
{
  $callerInfo = debug_backtrace();
  array_splice($callerInfo, 0, 1);
  $i = 0;
  $last = count($callerInfo) - 1;
  $varInfo = '<ul><li>';
  foreach ($callerInfo as $step) {
    $varInfo .= $step['class'].':<strong>'.$step['function'].'()</strong>';
    if ($i != $last) {
      $varInfo .= ' &laquo; ';
    }
    $i++;
  }
  $varInfo .= '</li></ul>';

  return $varInfo;
}

/**
 * Benchmarking
 * __________________________________________________________________
 */
function benchmark()
{
	static $start = NULL;
	if (is_null($start)) {
		$start = getMicrotime();
	} else {
		$benchmark = getMicrotime() - $start;
		$start = getMicrotime();
		return $benchmark;
	}
}

/**
 * get Microtime
 * ________________________________________________________________
 */
function getMicrotime()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

/**
 * Aus String UNIX-gerechten Datei/Verzeichnisnamen erzeugen
 * Achtung: auch als CSS Selektor gedacht, daher sind
 * nur Binde- und Unterstriche, Zahlen und Buchstaben erlaubt
 * __________________________________________________________
 */
function makeFileName($str)
{
  $str = trim($str);
  $str = str_replace(array('ä','ö','ü','ß','Ä','Ö','Ü'), array('ae','oe','ue','ss','Ae','Oe','Ue'), $str);
  $str = preg_replace('/[^a-zA-Z0-9\-\_]/', '-', $str);
  return $str;
}

/**
 * Endung einer Datei ermitteln
 * __________________________________________________________________
 */
function getFileExt($fname)
{
  return strtolower(pathinfo($fname, PATHINFO_EXTENSION));
}

/**
 * Versionsnummer auslesen
 * ____________________________________________________________________
 */
function getVer()
{
  $fnames = glob("*.ver");
  return basename($fnames[0], '.ver');
}

/**
 * Versuche, Mobilbrowser zu erkennen - discouraged, use
 * CSS instead
 * ____________________________________________________________________
 */
function isMobile()
{
  if (defined('FORCE_MOBILE') && (FORCE_MOBILE == true)) {
    return true;
  }

  if (defined('FORCE_DESKTOP') && (FORCE_DESKTOP == true)) {
    return false;
  }

  $agents = array(
    'android','webos','iphone','ipad','ipod','blackberry','iemobile','opera mini'
  );

  // Prüfen der Browserkennung
  foreach ($agents as $agent) {
    if (isset($_SERVER["HTTP_USER_AGENT"])
        && strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), strtolower($agent)) !== false)
    {
      return true;
    }
  }
  return false;
}

/**
 * is ajax?
 * ____________________________________________________________________
 */
function isAjax()
{
  return (getReqVar('ajax') === false) ? false : true;
}

/**
 * create random password
 * ____________________________________________________________________
 */
function randomStr($length = 5, $type = ALPHANUMERIC)
{
  switch ($type) {
    case ALPHA:        $chars = "abcdefghijkmnpqrstuvwxyz"; break;
    case NUMERIC:      $chars = "123456789"; break;
    case ALPHANUMERIC: $chars = "abcdefghijkmnpqrstuvwxyz123456789"; break;
  }

  list($usec, $sec) = explode(' ', microtime());
  $seed = (float) $sec + ((float) $usec * 100000);
  mt_srand($seed);
  $i = 0;
  $pass = '';

  while ($i < $length)
  {
    $num = mt_rand() % 33;
    $tmp = substr($chars, $num, 1);
    $pass = $pass.$tmp;
    $i++;
  }

  return $pass;
}

/**
 * create htpasswd compatible password
 * ____________________________________________________________________
 */
function shaPwd($pass = '')
{
  return ($pass != '') ? "{SHA}".base64_encode(pack("H*", sha1($pass))) : '';
}

/**
 * ellipsis
 * ____________________________________________________________________
 */
function ellipsis($str, $length)
{
  if (strlen($str) > $length) {
    $separator = '...';
    $separatorlength = strlen($separator) ;
    $maxlength = $length - $separatorlength;
    $start = $maxlength / 2;
    $trunc = strlen($str) - $maxlength;
    return substr_replace($str, $separator, $start, $trunc);
  } else {
    return $str;
  }
}

/**
 * wrapper for get_object_vars to use INSIDE of classes
 * ______________________________________________________________________
 */
function getPublicObjectVars($obj)
{
  return get_object_vars($obj);
}

/**
 * get Object fields as array
 * _________________________________________________________________
 */
function getObjProps($obj, $details = '')
{
  $props = get_object_vars($obj);

  if ($details != '')
  {
    $details = explode(';', $details);

    if (is_array($details))
    {
      foreach ($details as $detail) {
        foreach ($props as $propKey => $prop) {
          if ($propKey == $detail) {
            $nProps[$propKey] = $prop;
            break;
          }
        }
      }
    }
    else
    {
      $nProps = $props;
    }
  }
  else
  {
    $nProps = $props;
  }

  return $nProps;
}

/**
 * returns full path for fragment as URL
 * _________________________________________________________________
 */
function getPathURL($relPath)
{
  $protocol = ($_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
  $slash = mb_substr($relPath, 0, 1) == '/' ? '' : '/';
  return $protocol.$_SERVER['HTTP_HOST'].$slash.$relPath;
}

/**
 * returns full path for fragment as FS path
 * _________________________________________________________________
 */
function getPathFS($relPath)
{
  $slash = mb_substr($relPath, 0, 1) == '/' ? '' : '/';
  return $_SERVER['DOCUMENT_ROOT'].$slash.$relPath;
}

/**
 * project root as relative path
 * _________________________________________________________________
 */
function getProjectRoot()
{
  return rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
}

/**
 * project root as URL
 * _________________________________________________________________
 */
function getProjectRootURL()
{
  $protocol = ($_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
  return $protocol.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
}

/**
 * Send mail
 * _________________________________________________________________
 */
function sendEmail($from, $to, $subject, $msg)
{
  if (substr(PHP_OS, 0, 3) == 'WIN') { $crlf = "\r\n"; } else { $crlf = "\n"; }

  $mailheaders  = 'From: '.$from.$crlf;
  $mailheaders .= 'Content-type: text/plain; charset=iso-8859-1'.$crlf;
  $mailheaders .= 'MIME-Version: 1.0'.$crlf;
  $mailheaders .= 'Content-Transfer-Encoding: 8bit'.$crlf;
  $mailheaders .= 'Reply-To: '.$from.$crlf;
  $mailheaders .= 'Return-Path: '.$from.$crlf.$crlf;

  if (@mail($to, $subject, $msg, $mailheaders)) {
    return true;
  }
  return false;
}

/**
 * returns an obfuscated email adress
 * _________________________________________________________________
 */
function obfuscateStr($originalString)
{
  $encodedString = "";
  $nowCodeString = "";
  $randomNumber = -1;
  $isUTF8 = false;

	if (mb_check_encoding($originalString, 'UTF-8')) {
		$originalString = utf8_decode($originalString);
	}

  $originalLength = strlen($originalString);

  for ($i = 0; $i < $originalLength; $i++)
  {
    $encodeMode = rand(1, 2);
    switch ($encodeMode)
    {
      case 1: //Decimal code
      {
        $nowCodeString = "&#".ord($originalString[$i]).";";
        break;
      }
      case 2: //Hexadecimal code
      {
        $nowCodeString = "&#x".dechex(ord($originalString[$i])).";";
        break;
      }
      default:
      {
        return 'Error';
      }
    }
    $encodedString .= $nowCodeString;
  }

	if ($isUTF8) {
		$encodedString = utf8_encode($encodedString);
	}

  return $encodedString;
}

/**
 * returns an obfuscated email link
 * ____________________________________________________________________
 */
function encodeEmail($originalString)
{
  return obfuscateStr($originalString);
}

/**
 * returns an obfuscated email link
 * ____________________________________________________________________
 */
function emailLink($originalString)
{
  $encodedString = encodeEmail($originalString);
  return '<a href="mailto:'.$encodedString.'">'.$encodedString.'</a>';
}

/**
 * Umleitung
 * ____________________________________________________________________
 */
function redirect($fname = '')
{
  header('Location: '.getProjectRootURL().'/'.$fname);
}

/**
 * Request via curl, good for proxies
 * ____________________________________________________________________
 */
function request($url)
{
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($ch, CURLOPT_COOKIESESSION, true );
  curl_setopt($ch, CURLOPT_COOKIEJAR, './cookie.txt');
  curl_setopt($ch, CURLOPT_COOKIEFILE, './cookie.txt');
  curl_setopt($ch, CURLOPT_HTTPGET, true);

  curl_setopt($ch, CURLOPT_USERAGENT, 'mkGrab/0.99 (http://www.meyerk.com/)');
  $result = curl_exec($ch);
  curl_close($ch);

  if ($result === false)
  {
    throw new Exception('cURL Error: "'.trim(strip_tags(curl_error($ch))).'". URL was: "'.$url.'".');
  }
  else
  {
    $result = trim($result);
    return $result;
  }
}

/**
 * get URL parameters and EP from an URL string
 * ____________________________________________________________________
 */
function getLocationParams($url)
{
  // Get target file
  $urlArr = parse_url($url);
  $pInfo = pathinfo($urlArr['path']);
  $file = $pInfo['filename'];

  // Get future GET params = query string
  $query = str_replace('&amp;', '&', $urlArr['query']);
  $q = array();
  parse_str($query, $q);

	$q['ep'] = $file;
	return $q;
}

/**
 * getRandomElement
 * ______________________________________________________________________
 */
function getRandomElementIdx($arr)
{
  $x = false;
  $max = count($arr)-1;
  if ($max > 0) {
    $x = mt_rand(0, $max);
  }
	return $x;
}

?>
