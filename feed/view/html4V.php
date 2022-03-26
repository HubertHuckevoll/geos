<?php

class html4V extends \view
{

  /**
   * draw page
   * _____________________________________________________________________
   */
  public function drawPage(string $viewFunc = '') : void
  {
    $erg = '';
    $erg .= '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
    $erg .= '<html>';
    $erg .= '<head>';
    $erg .= '<title>'.$this->getData('appName').'</title>';
    $erg .= $this->debugVars();
    $erg .= '</head>';

    $erg .= $this->exec($viewFunc);

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
    $erg = '';
    $erg .= '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
    $erg .= '<html>';
    $erg .= '<head>';
    $erg .= '<title>'.$this->getData('appName').'</title>';
    $erg .= $this->debugVars();
    $erg .= '</head>';

    $erg .= '<h3>Fehler:</h3>';
    $erg .= '<p>'.$e->getMessage().'</p>';

    $erg .= '</html>';

    header('Content-Type: text/html; charset=iso-8859-1');
    echo $erg;
  }

  /**
   * frameset
   * _____________________________________________________________________
   */
  public function drawFrameset()
  {
    $erg .= '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">'.
              '<html>'.
              '<head>'.
                '<title>'.$this->getData('appName').'/'.$this->getData('tsvName').'</title>'.
                '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">'.
              '</head>'.
              '<frameset rows="11%,*" frameborder="0" border="0" framespacing="0">'.
                '<frame name="top" src="'.$this->href(['hook' => 'categories']).'" scrolling="no">'.
                '<frameset cols="*,600,*" frameborder="0" border="0" framespacing="0">'.
                  '<frame name="left"   scrolling="auto" src="'.$this->href(['hook' => 'feedsForCat', 'tableIdx' => 0]).'">'.
                  '<frame name="right"  scrolling="auto">'.
                  '<frame name="middle" scrolling="auto">'.
                '</frameset>'.
                '<noframes>'.
                  '<p>Your browser does not support frames. Use the non-frame version of this page.</p>'.
                '</noframes>'.
              '</frameset>'.
            '</html>';

    header('Content-Type: text/html; charset=iso-8859-1');
    echo $erg;
  }

  /**
   * Draw top frame
   * _________________________________________________________________
   */
  public function categories()
  {
    $tableName = $this->data['categories']['tableName'];
    $feedTable = $this->data['categories']['sheets'];
    $erg = '';

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
                  '<h1>'.$this->getData('appName').$this->link(array('hook' => 'setup'), '.', array('target' => '_top')).'</h1>'.
                '</font>'.
              '</td>'.
              '<td>'.
              '<font face="'.$this->getData('font').'">';

    for ($i = 0; $i < count($feedTable); $i++)
    {
      $table = $feedTable[$i];
      $erg .= $this->link(array('hook' => 'feedsForCat',
                                'tableIdx' => $i),
                          $table['name'],
                          array('target' => 'left'));

      if ($i != (count($feedTable) - 1))
      {
        $erg .= ' | ';
      }
    }

    $erg .= '</font>'.
            '</td>'.
            '</tr>'.
            '</table>'.
            '</body>';

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
    $erg = '';

    if ($this->getData('uim') == 'l')
    { // light mode
      $erg .= '<body bgcolor="#FFFFFF" text="#000000" link="#000080" vlink="#000080">';
    }
    else
    { // dark mode
      $erg .= '<body text="#FFFFFF" bgcolor="#000000" link="#006699" vlink="#006699">';
    }

    //$erg .= '<body bgcolor="#FFFFFF" link="#0000FF" vlink="#0000FF">';
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

  /**
   * articles
   * _________________________________________________________________
   */
  public function articlesForFeed()
  {
    $feed = $this->getData('articles');
    $tableIdx = $this->getData('tableIdx');
    $feedIdx = $this->getData('feedIdx');
    $articles = $feed['data'];
    $erg = '';

    if ($this->getData('uim') == 'l')
    { // light mode
      $erg .= '<body bgcolor="#FFFFFF" text="#000000" link="#000080" vlink="#000080">';
    }
    else
    { // dark mode
      $erg .= '<body text="#FFFFFF" bgcolor="#000000" link="#006699" vlink="#006699">';
    }

    $erg .= '<font face="'.$this->getData('font').'">';
    $erg .= '<h3>'.$this->getData('feedName').'</h3>';

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

        $erg .= '<p>'.$this->link(array('hook' => 'previewArticle',
                                        'tableIdx' => $tableIdx,
                                        'feedIdx' => $feedIdx,
                                        'articleIdx' => $i),
                                  $article['title'],
                                  array('target' => 'right')).
                '</p>';

        if ($this->stateParams['iU'] >= IMAGE_USE_ALL)
        {
          if ($article['image'] != '')
          {
            $erg .= '<center><img src="'.$this->imageProxy($article['image'], 128).'"></center>';
          }
        }

        $erg .= '<p>';
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

    $erg .= '</font>';
    $erg .= '</body>';

    return $erg;
  }

  /**
   * Preview
   * _________________________________________________________________
   */
  public function previewArticle()
  {
    $article = $this->getData('article');

    if ($this->getData('uim') == 'l')
    { // light mode
      $erg .= '<body bgcolor="#FFFFFF" text="#000000" link="#000080" vlink="#000080">';
    }
    else
    { // dark mode
      $erg .= '<body text="#FFFFFF" bgcolor="#000000" link="#006699" vlink="#006699">';
    }

    //$erg = '<body bgcolor="#FFFFFF" link="#0000FF" vlink="#0000FF">'.
    $erg .= '<tr>'.
            '<td>'.
            '<font face="'.$this->getData('font').'">'.
            '<h3>'.$this->getData('headline').'</h3>';

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
    $erg .= '<small><a href="'.$this->getData('articleFullLink').'" target="_blank">'.wordwrap($this->getData('articleFullLink'), 75, "\r", true).'</a></small>';

    $erg .= '</font>'.
            '</td>'.
            '</tr>'.
            '</table>'.
            '</body>';

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



}

?>
