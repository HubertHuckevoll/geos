<?php

namespace html4V;

class previewArticleV extends html4V
{

  /**
   * Preview
   * _________________________________________________________________
   */
  public function renderMainContent()
  {
    $article = $this->getData('article');

    $erg = '<body bgcolor="#FFFFFF" link="#0000FF" vlink="#0000FF">'.
              '<table border="0" width="95%" cellpadding="0" cellmargin="0">'. // the table is a hack for the GEOS Browser!
              '<tr>'.
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

}

?>
