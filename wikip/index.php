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
  public $locs = array(
    "de" => "German",
    "en" => "English",
    "sv" => "Swedish",
    "es" => "Spanish",
    "it" => "Italian",
    "pt" => "Portuguesen",
    "fr" => "French",
    "pl" => "Polish"
  );

  /**
   * Konstruktor
   * _________________________________________________________________
   */
  public function __construct()
  {
    $this->ui =  (getReqVar('ui')  != '') ? getReqVar('ui')  : 'html2V';
    $this->loc = (getReqVar('loc') != '') ? getReqVar('loc') : 'de';
    $vname = '';

    parent::__construct();

    if ($this->hookWasEmpty == true)
    {
      if ($this->ui == 'html4V')
      {
        $vName = 'framesetV';
      }
      else
      {
        $vName = 'indexV';
      }
    }
    else
    {
      $vName = $this->hook.'V';
    }

    $vName = $this->ui."\\".$vName;
    $this->view = new $vName();

    $this->wp = new wikipM($this->loc);

    $this->view->stateParams = array('ui' => $this->ui, 'loc' => $this->loc);
    $this->view->setData('appName', $this->appName);
    $this->view->setData('hook', $this->hook);
    $this->view->setData('font', $this->font);
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

      $this->view->drawPage();
    }
    catch(Exception $e)
    {
      $this->view->drawPage();
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

      $images = array();

      $fulltext = $this->wp->fulltext($title);
      $images = $this->wp->media($title);

      $this->view->setData('loc', $this->loc);
      $this->view->setData('title', $title);
      $this->view->setData('fulltext', $fulltext);
      $this->view->setData('images', $images);

      $this->view->drawPage();
    }
    catch(Exception $e)
    {
      $this->view->drawPage($e);
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

      $this->view->drawPage();
    }
    catch(Exception $e)
    {
      $this->view->drawPage($e);
    }
  }
}

$app = new app();
$app->run();

?>
