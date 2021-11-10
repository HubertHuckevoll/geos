<?php

namespace html4V;

class indexV extends html4V
{

  /**
   * Preview
   * _________________________________________________________________
   */
  public function renderMainContent()
  {
    $term = $this->getData('term');
    $results = (array) $this->getData('results');
    $locs = $this->getData('locs');
    $erg = '';
    
    $erg .= '<h3>'.$this->getData('appName').'</h3>';
    
    $erg .= '<table>';

    $erg .= '<tr><td valign="middle" width="30%">'.
              '<font face="'.$this->getData('font').'" size="0">'.
                '<a href="index.php" target="_top">(Click here for the HTML2 version)</a>'.
              '</font>'.
            '</td></tr>';
            
    $erg .= '<tr><td>&nbsp;</td></tr>'; // "Leerzeile"

    $erg .= '<tr><td>';
    $erg .= '<font face="'.$this->getData('font').'">'.
              '<form action="index.php" method="get" target="left">'.
                '<input type="hidden" name="hook" value="index">'.
                '<input type="hidden" name="ui" value="html4V">';
    $erg .= '<select name="loc">';
    foreach($locs as $locK => $locV)
    {
      $sel = ($locK == $this->stateParams['loc']) ? ' selected=selected' : '';
      $erg .= '<option value="'.$locK.'"'.$sel.'>'.$locV.'</option>';
    }
    $erg .= '</select>';
    $erg .= '<br>';

    $erg .= '<input type="text" name="term" size="20" value="'.$term.'">';
    $erg .= '<br>';
    $erg .= '<input type="submit">';
    $erg .= '</form>'.
            '</font>'.
            '</td>'.
            '</tr>';

    $erg .= '<tr><td>&nbsp;</td></tr>'; // "Leerzeile"

    if (count($results) > 0)
    {

      $erg .= '<tr><td>';
      $erg .= '<font face="'.$this->getData('font').'">';
      
      foreach ($results as $result)
      {
        $erg .= '<p>';
        $erg .= $this->link(array('hook' => 'fulltext', 'title' => $result['title']), $result['title'], array('target' => 'middle'));
        $erg .= '<br>';
        $erg .= '...'.$result['snippet'].'...';
        $erg .= '</p>';
      }
      
      $erg .= '</font>';
      $erg .= '</td></tr>';
    }
    
    $erg .= '</table>';

    return $erg;
  }
  
}

?>
