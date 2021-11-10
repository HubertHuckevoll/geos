<?php

require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/resize.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/logger.php');

$imgF = getReqVar('file');
$width = getReqVar('width');
$height = getReqVar('height');

$r = new resize();
$img = $r->smartResizeImage($imgF, $width, $height);

if ($img != false)
{
  header('Content-Type: image/gif');
  imagegif($img);
}
else
{
  header('Content-Type: image/gif');
  echo base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');
}

?>