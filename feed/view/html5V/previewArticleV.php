<?php

namespace html5V;

class previewArticleV extends html5V
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
    $erg .= $this->link(array('hook' => 'feedsForCat',
                              'tableIdx' => $this->getData('tableIdx')),
                        $this->getData('tableName'));
    $erg .= '&nbsp;&gt;&nbsp;';
    $erg .= $this->link(array('hook' => 'articlesForFeed',
                              'tableIdx' => $this->getData('tableIdx'),
                              'feedIdx' => $this->getData('feedIdx')),
                        $this->getData('feedName'));

    return $erg;
  }

  /**
   * Preview
   * _________________________________________________________________
   */
  public function renderMainContent()
  {
    $article = $this->getData('article');
    $erg = '';

    $erg .= '<h3>'.$this->getData('headline').'</h3>';

    if ($this->stateParams['iU'] >= IMAGE_USE_SOME)
    {
      $erg .= '<p style="text-align: center;"><img src="'.$article['meta']['image'].'" style="max-width: 400px;"></p>';
    }

    foreach ($article['text'] as $p)
    {
      $erg .= '<p>'.$p.'</p>';
    }

    $erg .= '<a href="'.$this->getData('articleFullLink').'" target="_blank">'.$this->getData('articleFullLink').'</a>';

    return $erg;
  }

}

?>
