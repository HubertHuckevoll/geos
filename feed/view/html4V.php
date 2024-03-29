<?php

class html4V extends baseV
{
  /**
   * Draw top frame
   * _________________________________________________________________
   */
  public function drawCategories(): void
  {
    $categories = $this->getData('categories');
    $erg = '';
    $i = 0;

    $erg .= $this->openPage();
    if ($this->getData('uim') == 'l')
    { // light mode
      $erg .= '<body bgcolor="#000080" text="#FFFFFF" link="#FFFFFF" vlink="FFFFFF">';
    }
    else
    { // dark mode
      $erg .= '<body text="#FFFFFF" bgcolor="#000000" link="#006699" vlink="#006699">';
    }

    $erg .= '<table border="0" width="100%" cellpadding="1">'.
            '<tr>'.
              '<td>'.
                '<font face="'.$this->getData('font').'">'.
                  '<h1>'.$this->getData('appName').$this->link(['hook' => 'setup'], '.', ['target' => '_top']).'</h1>'.
                '</font>'.
              '</td>'.
              '<td>'.
              '<font face="'.$this->getData('font').'">';

    foreach ($categories as $cat)
    {
      $erg .= $this->link(['hook' => 'feedsForCategory', 'tableIdx' => $i],
                          $cat,
                          ['target' => 'left']);

      if ($i != (count($categories) - 1))
      {
        $erg .= ' | ';
      }

      $i++;
    }

    $erg .= '</font>'.
            '</td>'.
            '</tr>'.
            '</table>'.
            '</body>';
    $erg .= $this->closePage();

    $this->send($erg);
  }

  /**
   * Services
   * _________________________________________________________________
   */
  public function drawFeedsForCategory():void
  {
    $tableIdx = $this->getData('tableIdx');
    $feeds = $this->getData('feeds');
    $category = $this->getData('category');
    $headline = $this->getData('headline');
    $erg = '';

    $erg .= $this->openPage();
    if ($this->getData('uim') == 'l')
    { // light mode
      $erg .= '<body bgcolor="#FFFFFF" text="#000000" link="#000080" vlink="#000080">';
    }
    else
    { // dark mode
      $erg .= '<body text="#FFFFFF" bgcolor="#000000" link="#006699" vlink="#006699">';
    }

    $erg .= '<font face="'.$this->getData('font').'">';
    $erg .= '<h3>'.$headline.'</h3>';

    for ($i = 0; $i < count($feeds); $i++)
    {
      $feed = $feeds[$i];
      $erg .= '<p>';
      $erg .= $this->link(['hook' => 'articlesForFeed', 'tableIdx' => $tableIdx, 'feedIdx' => $i],
                          $feed['service'],
                          ['target' => 'middle']);
      $erg .= '</p>';
    }

    $erg .= '</font>';
    $erg .= '</body>';
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
    $feedName = $this->getData('feedName');
    $erg = '';

    $erg .= $this->openPage();
    if ($this->getData('uim') == 'l')
    { // light mode
      $erg .= '<body bgcolor="#FFFFFF" text="#000000" link="#000080" vlink="#000080">';
    }
    else
    { // dark mode
      $erg .= '<body text="#FFFFFF" bgcolor="#000000" link="#006699" vlink="#006699">';
    }

    $erg .= '<font face="'.$this->getData('font').'">';
    $erg .= '<h3>'.$feedName.'</h3>';

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
                             $article['title'],
                             ['target' => 'right']);
        $erg .= '</p>';

        if ($this->stateParams['iU'] >= IMAGE_USE_ALL)
        {
          if (isset($article['image']) && ($article['image'] != ''))
          {
            $erg .= '<center><img src="'.$this->imageProxy($article['image'], 128).'"></center>';
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
    $erg .= '<small>'.$this->getData('feedURL').'</small>';

    $erg .= '</font>';
    $erg .= '</body>';
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

    $erg .= $this->openPage();
    if ($this->getData('uim') == 'l')
    { // light mode
      $erg .= '<body bgcolor="#FFFFFF" text="#000000" link="#000080" vlink="#000080">';
    }
    else
    { // dark mode
      $erg .= '<body text="#FFFFFF" bgcolor="#000000" link="#006699" vlink="#006699">';
    }

    $erg .= '<tr>'.
            '<td>'.
            '<font face="'.$this->getData('font').'">'.
            '<h3>'.$headline.'</h3>';

    if ($this->stateParams['iU'] >= IMAGE_USE_SOME)
    {
      $erg .= '<center><img src="'.$this->imageProxy($article['meta']['image'], 400).'"></center>';
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

    $erg .= '<hr>';
    $erg .= '<small><a href="'.$articleFullLink.'" target="_blank">'.wordwrap($articleFullLink, 75, "\r", true).'</a></small>';

    $erg .= '</font>'.
            '</td>'.
            '</tr>'.
            '</table>'.
            '</body>';

    $erg .= $this->closePage();

    $this->send($erg);
  }

  /**
   * frameset
   * _____________________________________________________________________
   */
  public function drawFrameset(): void
  {
    $erg  = '';
    $erg .= '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">'.
              '<html>'.
              '<head>'.
                '<title>'.$this->getData('appName').'/'.$this->getData('tsvName').'</title>'.
                '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">'.
              '</head>'.
              '<frameset rows="11%,*" frameborder="0" border="0" framespacing="0">'.
                '<frame name="top" src="'.$this->href(['hook' => 'categories']).'" scrolling="no">'.
                '<frameset cols="*,600,*" frameborder="0" border="0" framespacing="0">'.
                  '<frame name="left"   scrolling="auto" src="'.$this->href(['hook' => 'feedsForCategory', 'tableIdx' => 0]).'">'.
                  '<frame name="right"  scrolling="auto">'.
                  '<frame name="middle" scrolling="auto">'.
                '</frameset>'.
                '<noframes>'.
                  '<p>Your browser does not support frames. Use the non-frame version of this page.</p>'.
                '</noframes>'.
              '</frameset>'.
            '</html>';

    $this->send($erg);
  }

  /**
   * draw error page
   * _____________________________________________________________________
   */
  public function drawErrorPage(Exception $e): void
  {
    $erg  = '';
    $erg .= '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
    $erg .= '<html>';
    $erg .= '<head>';
    $erg .= '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">';
    $erg .= '<title>'.$this->getData('appName').'/'.$this->getData('tsvName').' - '.$this->getData('headline').'</title>';
    $erg .= $this->debugVars();
    $erg .= '</head>';
    $erg .= '<body>';

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
  protected function openPage(): string
  {
    $erg  = '';
    $erg .= '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
    $erg .= '<html>';
    $erg .= '<head>';
    $erg .= '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">';
    $erg .= '<title>'.$this->getData('appName').': '.$this->getData('headline').' ('.$this->getData('tsvName').')</title>';
    $erg .= $this->debugVars();
    $erg .= '</head>';

    return $erg;
  }

  /**
   * close page
   * ________________________________________________________________
   */
  protected function closePage(): string
  {
    $erg  = '';
    $erg .= '</html>';

    return $erg;
  }

}

?>