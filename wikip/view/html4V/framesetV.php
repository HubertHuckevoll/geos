<?php

namespace html4V;

class framesetV extends \view
{

  /**
   * frameset
   * _____________________________________________________________________
   */
  public function drawPage($e = null)
  {
    $erg .= '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">'.
            '<html>'.
              '<head>'.
                '<title>'.$this->getData('appName').'</title>'.
                '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">'.
              '</head>'.
              '<frameset cols="20%,50%,30%" frameborder="0" framespacing="0">'.
                '<frame name="left"   scrolling="auto" src="'.$this->href(array('hook' => 'index')).'">'.
                '<frame name="middle" scrolling="auto">'.
                '<frame name="right"  scrolling="auto">'.
              '</frameset>'.
              '<noframes>'.
                '<p>Your browser does not support frames. Use the non-frame version of this page.</p>'.
              '</noframes>'.
            '</html>';

    echo $erg;
  }
  
}

?>
