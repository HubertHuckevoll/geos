<?php

class baseV extends \view
{

  /**
   * render bread crumbs
   * _________________________________________________________________
   */
  public function renderBreadCrumbs($viewFunc)
  {
    $category = $this->getData('category');
    $tableIdx = $this->getData('tableIdx');

    $feedIdx = $this->getData('feedIdx');
    $feedName = $this->getData('feedName');

    $erg = '';

    switch ($viewFunc)
    {
      case 'categories':
        $erg .= '';
      break;

      case 'feedsForCat':
        $erg .= $this->link(['hook' => 'index'], 'Categories');
        $erg .= '&nbsp;&gt;&nbsp;';
        $erg .= '<b>'.$category.'</b>';
      break;

      case 'articlesForFeed':
        $erg .= $this->link(['hook' => 'index'], 'Categories');
        $erg .= '&nbsp;&gt;&nbsp;';
        $erg .= $this->link(['hook' => 'feedsForCat', 'tableIdx' => $this->getData('tableIdx')],
                            $category);
        $erg .= '&nbsp;&gt;&nbsp;';
        $erg .= '<b>'.$feedName.'</b>';
      break;

      case 'previewArticle':
        $erg .= $this->link(['hook' => 'index'], 'Categories');
        $erg .= '&nbsp;&gt;&nbsp;';
        $erg .= $this->link(['hook' => 'feedsForCat', 'tableIdx' => $this->getData('tableIdx')],
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
