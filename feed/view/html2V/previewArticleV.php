<?php

namespace html2V;

class previewArticleV extends html2V
{

  /**
   * Preview
   * _________________________________________________________________
   */
  public function renderMainContent()
  {
    $article = $this->getData('article');
    $erg = '';

    $erg .= '<hr>';
    $erg .= $this->link(['hook' => 'index'], 'Categories');
    $erg .= '&nbsp;&gt;&nbsp;';
    $erg .= $this->link(['hook' => 'feedsForCat',
                         'tableIdx' => $this->getData('tableIdx')],
                         $this->getData('tableName'));
    $erg .= '&nbsp;&gt;&nbsp;';
    $erg .= $this->link(['hook' => 'articlesForFeed',
                         'tableIdx' => $this->getData('tableIdx'),
                         'feedIdx' => $this->getData('feedIdx')],
                          $this->getData('feedName'));
    $erg .= '<hr>';

    $erg .= '<h3>'.$this->getData('headline').'</h3>';

    if ($this->stateParams['iU'] >= IMAGE_USE_SOME)
    {
      $erg .= '<p><img src="'.$this->imageProxy($article['meta']['image'], 400).'"></p>';
    }

    for ($i=0; $i < count($article['text']); $i++)
    {
      $p = $article['text'][$i];
      $erg .= '<p>';
      $erg .= '<a name="p'.($i+1).'" href="#p'.($i+1).'">['.($i+1).']</a>';
      $erg .= '&nbsp;';
      $str = wordwrap($p, 70, "<br>", true);
      $erg .= $str;
      $erg .= '</p>';
    }

    $erg .= '<hr>';
    $erg .= '<small><a href="'.$this->getData('articleFullLink').'" target="_blank">'.wordwrap($this->getData('articleFullLink'), 75, "\r", true).'</a></small>';

    return $erg;
  }

}

?>
