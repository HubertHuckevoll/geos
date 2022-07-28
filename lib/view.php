<?php

class view
{
  public $stateParams = [];
  public $data = [];

  /**
   * Konstruktor
   * ________________________________________________________________
   */
	public function __construct()
	{
	  //setlocale(LC_ALL, 'de_DE');
	}

  /**
   * add Data From Array
   * _________________________________________________________________
   */
  public function addDataFromArray(array $data)
  {
    $this->data = array_merge($this->data, (array) $data);
  }

  /**
   * set data key
   * _________________________________________________________________
   */
  public function setData(string $key, $val)
  {
    $this->data[$key] = $val;
  }

  /**
   * get data key
   * _________________________________________________________________
   */
  public function getData(string $key)
  {
    return $this->data[$key] ?? '';
  }

  /**
   * reset data
   * _________________________________________________________________
   */
  public function resetData()
  {
    $this->data = [];  // model data
  }

  /**
   * render debugging fragment
   * _________________________________________________________________
   */
  public function debugVars()
  {
    $vars = (array) $this->getData('debug');
    $str = '';
    if (count($vars) > 0)
    {
      foreach($vars as $var => $val)
      {
        $str .= "<!-- ".$var.': '.$val." -->\r\n";
      }
    }

    return $str;
  }

  /**
   * Execute a partial view function
   * returns a html fragment
   * call this function from your drawPage function
   * and prepend and append the rest of the HTML there
   * _________________________________________________________________
   */
  public function exec(string $viewFunc): string
  {
    if (method_exists($this, $viewFunc))
    {
      return $this->$viewFunc();
    }
    else
    {
      throw new Exception('Unknown function call "'.$viewFunc.'" for object "'.get_class($this).'".');
    }
  }

  /**
   * output page - overwrite me!
   * prepend and append boilerplate html
   * _________________________________________________________________
   */
  public function drawPage(string $viewFunc = ''): void
  {
    // html / head / body / sidebar...
    echo $this->exec($viewFunc);
    // close everything
  }

  /**
   * error version for single class views
   * ________________________________________________________________
   */
  public function drawErrorPage(Exception $e): void
  {
    echo $e->getMessage();
  }

  /**
   * create a link
   * pass get params, link text and attributes
   * ________________________________________________________________
   */
  public function link(array $paramsA, string $textS, array $attrA = []): string
  {
    $erg = '';
    $attrs = '';
    $href = $this->href($paramsA);

    if (count($attrA) > 0)
    {
      foreach ($attrA as $attrK => $attrV)
      {
        $attrs .= ' '.$attrK.'="'.$attrV.'"';
      }
    }

    $erg .= '<a href="'.$href.'"'.$attrs.'>'.$textS.'</a>';

    return $erg;
  }

  /**
   * make a good href
   * add state params to every request
   * ___________________________________________________________________
   */
  public function href(array $paramsA): string
  {
    $paramsA = array_merge($paramsA, $this->stateParams);

    $href = 'index.php';
    $max = count($paramsA);
    $i = 0;

    if ($max > 0)
    {
      $href .= '?';
    }

    foreach ($paramsA as $paramK => $paramV)
    {
      $href .= $paramK.'='.urlencode($paramV);
      if ($i < ($max-1)) {
        $href .= '&amp;';
      }
      $i++;
    }

    return $href;
  }

  /**
   * image proxy
   * ________________________________________________________________
   */
  public function imageProxy(string $img, int $newWidth): string
  {
    $img = 'http://'.$_SERVER['HTTP_HOST'].'/geos/tools/2gif.php?file='.urlencode($img).'&width='.(string) $newWidth;
    return $img;
  }
}

?>
