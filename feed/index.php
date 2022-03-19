<?php

// Helpers
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/helpers.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/logger.php');

// Basics
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/control.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/model.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/view.php');

// Autoloader
require_once('autoload.php');

// Flags
define('CACHE', true);
define('STATS', true);
//error_reporting(E_ALL);

define('IMAGE_USE_NONE',   0); // no images
define('IMAGE_USE_SOME',   1); // header image for article
define('IMAGE_USE_MEDIUM', 2); // header image for article, feed logo
define('IMAGE_USE_ALL',    3); // header image, feed logo, article images in feed view


/**
 * feed controller
 * ________________________________________________________________
 */
class feed extends control
{
  public $tableIdx = '';
  public $feedIdx = '';
  public $ui = '';
  public $uim = '';
  public $iU = '';

  public $tsv = ''; // pubhtml: https://docs.google.com/spreadsheets/d/e/2PACX-1vTm6Ks8tSwkcgTomyM7Q-EBN24FQB8hqeTNqObrSZ2etYEhbEXC5xdm5g-OyldWWZljJgi8teqXFnyz/pubhtml
                    // source: https://docs.google.com/spreadsheets/d/1xEbY3h-U2jYf6UyV7y9wNNS6aXerNne-HZg3DG6Xt_A/edit?usp=sharing
  public $appName = 'feed';
  public $font = 'Arial'; // Courier, Times New Roman, Arial

  /**
   * Konstruktor
   * _________________________________________________________________
   */
  public function __construct()
  {
    $this->tsv = getReqVar('tsv');
    $this->ui = (getReqVar('ui') != '') ? getReqVar('ui') : 'html2V';
    $this->uim = (getReqVar('uim') != '') ? getReqVar('uim') : 'l';
    $this->iU = (getReqVar('iU') != '') ? getReqVar('iU') : IMAGE_USE_NONE;
    $hook = getReqVar('hook');
    $matches = array();

    if (($this->tsv == false) || ($hook == 'setup'))
    {
      // Set login view
      parent::__construct();
      $this->view = new loginV();
      $this->view->stateParams = array('ui' => $this->ui, 'uim' => $this->uim, 'tsv' => $this->tsv, 'iU' => $this->iU);
      $this->view->setData('appName', $this->appName);
    }
    else
    {
      // other views
      parent::__construct();
      $this->view = new $this->ui();
      $this->view->stateParams = array('ui' => $this->ui, 'uim' => $this->uim, 'tsv' => $this->tsv, 'iU' => $this->iU);
      $this->view->setData('appName', $this->appName);
      $this->view->setData('hook', $this->hook);
      $this->view->setData('font', $this->font);
      $this->view->setData('uim', $this->uim);
    }
  }

  /**
   * Setup gdoc and view
   * _________________________________________________________________
   */
  public function setup()
  {
    $this->view->drawPage();
  }

  /**
   * Index - is called when no hook is provided
   * FIXME => this should be view-independent...?
   * _________________________________________________________________
   */
  public function index()
  {
    try
    {
      if ($this->tsv == false)
      { // draw setup screen
        $this->setup();
      }
      else
      { // draw either index (categories) or html4 frameset, which itself loads the frames...
        if ($this->ui == 'html4V')
        {
          // we need tableName for the frameset - so we have to make this costly request
          $gs = new tsvM();
          $feedTable = $gs->fetchTable($this->tsv);
          $this->view->setData('tsvName', $feedTable['tableName']);
          $this->view->drawFrameset();
        }
        else
        {
          $this->categories();
        }
      }
    }
    catch(Exception $e)
    {
      $this->view->drawErrorPage($e);
    }
  }

  /**
   * categories of the table
   * _________________________________________________________________
   */
  public function categories()
  {
    try
    {
      $gs = new tsvM();
      $feedTable = $gs->fetchTable($this->tsv);

      $this->view->setData('headline', 'Categories');
      $this->view->setData('categories', $feedTable);
      $this->view->setData('tsvName', $feedTable['tableName']);

      $this->view->drawPage('categories');
    }
    catch(Exception $e)
    {
      $this->view->drawErrorPage($e);
    }

  }

