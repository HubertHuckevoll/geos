<?php
require_once('cachedRequestM.php');

class tsvM extends cachedRequestM
{

  /**
   * Konstruktor
   * ________________________________________________________________
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * fetch a GDocs table
   * ________________________________________________________________
   */
  public function fetchTable($tsvURL)
  {
    $tables = array();
    $fTable = array();
    $entry = array();
    $rawData = '';

    try
    {
      $rawData = $this->grab($tsvURL);

      if ($rawData != '')
      {
        $lines = str_getcsv($rawData, "\n"); // parse the rows
        $firstLine = array_shift($lines);
        $keys = str_getcsv($firstLine, "\t");

        foreach($lines as &$line)
        {
          $entry = array();
          $lineArr = str_getcsv($line, "\t"); // parse the items in rows
          for ($i = 0; $i < count($keys); $i++)
          {
            if ($i == 0)
            {
              $category = $lineArr[$i];
            }
            else
            {
              $entry[$keys[$i]] = $lineArr[$i];
            }
          }
          $tables[$category][] = $entry;
        }

        $fTable['tableName'] = substr($tsvURL, strrpos($tsvURL, '/')+1);
        foreach($tables as $tableName => $table)
        {
          $fTable['sheets'][] = array('name' => $tableName, 'data' => $table);
        }

        return $fTable;
      }
      else
      {
        throw new Exception('Requested TSV file was empty.');
      }
    }
    catch (Exception $e)
    {
      throw $e;
    }
  }
}

?>