<?php
require_once('cachedRequestM.php');

class GSheetsM extends cachedRequestM
{

  /**
   * Konstruktor
   * ________________________________________________________________
   */
  public function __construct()
  {
    parent::__construct();
    libxml_use_internal_errors(true);
  }

  /**
   * fetch a GDocs table
   * ________________________________________________________________
   */
  public function fetchTable($gsheet)
  {
    $xp = null;
    $dom = null;
    $data = array();
    $nodes = null;
    $node = null;
    $tables = array();
    $entry = array();
    $html = false;

    try
    {
      $html = $this->grab('https://docs.google.com/spreadsheets/d/e/'.$gsheet.'/pubhtml');

      if ($html != '')
      {
        $dom = new DomDocument();
        $dom->loadHTML($html);
        $dom->preserveWhiteSpace = false;
        $xp = new DOMXpath($dom);

        // Grab title of the full table
        $nodes = $xp->query('//*[@id="doc-title"]');
        foreach ($nodes as $node)
        {
          $data['tableName'] = $this->Utf8ToIso($node->nodeValue);
        }

        // just one sheet
        if (strpos($data['tableName'], ':'))
        {
          $comps = explode(' : ', $data['tableName']);
          $data['tableName'] = $comps[0];
          $sheet0 = $comps[1];
          $tables['0'] = $sheet0;
        }
        else
        {
          // Grab sheet names
          $nodes = $xp->query("//*[@id='sheet-menu']//a");

          foreach($nodes as $node)
          {
            $id = $node->parentNode->getAttribute('id');
            $id = substr($id, strlen('sheet-button-'));

            $tables[$id] = $this->Utf8ToIso(trim($node->nodeValue));
          }
        }

        // find data for each sheet
        foreach($tables as $tableId => $tableName)
        {
          $entry = array();
          $entry['name'] = $this->Utf8ToIso(trim($tableName));

          $nodes = $xp->query("//*[@id='".$tableId."']//tr");

          $i = 0;
          foreach($nodes as $node)
          {
            if ($node->nodeValue != '')
            {
              if ($i == 0)
              {
                $colNames = array();
                $tds = $xp->query(".//td", $node);
                foreach ($tds as $td)
                {
                  $colNames[] = $this->Utf8ToIso(trim($td->nodeValue));
                }
              }
              else
              {
                $lineA = array();
                for ($z = 0; $z < count($colNames); $z++)
                {
                  $lineA[$colNames[$z]] = $this->Utf8ToIso(trim($node->childNodes->item($z+1)->nodeValue));
                }
                $entry['data'][] = $lineA;
              }
              $i++;
            }
          }

          $data['sheets'][] = $entry;
        }

        return $data;
      }
      else
      {
        throw new Exception('Requested GDocs Sheets Document was empty.');
      }
    }
    catch (Exception $e)
    {
      throw $e;
    }
  }
}

?>