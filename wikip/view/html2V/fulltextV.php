<?php

namespace html2V;

class fulltextV extends html2V
{

  /**
   * Preview
   * _________________________________________________________________
   */
  public function renderMainContent()
  {
    $fulltext = $this->getData('fulltext');
    $numImages = (int) count($this->getData('images'));
    $title = $this->getData('title');
    $link = 'https://'.$this->getData('loc').'.wikipedia.org/wiki/'.str_replace(' ', '_', $title);
    $erg = '';

    $erg .= '<h3>'.$title.'</h3>';

    if ($numImages != 0)
    {
      $erg .= '<p>';
      $erg .= $this->link(array('hook' => 'media', 'title' => $title), '(Load Images)');
      $erg .= '</p>';
    }
    
    $erg .= '<p>';
    $erg .= $fulltext;
    $erg .= '</p>';
    
    $erg .= '<p>';
    $erg .= '(Source:&nbsp;<a href="'.$link.'" target="_blank">'.$link.'</a>)';
    $erg .= '</p>';

    return $erg;
  }
  
}

?>
