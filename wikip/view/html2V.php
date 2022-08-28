<?php

class html2V extends \view
{

  /**
   * Index
   * _________________________________________________________________
   */
  public function drawIndex(): void
  {
    $term = $this->getData('term');
    $results = (array) $this->getData('results');
    $locs = $this->getData('locs');
    $erg = '';

    $erg .= $this->openPage();

    $erg .= '<p>'.
              '<a href="index.php?ui=html4V">(Click here for the HTML4 version)</a>'.
            '</p>';

    $erg .= '<hr>';

    $erg .= '<p>'.
              '<form action="index.php" method="get">'.
                '<input type="hidden" name="hook" value="index">'.
                '<input type="hidden" name="ui" value="html2V">'.
                '<input type="text" name="term" value="'.$term.'">&nbsp;'.
                '<select name="loc">';

    foreach($locs as $locK => $locV)
    {
      $sel = ($locK == $this->stateParams['loc']) ? ' selected=selected' : '';
      $erg .= '<option value="'.$locK.'"'.$sel.'>'.$locV.'</option>';
    }

    $erg .= '</select>'.
            '<br><br>'.
            '<input type="submit">'.
            '</form>'.
            '</p>';

    $erg .= '<hr>';

    if (count($results) > 0)
    {
      foreach ($results as $result)
      {
        if (isset($result['title']) && isset($result['snippet']))
        {
          $erg .= '<p>';
          $erg .= $this->link(['hook' => 'fulltext', 'title' => $result['title']], $result['title']);
          $erg .= '<br>';
          $erg .= '...'.$result['snippet'].'...';
          $erg .= '</p>';
        }
      }
    }

    $erg .= $this->closePage();

    header('Content-Type: text/html; charset=iso-8859-1');
    echo $erg;
  }

  /**
   * Preview
   * _________________________________________________________________
   */
  public function drawFulltext(): void
  {
    $fulltext = $this->getData('fulltext');
    $numImages = (int) count($this->getData('images'));
    $title = $this->getData('title');
    $link = 'https://'.$this->getData('loc').'.wikipedia.org/wiki/'.str_replace(' ', '_', $title);
    $erg = '';

    $erg .= $this->openPage();
    $erg .= '<h3>'.$title.'</h3>';

    if ($numImages != 0)
    {
      $erg .= '<p>';
      $erg .= $this->link(['hook' => 'media', 'title' => $title], '(Load Images)');
      $erg .= '</p>';
    }

    $erg .= '<p>';
    $erg .= $fulltext;
    $erg .= '</p>';

    $erg .= '<p>';
    $erg .= '(Source:&nbsp;<a href="'.$link.'" target="_blank">'.$link.'</a>)';
    $erg .= '</p>';

    $erg .= $this->closePage();

    header('Content-Type: text/html; charset=iso-8859-1');
    echo $erg;
  }

  /**
   * Media
   * _________________________________________________________________
   */
  public function drawMedia(): void
  {
    $images = (array) $this->getData('images');
    $imgCount = count($images);
    $imgPerPage = 3;
    $startIdx = 0;
    $endIdx = 0;
    $page = (int) ($this->getData('page') != false) ? $this->getData('page') : 0;
    $numPages = 0;
    $erg = '';

    $erg .= $this->openPage();

    $erg .= '<h3>Images for "'.$this->getData('title').'"</h3>';

    if ($imgCount > 0)
    {
      $numPages = $imgCount / $imgPerPage;
      if ($imgCount % $imgPerPage != 0)
      {
        $numPages = ceil($numPages);
      }

      $startIdx = $page * $imgPerPage;
      $endIdx = $startIdx + $imgPerPage;

      for($i = $startIdx; $i < $endIdx; $i++)
      {
        if (isset($images[$i]))
        {
          $item = $images[$i];
          $erg .= '<p>';
          $erg .= '<img src="http://'.$_SERVER['HTTP_HOST'].'/geos/tools/2gif.php?file='.urlencode($item['url']).'&width=256"><br>';
          $erg .= $item['caption'];
          $erg .= '</p>';
        }
      }

      $erg .= '<p>';
      $erg .= '<b>Pages:</b>';
      for($i = 0; $i < $numPages; $i++)
      {
        if ($i != $page)
        {
          $erg .= ' '.$this->link(['hook' => 'media', 'title' => $this->getData('title'), 'page' => $i], $i+1).' ';
        }
        else
        {
          $erg .= ' '.($i+1).' ';
        }
      }
      $erg .= '</p>';
    }
    else
    {
      $erg .= '<p>No files.</p>';
    }

    $erg .= $this->closePage();

    header('Content-Type: text/html; charset=iso-8859-1');
    echo $erg;
  }

  /**
   * draw error page
   * ________________________________________________________________
   */
  public function drawErrorPage(Exception $e): void
  {
    $erg  = '';
    $erg .= '<!DOCTYPE html PUBLIC "-//IETF//DTD HTML 2.0//EN">';
    $erg .= '<html>';
    $erg .= '<head>';
    $erg .= '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">';
    $erg .= '<title>'.$this->getData('appName').'</title>';
    $erg .= '</head>';

    $erg .= '<body>';
    $erg .= '<h1>'.$this->getData('appName').'</h1>';
    $erg .= '<h3>Error:</h3>';
    $erg .= '<p>'.$e->getMessage().'</p>';
    $erg .= '</body>';

    $erg .= '</html>';

    header('Content-Type: text/html; charset=iso-8859-1');
    echo $erg;
  }

  /**
   * open page
   * ________________________________________________________________
   */
  protected function openPage(): string
  {
    $erg  = '';
    $erg .= '<!DOCTYPE html PUBLIC "-//IETF//DTD HTML 2.0//EN">';
    $erg .= '<html>';

    $erg .= '<head>';
    $erg .= '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">';
    $erg .= '<title>'.$this->getData('appName').'</title>';
    $erg .= '</head>';

    $erg .= '<body>';
    $erg .= '<h1>'.$this->getData('appName').'</h1>';
    $erg .= '<h3>'.$this->getData('headline').'</h3>';

    return $erg;
  }

  /**
   * close Page
   * ________________________________________________________________
   */
  protected function closePage(): string
  {
    $erg  = '';
    $erg .= '</body>';
    $erg .= '</html>';

    return $erg;
  }

}

?>
