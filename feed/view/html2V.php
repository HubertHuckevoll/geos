<?php

class html2V extends \baseV
{
  /**
   * draw page
   * _____________________________________________________________________
   */
  public function drawPage(string $viewFunc = '') : void
  {
    $erg  = '';
    $erg .= '<!DOCTYPE html PUBLIC "-//IETF//DTD HTML 2.0//EN">';
    $erg .= '<html>';
    $erg .= '<head>';
    $erg .= '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">';
    $erg .= '<title>'.$this->getData('appName').': '.$this->getData('headline').' ('.$this->getData('tsvName').')</title>';
    $erg .= $this->debugVars();
    $erg .= '</head>';

    // this is unsupported in HTML2 but we try anyway...
    if ($this->getData('uim') == 'l')
    { // light mode
      $erg .= '<body text="#000000" bgcolor="#FFFFFF" link="#0000FF" vlink="#0000FF">';
    }
    else
    { // dark mode
      $erg .= '<body text="#FFFFFF" bgcolor="#000000" link="#006699" vlink="#006699">';
    }

    $erg .= '<h1>'.$this->getData('appName').$this->link(['hook' => 'setup'], '.').'</h1>';

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
    $erg  = '';
    $erg .= '<!DOCTYPE html PUBLIC "-//IETF//DTD HTML 2.0//EN">';
    $erg .= '<html>';
    $erg .= '<head>';
    $erg .= '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">';
    $erg .= '<title>'.$this->getData('appName').'/'.$this->getData('tsvName').' - '.$this->getData('headline').'</title>';
    $erg .= $this->debugVars();
    $erg .= '</head>';

    // this is unsupported in HTML2 but we try anyway...
    if ($this->getData('uim') == 'l')
    { // light mode
      $erg .= '<body text="#000000" bgcolor="#FFFFFF" link="#0000FF" vlink="#0000FF">';
    }
    else
    { // dark mode
      $erg .= '<body text="#FFFFFF" bgcolor="#000000" link="#006699" vlink="#006699">';
    }

    $erg .= '<h1>'.$this->getData('appName').$this->link(['hook' => 'setup'], '.').'</h1>';

    $erg .= '<h3>Fehler:</h3>';
    $erg .= '<p>'.$e->getMessage().'</p>';

    $erg .= '</body>';
    $erg .= '</html>';

	  header('Content-Type: text/html; charset=iso-8859-1');
    echo $erg;
  }

  /**
   * Categories
   * _________________________________________________________________
   */
  public function categories()
  {
    $categories = $this->getData('categories');
    $erg = '';
    $i = 0;

    $erg .= '<hr>';
    $erg .= $this->renderBreadCrumbs('categories');
    $erg .= '<hr>';

    $erg .= '<ul>';
    foreach($categories as $cat)
    {
      $erg .= '<li>';
      $erg .= $this->link(['hook' => 'feedsForCat',
                           'tableIdx' => $i],
                           $cat);
      $erg .= '</li>';
      $i++;
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
    $feeds = $this->getData('feeds');
    $erg = '';

    $erg .= '<hr>';
    $erg .= $this->renderBreadCrumbs('feedsForCat');
    $erg .= '<hr>';

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

    return $erg;
  }

  /**
   * articles
   * _________________________________________________________________
   */
  public function articlesForFeed()
  {
    $feed = $this->getData('feedData');
    $articles = $feed['data'];
    $tableIdx = $this->getData('tableIdx');
    $feedIdx = $this->getData('feedIdx');
    $feedURL = $this->getData('feedURL');
    $img = '';
    $erg = '';

    $erg .= '<hr>';
    $erg .= $this->renderBreadCrumbs('articlesForFeed');
    $erg .= '<hr>';

    if ($this->stateParams['iU'] >= IMAGE_USE_MEDIUM)
    {
      if ($this->hasLogo($feed))
      {
        $erg .= '<p><img src="'.$this->imageProxy($feed['meta']['logo'], 64).'" alt="'.$feed['meta']['logo'].'"></p>';
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
          if ($article['image'] != '')
          {
            $erg .= '<p><img src="'.$this->imageProxy($article['image'], 128).'"></p>';
          }
        }

        $erg .= '<p>';
        $desc = $article['description'];
        $desc = wordwrap($desc, 70, "<br>", true);
        $erg .= $desc;

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
    $erg .= '<small>'.$feedURL.'</small>';

    return $erg;
  }

  /**
   * Preview
   * _________________________________________________________________
   */
  public function previewArticle()
  {
    $article = $this->getData('article');
    $tableIdx = $this->getData('tableIdx');
    $feedIdx = $this->getData('feedIdx');
    $headline = $this->getData('headline');
    $articleFullLink = $this->getData('articleFullLink');

    $erg = '';

    $erg .= '<hr>';
    $erg .= $this->renderBreadCrumbs('previewArticle');
    $erg .= '<hr>';

    $erg .= '<h3>'.$headline.'</h3>';

    if ($this->stateParams['iU'] >= IMAGE_USE_SOME)
    {
      $erg .= '<p><img src="'.$this->imageProxy($article['meta']['image'], 400).'"></p>';
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
      $erg .= wordwrap($str, 70, "<br>", true);
      $erg .= '</'.$tag.'>';
    }

    $erg .= '<hr>';
    $erg .= '<small><a href="'.$articleFullLink.'" target="_blank">'.wordwrap($articleFullLink, 75, "\r", true).'</a></small>';

    return $erg;
  }

}

?>
