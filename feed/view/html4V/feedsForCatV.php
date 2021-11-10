<?php

namespace html4V;

class feedsForCatV extends html4V
{

  /**
   * Services
   * _________________________________________________________________
   */
  public function renderMainContent()
  {
    $tableIdx = $this->getData('tableIdx');
    $feeds = $this->getData('services');
    $erg = '';

    $erg .= '<body bgcolor="#FFFFFF" link="#0000FF" vlink="#0000FF">';
    $erg .= '<font face="'.$this->getData('font').'">';
    $erg .= '<h3>'.$this->getData('headline').'</h3>';

    for ($i = 0; $i < count($feeds); $i++)
    {
      $feed = $feeds[$i];
      $erg .= '<p>';
      $erg .= $this->link(array('hook' => 'articlesForFeed',
                                'tableIdx' => $tableIdx,
                                'feedIdx' => $i),
                          $feed['service'],
                          array('target' => 'middle'));
      $erg .= '</p>';
    }
    
    $erg .= '</font>';
    $erg .= '</body>';
    
    return $erg;
  }
  
}

?>
