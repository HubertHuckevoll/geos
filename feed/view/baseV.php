<?php

class baseV extends \view
{

  /**
   * render bread crumbs
   * _________________________________________________________________
   */
  protected function renderBreadCrumbs(string $viewFunc): string
  {
    $category = $this->getData('category');
    $feedName = $this->getData('feedName');
    $erg = '';

    switch ($viewFunc)
    {
      case 'categories':
        $erg .= '<b>Categories</b>';
      break;

      case 'feedsForCategory':
        $erg .= $this->link(['hook' => 'index'], 'Categories');
        $erg .= '&nbsp;&gt;&nbsp;';
        $erg .= '<b>'.$category.'</b>';
      break;

      case 'articlesForFeed':
        $erg .= $this->link(['hook' => 'index'], 'Categories');
        $erg .= '&nbsp;&gt;&nbsp;';
        $erg .= $this->link(['hook' => 'feedsForCategory', 'tableIdx' => $this->getData('tableIdx')],
                            $category);
        $erg .= '&nbsp;&gt;&nbsp;';
        $erg .= '<b>'.$feedName.'</b>';
      break;

      case 'previewArticle':
        $erg .= $this->link(['hook' => 'index'], 'Categories');
        $erg .= '&nbsp;&gt;&nbsp;';
        $erg .= $this->link(['hook' => 'feedsForCategory', 'tableIdx' => $this->getData('tableIdx')],
                            $category);
        $erg .= '&nbsp;&gt;&nbsp;';
        $erg .= $this->link(['hook' => 'articlesForFeed', 'tableIdx' => $this->getData('tableIdx'), 'feedIdx' => $this->getData('feedIdx')],
                            $feedName);
      break;
    }

    return $erg;
  }

  /**
   * check if remote file exists
   * ________________________________________________________________
   */
  protected function hasLogo($feed)
  {
    if (isset($feed['meta']['logo']) && ($feed['meta']['logo'] != ''))
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
