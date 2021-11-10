<?php

namespace html3V;

class previewArticleV extends html3V
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
    $erg .= $this->link(array('hook' => 'index'), 'Categories');
    $erg .= '&nbsp;&gt;&nbsp;';
    $erg .= $this->link(array('hook' => 'feedsForCat',
                              'tableIdx' => $this->getData('tableIdx')),
                        $this->getData('tableName'));
    $erg .= '&nbsp;&gt;&nbsp;';
    $erg .= $this->link(array('hook' => 'articlesForFeed',
                              'tableIdx' => $this->getData('tableIdx'),
                              'feedIdx' => $this->getData('feedIdx')),
                        $this->getData('feedName'));
    $erg .= '<hr>';

    $erg .= '<h3>'.$this->getData('headline').'</h3>';

    if ($this->stateParams['iU'] >= IMAGE_USE_SOME)
    {
      $erg .= '<center><img src="'.$this->imageProxy($article['meta']['image'], 400).'"></center>';
    }

    for ($i=0; $i < count($article['text']); $i++)
    {
      $p = $article['text'][$i];
      $erg .= '<p>';
      $erg .= '<a name="p'.($i+1).'" href="#p'.($i+1).'">['.($i+1).']</a>';
      $erg .= '&nbsp;';
      $erg .= $p;
      $erg .= '</p>';
    }

    $erg .= '<hr>';
    // this has to be a link with a wordwrapped text to make sure
    // Skipper doesn't grow the table cell because the text is
    // too long...
    $erg .= '<small><a href="'.$this->getData('articleFullLink').'" target="_blank">'.wordwrap($this->getData('articleFullLink'), 75, "\r", true).'</a></small>';

    return $erg;
  }

}

?>
