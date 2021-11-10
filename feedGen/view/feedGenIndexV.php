<?php

class feedGenIndexV extends view
{
  public function drawPage($e = null)
  {
    $html = '';

    if ($e == null)
    {
      $html .= '<!DOCTYPE html PUBLIC "-//IETF//DTD HTML 2.0//EN">';
      $html .= '<html>';
      $html .= '<head>';
      $html .= '<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">'; //this must also be set in the header...
      $html .= '<title>feedGen</title>';
      $html .= '</head>';
      $html .= '<body bgcolor="#FFFFFF" link="#0000FF" vlink="#0000FF">';
      $html .= '<h1>feedGen</h1>';
      $html .= '<hr>';

      foreach($this->getData('sheets') as $sheet)
      {
        $html .= '<h3>'.$sheet['name'].'</h3>';
        $html .= '<ul>';
        foreach($sheet['data'] as $service)
        {
          $html .= '<li><a href="index.php?hook=fetch&service='.urlencode($service['service']).'" target="_blank">'.$service['title'].'</a></li>';
        }
        $html .= '</ul>';
      }

      $html .= '</ul>';
      $html .= '<hr>';
      $html .= '<h5>by MeyerK, 2019ff</h5>';
      $html .= '</body>';
    }
    else
    {
      $html = '<h3>Error: '.$e->getMessage().'</h3>';
    }

    header('Content-Type: text/html; charset=iso-8859-1');
    echo $html;
  }

}
?>