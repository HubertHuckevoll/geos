<?php

/**
 * Auto loader
 * ________________________________________________________________
 */
spl_autoload_register(function(string $className)
{
  $fname = null;
  $ct = substr($className, -1);

  switch($ct)
  {
    case 'V':
      $fname = $_SERVER["DOCUMENT_ROOT"].'/geos/wikip/view/'.$className.'.php';
    break;

    case 'M':
      $fname = $_SERVER["DOCUMENT_ROOT"].'/geos/wikip/model/'.$className.'.php';

      if (!file_exists($fname))
      {
        $fname = $_SERVER["DOCUMENT_ROOT"].'/geos/lib/'.$className.'.php';
      }
    break;
  }

  if (file_exists($fname))
  {
    require_once($fname);
  }
  else
  {
    die('Couldn\'t autoload class "'.$className.'" from "'.$fname.'"');
  }

});

?>
