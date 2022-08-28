<?php

require_once('helpers.php');

class control
{
  public $view = null;
  public $ui = 'generic';

  public $hook = null;
  public $hookWasEmpty = false;

  /**
   * Konstruktor
   * _________________________________________________________________
   */
  public function __construct()
  {
    // Set user hook
    if (getReqVar('hook') !== false)
    {
      $this->hook = getReqVar('hook');
    }
    else
    {
      $this->hook = 'index';
      $this->hookWasEmpty = true;
    }
  }

  /**
   * Execute a controller
   * _________________________________________________________________
   */
  public function exec(object $obj, string $method): mixed
  {
    if (method_exists($obj, $method))
    {
      return $obj->$method();
    }
    else
    {
      throw new Exception('Unknown function call "'.$method.'" for object "'.get_class($obj).'".');
    }
  }

  /**
   * run!
   * _________________________________________________________________
   */
  public function run()
  {
    try
    {
      $this->exec($this, $this->hook);
    }
    catch(Exception $e)
    {
      exit($e->getMessage()); // last resort: uncatched error
    }
  }

  /**
   * Index - is called when no hook is provided
   * _________________________________________________________________
   */
  public function index()
  {
  }
}

?>
