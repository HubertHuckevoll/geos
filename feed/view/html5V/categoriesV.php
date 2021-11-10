<?php

namespace html5V;

class categoriesV extends html5V
{
  
  /**
   * render bread crumbs
   * ________________________________________________________________
   */
  public function renderBreadCrumbs($feed)
  {
    return '';
  }
  
  /**
   * Categories
   * _________________________________________________________________
   */
  public function renderMainContent()
  {
    $tableName = $this->data['categories']['tableName'];
    $feedTable = $this->data['categories']['sheets'];
    $erg = '';

    $erg .= '<ul>';
    for ($i = 0; $i < count($feedTable); $i++)
    {
      $table = $feedTable[$i];
      $erg .= '<li>'.$this->link(array('hook' => 'feedsForCat',
                                       'tableIdx' => $i),
                                 $table['name']).'</li>';
    }
    $erg .= '</ul>';
    
    return $erg;
  }

}

?>
