<?php

class feedGenV extends view
{
  public function drawPage(string $viewFunc = '') : void
  {
    $html = '';

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

    header('Content-Type: text/html; charset=iso-8859-1');
    echo $html;
  }

  public function drawFeed() : void
  {
    $rss  = '';
    $rss .= '<?xml version="1.0" encoding="ISO-8859-1"?>'."\r\n";
    $rss .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'."\r\n";

    $rss .= '<channel>'."\r\n";
    $rss .= '<title>'.$this->getData('title').'</title>'."\r\n";
    $rss .= '<link><![CDATA['.$this->getData('homepage').']]></link>'."\r\n";
    $rss .= '<description>'.$this->getData('description').'</description>'."\r\n";
    $rss .= '<copyright>'.$this->getData('copyright').'</copyright>'."\r\n";

    $items = $this->getData('content');
    if ($items !== null)
    {
      foreach($items as $item)
      {
        $rss .= '<item>'."\r\n";
        $rss .= '<title>'.$item['title'].'</title>'."\r\n";
        $rss .= '<description>'.$item['description'].'</description>'."\r\n";
        $rss .= '<link><![CDATA['.$item['link'].']]></link>'."\r\n";
        $rss .= '<guid><![CDATA['.$item['link'].']]></guid>'."\r\n";
        $rss .= '<pubDate>'.date("D, d M Y H:i:s O").'</pubDate>'."\r\n";
        $rss .= '</item>'."\r\n";
      }
    }

    $rss .= '</channel>'."\r\n";
    $rss .= '</rss>';

    // , charset=ISO-8859-1
    header('Content-Type: application/xml');
    echo $rss;
  }

  public function drawErrorPage(Exception $e) : void
  {
    $html = '';
    $html = '<h3>Error: '.$e->getMessage().'</h3>';

    header('Content-Type: text/html; charset=iso-8859-1');
    echo $html;
  }

}
?>