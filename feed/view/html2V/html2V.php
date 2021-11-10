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
    $erg .= '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">'; //this is also set in the header, see view.php
    $erg .= '<title>'.$this->getData('appName').'/'.$this->getData('gsheetName').' - '.$this->getData('headline').'</title>';
    $erg .= $this->debugVars();
    $erg .= '</head>';

    $erg .= '<body bgcolor="#FFFFFF" link="#0000FF" vlink="#0000FF">';

    $erg .= '<h1>'.$this->getData('appName').$this->link(array('hook' => 'setup'), '.').'</h1>';

    if ($e === null)
    {
      $erg .= $this->renderMainContent();
    }
    else
    {
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
