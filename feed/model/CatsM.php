<?php

class CatsM extends model
{
  public $tsvF = '';
  public $tsvData = [];

  /**
   * Konstruktor
   * ________________________________________________________________
   */
  public function __construct($tsvF)
  {
    $this->tsvF = $tsvF;

    $tsvObj = new tsvM();
    $this->tsvData = $tsvObj->fetchTable($this->tsvF);
  }

  /**
   * get TSV data
   * ________________________________________________________________
   */
  public function getTableData()
  {
    return $this->tsvData['data'];
  }

  /**
   * Table Name
   * ________________________________________________________________
   */
  public function getTableName()
  {
    return $this->tsvData['name'];
  }

  /**
   * get Category names
   * ________________________________________________________________
   */
  public function getCatNames()
  {
    $catArray = [];

    foreach ($this->getTableData() as $entry)
    {
      if (!in_array($entry['category'], $catArray))
      {
        $catArray[] = $entry['category'];
      }
    }

    return $catArray;
  }

  /**
   * get category name for idx
   * ________________________________________________________________
   */
  public function getCatName($catIdx)
  {
    $cats = $this->getCatNames();
    $cat = $cats[$catIdx];

    return $cat;
  }

  /**
   * get feeds for category index
   * ________________________________________________________________
   */
  public function getFeedsForCatIdx($catIdx)
  {
    $cat = $this->getCatName($catIdx);

    foreach($this->getTableData() as $entry)
    {
      if ($entry['category'] == $cat)
      {
        $catEntries[] = $entry;
      }
    }

    return $catEntries;
  }

}

?>