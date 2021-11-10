<?php

namespace html5V;

class html5V extends \view
{
  /**
   * draw page
   * _____________________________________________________________________
   */
  public function drawPage($e = null)
  {
    $erg .= '<!DOCTYPE html>';
    $erg .= '<html>';
    $erg .= '<head>';
    $erg .= '<title>'.$this->getData('appName').'/'.$this->getData('gsheetName').' - '.$this->getData('headline').'</title>';
    $erg .= '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">'; //this is also set in the header
    $erg .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
    $erg .= '<link rel="stylesheet" href="https://fonts.xz.style/serve/inter.css">';
    $erg .= '<link rel="stylesheet" href="https://newcss.net/new.min.css">';

    // add this for dark mode
    // $erg .= '<link rel="stylesheet" href="https://newcss.net/theme/night.css">';

    $erg .= $this->debugVars();
    $erg .= '</head>';

    $erg .= '<body>';

    if ($e === null)
    {
      $erg .= '<header>';
      $erg .= '<h1>'.$this->getData('appName').$this->link(array('hook' => 'setup'), '.').'</h1>';
      $erg .= '<p>'.$this->renderBreadCrumbs($this->getData('articles')).'</p>';
      $erg .= '</header>';

      $erg .= $this->renderMainContent();
    }
    else
    {
      $erg .= '<header>';
      $erg .= '<h1>'.$this->getData('appName').$this->link(array('hook' => 'setup'), '.').'</h1>';
      $erg .= '</header>';

      $erg .= '<h3>Fehler:</h3>';
      $erg .= '<p>'.$e->getMessage().'</p>';
    }

    $erg .= '</body>';
    $erg .= '</html>';

    header('Content-Type: text/html; charset=iso-8859-1');
    echo $erg;
  }

}

?>
