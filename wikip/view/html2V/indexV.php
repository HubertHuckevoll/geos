<?php

namespace html2V;

class indexV extends html2V
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
    
    $erg .= '<p>'.
              '<a href="index.php?ui=html4V">(Click here for the HTML4 version)</a>'.
            '</p>';

    $erg .= '<hr>';

    $erg .= '<p>'.
              '<form action="index.php" method="get">'.
                '<input type="hidden" name="hook" value="index">'.
                '<input type="hidden" name="ui" value="html2V">'.
                '<input type="text" name="term" value="'.$term.'">&nbsp;'.
                '<select name="loc">';

    foreach($locs as $locK => $locV)
    {
      $sel = ($locK == $this->stateParams['loc']) ? ' selected=selected' : '';
      $erg .= '<option value="'.$locK.'"'.$sel.'>'.$locV.'</option>';
    }

    $erg .= '</select>'.
            '<br><br>'.
            '<input type="submit">'.
            '</form>'.
            '</p>';
            
    $erg .= '<hr>';

    if (count($results) > 0)
    {
      foreach ($results as $result)
      {
        $erg .= '<p>';
        $erg .= $this->link(array('hook' => 'fulltext', 'title' => $result['title']), $result['title']);
        $erg .= '<br>';
        $erg .= '...'.$result['snippet'].'...';
        $erg .= '</p>';
      }
    }

    return $erg;
  }
  
}

?>
