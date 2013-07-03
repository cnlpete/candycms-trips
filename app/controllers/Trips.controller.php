<?php
/**
 * Copyright (C) Hauke Schade, 2013

 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
**/
namespace CandyCMS\Controllers;

use CandyCMS\Core\Helpers\Helper;
use CandyCMS\Core\Helpers\I18n;

class Trips extends \CandyCMS\Core\Controllers\Main {

  protected function _overview() {
    $oTemplate = $this->oSmarty->getTemplate('trips', 'overview');
    $this->oSmarty->setTemplateDir($oTemplate);

    if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID))
      $this->oSmarty->assign('trips', $this->_oModel->getTrips());
    $this->setTitle(I18n::get('global.trips'));

    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }

  protected function _show() {
    if ($this->_iId) {
      $oTemplate = $this->oSmarty->getTemplate('trips', 'show');
      $this->oSmarty->setTemplateDir($oTemplate);

      $aData = $this->_oModel->getTrip($this->_iId);
      $sTitle = $aData['title'] . ' - ' . I18n::get('global.trips');
      $this->setTitle($sTitle);
      $this->oSmarty->assign('trip', $aData);

      if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID)) {
        $this->oSmarty->assign('trippolylines', $this->_oModel->getTripPolylines($aData['id']));
        $this->oSmarty->assign('tripmarkers', $this->_oModel->getTripMarkers($aData['id']));
      }

      # add rss info
      $this->_aRSSInfo[] = array(
                              'url' => WEBSITE_URL . '/trips/' . $this->_iId . '.rss',
                              'title' => $sTitle);

      $this->oSmarty->setTemplateDir($oTemplate);
      return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
    }
    else
      return $this->_overview();
  }

  protected function _showRSS() {
    $oTemplate =  $this->oSmarty->getTemplate($this->_sController, 'showRSS');
    $this->oSmarty->setTemplateDir($oTemplate);

    if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID)) {
      $aData = $this->_oModel->getTrip($this->_iId);
      $this->_aData = $this->_oModel->getTripMarkers($aData['id']);

      $this->oSmarty->assign('data', $this->_aData);
      $this->oSmarty->assign('_WEBSITE', array(
          'title' => $aData['title'] . ' - ' . I18n::get('global.trips'),
          'date'  => date('D, d M Y H:i:s O', time()),
          'id'    => $this->_iId
      ));
    }

    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }

  protected function _showFormTemplate() {
    $this->oSmarty->assign('_colors_', json_encode(array('aqua','black','blue','fuchsia','gray','grey','green','lime','maroon','navy','olive','purple','red','silver','teal','white','yellow','orange')));

    // Check whether this is update or create
    $this->oSmarty->assign('trippolylines', $this->_oModel->getTripPolylines($this->_aRequest['id'], false));
    $this->oSmarty->assign('tripmarkers', $this->_oModel->getTripMarkers($this->_aRequest['id'], false));

    return parent::_showFormTemplate();
  }

  protected function _create() {
    $this->_setError('color');

    $this->setTitle(I18n::get('trips.title.create'));

    $this->_sRedirectURL = '/' . $this->_sController . '/' . $this->_aRequest['id'];
    return parent::_create();
  }

  protected function _update() {
    $this->_setError('color');

    $this->setTitle(I18n::get('trips.title.update'));

    $this->_sRedirectURL = '/' . $this->_sController . '/' . $this->_iId;
    return parent::_update();
  }

  public function createmarker() {
    if ($this->_aSession['user']['role'] < 3)
      return Helper::redirectTo('/errors/401');

    return isset($this->_aRequest[$this->_sController]) ||
            isset($this->_aRequest['type']) && 'json' == $this->_aRequest['type'] ?
            $this->_createmarker() :
            $this->_showFormTemplate();
  }

  protected function _createmarker() {
    $this->_setError('title');
    $this->_setError('content');
    $this->_setError('lat');
    $this->_setError('long');

    $this->_aRequest['triptype'] = 'marker';

    $result = $this->_oModel->create();
    if ($result)
      $this->oSmarty->clearControllerCache($this->_sController);
    //TODO return instead
    header('Content-Type: application/json');
    exit(json_encode(array('success' => $result, 'id' => $this->_oModel->getLastInsertId())));
  }

  public function updatemarker() {
    if ($this->_aSession['user']['role'] < 3)
      return Helper::redirectTo('/errors/401');

    return isset($this->_aRequest[$this->_sController]) ||
            isset($this->_aRequest['type']) && 'json' == $this->_aRequest['type'] ?
            $this->_updatemarker() :
            $this->_showFormTemplate();
  }

  protected function _updatemarker() {
    if (isset($this->_aRequest[$this->_sController]['title'])) {
      $this->_setError('title');
      $this->_setError('content');
    }
    else {
      $this->_setError('lat');
      $this->_setError('long');
    }
    $this->_setError('marker_id');

    $this->_aRequest['triptype'] = 'marker';

    $result = $this->_oModel->update();
    if ($result)
      $this->oSmarty->clearControllerCache($this->_sController);
    //TODO return instead
    header('Content-Type: application/json');
    exit(json_encode(array('success' => $result)));
  }

  public function destroymarker() {
    if ($this->_aSession['user']['role'] < 3)
      return Helper::redirectTo('/errors/401');

    $this->_aRequest['triptype'] = 'marker';
    $this->_sRedirectURL = '/' . $this->_sController . '/' . $this->_iId . '/';

    // BUGFIX for candy, since the header type of Helper gets overwritten ...
    return isset($this->_aRequest[$this->_sController]) ||
            isset($this->_aRequest['type']) && 'json' == $this->_aRequest['type'] ?
            exit($this->_destroy()) : // <-- do not want exit here ...
            $this->_showFormTemplate();
  }

  public function createpolyline() {
    if ($this->_aSession['user']['role'] < 3)
      return Helper::redirectTo('/errors/401');

    return isset($this->_aRequest[$this->_sController]) ||
            isset($this->_aRequest['type']) && 'json' == $this->_aRequest['type'] ?
            $this->_createpolyline() :
            $this->_showFormTemplate();
  }

  protected function _createpolyline() {
    $this->_setError('latlngs');

    $this->_aRequest['triptype'] = 'polyline';

    $result = $this->_oModel->create();
    if ($result)
      $this->oSmarty->clearControllerCache($this->_sController);
    //TODO return instead
    header('Content-Type: application/json');
    exit(json_encode(array('success' => $result, 'id' => $this->_oModel->getLastInsertId())));
  }

  public function updatepolyline() {
    if ($this->_aSession['user']['role'] < 3)
      return Helper::redirectTo('/errors/401');

    return isset($this->_aRequest[$this->_sController]) ||
            isset($this->_aRequest['type']) && 'json' == $this->_aRequest['type'] ?
            $this->_updatepolyline() :
            $this->_showFormTemplate();
  }

  protected function _updatepolyline() {
    $this->_setError('latlngs');
    $this->_setError('polyline_id');

    $this->_aRequest['triptype'] = 'polyline';

    $result = $this->_oModel->update();
    if ($result)
      $this->oSmarty->clearControllerCache($this->_sController);
    //TODO return instead
    header('Content-Type: application/json');
    exit(json_encode(array('success' => $result)));
  }

  public function destroypolyline() {
    if ($this->_aSession['user']['role'] < 3)
      return Helper::redirectTo('/errors/401');

    $this->_aRequest['triptype'] = 'polyline';
    $this->_sRedirectURL = '/' . $this->_sController . '/' . $this->_iId . '/';

    // BUGFIX for candy, since the header type of Helper gets overwritten ...
    return isset($this->_aRequest[$this->_sController]) ||
            isset($this->_aRequest['type']) && 'json' == $this->_aRequest['type'] ?
            exit($this->_destroy()) : // <-- do not want exit here ...
            $this->_showFormTemplate();
  }
}
