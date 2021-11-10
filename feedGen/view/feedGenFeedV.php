<?php

class feedGenFeedV extends view
{
  public function drawPage($e = null)
  {
    $rss  = '';
    $rss .= '<?xml version="1.0" encoding="ISO-8859-1"?>'."\r\n";
    $rss .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'."\r\n";

    $rss .= '<channel>'."\r\n";
    $rss .= '<title>'.$this->getData('title').'</title>'."\r\n";
    $rss .= '<link><![CDATA['.$this->getData('homepage').']]></link>'."\r\n";
    $rss .= '<description>'.$this->getData('description').'</description>'."\r\n";
    $rss .= '<copyright>'.$this->getData('copyright').'</copyright>'."\r\n";
    $rss .= '<![CDATA[<atom:link href="'.$this->getData('feedUrl').'" rel="self" type="application/rss+xml"/>]]>'."\r\n";

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
    $rss .= '</rss>'."\r\n";

    header('Content-Type: text/xml, charset=ISO-8859-1');
    echo $rss;
  }
}
?>