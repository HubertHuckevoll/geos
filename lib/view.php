<?php

class view
{
  public $stateParams = array();
  public $data = array();

  /**
   * Konstruktor
   * ________________________________________________________________
   */
	function __construct()
	{
	  //setlocale(LC_ALL, 'de_DE');
	}

  /**
   * add Data From Array
   * _________________________________________________________________
   */
  public function addDataFromArray($data)
  {
    $this->data = array_merge($this->data, (array) $data);
  }

  /**
   * set data key
   * _________________________________________________________________
   */
  public function setData($key, $val)
  {
    $this->data[$key] = $val;
  }

  /**
   * get data key
   * _________________________________________________________________
   */
  public function getData($key)
  {
    return $this->data[$key];
  }

  /**
   * reset data
   * _________________________________________________________________
   */
  public function reset()
  {
    $this->data = array();  // model data
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
   * create a link
   * pass get params, link text and attributes
   * ________________________________________________________________
   */
  public function link($paramsA, $textS, $attrA = array())
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
  public function href($paramsA)
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
   * output page - overwrite me
   * pass an Exception to create an error page
   * _________________________________________________________________
   */
  public function drawPage($e = null)
  {
    echo '';
  }

  /**
   * image proxy
   * ________________________________________________________________
   */
  public function imageProxy($img, $newWidth)
  {
    $img = 'http://'.$_SERVER['HTTP_HOST'].'/geos/tools/2gif.php?file='.urlencode($img).'&width='.$newWidth;
    return $img;
  }
}

?>
