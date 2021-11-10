<?php

namespace html4V;

class categoriesV extends html4V
{
  /**
   * Draw top frame
   * _________________________________________________________________
   */
  public function renderMainContent()
  {
    $tableName = $this->data['categories']['tableName'];
    $feedTable = $this->data['categories']['sheets'];
    $erg = '';

    $erg .= '<body bgcolor="#000080" text="#FFFFFF" link="#FFFFFF" vlink="FFFFFF">';
    $erg .= '<table border="0" width="100%" cellpadding="1">'.
            '<tr>'.
              '<td>'.
                '<font face="'.$this->getData('font').'">'.
                  '<h1>'.$this->getData('appName').$this->link(array('hook' => 'setup'), '.', array('target' => '_top')).'</h1>'.
                '</font>'.
              '</td>'.
              '<td>'.
              '<font face="'.$this->getData('font').'">';
              
    for ($i = 0; $i < count($feedTable); $i++)
    {
      $table = $feedTable[$i];
      $erg .= $this->link(array('hook' => 'feedsForCat',
                                'tableIdx' => $i),
                          $table['name'],
                          array('target' => 'left'));
      
      if ($i != (count($feedTable) - 1))
      {
        $erg .= ' | ';
      }
    }

    $erg .= '</font>'.
            '</td>'.
            '</tr>'.
            '</table>'.
            '</body>';

    return $erg;
  }
  
}

?>
