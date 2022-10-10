<?php

class pView
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
  public function addDataFromArray(array $data): void
  {
    $this->data = array_merge($this->data, (array) $data);
  }

  /**
   * set data key
   * _________________________________________________________________
   */
  public function setData(string $key, mixed $val): void
  {
    $this->data[$key] = $val;
  }

  /**
   * get data key
   * _________________________________________________________________
   */
  public function getData(string $key): mixed
  {
    return $this->data[$key] ?? '';
  }

  /**
   * reset data
   * _________________________________________________________________
   */
  public function resetData(): void
  {
    $this->data = [];  // model data
  }

  /**
   * render debugging fragment
   * _________________________________________________________________
   */
  public function debugVars(): string
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
   * draw the page (everything between openPage() and closePage())
   * ________________________________________________________________
   */
  public function drawPage()
  {
    $str  = '';
    $str .= $this->openPage();
    $str .= $this->closePage();

    echo $str;
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

  /**
   * open page
   * _____________________________________________________________________
   */
  protected function openPage(): string
  {
    $erg  = '';
    return $erg;
  }

  /**
   * close page
   * ________________________________________________________________
   */
  protected function closePage(): string
  {
    $erg  = '';
    return $erg;
  }

}

?>
