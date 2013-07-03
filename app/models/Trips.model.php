<?php
/**
 * Copyright (C) Hauke Schade, 2013

 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
**/
namespace CandyCMS\Models;

use CandyCMS\core\Helpers\AdvancedException;
use CandyCMS\core\Helpers\Helper;
use CandyCMS\core\Helpers\Page;
use PDO;

class Trips extends \CandyCMS\core\Models\Main {

  public function getId($iId, $bUpdate = false) {
    $sType = $this->_aRequest['triptype'];
    switch ($sType) {
      case 'polyline':
        $sTable = 'trip_polylines';
      break;
      case 'marker':
        $sTable = 'trip_markers';
      break;
      default:
        $sTable = 'trips';
      break;
    }
    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        t.*,
                                        UNIX_TIMESTAMP(t.date) as date,
                                        u.id AS user_id,
                                        u.name AS user_name,
                                        u.surname AS user_surname,
                                        u.email AS user_email
                                      FROM " . SQL_PREFIX . $sTable . " t
                                      LEFT JOIN " . SQL_PREFIX . "users u
                                      ON t.author_id=u.id
                                      WHERE t.id = :id
                                      LIMIT 1");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      $oQuery->execute();

      $aRow = $oQuery->fetch(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth('1000 - ' . $p->getMessage());
      exit('SQL error.');
    }

    if ($bUpdate === true)
      $aRow = $this->_formatForUpdate($aRow);
    else {
      $aRow = $this->_formatForOutput(
                        $aRow,
                        array('id', 'author_id'),
                        array('active'),
                        'trips');
    }
    return $aRow;
  }

  public function getTrip($iId) {
    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        t.*,
                                        UNIX_TIMESTAMP(t.date) as date,
                                        UNIX_TIMESTAMP(t.start_date) as start_date,
                                        UNIX_TIMESTAMP(t.end_date) as end_date,
                                        u.id AS user_id,
                                        u.name AS user_name,
                                        u.surname AS user_surname,
                                        u.email AS user_email
                                      FROM " . SQL_PREFIX . "trips t
                                      LEFT JOIN " . SQL_PREFIX . "users u
                                      ON t.author_id=u.id
                                      WHERE t.id = :id
                                      LIMIT 1");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      $oQuery->execute();

      $aRow = $oQuery->fetch(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth('1001 - ' . $p->getMessage());
      exit('SQL error.');
    }

    # Format the data
    $aRow = $this->_formatForOutput(
                      $aRow,
                      array('id', 'author_id'),
                      array('active'),
                      'trips');
    $this->_formatDates($aRow, 'start_date');
    $this->_formatDates($aRow, 'end_date');

    return $aRow;
  }
  
  public function getTrips($iLimit = 10) {
    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        t.*,
                                        UNIX_TIMESTAMP(t.date) as date,
                                        UNIX_TIMESTAMP(t.start_date) as start_date,
                                        UNIX_TIMESTAMP(t.end_date) as end_date,
                                        u.id AS user_id,
                                        u.name AS user_name,
                                        u.surname AS user_surname,
                                        u.email AS user_email
                                      FROM " . SQL_PREFIX . "trips t
                                      LEFT JOIN " . SQL_PREFIX . "users u
                                      ON t.author_id=u.id
                                      ORDER BY t.date DESC
                                      LIMIT :limit");

      $oQuery->bindParam('limit', $iLimit, PDO::PARAM_INT);
      $oQuery->execute();

      $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth('1002 - ' . $p->getMessage());
      exit('SQL error.');
    }

    # Format the data
    foreach ($aResult as $iKey => $aRow) {
      $aResult[$iKey] = $this->_formatForOutput(
              $aRow,
              array('id', 'author_id'),
              array('active'),
              'trips');
      $this->_formatDates($aResult[$iKey], 'start_date');
      $this->_formatDates($aResult[$iKey], 'end_date');
    }

