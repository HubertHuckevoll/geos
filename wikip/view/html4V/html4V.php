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
    $erg .= '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">';
    $erg .= '<title>'.$this->getData('appName').'</title>';
    $erg .= '</head>';
    
    if ($e === null)
    {
      $erg .= '<body bgcolor="#FFFFFF" link="#0000FF" vlink="#0000FF">';
      $erg .= '<font face="'.$this->getData('font').'">';
      $erg .= $this->renderMainContent();
      $erg .= '</font>';
      $erg .= '</body>';
    }
    else
    {
      $erg .= '<body bgcolor="#FFFFFF" link="#0000FF" vlink="#0000FF">';
      $erg .= '<font face="'.$this->getData('font').'">';
      $erg .= '<h3>Error:</h3>';
      $erg .= '<p>';
      $erg .= $e->getMessage();
      $erg .= '</font>';
      $erg .= '</p>';
      $erg .= '</body>';
    }
    
    $erg .= '</html>';
    
    header('Content-Type: text/html; charset=iso-8859-1');
    echo $erg;
  }
  
}

?>