  /**
   * feeds within one category of the table
   * _________________________________________________________________
   */
  public function feedsForCat()
  {
    try
    {
      $tableIdx = getReqVar('tableIdx');

      $gs = new tsvM();
      $feedTable = $gs->fetchTable($this->tsv);
      $table     = $feedTable['sheets'][$tableIdx]['data'];
      $tableName = $feedTable['sheets'][$tableIdx]['name'];

      $this->view->setData('tsvName', $feedTable['tableName']);
      $this->view->setData('tableIdx', $tableIdx);
      $this->view->setData('tableName', $tableName);
      $this->view->setData('headline', $tableName);
      $this->view->setData('services', $table);

      $this->view->drawPage('feedsForCat');
    }
    catch(Exception $e)
    {
      $this->view->drawErrorPage($e);
    }

  }

  /**
   * articles within a feed
   * _________________________________________________________________
   */
  public function articlesForFeed()
  {
    try
    {
      $tableIdx = getReqVar('tableIdx');
      $feedIdx  = getReqVar('feedIdx');

      $gs = new tsvM();
      $feedTable = $gs->fetchTable($this->tsv);
      $table     = $feedTable['sheets'][$tableIdx]['data'];
      $tableName = $feedTable['sheets'][$tableIdx]['name'];

      $feedObj = new FeedsM();
      $feed = $feedObj->fetchRSS($table[$feedIdx]['url']);

      $this->view->setData('feedURL', $table[$feedIdx]['url']);
      $this->view->setData('tsvName', $feedTable['tableName']);
      $this->view->setData('tableIdx', $tableIdx);
      $this->view->setData('tableName', $tableName);
      $this->view->setData('feedIdx', $feedIdx);
      $this->view->setData('feedName', $feed['meta']['title']);
      $this->view->setData('headline', $feed['meta']['description']);
      $this->view->setData('articles', $feed);

      $this->view->drawPage('articlesForFeed');
    }
    catch(Exception $e)
    {
      $this->view->drawErrorPage($e);
    }

  }

  /**
   * previewArticle
   * _________________________________________________________________
   */
  public function previewArticle()
  {
    try
    {
      $tableIdx    = getReqVar('tableIdx');
      $feedIdx     = getReqVar('feedIdx');
      $articleIdx  = getReqVar('articleIdx');

      $gs = new tsvM();
      $feedTable = $gs->fetchTable($this->tsv);
      $table     = $feedTable['sheets'][$tableIdx]['data'];
      $tableName = $feedTable['sheets'][$tableIdx]['name'];
      $xpath     = $table[$feedIdx]['xpath'];
      $url       = $table[$feedIdx]['url'];

      $feedObj = new FeedsM();
      $feed = $feedObj->fetchRSS($url);
      $feedName = $table[$feedIdx]['service'];

      $c = new Scraper2M();
      $article = $c->fetchContent($feed['data'][$articleIdx]['link'], $xpath);

      $this->view->setData('debug', array('xpath' => $xpath, 'feedURL' => $url, 'pageURL' => $feed['data'][$articleIdx]['link']));
      $this->view->setData('tsvName', $feedTable['tableName']);
      $this->view->setData('article', $article);
      $this->view->setData('tableIdx', $tableIdx);
      $this->view->setData('tableName', $tableName);
      $this->view->setData('feedIdx', $feedIdx);
      $this->view->setData('feedName', $feedName);
      $this->view->setData('headline', $feed['data'][$articleIdx]['title']);
      $this->view->setData('articleFullLink', $feed['data'][$articleIdx]['link']);

      $this->view->drawPage('previewArticle');
    }
    catch(Exception $e)
    {
      $this->view->drawErrorPage($e);
    }
  }

}

$app = new feed();
$app->run();

?>
