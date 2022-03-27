<?php
// Basics
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/helpers.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/logger.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/control.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/model.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/view.php');

// Advanced
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/tsvM.php');
//require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/GSheetsM.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/cachedRequestM.php');

// Models & Views
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/feedGen/model/feedGenM.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/feedGen/view/feedGenV.php');

// Flags
define('NO_CACHE', true);

/**
 * feed generator controller
 * ________________________________________________________________
 */
class feedGen extends control
{
  public $tsvF = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vRqO1JutfOToyPQaq-vCGNmVePhO7TOne-Ws_HidGMW17IvbEAqb_fb_tVgNBFY8Cdzl-4twJLRKwf_/pub?output=tsv';
  public $appName = 'feedGen';

  /**
   * Konstruktor
   * _________________________________________________________________
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * list available services
   * FIXME: make this a feed of feeds?
   * _________________________________________________________________
   */
  public function index()
  {
    try
    {
      $this->view = new feedGenV();

      $gs = new tsvM();
      $feedTable = $gs->fetchTable($this->tsvF);
      $feedTable = $feedTable['data'];

      $this->view->setData('table', $feedTable);
      $this->view->drawPage();
    }
    catch(Exception $e)
    {
      $this->view->drawErrorPage($e);
    }
  }

  /**
   * fetch / build an RSS feed
   * _________________________________________________________________
   */
  public function fetch()
  {
    try
    {
      $this->view = new feedGenV();
      $service = getReqVar('service');

      $gs = new tsvM();
      $feedTable = $gs->fetchTable($this->tsvF);
      $entries = $feedTable['data'];

      foreach($entries as $item)
      {
        if ($item['service'] == $service)
        {
          $fM = new feedGenM();
          libxml_use_internal_errors(true);
          $data = $fM->fetchContent($item['url'], $item['linkXpath'], $item['descXpath']);

          $this->view->setData('title', $item['title']);
          $this->view->setData('homepage', $item['url']);
          $this->view->setData('description', '<![CDATA[ Feed for "'.$item['url'].'" ]]>');
          $this->view->setData('feedUrl', getProjectRootURL().'/index.php?hook=fetch&service='.$service);
          $this->view->setData('copyright', '"feedGen", created by MeyerK, 2019ff');
          $this->view->setData('content', $data);

          $this->view->drawFeed();

          break;
        }
      }
    }
    catch(Exception $e)
    {
      $this->view->setData('title', 'Error');
      $this->view->setData('homepage', getProjectRootURL().'/index.php?hook=index');
      $this->view->setData('description', '<![CDATA['.$e->getMessage().']]>');
      $this->view->setData('feedUrl', getProjectRootURL().'/index.php?hook=index');
      $this->view->setData('copyright', '"feedGen" created by MeyerK, 2019ff');
      $this->view->setData('content', null);

      $this->view->drawFeed();
    }
  }
}

$app = new feedGen();
$app->run();

?>
