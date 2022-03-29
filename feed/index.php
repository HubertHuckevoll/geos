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
    $this->tsv   = getReqVar('tsv');
    $this->ui    = (getReqVar('ui')  != '') ? getReqVar('ui')  : 'html2V';
    $this->uim   = (getReqVar('uim') != '') ? getReqVar('uim') : 'l';
    $this->iU    = (getReqVar('iU')  != '') ? getReqVar('iU')  : IMAGE_USE_NONE;
    $hook        = getReqVar('hook');
    $matches     = [];

    if (($this->tsv == false) || ($hook == 'setup'))
    {
      // Set login view
      parent::__construct();
      $this->view = new loginV();
      $this->view->stateParams = ['ui' => $this->ui, 'uim' => $this->uim, 'tsv' => $this->tsv, 'iU' => $this->iU];
      $this->view->setData('appName', $this->appName);
    }
    else
    {
      // other views
      parent::__construct();
      $this->view = new $this->ui();
      $this->view->stateParams = ['ui' => $this->ui, 'uim' => $this->uim, 'tsv' => $this->tsv, 'iU' => $this->iU];
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
          $gs = new CatsM($this->tsv);
          $this->view->setData('tsvName', $gs->getTableName());
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
      $gs = new CatsM($this->tsv);
      $categories = $gs->getCatNames();

      $this->view->setData('headline', 'Categories');
      $this->view->setData('categories', $categories);

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

      $gs        = new CatsM($this->tsv);
      $feeds     = $gs->getFeedsForCatIdx($tableIdx);
      $category  = $gs->getCatName($tableIdx);
      $tableName = $gs->getTableName();

      $this->view->setData('tsvName', $tableName);
      $this->view->setData('headline', $category);

      $this->view->setData('tableIdx', $tableIdx);
      $this->view->setData('category', $category);
      $this->view->setData('feeds', $feeds);

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

      $gs        = new CatsM($this->tsv);
      $feeds     = $gs->getFeedsForCatIdx($tableIdx);
      $feed      = $feeds[$feedIdx];
      $tableName = $gs->getTableName();
      $category  = $gs->getCatName($tableIdx);

      $feedObj  = new FeedsM();
      $feedData = $feedObj->fetchRSS($feed['url']);

      $this->view->setData('feedURL', $feed['url']);
      $this->view->setData('tsvName', $tableName);
      $this->view->setData('tableIdx', $tableIdx);
      $this->view->setData('headline', $feedData['meta']['title']);

      $this->view->setData('category', $category);
      $this->view->setData('feedIdx', $feedIdx);
      $this->view->setData('feedName', $feedData['meta']['title']);
      $this->view->setData('feedData', $feedData);

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

      $gs        = new CatsM($this->tsv);
      $feeds     = $gs->getFeedsForCatIdx($tableIdx);
      $feed      = $feeds[$feedIdx];
      $tableName = $gs->getTableName();
      $category  = $gs->getCatName($tableIdx);
      $xpath     = $feed['xpath'];
      $url       = $feed['url'];

      $feedObj   = new FeedsM();
      $feedData  = $feedObj->fetchRSS($feed['url']);
      $feedName  = $feed['service'];

      $c               = new Scraper2M();
      $article         = $c->fetchContent($feedData['data'][$articleIdx]['link'], $xpath);
      $articleFullLink = $feedData['data'][$articleIdx]['link'];

      $this->view->setData('debug', ['xpath' => $xpath, 'feedURL' => $url, 'pageURL' => $articleFullLink]);
      $this->view->setData('tsvName', $tableName);
      $this->view->setData('headline', $article['meta']['title']);

      $this->view->setData('article', $article);
      $this->view->setData('tableIdx', $tableIdx);
      $this->view->setData('tableName', $tableName);
      $this->view->setData('category', $category);
      $this->view->setData('feedIdx', $feedIdx);
      $this->view->setData('feedName', $feedName);
      $this->view->setData('articleFullLink', $articleFullLink);

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
