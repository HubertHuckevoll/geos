<?php

class logger
{
  const HFILE = 'logger.html';
  const TFILE = 'logger.txt';

  /**
   * debug variable in html file
   * ________________________________________________________________
   */
  public static function vh(...$variables)
  {
    self::startSession();

    $caller_info = debug_backtrace();
    $file = $caller_info[0]['file'];
    $line = $caller_info[0]['line'];
    $func = $caller_info[1]['function'];
    $srcFile = file($file);
    $debugLine = $srcFile[$line - 1];
    $curFunc = __FUNCTION__;

    $vars = self::assignVars($curFunc, $debugLine, $variables);

    self::drawVarHTML($file, $line, $func, $vars);
  }

  /**
   * debug variable in text file
   * ________________________________________________________________
   */
  public static function vt(...$variables)
  {
    self::startSession();

    $caller_info = debug_backtrace();
    $file = $caller_info[0]['file'];
    $line = $caller_info[0]['line'];
    $func = $caller_info[1]['function'];
    $srcFile = file($file);
    $debugLine = $srcFile[$line - 1];
    $curFunc = __FUNCTION__;

    $vars = self::assignVars($curFunc, $debugLine, $variables);

    self::drawVarText($file, $line, $func, $vars);
  }

  /**
   * log calling stack HTML
   * ________________________________________________________________
   */
  public static function sh()
  {
    self::startSession();

    $callerInfo = debug_backtrace();
    array_splice($callerInfo, 0, 1);

    self::drawStackHTML($callerInfo);
  }

  /**
   * log calling stack TEXT
   * ________________________________________________________________
   */
  public static function st()
  {
    self::startSession();

    $callerInfo = debug_backtrace();
    array_splice($callerInfo, 0, 1);

    self::drawStackText($callerInfo);
  }

  /**
   * VIEW functions
   */

  /**
   * log HTML
   * ________________________________________________________________
   */
  protected static function drawVarHTML($file, $line, $func, $vars)
  {
    $out = '';

    $out .= '<div style="border: 1px solid #DDD; padding: 5px; margin-bottom: 5px;">';
    foreach($vars as $var => $val)
    {
      $varInfo = print_r($val, true);
      $out .= '<p>';
      $out .= '<strong style="text-decoration: underline;">'.$var.'</strong> in '.basename($file).':'.$line.', "'.$func.'"';
      $out .= '</p>';
      $out .= '<pre>"'.$varInfo.'"</pre>';
    }
    $out .= '</div>';

    self::writeHFile($out);
  }

  /**
   * log text file
   * ________________________________________________________________
   */
  protected static function drawVarText($file, $line, $func, $vars)
  {
    $out = '';
    $char = '~';

    foreach ($vars as $var => $val)
    {
      $varInfo = print_r($val, true);
      $introLine = '[['.$var.']] ('.basename($file).':'.$line.', "'.$func.'")';

      $out = "\r\n".
             str_pad($char, strlen($introLine), $char)."\r\n".
             $introLine."\r\n".
             str_pad($char, strlen($introLine), $char)."\r\n".
             $varInfo."\r\n\r\n";
    }

    self::writeTFile($out);
  }

  /**
   * draw stack HTML
   * __________________________________________________________________
   */
  public static function drawStackHTML($callerInfo)
  {
    $i = 0;
    $last = count($callerInfo) - 1;

    $out .= '<div><strong>Calling Stack</strong></div>'.
            '<div style="border: 1px solid #DDD; padding: 5px; margin-bottom: 5px;">';

    foreach ($callerInfo as $step)
    {
      $out .= $step['class'].':<strong>'.$step['function'].'()</strong>';
      if ($i != $last)
      {
        $out .= ' &laquo; ';
      }
      $i++;
    }
    $out .= '</div>';

    self::writeHFile($out);
  }

  /**
   * draw stack Text
   * __________________________________________________________________
   */
  public static function drawStackText($callerInfo)
  {
    $varInfo = '';

    $i = 0;
    $last = count($callerInfo) - 1;

    foreach ($callerInfo as $step)
    {
      $varInfo .= $step['class'].':*** '.$step['function'].'() ***';
      if ($i != $last)
      {
        $varInfo .= ' << ';
      }
      $i++;
    }

    self::writeTFile($varInfo);
  }


  /**
   * "Model" and helper functions
   * /

  /**
   * start debugging sessions
   * ________________________________________________________________
   */
  public static function startSession()
  {
    if (!defined('DEBUG_FLAG'))
    {
      @unlink(self::HFILE);
      @unlink(self::TFILE);
      define('DEBUG_FLAG', true);
    }
  }

  /**
   * assign variables
   * ________________________________________________________________
   */
  protected static function assignVars($curFunc, $debugLine, $variables)
  {
    preg_match('/.*'.$curFunc.'\((.*)\).*/', $debugLine, $varNames);

    if (isset($varNames[1]))
    {
      $varNames = explode(', ', $varNames[1]);
    }

    $vars = array_combine($varNames, $variables);

    return $vars;
  }

  /**
   * write HTML file
   * ________________________________________________________________
   */
  public static function writeHFile($varInfo)
  {
    $body = '';

    $fc = @file_get_contents(self::HFILE);
    if ($fc != '')
    {
      $matches = array();
      preg_match('/<body>(.*)<\/body>/s', $fc, $matches);
      $body = $matches[1];
    }

    $body = '<html><head><title>Logger</title></head><body>'.
            $varInfo.
            $body.
            '</body></html>';

    file_put_contents(self::HFILE, $body);
  }

  /**
   * log text file
   * ________________________________________________________________
   */
  protected static function writeTFile($var)
  {
    $body = @file_get_contents(self::TFILE);

    file_put_contents(self::TFILE, $var.$body);
  }

}

?>