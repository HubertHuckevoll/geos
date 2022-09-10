<?php

class html3V extends \baseV
{
  /**
   * Categories
   * _________________________________________________________________
   */
  public function drawCategories()
  {
    $categories = $this->getData('categories');
    $erg = '';
    $i = 0;

    $erg .= $this->openPage();
    $erg .= '<hr>';
    $erg .= $this->renderBreadCrumbs('categories');
    $erg .= '<hr>';

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

    header('Content-Type: text/html; charset=iso-8859-1');
    echo $erg;
  }

  /**
   * Services
   * _________________________________________________________________
   */
  public function drawFeedsForCategory()
  {
    $tableIdx = $this->getData('tableIdx');
    $feeds = $this->getData('feeds');
    $erg = '';

    $erg .= $this->openPage();
    $erg .= '<hr>';
    $erg .= $this->renderBreadCrumbs('feedsForCategory');
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
	  $erg .= $this->closePage();

    header('Content-Type: text/html; charset=iso-8859-1');
    echo $erg;
  }

  /**
   * Articles
   * _________________________________________________________________
   */
  public function drawArticlesForFeed()
  {
    $feed = $this->getData('feedData');
    $articles = $feed['data'];
    $tableIdx = $this->getData('tableIdx');
    $feedIdx = $this->getData('feedIdx');
    $feedURL = $this->getData('feedURL');
    $erg = '';

    $erg .= $this->openPage();
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
          if (isset($article['image']) && ($article['image'] != ''))
          {
            $erg .= '<p><img src="'.$this->imageProxy($article['image'], 128).'"></p>';
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

    header('Content-Type: text/html; charset=iso-8859-1');
    echo $erg;
  }

  /**
   * Preview
   * _________________________________________________________________
   */
  public function drawPreviewArticle()
  {
    $article = $this->getData('article');
    $headline = $this->getData('headline');
    $articleFullLink = $this->getData('articleFullLink');
    $erg = '';

    $erg .= $this->openPage();
    $erg .= '<hr>';
    $erg .= $this->renderBreadCrumbs('previewArticle');
    $erg .= '<hr>';

    $erg .= '<h3>'.$headline.'</h3>';

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
    // this has to be a link with a wordwrapped text to make sure
    // Skipper doesn't grow the table cell because the text is
    // too long...
    $erg .= '<small><a href="'.$articleFullLink.'" target="_blank">'.wordwrap($articleFullLink, 75, "\r", true).'</a></small>';
	  $erg .= $this->closePage();

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
   * open page
   * _____________________________________________________________________
   */
  protected function openPage(): string
  {
    $erg  = '';
    $erg .= '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 3.2//EN">';

    $erg .= '<html>';
    $erg .= '<head>';
    $erg .= '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">';
    $erg .= '<title>'.$this->getData('appName').': '.$this->getData('headline').' ('.$this->getData('tsvName').')</title>';
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

    return $erg;
  }

  /**
   * close the page
   * ________________________________________________________________
   */
  protected function closePage(): string
  {
    $erg  = '';
    $erg .= '</font>';
    $erg .= '</td>'.
            '<td></td>'.
            '</tr>'.
            '</table';

    $erg .= '</body>';
    $erg .= '</html>';

    return $erg;
  }
}

?>
