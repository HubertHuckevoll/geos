<?php

class html3V extends \baseV
{
  /**
   * draw page
   * _____________________________________________________________________
   */
  public function drawPage(string $viewFunc = '') : void
  {
    $erg  = '';
    $erg .= '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 3.2//EN">';

    $erg .= '<html>';
    $erg .= '<head>';
    $erg .= '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">';
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
                '<h1>'.$this->getData('appName').$this->link(['hook' => 'setup'], '.').'</h1>';
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
    $erg  = '';
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
   * Categories
   * _________________________________________________________________
   */
  public function categories()
  {
    $categories = $this->getData('categories');
    $erg = '';
    $i = 0;

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
   * Articles
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
    $erg .= '<small><a href="'.$articleFullLink.'" target="_blank">'.wordwrap($articleFullLink, 75, "\r", true).'</a></small>';

    return $erg;
  }

}

?>
