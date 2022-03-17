<?php

class loginV extends \view
{
  /**
   * draw page
   * _____________________________________________________________________
   */
  public function drawPage(string $viewFunc = '') : void
  {
    $h2sel = ($this->stateParams['ui'] == 'html2V') ? ' selected' : '';
    $h3sel = ($this->stateParams['ui'] == 'html3V') ? ' selected' : '';
    $h4sel = ($this->stateParams['ui'] == 'html4V') ? ' selected' : '';
    $h5sel = ($this->stateParams['ui'] == 'html5V') ? ' selected' : '';

    $uimL = ($this->stateParams['uim'] == 'l') ? ' selected' : '';
    $uimD = ($this->stateParams['uim'] == 'd') ? ' selected' : '';

    $imgUse0 = ($this->stateParams['iU'] == '0') ? ' selected' : '';
    $imgUse1 = ($this->stateParams['iU'] == '1') ? ' selected' : '';
    $imgUse2 = ($this->stateParams['iU'] == '2') ? ' selected' : '';
    $imgUse3 = ($this->stateParams['iU'] == '3') ? ' selected' : '';

    $erg .= '<!DOCTYPE html PUBLIC "-//IETF//DTD HTML 2.0//EN">'.
            '<html>'.
            '<head>'.
              '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">'. //this must also be set in the header!                '<title>'.$this->getData('appName').'/setup</title>'.
            '</head>'.
            '<body bgcolor="#FFFFFF" link="#0000FF" vlink="#0000FF">'.
            '<h1>'.'&nbsp;'.$this->getData('appName').'</h1>';

    $erg .= '<form action="index.php" method="get">'.
              '<hr>'.
                '<input type="hidden" name="hook" value="index">'.
                '&nbsp;'.
                'TSV-file URL'.'&nbsp;-&nbsp;'.
                '<input type="text" name="tsv" value="'.$this->stateParams['tsv'].'">'.

                '<br><br>'.
                '&nbsp;'.
                'UI'.'&nbsp;-&nbsp;'.
                '<select name="ui">'.
                  '<option value="html2V"'.$h2sel.'>HTML 2 (very basic)</option>'.
                  '<option value="html3V"'.$h3sel.'>HTML 3 (fonts, colors, tables)</option>'.
                  '<option value="html4V"'.$h4sel.'>HTML 4 (frames, fonts, colors, tables)</option>'.
                  '<option value="html5V"'.$h5sel.'>HTML 5 (modern tags, CSS - using NEW.CSS)</option>'.
                '</select>'.

                '<br><br>'.
                '&nbsp;'.
                'UI Mode (only for HTML 3 and above)'.'&nbsp;-&nbsp;'.
                '<select name="uim">'.
                  '<option value="l"'.$uimL.'>Light Mode</option>'.
                  '<option value="d"'.$uimD.'>Dark Mode</option>'.
                '</select>'.

                '<br><br>'.
                '&nbsp;'.
                'Image Use'.'&nbsp;-&nbsp;'.
                '<select name="iU">'.
                  '<option value="0"'.$imgUse0.'>None</option>'.
                  '<option value="1"'.$imgUse1.'>Light</option>'.
                  '<option value="2"'.$imgUse2.'>Medium</option>'.
                  '<option value="3"'.$imgUse3.'>Heavy</option>'.
                '</select>'.

              '<br><br>'.
              '&nbsp;'.
              '<input type="submit" value="Go">'.
            '</form>';
    $erg .= '<hr>';
    $erg .= '&nbsp;';
    $erg .= '<a href="about.html">?</a>';
    $erg .= '</body>';
    $erg .= '</html>';

    header('Content-Type: text/html; charset=iso-8859-1');
    echo $erg;
  }

}

?>
