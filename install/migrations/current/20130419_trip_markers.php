<?php

namespace candyCMS;

class MigrationScript {

  public static function run($oDb) {

    // first generate the new fields
    $sSQL =
        'ALTER TABLE ' . SQL_PREFIX . 'trip_markers ADD `lat` decimal(10,6) NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'trip_markers ADD `long` decimal(10,6) NOT NULL;' .
        'ALTER TABLE ' . SQL_PREFIX . 'trip_markers ADD `trip_id` int(11) NOT NULL;';

    $bNewCols = $oDb->exec($sSQL);

    if ($bNewCols === false)
      return false;

    // move the data
    $sSQL =
        'UPDATE ' . SQL_PREFIX . 'trip_markers AS tm JOIN ' . SQL_PREFIX . 'trip_points AS tp ON tp.id = tm.trippoint_id SET tm.lat = tp.lat, tm.long = tp.long, tm.trip_id = tp.trip_id;';

    $bMoveData = $oDb->exec($sSQL);

    if ($bMoveData === false)
      return false;

    // drop the old columns
    $sSQL =
        'ALTER TABLE ' . SQL_PREFIX . 'trip_markers DROP `trippoint_id`;' . 
        'ALTER TABLE ' . SQL_PREFIX . 'trip_markers DROP `active`;';

    $bDropCols = $oDb->exec($sSQL);

    if ($bDropCols === false)
      return false;

    return true;
  }
}

?>
