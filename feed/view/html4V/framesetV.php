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
                '<title>'.$this->getData('appName').'/'.$this->getData('tsvName').'</title>'.
                '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">'.
              '</head>'.
              '<frameset rows="11%,*" frameborder="0" border="0" framespacing="0">'.
                '<frame name="top" src="'.$this->href(['hook' => 'categories']).'" scrolling="no">'.
                '<frameset cols="*,600,*" frameborder="0" border="0" framespacing="0">'.
                  '<frame name="left"   scrolling="auto" src="'.$this->href(['hook' => 'feedsForCat', 'tableIdx' => 0]).'">'.
                  '<frame name="right"  scrolling="auto">'.
                  '<frame name="middle" scrolling="auto">'.
                '</frameset>'.
                '<noframes>'.
                  '<p>Your browser does not support frames. Use the non-frame version of this page.</p>'.
                '</noframes>'.
              '</frameset>'.
            '</html>';

    echo $erg;
  }

}

?>
