<?php
// Basics
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/helpers.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/logger.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/control.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/model.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/view.php');

// Advanced
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/GSheetsM.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/cachedRequestM.php');

// Models & Views
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/feedGen/model/feedGenM.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/feedGen/view/feedGenIndexV.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/feedGen/view/feedGenFeedV.php');

// Flags
define('NO_CACHE', true);

/**
 * feed generator controller
 * ________________________________________________________________
 */
class feedGen extends control
{
  public $gsheet = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vRqO1JutfOToyPQaq-vCGNmVePhO7TOne-Ws_HidGMW17IvbEAqb_fb_tVgNBFY8Cdzl-4twJLRKwf_/pubhtml';
  public $appName = 'feedGen';

  /**
   * Konstruktor
   * _________________________________________________________________
   */
  public function __construct()
  {
    parent::__construct();

    // extract the sheet id
    if (preg_match("/e\/(.*)\/pubhtml/", $this->gsheet, $matches) == 1)
    {
      if (isset($matches[1]))
      {
        $this->gsheet = $matches[1];
      }
    }
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
      $this->view = new feedGenIndexV();

      $gs = new GSheetsM();
      $feedTable = $gs->fetchTable($this->gsheet);

      $this->view->setData('sheets', $feedTable['sheets']);
      $this->view->drawPage();
    }
    catch(Exception $e)
    {
      $this->view->drawPage($e);
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
      $this->view = new feedGenFeedV();
      $service = getReqVar('service');

      $gs = new GSheetsM();
      $feedTable = $gs->fetchTable($this->gsheet);

      foreach($feedTable['sheets'] as $sheet)
      {
        foreach($sheet['data'] as $item)
        {
          if ($item['service'] == $service)
          {
            $fM = new feedGenM();
            $data = $fM->fetchContent($item['url'], $item['linkXpath'], $item['descXpath']);

            $this->view->setData('title', $item['title']);
            $this->view->setData('homepage', $item['url']);
            $this->view->setData('description', '<![CDATA[ Feed for "'.$item['url'].'" ]]>');
            $this->view->setData('feedUrl', getProjectRootURL().'/index.php?hook=fetch&service='.$service);
            $this->view->setData('copyright', '"feedGen" created by MeyerK, 2019ff');
            $this->view->setData('content', $data);

            $this->view->drawPage();

            break;
          }
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

      $this->view->drawPage($e);
    }
  }
}

$app = new feedGen();
$app->run();

?>
