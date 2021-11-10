<?php

namespace html2V;

class html2V extends \view
{
  /**
   * draw page
   * _____________________________________________________________________
   */
  public function drawPage($e = null)
  {
    $erg .= '<!DOCTYPE html PUBLIC "-//IETF//DTD HTML 2.0//EN">';
    $erg .= '<html>';
    $erg .= '<head>';
    $erg .= '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">';
    $erg .= '<title>'.$this->getData('appName').'</title>';
    $erg .= '</head>';

    $erg .= '<body>';
    
    $erg .= '<h1>'.$this->getData('appName').'</h1>';

    if ($e === null)
    {
      $erg .= '<h3>'.$this->getData('headline').'</h3>';
      $erg .= $this->renderMainContent();
    }
    else
    {
      $erg .= '<h3>Error:</h3>';
      $erg .= '<p>'.$e->getMessage().'</p>';
    }
    
    $erg .= '</body>';
    $erg .= '</html>';

    header('Content-Type: text/html; charset=iso-8859-1');
    echo $erg;
  }
  
}

?>
