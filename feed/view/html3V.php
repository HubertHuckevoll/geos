<?php

class html3V extends \view
{
  /**
   * draw page
   * _____________________________________________________________________
   */
  public function drawPage(string $viewFunc = '') : void
  {
    $erg .= '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 3.2//EN">';

    $erg .= '<html>';
    $erg .= '<head>';
    $erg .= '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">'; //this is also set in the header, see view.php
    $erg .= '<title>'.$this->getData('appName').'/'.$this->getData('tsvName').' - '.$this->getData('headline').'</title>';
    $erg .= $this->debugVars();
    $erg .= '</head>';

    if ($this->getData('uim') == 'l')
    { // light mode
      $erg .= '<body text="#000000" bgcolor="#FFFFFF" link="#0000FF" vlink="#0000FF">';
    }
    else
    { // dark mode
      $erg .= '<body text="#FFFFFF" bgcolor="#000000" link="#006699" vlink="#006699">';
    }

    $erg .= '<table border="0" width="100%" cellpadding="0">'.
            '<tr>'.
              '<td></td>'.
              '<td width="600">'.
                '<font face="'.$this->getData('font').'">'.
                '<h1>'.$this->getData('appName').$this->link(array('hook' => 'setup'), '.').'</h1>';
                $erg .= $this->exec($viewFunc);
    $erg .=     '</font>';
    $erg .=   '</td>'.
            '<td></td>'.
            '</tr>'.
            '</table';

    $erg .= '</body>';
    $erg .= '</html>';

	  header('Content-Type: text/html; charset=iso-8859-1');
    echo $erg;
  }

  /**
   * draw page
   * _____________________________________________________________________
   */
  public function drawErrorPage(Exception $e) : void
  {
    $erg .= '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 3.2//EN">';

    $erg .= '<html>';
    $erg .= '<head>';
    $erg .= '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">'; //this is also set in the header, see view.php
    $erg .= '<title>'.$this->getData('appName').'/'.$this->getData('tsvName').' - '.$this->getData('headline').'</title>';
    $erg .= $this->debugVars();
    $erg .= '</head>';

    if ($this->getData('uim') == 'l')
    { // light mode
      $erg .= '<body text="#000000" bgcolor="#FFFFFF" link="#0000FF" vlink="#0000FF">';
    }
    else
    { // dark mode
      $erg .= '<body text="#FFFFFF" bgcolor="#000000" link="#006699" vlink="#006699">';
    }

    $erg .= '<table border="0" width="100%" cellpadding="0">'.
            '<tr>'.
              '<td></td>'.
              '<td width="600">'.
                '<font face="'.$this->getData('font').'">'.
                '<h1>'.$this->getData('appName').$this->link(array('hook' => 'setup'), '.').'</h1>';
                $erg .= '<h3>Fehler:</h3>';
                $erg .= '<p>'.$e->getMessage().'</p>';
    $erg .=     '</font>';
    $erg .=   '</td>'.
            '<td></td>'.
            '</tr>'.
            '</table';

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

    $articles = $feed['data'];
    $erg .= '<hr>';
    $erg .= $this->link(array('hook' => 'index'), 'Categories');
    $erg .= '&nbsp;&gt;&nbsp;';
    $erg .= $this->link(array('hook' => 'feedsForCat',
                              'tableIdx' => $this->getData('tableIdx')),
                        $this->getData('tableName'));
    $erg .= '&nbsp;&gt;&nbsp;';
    $erg .= '<b>'.$this->getData('feedName').'</b>';
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
            $erg .= '<p><img src="'.$this->imageProxy($article['image'], 128).'"></p>';
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

    $erg .= '<hr>';
    $erg .= $this->link(array('hook' => 'index'), 'Categories');
    $erg .= '&nbsp;&gt;&nbsp;';
    $erg .= '<b>'.$this->getData('tableName').'</b>';
    $erg .= '<hr>';

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
