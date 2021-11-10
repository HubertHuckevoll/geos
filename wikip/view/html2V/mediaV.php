<?php

namespace html2V;

class mediaV extends html2V
{

  /**
   * Preview
   * _________________________________________________________________
   */
  public function renderMainContent()
  {
    $images = (array) $this->getData('images');
    $imgCount = count($images);
    $imgPerPage = 3;
    $startIdx = 0;
    $endIdx = 0;
    $page = (int) ($this->getData('page') != false) ? $this->getData('page') : 0;
    $numPages = 0;
    $erg = '';
    
    $erg .= '<h3>Images for "'.$this->getData('title').'"</h3>';
    
    if ($imgCount > 0)
    {
      $numPages = $imgCount / $imgPerPage;
      if ($imgCount % $imgPerPage != 0)
      {
        $numPages = ceil($numPages);
      }
      
      $startIdx = $page * $imgPerPage;
      $endIdx = $startIdx + $imgPerPage;
      
      for($i = $startIdx; $i < $endIdx; $i++)
      {
        if (isset($images[$i]))
        {
          $item = $images[$i];
          $erg .= '<p>';
          $erg .= '<img src="http://'.$_SERVER['HTTP_HOST'].'/geos/tools/2gif.php?file='.urlencode($item['url']).'&width=256"><br>';
          $erg .= $item['caption'];
          $erg .= '</p>';
        }
      }

      $erg .= '<p>';
      $erg .= '<b>Pages:</b>';
      for($i = 0; $i < $numPages; $i++)
      {
        if ($i != $page)
        {
          $erg .= ' '.$this->link(array('hook' => 'media', 'title' => $this->getData('title'), 'page' => $i), $i+1).' ';
        }
        else
        {
          $erg .= ' '.($i+1).' ';
        }
      }
      $erg .= '</p>';
    }
    else
    {
      $erg .= '<p>No files.</p>';
    }
    
    return $erg;
  }
  
}

?>
