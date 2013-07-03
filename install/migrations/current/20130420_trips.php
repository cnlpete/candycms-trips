<?php

namespace candyCMS;

use PDO;

class MigrationScript {

  public static function run($oDb) {

    // first generate the new table
    $sSQL = 'CREATE TABLE `' . SQL_PREFIX . 'trip_polylines` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `trip_id` int(11) NOT NULL,
              `author_id` int(11) NOT NULL,
              `date` datetime NOT NULL,
              `latlngs` text NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;';

    $bNewCols = $oDb->exec($sSQL);
    if ($bNewCols === false)
      return false;

    // move the data
    $sSQL = "SELECT tp.* FROM " . SQL_PREFIX . "trip_points tp
              ORDER BY tp.position ASC,
                tp.date ASC,
                tp.id ASC;";
    $oQuery = $oDb->prepare($sSQL);
    $oQuery->execute();
    $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);

    $aAuthors = array();
    $aDates = array();
    $aResults = array();
    foreach ($aResult as $aRow) {
      if (!$aResults[$aRow['trip_id']])
        $aResults[$aRow['trip_id']] = array();
      $aResults[$aRow['trip_id']][] = array('lat' => $aRow['lat'], 'lng' => $aRow['long']);
      if (!$aAuthors[$aRow['trip_id']])
        $aAuthors[$aRow['trip_id']] = $aRow['author_id'];
      if (!$aDates[$aRow['trip_id']])
        $aDates[$aRow['trip_id']] = $aRow['date'];
    }

    $sSQL = '';
    foreach ($aResults as $iTripId => $aRow) {
      $sSQL .= 'INSERT INTO `' . SQL_PREFIX . 'trip_polylines` (`trip_id`, `author_id`, `date`, `latlngs`) VALUES ';
      $sSQL .= "(" . $iTripId . ", " . $aAuthors[$iTripId] . ", '" . $aDates[$iTripId] . "', '" . json_encode($aRow) . "');";
    }
    $bMoveData = $oDb->exec($sSQL);

    if ($bMoveData === false)
      return false;

    // drop the old table
    $sSQL = "DROP TABLE " . SQL_PREFIX . "trip_points";
    $bDropCols = $oDb->exec($sSQL);
    if ($bDropCols === false)
      return false;

    return true;
  }
}

