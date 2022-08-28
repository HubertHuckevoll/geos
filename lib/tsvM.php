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
   * fetch a TSV table
   * ________________________________________________________________
   */
  public function fetchTable(string $tsvURL): array
  {
    $tableName = $this->getTableName($tsvURL);
    $table = [];
    $entry = [];
    $rawData = '';

    try
    {
      $rawData = $this->grab($tsvURL);

      if ($rawData != '')
      {
        $table['name'] = $tableName;
        $lines = str_getcsv($rawData, "\n"); // parse the rows
        $firstLine = array_shift($lines);
        $keys = str_getcsv($firstLine, "\t");

        foreach($lines as &$line)
        {
          $entry = [];
          $lineArr = str_getcsv($line, "\t"); // parse the items in rows
          for ($i = 0; $i < count($keys); $i++)
          {
            if (isset($lineArr[$i]))
            {
              $entry[$keys[$i]] = $this->Utf8ToIso($lineArr[$i]);
            }
          }

          $table['data'][] = $entry;
        }

        return $table;
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

  public function getTableName(string $tsvURL): string
  {
    $tableName = substr($tsvURL, strrpos($tsvURL, '/') + 1);
    return $tableName;
  }
}

?>