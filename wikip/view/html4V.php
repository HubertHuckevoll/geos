<?php

class html4V extends pView
{

  /**
   * draw frameset
   * _____________________________________________________________________
   */
  public function drawFrameset(): void
  {
    $erg  = '';
    $erg .= '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">'.
            '<html>'.
              '<head>'.
                '<title>'.$this->getData('appName').'</title>'.
                '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">'.
              '</head>'.
              '<frameset cols="20%,50%,30%" frameborder="0" framespacing="0">'.
                '<frame name="left"   scrolling="auto" src="'.$this->href(['hook' => 'index', 'ui' => 'html4V']).'">'.
                '<frame name="middle" scrolling="auto">'.
                '<frame name="right"  scrolling="auto">'.
              '</frameset>'.
              '<noframes>'.
                '<p>Your browser does not support frames. Use the non-frame version of this page.</p>'.
              '</noframes>'.
            '</html>';

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

    $erg .= '<table><tr><td>';
    $erg .= '<font face="'.$this->getData('font').'">';

    if ($numImages != 0)
    {
      $erg .= '<p>';
      $erg .= $this->link(['hook' => 'media', 'title' => $title], '(Load Images)', ['target' => 'right']);
      $erg .= '</p>';
    }

    $erg .= '<p>';
    $erg .= $fulltext;
    $erg .= '</p>';

    $erg .= '<p>';
    $erg .= '(Source:&nbsp;<a href="'.$link.'" target="_blank">'.$link.'</a>)';
    $erg .= '</p>';

    $erg .= '</font>';
    $erg .= '</td></tr></table>';

    $erg .= $this->closePage();

    header('Content-Type: text/html; charset=iso-8859-1');
    echo $erg;
  }

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

    $erg .= '<h3>'.$this->getData('appName').'</h3>';

    $erg .= '<table>';

    $erg .= '<tr><td valign="middle" width="30%">'.
              '<font face="'.$this->getData('font').'" size="0">'.
                '<a href="index.php" target="_top">(Click here for the HTML2 version)</a>'.
              '</font>'.
            '</td></tr>';

    $erg .= '<tr><td>&nbsp;</td></tr>'; // "Leerzeile"

    $erg .= '<tr><td>';
    $erg .= '<font face="'.$this->getData('font').'">'.
              '<form action="index.php" method="get" target="left">'.
                '<input type="hidden" name="hook" value="index">'.
                '<input type="hidden" name="ui" value="html4V">';
    $erg .= '<select name="loc">';
    foreach($locs as $locK => $locV)
    {
      $sel = ($locK == $this->stateParams['loc']) ? ' selected=selected' : '';
      $erg .= '<option value="'.$locK.'"'.$sel.'>'.$locV.'</option>';
    }
    $erg .= '</select>';
    $erg .= '<br>';

    $erg .= '<input type="text" name="term" size="20" value="'.$term.'">';
    $erg .= '<br>';
    $erg .= '<input type="submit">';
    $erg .= '</form>'.
            '</font>'.
            '</td>'.
            '</tr>';

    $erg .= '<tr><td>&nbsp;</td></tr>'; // "Leerzeile"

    if (count($results) > 0)
    {
      $erg .= '<tr><td>';
      $erg .= '<font face="'.$this->getData('font').'">';

      foreach ($results as $result)
      {
        if (isset($result['title']) && isset($result['snippet']))
        {
          $erg .= '<p>';
          $erg .= $this->link(['hook' => 'fulltext', 'title' => $result['title']], $result['title'], ['target' => 'middle']);
          $erg .= '<br>';
          $erg .= '...'.$result['snippet'].'...';
          $erg .= '</p>';
        }
      }

      $erg .= '</font>';
      $erg .= '</td></tr>';
    }

    $erg .= '</table>';

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

    $erg  = $this->openPage();

    $erg .= '<h3>Images for "'.$this->getData('title').'"</h3>';

    $erg .= '<table><tr><td>';
    $erg .= '<font face="'.$this->getData('font').'">';

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

    $erg .= '</font>';
    $erg .= '</td></tr></table>';

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
    $erg = '';
    $erg .= '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
    $erg .= '<html>';

    $erg .= '<head>';
    $erg .= '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">';
    $erg .= '<title>'.$this->getData('appName').'</title>';
    $erg .= '</head>';

    $erg .= '<body bgcolor="#FFFFFF" link="#0000FF" vlink="#0000FF">';
    $erg .= '<font face="'.$this->getData('font').'">';
    $erg .= '<h3>Error:</h3>';
    $erg .= '<p>';
    $erg .= $e->getMessage();
    $erg .= '</font>';
    $erg .= '</p>';
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
    $erg = '';
    $erg .= '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
    $erg .= '<html>';

    $erg .= '<head>';
    $erg .= '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">';
    $erg .= '<title>'.$this->getData('appName').'</title>';
    $erg .= '</head>';

    $erg .= '<body bgcolor="#FFFFFF" link="#0000FF" vlink="#0000FF">';
    $erg .= '<font face="'.$this->getData('font').'">';

    return $erg;
  }

  /**
   * close Page
   * ________________________________________________________________
   */
  protected function closePage(): string
  {
    $erg  = '';
    $erg .= '</font>';
    $erg .= '</body>';
    $erg .= '</html>';

    return $erg;
  }

}

?>
