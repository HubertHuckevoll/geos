<?php

namespace html4V;

class html4V extends \view
{

  /**
   * draw page
   * _____________________________________________________________________
   */
  public function drawPage($e = null)
  {
    $erg = '';
    $erg .= '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
    $erg .= '<html>';
    $erg .= '<head>';
    $erg .= '<title>'.$this->getData('appName').'</title>';
    $erg .= $this->debugVars();
    $erg .= '</head>';
    
    if ($e === null)
    {
      $erg .= $this->renderMainContent();
    }
    else
    {
      $erg .= '<h3>Fehler:</h3>';
      $erg .= '<p>'.$e->getMessage().'</p>';
    }
    
    $erg .= '</html>';
    
    header('Content-Type: text/html; charset=iso-8859-1');
    echo $erg;
  }
  
}

?>
