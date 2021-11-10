<?php

namespace html5V;

class feedsForCatV extends html5V
{
  
  /**
   * render bread crumbs
   * _________________________________________________________________
   */
  public function renderBreadCrumbs($feed)
  {
    $erg = '';
    $erg .= $this->link(array('hook' => 'index'), 'Categories');
    $erg .= '&nbsp;&gt;&nbsp;';
    $erg .= '<b>'.$this->getData('tableName').'</b>';

    return $erg;
  }

  /**
   * Services
   * _________________________________________________________________
   */
  public function renderMainContent()
  {
    $tableIdx = $this->getData('tableIdx');
    $feeds = $this->getData('services');

    $erg .= '<ul>';
    for ($i = 0; $i < count($feeds); $i++)
    {
      $feed = $feeds[$i];
      $erg .= '<li>'.$this->link(array('hook' => 'articlesForFeed',
                                       'tableIdx' => $tableIdx,
                                       'feedIdx' => $i),
                                 $feed['service']).'</li>';
    }
    $erg .= '</ul>';
    
    return $erg;
  }
  
}

?>
