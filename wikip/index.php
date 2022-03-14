<?php

require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/helpers.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/logger.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/control.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/model.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/view.php');
require_once('autoload.php');

/**
 * app controller
 * ________________________________________________________________
 */
class app extends control
{
  public $ui = '';
  public $appName = 'Wiki P.';
  public $font = 'Arial'; // Courier, Times New Roman, Arial => Arial looks best on GEOS
  public $loc = 'de';
  public $wp = null;
  public $locs = [
    "de" => "German",
    "en" => "English",
    "sv" => "Swedish",
    "es" => "Spanish",
    "it" => "Italian",
    "pt" => "Portuguesen",
    "fr" => "French",
    "pl" => "Polish"
  ];

  /**
   * Konstruktor
   * _________________________________________________________________
   */
  public function __construct()
  {
    $this->loc = (getReqVar('loc') != '') ? getReqVar('loc') : 'de';
    $vName = (getReqVar('ui')  != '') ? getReqVar('ui')  : 'html2V';

    parent::__construct();

    if (
         ($this->hookWasEmpty == true) &&
         ($vName == 'html4V')
       )
    {
      $this->view = new $vName();
      $this->view->setData('appName', $this->appName);
      $this->view->drawFrameset();
    }
    else
    {
      $this->view = new $vName();
      $this->wp = new wikipM($this->loc);

      $this->view->stateParams = ['ui' => $vName, 'loc' => $this->loc];
      $this->view->setData('appName', $this->appName);
      $this->view->setData('hook', $this->hook);
      $this->view->setData('font', $this->font);
    }
  }

  /**
   * Index - is called when no hook is provided,
   * shows search results as well
   * _________________________________________________________________
   */
  public function index()
  {
    try
    {
      $data = null;
      $term = getReqVar('term');

      $this->view->setData('locs', $this->locs);
      $this->view->setData('term', $term);

      if ($term != '')
      {
        $data = $this->wp->search($term);
        $this->view->setData('results', $data);
      }

      $this->view->drawPage('index');
    }
    catch(Exception $e)
    {
      $this->view->drawErrorPage($e);
    }
  }

  /**
   * Fulltext
   * _________________________________________________________________
   */
  public function fulltext()
  {
    try
    {
      $title = getReqVar('title');

      $images = [];

      $fulltext = $this->wp->fulltext($title);
      $images = $this->wp->media($title);

      $this->view->setData('loc', $this->loc);
      $this->view->setData('title', $title);
      $this->view->setData('fulltext', $fulltext);
      $this->view->setData('images', $images);

      $this->view->drawPage('fulltext');
    }
    catch(Exception $e)
    {
      $this->view->drawErrorPage($e);
    }
  }

  /**
   * fetch images
   * _________________________________________________________________
   */
  public function media()
  {
    try
    {
      $title = getReqVar('title');
      $page = getReqVar('page');

      $this->view->setData('page', $page);
      $this->view->setData('title', $title);

      $images = $this->wp->media($title);
      $this->view->setData('images', $images);

      $this->view->drawPage('media');
    }
    catch(Exception $e)
    {
      $this->view->drawErrorPage($e);
    }
  }
}

$app = new app();
$app->run();

?>
