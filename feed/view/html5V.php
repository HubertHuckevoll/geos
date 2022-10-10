<?php

class html5V extends baseV
{
  /**
   * Categories
   * _________________________________________________________________
   */
  public function drawCategories(): void
  {
    $categories = $this->getData('categories');
    $erg = '';
    $i = 0;

    $erg .= $this->openPage('categories');
    $erg .= '<ul>';
    foreach($categories as $cat)
    {
      $erg .= '<li>';
      $erg .= $this->link(['hook' => 'feedsForCategory',
                           'tableIdx' => $i],
                           $cat);
      $erg .= '</li>';
      $i++;
    }
    $erg .= '</ul>';

	  $erg .= $this->closePage();

    $this->send($erg);
  }

  /**
   * Services
   * _________________________________________________________________
   */
  public function drawFeedsForCategory(): void
  {
    $tableIdx = $this->getData('tableIdx');
    $feeds = $this->getData('feeds');
    $erg = '';

    $erg .= $this->openPage('feedsForCategory');
    $erg .= '<ul>';
    for ($i = 0; $i < count($feeds); $i++)
    {
      $feed = $feeds[$i];
      $erg .= '<li>';
      $erg .= $this->link(['hook' => 'articlesForFeed', 'tableIdx' => $tableIdx, 'feedIdx' => $i],
                          $feed['service']);
      $erg .= '</li>';
    }
    $erg .= '</ul>';
	  $erg .= $this->closePage();

    $this->send($erg);
  }

  /**
   * articles
   * _________________________________________________________________
   */
  public function drawArticlesForFeed(): void
  {
    $feed = $this->getData('feedData');
    $articles = $feed['data'];
    $tableIdx = $this->getData('tableIdx');
    $feedIdx = $this->getData('feedIdx');
    $feedURL = $this->getData('feedURL');
    $erg = '';

    $erg .= $this->openPage('articlesForFeed');
    if ($this->stateParams['iU'] >= IMAGE_USE_MEDIUM)
    {
      if ($this->hasLogo($feed))
      {
        $logo = $feed['meta']['logo'];
        $erg .= '<p><img src="'.$logo.'" style="max-width: 64px;" alt="'.$logo.'"><p>';
      }
    }

    if (is_countable($articles))
    {
      for ($i = 0; $i < count($articles); $i++)
      {
        $article = $articles[$i];

        $erg .= '<p>';
        $erg .= $this->link(['hook' => 'previewArticle', 'tableIdx' => $tableIdx, 'feedIdx' => $feedIdx, 'articleIdx' => $i],
                            $article['title']);
        $erg .= '</p>';

        if ($this->stateParams['iU'] >= IMAGE_USE_ALL)
        {
          if (isset($article['image']) && ($article['image'] != ''))
          {
            $erg .= '<p><img src="'.$article['image'].'" style="width: 128px;"><p>';
          }
        }

        $erg .= '<p>';
        $erg .= $article['description'];
        $date = $article['date'];
        if ($date != '')
        {
          $dt = new DateTime($date);
          $erg .= '&nbsp;<i>('.$dt->format(DATE_RSS).')</i>';
        }
        $erg .= '</p>';

        $erg .= ($i !== (count($articles)-1)) ? '<br>' : '';
      }
    }

    $erg .= '<hr>';
    $erg .= '<small>'.$feedURL.'</small>';
	  $erg .= $this->closePage();

    $this->send($erg);
  }

  /**
   * Preview
   * _________________________________________________________________
   */
  public function drawPreviewArticle(): void
  {
    $article = $this->getData('article');
    $headline = $this->getData('headline');
    $articleFullLink = $this->getData('articleFullLink');
    $erg = '';

    $erg .= $this->openPage('previewArticle');
    $erg .= '<h3>'.$headline.'</h3>';
    if ($this->stateParams['iU'] >= IMAGE_USE_SOME)
    {
      $erg .= '<p style="text-align: center;"><img src="'.$article['meta']['image'].'" style="max-width: 400px;"></p>';
    }

    foreach ($article['text'] as $node)
    {
      $tag = $node['tag'];
      $str = $node['content'];

      if (preg_match('/h[2-5]/', $tag))
      {
        $tag = 'h4';
      }

      $erg .= '<'.$tag.'>';
      $erg .= $str;
      $erg .= '</'.$tag.'>';
    }

    $erg .= '<a href="'.$articleFullLink.'" target="_blank">'.$articleFullLink.'</a>';
	  $erg .= $this->closePage();

    $this->send($erg);
  }

  /**
   * draw error page
   * _____________________________________________________________________
   */
  public function drawErrorPage(Exception $e): void
  {
    $erg  = '';
    $erg .= '<!DOCTYPE html>';
    $erg .= '<html>';
    $erg .= '<head>';
    $erg .= '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">';
    $erg .= '<title>'.$this->getData('appName').'/'.$this->getData('tsvName').' - '.$this->getData('headline').'</title>';

    $erg .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
    $erg .= '<link rel="stylesheet" href="https://fonts.xz.style/serve/inter.css">';
    $erg .= '<link rel="stylesheet" href="https://newcss.net/new.min.css">';

    // dark mode
    if ($this->getData('uim') == 'd')
    {
      $erg .= '<link rel="stylesheet" href="https://newcss.net/theme/night.css">';
    }

    $erg .= $this->debugVars();
    $erg .= '</head>';

    $erg .= '<body>';

    $erg .= '<header>';
    $erg .= '<h1>'.$this->getData('appName').$this->link(['hook' => 'setup'], '.').'</h1>';
    $erg .= '</header>';

    $erg .= '<h3>Fehler:</h3>';
    $erg .= '<p>'.$e->getMessage().'</p>';

    $erg .= '</body>';
    $erg .= '</html>';

    $this->send($erg);
  }

  /**
   * open page
   * _____________________________________________________________________
   */
  protected function openPage(string $viewFunc = ''): string
  {
    $erg  = '';
    $erg .= '<!DOCTYPE html>';
    $erg .= '<html>';
    $erg .= '<head>';
    $erg .= '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">';
    $erg .= '<title>'.$this->getData('appName').': '.$this->getData('headline').' ('.$this->getData('tsvName').')</title>';
    $erg .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
    $erg .= '<link rel="stylesheet" href="https://fonts.xz.style/serve/inter.css">';
    $erg .= '<link rel="stylesheet" href="https://newcss.net/new.min.css">';

    // dark mode
    if ($this->getData('uim') == 'd')
    {
      $erg .= '<link rel="stylesheet" href="https://newcss.net/theme/night.css">';
    }

    $erg .= $this->debugVars();
    $erg .= '</head>';
    $erg .= '<body>';

    $erg .= '<header>';
    $erg .= '<h1>'.$this->getData('appName').$this->link(['hook' => 'setup'], '.').'</h1>';
    $erg .= '<nav>'.$this->renderBreadCrumbs($viewFunc).'</nav>';
    $erg .= '</header>';

    return $erg;
  }

  /**
   * close the page
   * ________________________________________________________________
   */
  protected function closePage(): string
  {
    $erg  = '';
    $erg .= '</body>';
    $erg .= '</html>';

    return $erg;
  }

}

?>
