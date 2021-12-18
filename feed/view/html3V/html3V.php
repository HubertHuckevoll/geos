<?php

namespace html3V;

class html3V extends \view
{
  /**
   * draw page
   * _____________________________________________________________________
   */
  public function drawPage($e = null)
  {
    $erg .= '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 3.2//EN">';

    $erg .= '<html>';
    $erg .= '<head>';
    $erg .= '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">'; //this is also set in the header, see view.php
    $erg .= '<title>'.$this->getData('appName').'/'.$this->getData('tsvName').' - '.$this->getData('headline').'</title>';
    $erg .= $this->debugVars();
    $erg .= '</head>';

    if ($this->getData('uim') == 'l')
    { // light mode
      $erg .= '<body text="#000000" bgcolor="#FFFFFF" link="#0000FF" vlink="#0000FF">';
    }
    else
    { // dark mode
      $erg .= '<body text="#FFFFFF" bgcolor="#000000" link="#006699" vlink="#006699">';
    }

    $erg .= '<table border="0" width="100%" cellpadding="0">'.
            '<tr>'.
              '<td></td>'.
              '<td width="600">'.
                '<font face="'.$this->getData('font').'">'.
                '<h1>'.$this->getData('appName').$this->link(array('hook' => 'setup'), '.').'</h1>';

                if ($e === null)
                {
                  $erg .= $this->renderMainContent();
                }
                else
                {
                  $erg .= '<h3>Fehler:</h3>';
                  $erg .= '<p>'.$e->getMessage().'</p>';
                }

    $erg .=     '</font>';
    $erg .=   '</td>'.
            '<td></td>'.
            '</tr>'.
            '</table';

    $erg .= '</body>';
    $erg .= '</html>';

	  header('Content-Type: text/html; charset=iso-8859-1');
    echo $erg;
  }

}

?>
