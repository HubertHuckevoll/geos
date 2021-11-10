<?php

namespace html5V;

class articlesForFeedV extends html5V
{

  /**
   * render bread crumbs
   * _________________________________________________________________
   */
  public function renderBreadCrumbs($feed)
  {
    $title = $this->getData('feedName');
    $erg = '';

    $erg .= $this->link(array('hook' => 'index'), 'Categories');
    $erg .= '&nbsp;&gt;&nbsp;';
    $erg .= $this->link(array('hook' => 'feedsForCat',
                              'tableIdx' => $this->getData('tableIdx')),
                        $this->getData('tableName'));
    $erg .= '&nbsp;&gt;&nbsp;';
    $erg .= '<b>'.$title.'</b>';

    return $erg;
  }

  /**
   * Articles
   * _________________________________________________________________
   */
  public function renderMainContent()
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
