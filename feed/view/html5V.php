<?php

class html5V extends \view
{
  /**
   * draw page
   * _____________________________________________________________________
   */
  public function drawPage(string $viewFunc = '') : void
  {
    $erg .= '<!DOCTYPE html>';
    $erg .= '<html>';
    $erg .= '<head>';
    $erg .= '<title>'.$this->getData('appName').'/'.$this->getData('tsvName').' - '.$this->getData('headline').'</title>';
    $erg .= '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">'; //this is also set in the header
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
    $erg .= '<h1>'.$this->getData('appName').$this->link(array('hook' => 'setup'), '.').'</h1>';
    $erg .= '<p>'.$this->renderBreadCrumbs($viewFunc, $this->getData('articles')).'</p>';
    $erg .= '</header>';

    $erg .= $this->exec($viewFunc);

    $erg .= '</body>';
    $erg .= '</html>';

    header('Content-Type: text/html; charset=iso-8859-1');
    echo $erg;
  }

  /**
   * draw error page
   * _____________________________________________________________________
   */
  public function drawErrorPage(Exception $e) : void
  {
    $erg .= '<!DOCTYPE html>';
    $erg .= '<html>';
    $erg .= '<head>';
    $erg .= '<title>'.$this->getData('appName').'/'.$this->getData('tsvName').' - '.$this->getData('headline').'</title>';
    $erg .= '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">'; //this is also set in the header
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
    $erg .= '<h1>'.$this->getData('appName').$this->link(array('hook' => 'setup'), '.').'</h1>';
    $erg .= '</header>';

    $erg .= '<h3>Fehler:</h3>';
    $erg .= '<p>'.$e->getMessage().'</p>';

    $erg .= '</body>';
    $erg .= '</html>';

    header('Content-Type: text/html; charset=iso-8859-1');
    echo $erg;
  }

  /**
   * Articles
   * _________________________________________________________________
   */
  public function articlesForFeed()
  {
    $feed = $this->getData('articles');
    $tableIdx = $this->getData('tableIdx');
    $feedIdx = $this->getData('feedIdx');
    $img = '';
    $erg = '';

    $articles = $feed['data'];

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
        $article  = $articles[$i];
        $erg .= '<p>'.
                  $this->link(array('hook' => 'previewArticle',
                                    'tableIdx' => $tableIdx,
                                    'feedIdx' => $feedIdx,
                                    'articleIdx' => $i),
                                     $article['title']).
                '</p>';

        if ($this->stateParams['iU'] >= IMAGE_USE_ALL)
        {
          if ($article['image'] != '')
          {
            $erg .= '<p><img src="'.$article['image'].'" style="width: 128px;"><p>';
          }
        }

        $erg .= '<p>';
        $erg .= ($article['description'] != '') ? $article['description'] : 'n/a';
        $date = $article['date'];
        if ($date != '')
        {
          $date = date_parse($date);
          $date = strtotime($date['day'].'.'.$date['month'].'.'.$date['year']);
          $date = strftime('%A, %B %e, %Y', $date);
          $erg .= '&nbsp;<i>('.$date.')</i>';
        }
        $erg .= '</p>';

        $erg .= ($i !== (count($articles)-1)) ? '<br>' : '';
      }
    }

    $erg .= '<hr>';
    $erg .= '<small>'.$this->getData('feedURL').'</small>';

    return $erg;
  }

  /**
   * Categories
   * _________________________________________________________________
   */
  public function categories()
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

  /**
   * Services
   * _________________________________________________________________
   */
  public function feedsForCat()
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

  /**
   * Preview
   * _________________________________________________________________
   */
  public function previewArticle()
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


  /**
   * check if remote file exists
   * ________________________________________________________________
   */
  protected function hasLogo($feed)
  {
    if ($feed['meta']['logo'] != '')
    {
      $handle = @fopen($feed['meta']['logo'], 'r');
      if ($handle !== false)
      {
        fclose($handle);
        return true;
      }
    }
    return false;
  }

  /**
   * render bread crumbs
   * articles for feed
   * _________________________________________________________________
   */
  public function renderBreadCrumbs($viewFunc, $feed)
  {
    $title = $this->getData('feedName');
    $erg = '';

    switch ($viewFunc)
    {
      case 'categories':
        $erg .= '';
      break;

      case 'feedsForCat':
        $erg .= $this->link(['hook' => 'index'], 'Categories');
        $erg .= '&nbsp;&gt;&nbsp;';
        $erg .= '<b>'.$this->getData('tableName').'</b>';
      break;

      case 'articlesForFeed':
        $erg .= $this->link(['hook' => 'index'], 'Categories');
        $erg .= '&nbsp;&gt;&nbsp;';
        $erg .= $this->link(['hook' => 'feedsForCat', 'tableIdx' => $this->getData('tableIdx')],
                            $this->getData('tableName'));
        $erg .= '&nbsp;&gt;&nbsp;';
        $erg .= '<b>'.$title.'</b>';
      break;

      case 'previewArticle':
        $erg .= $this->link(['hook' => 'index'], 'Categories');
        $erg .= '&nbsp;&gt;&nbsp;';
        $erg .= $this->link(['hook' => 'feedsForCat', 'tableIdx' => $this->getData('tableIdx')],
                            $this->getData('tableName'));
        $erg .= '&nbsp;&gt;&nbsp;';
        $erg .= $this->link(['hook' => 'articlesForFeed', 'tableIdx' => $this->getData('tableIdx'), 'feedIdx' => $this->getData('feedIdx')],
                            $this->getData('feedName'));
      break;
    }

    return $erg;
  }

}

?>
