<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/geos/lib/model.php');

class cachedRequestM extends model
{
  public $cacheTime = 20; // maximum age of cache: 20 minutes, 0 = disabled
  public $cacheDir = ''; // is set up at runtime
  public $cacheLifespan = 31; // clean every 31 days (or more)

  /**
   * set up cache dir
   * ________________________________________________________________
   */
  public function __construct()
  {
    $this->cacheDir = getPathFS(getProjectRoot().'/cache/');
    @mkdir($this->cacheDir);
  }

  /**
   * fetch a page / a feed... and cache it.
   * _________________________________________________________________
   */
  public function grab($url)
  {
    $fname = md5($url).'.tmp';
    $cacheFile = $this->cacheDir.$fname;
    $useCache = false;

    $this->clearCache();

    try
    {
      if (file_exists($cacheFile))
      {
        $fdate = filemtime($cacheFile);
        $tstmp = time();
        if (($tstmp - $fdate) < ($this->cacheTime * 60))
        { // use cache
          $useCache = true;
        }
        else
        { // cache file outdated
          $useCache = false;
        }
      }
      else
      { // cache does not exist yet
        $useCache = false;
      }

      // global override, for debugging purposes only
      if (defined('CACHE') && (CACHE == false))
      {
        $useCache = false;
      }

      if ($useCache == true)
      {
        $result = @file_get_contents($cacheFile);
      }
      else
      {
        $result = parent::grab($url);
        @file_put_contents($cacheFile, $result);
      }

      return $result;
    }
    catch (Exception $e)
    {
      throw($e);
    }
  }

  /**
   * clear cache every once in a while
   * __________________________________________________________________
   */
  protected function clearCache()
  {
    $cacheFlagFile = $this->cacheDir.'cache_cleared_flag.tmp';
    $fdate = (int) 0;
    $tstmp = time();
    $lifespan = $this->cacheLifespan * 24 * 60 * 60;

    if (file_exists($cacheFlagFile))
    {
      $fdate = filemtime($cacheFlagFile);
    }

    if (($tstmp - $fdate) >= $lifespan)
    {
      $files = scandir($this->cacheDir);
      foreach($files as $file)
      {
        if (($file != '.') and ($file != '..'))
        {
          if (($tstmp - filemtime($this->cacheDir.$file)) >= $lifespan)
          {
            unlink($this->cacheDir.$file);
          }
        }
      }

      file_put_contents($cacheFlagFile, $tstmp);
    }
  }

}

?>
