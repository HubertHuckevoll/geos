<?php

require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/helpers.php');

class resize
{
  function __construct()
  {
  }

  /**
   * not our code - lovingly stolen from a public github repository,
   * but adopted to our needs
   * _________________________________________________________________
   */
  public function smartResizeImage(string $file, int $width = 0, int $height = 0): mixed
  {
    $image = null;
    $image_resized = false;
    $imageStruct = $this->getImage($file);

    if ($imageStruct !== false)
    {
      // extract image
      $image = $imageStruct['image'];

      // Setting defaults and meta
      $width_old    = $imageStruct['width'];
      $height_old   = $imageStruct['height'];
      $final_width  = 0;
      $final_height = 0;

      // Calculating proportionality
      if ($width == 0)
      {
        $factor = $height/$height_old;
      }
      elseif ($height == 0)
      {
        $factor = $width/$width_old;
      }
      elseif (($width == 0) && ($height == 0))
      {
        $factor = 1; // basically: do nothing, no resize
      }
      else
      {
        $factor = min($width / $width_old, $height / $height_old);
      }

      $final_width  = round($width_old * $factor);
      $final_height = round($height_old * $factor);

      // Resizing image
      switch ($imageStruct['mime'])
      {
        case 'image/gif':
          $image_resized = imagecreatetruecolor($final_width, $final_height);

          $trnprt_indx        = 0;
          $trnprt_color       = [];
          $transparency       = imagecolortransparent($image);
          $transparent_color  = imagecolorsforindex($image, $trnprt_indx);
          $transparency       = imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
          imagefill($image_resized, 0, 0, $transparency);
          imagecolortransparent($image_resized, $transparency);
          imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $final_width, $final_height, $width_old, $height_old);
        break;

        case 'image/png':
          $image_resized = imagescale($image, $final_width, $final_height, IMG_NEAREST_NEIGHBOUR);

          imagesavealpha($image_resized, true);
          imagecolortransparent($image_resized, 127<<24);
        break;

        case 'image/jpeg':
          $image_resized = imagecreatetruecolor($final_width, $final_height);

          imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $final_width, $final_height, $width_old, $height_old);
        break;
      }

      return $image_resized;
    }

    return false;
  }

  /**
   * get image
   * ________________________________________________________________
   */
  protected function getImage(string $file): mixed
  {
    $ftype = '';
    $rawImage = '';
    $infoStruct = array();
    $result = array();

    // the request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $file);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_COOKIESESSION, true );
    curl_setopt($ch, CURLOPT_COOKIEJAR, './cookie.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, './cookie.txt');
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'mkGrab/0.99 (http://www.meyerk.com/)');

    // fetch the actual image
    $rawImage = curl_exec($ch);

    if (($rawImage != '') && (curl_error($ch) == ''))
    {
      $infoStruct = getimagesizefromstring($rawImage);
      $ftype = $infoStruct['mime'];

      if (
          ($ftype == 'image/gif') ||
          ($ftype == 'image/jpeg') ||
          ($ftype == 'image/png')
         )
      {
        $result['image'] = imagecreatefromstring($rawImage);
        $result['mime'] = $ftype;
        $result['width'] = $infoStruct[0];
        $result['height'] = $infoStruct[1];

        return $result;
      }
    }

    return false;
  }
}

?>