    if ($iLimit == 1)
      return $aResult[0];
    else
      return $aResult;
  }
  
  public function getTripPolylines($iId, $bFormat = true) {
    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        tp.*,
                                        UNIX_TIMESTAMP(tp.date) as date,
                                        u.id AS user_id,
                                        u.name AS user_name,
                                        u.surname AS user_surname,
                                        u.email AS user_email
                                      FROM " . SQL_PREFIX . "trip_polylines tp
                                      LEFT JOIN " . SQL_PREFIX . "users u
                                      ON tp.author_id=u.id
                                      WHERE tp.trip_id = :id
                                      ORDER BY tp.date ASC");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      $oQuery->execute();

      $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth('1003 - ' . $p->getMessage());
      exit('SQL error.');
    }

    if ($bFormat) {
      # Format the data
      foreach ($aResult as $iKey => $aRow) {
        $aResult[$iKey] = $this->_formatForOutput(
                $aRow,
                array('id', 'author_id'),
                array(),
                'trips');
        // keep latlngs in json format since that allows for easier tpl handling :)
        $aResult[$iKey]['url']          = '/' . $this->_sController . '/' . $this->_aRequest['id'];
        $aResult[$iKey]['url_update']   = '';
        $aResult[$iKey]['url_destroy']  = '';
      }
    }

    return $aResult;
  }

  public function getTripMarkers($iId, $bFormat = true) {
    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        tm.*,
                                        UNIX_TIMESTAMP(tm.date) as date,
                                        u.id AS user_id,
                                        u.name AS user_name,
                                        u.surname AS user_surname,
                                        u.email AS user_email
                                      FROM " . SQL_PREFIX . "trip_markers tm
                                      LEFT JOIN " . SQL_PREFIX . "users u
                                      ON tm.author_id=u.id
                                      WHERE tm.trip_id = :id
                                      ORDER BY
                                        tm.date DESC,
                                        tm.id DESC");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      $oQuery->execute();

      $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth('1004 - ' . $p->getMessage());
      exit('SQL error.');
    }

    if ($bFormat) {
      # Format the data
      foreach ($aResult as $iKey => $aRow) {
        $aResult[$iKey] = $this->_formatForOutput(
                $aRow,
                array('id', 'author_id'),
                array(),
                'trips');
        $aResult[$iKey]['url']          = '/' . $this->_sController . '/' . $iId;
        $aResult[$iKey]['url_clean']    = '/' . $this->_sController . '/' . $iId;
        $aResult[$iKey]['url_update']   = '';
        $aResult[$iKey]['url_destroy']  = '';
      }
    }

    return $aResult;
  }

  /* CREATE ACTIONS */

  private function _createTripStatement() {
    $oQuery = $this->_oDb->prepare("INSERT INTO
                                      " . SQL_PREFIX . "trips
                                    ( author_id,
                                        title,
                                        color,
                                        date,
                                        start_date,
                                        end_date,
                                        teaser)
                                    VALUES
                                      ( :author_id,
                                        :title,
                                        :color,
                                        NOW(),
                                        :start_date,
                                        :end_date,
                                        :teaser )");

    $oQuery->bindParam('author_id', $this->_aSession['user']['id'], PDO::PARAM_INT);

    foreach (array('title', 'color', 'teaser', 'start_date', 'end_date') as $sInput)
      $oQuery->bindParam(
              $sInput,
              Helper::formatInput($this->_aRequest[$this->_sController][$sInput], false),
              PDO::PARAM_STR);

    return $oQuery;
  }

  private function _createTripPolylineStatement() {
    $oQuery = $this->_oDb->prepare("INSERT INTO
                                      " . SQL_PREFIX . "trip_polylines
                                    ( `trip_id`,
                                      `author_id`,
                                      `latlngs`,
                                      `date`)
                                    VALUES
                                      ( :trip_id,
                                        :author_id,
                                        :latlngs,
                                        NOW())");

    $oQuery->bindParam('author_id', $this->_aSession['user']['id'], PDO::PARAM_INT);
    $oQuery->bindParam('trip_id', $this->_iId, PDO::PARAM_INT);
    $oQuery->bindValue('latlngs', json_encode($this->_aRequest[$this->_sController]['latlngs']), PDO::PARAM_STR);

    return $oQuery;
  }

  private function _createTripMarkerStatement() {
    $oQuery = $this->_oDb->prepare("INSERT INTO
                                      " . SQL_PREFIX . "trip_markers
                                    ( `trip_id`,
                                      `author_id`,
                                      `date`,
                                      `lat`,
                                      `long`,
                                      `title`,
                                      `content`)
                                    VALUES
                                      ( :trip_id,
                                        :author_id,
                                        NOW(),
                                        :lat,
                                        :long,
                                        :title,
                                        :content)");

    $oQuery->bindParam('author_id', $this->_aSession['user']['id'], PDO::PARAM_INT);
    $oQuery->bindParam('trip_id', $this->_iId, PDO::PARAM_INT);
    $oQuery->bindValue('lat', strval($this->_aRequest[$this->_sController]['lat']), PDO::PARAM_STR);
    $oQuery->bindValue('long', strval($this->_aRequest[$this->_sController]['long']), PDO::PARAM_STR);

    foreach (array('title', 'content') as $sInput)
      $oQuery->bindParam(
              $sInput,
              Helper::formatInput($this->_aRequest[$this->_sController][$sInput], false),
              PDO::PARAM_STR);

    return $oQuery;
  }

  public function create() {
    $sType = $this->_aRequest['triptype'];
    switch ($sType) {
      case 'polyline':
        $oStatement = $this->_createTripPolylineStatement();
      break;
      case 'marker':
        $oStatement = $this->_createTripMarkerStatement();
      break;
      default:
        $oStatement = $this->_createTripStatement();
      break;
    }

    try{
      $bReturn = $oStatement->execute();
      parent::$iLastInsertId = parent::$_oDbStatic->lastInsertId();

      return $bReturn;
    }
    catch (\PDOException $p) {
      try {
        $this->_oDb->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth('1010 - ' . $e->getMessage());
      }

      AdvancedException::reportBoth('1011 - ' . $p->getMessage());
      exit('SQL error.');
    }
  }

  /* UPDATE ACTIONS */

  private function _updateTripStatement($iId) {
    $oQuery = $this->_oDb->prepare("UPDATE " . SQL_PREFIX . "trips
                                    SET `title` = :title,
                                        `color` = :color,
                                        `teaser`= :teaser,
                                        `start_date` = :start_date,
                                        `end_date` = :end_date,
                                        `date` = NOW()
                                    WHERE id = :id
                                    LIMIT 1");

    $oQuery->bindParam('id', $iId, PDO::PARAM_INT);

    foreach (array('title', 'color', 'teaser', 'start_date', 'end_date') as $sInput)
      $oQuery->bindParam(
              $sInput,
              Helper::formatInput($this->_aRequest[$this->_sController][$sInput], false),
              PDO::PARAM_STR);

    return $oQuery;
  }

  private function _updateTripPolylineStatement($iId) {
    $oQuery = $this->_oDb->prepare("UPDATE " . SQL_PREFIX . "trip_polylines
                                    SET `latlngs` = :latlngs
                                    WHERE id = :id
                                    LIMIT 1");

    $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
    $oQuery->bindValue('latlngs', json_encode($this->_aRequest[$this->_sController]['latlngs']), PDO::PARAM_STR);

    return $oQuery;
  }

  private function _updateTripMarkerStatement($iId) {
    if (isset($this->_aRequest[$this->_sController]['title'])) {
      $oQuery = $this->_oDb->prepare("UPDATE " . SQL_PREFIX . "trip_markers
                                      SET `title` = :title,
                                          `content` = :content
                                      WHERE id = :id
                                      LIMIT 1");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      foreach (array('title', 'content') as $sInput)
        $oQuery->bindParam(
                $sInput,
                Helper::formatInput($this->_aRequest[$this->_sController][$sInput], false),
                PDO::PARAM_STR);
      return $oQuery;
    }
    else {
      $oQuery = $this->_oDb->prepare("UPDATE " . SQL_PREFIX . "trip_markers
                                      SET `lat` = :lat,
                                          `long` = :long
                                      WHERE id = :id
                                      LIMIT 1");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      $oQuery->bindValue('lat', strval($this->_aRequest[$this->_sController]['lat']), PDO::PARAM_STR);
      $oQuery->bindValue('long', strval($this->_aRequest[$this->_sController]['long']), PDO::PARAM_STR);
      return $oQuery;
    }
  }

  public function update($iId) {
    $sType = $this->_aRequest['triptype'];
    switch ($sType) {
      case 'polyline':
        $oStatement = $this->_updateTripPolylineStatement($this->_aRequest[$this->_sController]['polyline_id']);
      break;
      case 'marker':
        $oStatement = $this->_updateTripMarkerStatement($this->_aRequest[$this->_sController]['marker_id']);
      break;
      default:
        $oStatement = $this->_updateTripStatement($iId);
      break;
    }

    try{
      return $oStatement->execute();
    }
    catch (\PDOException $p) {
      try {
        $this->_oDb->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth('1012 - ' . $e->getMessage());
      }

      AdvancedException::reportBoth('1013 - ' . $p->getMessage());
      exit('SQL error.');
    }
  }

  public function destroy($iId) {
    $sType = $this->_aRequest['triptype'];
    switch ($sType) {
      case 'polyline':
        return parent::destroy($this->_aRequest[$this->_sController]['polyline_id'], 'trip_polylines');
      break;
      case 'marker':
        return parent::destroy($this->_aRequest[$this->_sController]['marker_id'], 'trip_markers');
      break;
      default:
        return parent::destroy($iId, 'trips');
      break;
    }
  }

}
