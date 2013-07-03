{strip}
  <div class='page-header'>
    <h1>
      {if $_REQUEST.action == 'create'}
        Trip erstellen
      {else}
        Trip '{$title}' bearbeiten
      {/if}
    </h1>
  </div>
  {if $_REQUEST.action == 'create'}
    <form method='post' class='form-horizontal'
          action='/{$_REQUEST.controller}/{$_REQUEST.action}'>
  {elseif $_REQUEST.action == 'update'}
    <form method='post' class='form-horizontal'
          action='/{$_REQUEST.controller}/{$_REQUEST.id}/{$_REQUEST.action}'>
  {/if}
    <div class='control-group{if isset($error.title)} alert alert-error{/if}'>
      <label for='input-title' class='control-label'>
        {$lang.global.title} <span title='{$lang.global.required}'>*</span>
      </label>
      <div class='controls'>
        <input name='{$_REQUEST.controller}[title]'
               value="{$title}"
               type='text'
               id='input-title' class='span4 required' required />
        <span class='help-inline'>
          {if isset($error.title)}
            {$error.title}
          {/if}
        </span>
      </div>
    </div>
    <div class='control-group{if isset($error.start_date)} alert alert-error{/if}'>
      <label for='input-start_date' class='control-label'>
        {$lang.global.date.start} <span title='{$lang.global.required}'>*</span>
      </label>
      <div class='controls'>
        <input type='date'
               name='{$_REQUEST.controller}[start_date]'
               value="{$start_date}" min='{$_SYSTEM.date}'
               id='input-start_date' class='span4 required' autocomplete required />
        {if isset($error.start_date)}
          <span class='help-inline'>{$error.start_date}</span>
        {/if}
        <p class='help-block'>
          {$lang.calendars.info.date_format}
        </p>
      </div>
    </div>
    <div class='control-group{if isset($error.end_date)} alert alert-error{/if}'>
      <label for='input-end_date' class='control-label'>
        {$lang.global.date.end}
      </label>
      <div class='controls'>
        <input type='date'
               name='{$_REQUEST.controller}[end_date]'
               value="{$end_date}" min='{$_SYSTEM.date}'
               id='input-end_date' class='span4' autocomplete />
        {if isset($error.end_date)}
          <span class='help-inline'>{$error.end_date}</span>
        {/if}
        <p class='help-block'>
          {$lang.calendars.info.date_format}
        </p>
      </div>
    </div>
    <div class='control-group'>
      <label for='input-color' class='control-label'>
        Farbe <span title='{$lang.global.required}'>*</span>
      </label>
      <div class='controls'>
        <input name='{$_REQUEST.controller}[color]'
               value="{$color}"
               type='text'
               id='input-color' class='span4 required'
               data-provide='typeahead' data-source='{$_colors_}' data-items='8' autocomplete='off' required />
        <span class='help-inline'>
          {if isset($error.color)}
            {$error.color}
          {/if}
        </span>
      </div>
    </div>
    <div class='control-group'>
      <label for='input-teaser' class='control-label'>
        {$lang.global.teaser}
      </label>
      <div class='controls'>
        <input name='{$_REQUEST.controller}[teaser]'
               value="{$teaser}" type='text' class='span4' id='input-teaser' />
        <span class='help-inline'></span>
        <p class='help-block'>
          {$lang.global.info.teaser}
        </p>
      </div>
    </div>

    <div data-role='fieldcontain'>
      <div class='form-actions' data-role='controlgroup'>
        {if isset($author_id)}
          <input type='hidden' value='{$author_id}' name='{$_REQUEST.controller}[author_id]' />
        {/if}
        {if $_REQUEST.action == 'create'}
          <input type='submit' class='btn btn-primary'
                 value="{$lang.global.create.create}" />
        {elseif $_REQUEST.action == 'update'}
          <input type='submit' class='btn btn-primary'
                 value="{$lang.global.update.update}" />
          <input type='button' class='btn btn-danger' value='{$lang.global.destroy.destroy}'
                 onclick="confirmDestroy('/{$_REQUEST.controller}/{$_REQUEST.id}/destroy')" />
          <input type='reset' class='btn' value='{$lang.global.reset}' />
          <input type='hidden' value='{$date}' name='{$_REQUEST.controller}[date]' />
        {/if}
      </div>
    </div>
  </form>

  <script type='text/javascript' src='{$_PATH.js.bootstrap}/bootstrap-typeahead{$_SYSTEM.compress_files_suffix}.js'></script>
  <script>
    $('#input-title').bind('keyup', function() {
      countCharLength(this, 128);
    });
  </script>

  {if $_REQUEST.action == 'update'}
    <div class='control-group'>
      <label class='control-label'>
        Klick auf die Map um die Position zu bearbeiten.
      </label>
      <div class='controls'>
        <div id='map' style="height: 400px;"></div>
      </div>
    </div>
    <script src="{$_PATH.js.app}/leaflet{$_SYSTEM.compress_files_suffix}.js"></script>
    <script src="{$_PATH.js.app}/leaflet.draw{$_SYSTEM.compress_files_suffix}.js"></script>
    <script src="{$_PATH.js.app}/leaflet.providers{$_SYSTEM.compress_files_suffix}.js"></script>
    <script>
      L.Icon.Default.imagePath = '{$_PATH.img.app}/leaflet/';

      var map = new L.Map('map');
      var baseLayers = ["Stamen.Watercolor", "OpenStreetMap.DE"];
      var layerControl = L.control.layers.provided(baseLayers,[]).addTo(map);

      var drawnItems = new L.FeatureGroup();
      map.addLayer(drawnItems);

      var drawControl = new L.Control.Draw({
        draw: {
          position: 'topleft',
          polygon: false,
          rectangle: false,
          circle: false
        },
        edit: { featureGroup: drawnItems }
      } );
      map.addControl(drawControl);

      /* add the trips polylines */
      function addPolyline(id, latlngs) {
        var polyline = new L.Polyline(latlngs, { color: 'green' });
        polyline.id = id;
        drawnItems.addLayer(polyline);

        return polyline.getBounds();
      }

      {foreach $trippolylines as $tp}
        map.fitBounds(addPolyline({$tp.id}, {$tp.latlngs}));
      {/foreach}

      /* zoom the map to the polyline */
      {if !$trippolylines}
        var kiel = new L.LatLng(54, 10);
        map.setView(kiel, 7);
      {/if}

      var markers = { };
      function addMarker(latLng, id, title, content) {
        var m = new L.Marker(latLng);
        m.id = id;
        drawnItems.addLayer(m);
        m.bindPopup( '<form data-id="' + id + '">' +
          '<input class="js-title" type="text" value="' + title + '" />' +
          '<textarea class="js-content">' + content + '</textarea>' +
          '<input class="btn btn-primary js-submit" type="submit" value="{$lang.global.update.update}" /></form>' );
        markers[id] = m;
      }
      function editMarker(id, title, content) {
        markers[id].unbindPopup();
        markers[id].bindPopup( '<form>' +
          '<input class="js-title" type="text" value="' + title + '" />' +
          '<textarea class="js-content">' + content + '</textarea>' +
          '<input class="btn btn-primary js-submit" type="submit" value="{$lang.global.update.update}" /></form>' );
      }
      function removeMarker(id) {
        map.removeLayer(markers[id]);
      }

      {foreach $tripmarkers as $tm}
        addMarker(new L.LatLng( {$tm.lat},{$tm.long} ), {$tm.id}, "{$tm.title|escape}", "{$tm.content|replace:"\n":'\n'|strip}");
      {/foreach}

      map.on('draw:created', function (e) {
        var type = e.layerType,
            layer = e.layer;

        if (type === 'marker') {
          /* TODO query for title and content */
          var latlng = layer.getLatLng();
          var data = { 'trips': {
                          'lat': latlng.lat,
                          'long': latlng.lng,
                          'title': 'mein titel',
                          'content': 'mein inhalt' } };
          $.post('/{$_REQUEST.controller}/{$_REQUEST.id}/createmarker.json', data, function(result) {
            if (result.success) {
              addMarker(latlng, result.id, data.trips.title, data.trips.content);
            }
            else {
              showFlashMessage('error', lang.save_error);
            }
          } );
        }
        else {
          /* polyline edit, got the id in it :) */
          var latlngs = layer.getLatLngs();
          var alatlngs = new Array();
          for (k = 0; k < latlngs.length; k++)
            alatlngs[k] = { 'lat': latlngs[k].lat, 'lng': latlngs[k].lng };
          var data = { 'trips': { 'latlngs': alatlngs } };
          $.post('/{$_REQUEST.controller}/{$_REQUEST.id}/createpolyline.json', data, function(result) {
            if (result.success) {
              showFlashMessage('success', lang.save_successful);
              layer.id = result.id;
              drawnItems.addLayer(layer);
            }
            else {
              showFlashMessage('error', lang.save_error);
            }
          } );
        }
      } );

      map.on('draw:edited', function (e) {
        var layers = e.layers;
        layers.eachLayer(function (layer) {
            if (layer.getLatLng) {
              /* marker edit, got the id in it :) */
              var latlng = layer.getLatLng();
              var data = { 'trips': {
                              'marker_id': layer.id,
                              'lat': latlng.lat,
                              'long': latlng.lng } };
              $.post('/{$_REQUEST.controller}/{$_REQUEST.id}/updatemarker.json', data, function(result) {
                if (result.success) {
                  showFlashMessage('success', lang.save_successful);
                }
                else {
                  showFlashMessage('error', lang.save_error);
                }
              } );
            }
            else if (layer.getLatLngs) {
              /* polyline edit, got the id in it :) */
              var latlngs = layer.getLatLngs();
              var alatlngs = new Array();
              for (k = 0; k < latlngs.length; k++)
                alatlngs[k] = { 'lat': latlngs[k].lat, 'lng': latlngs[k].lng };
              /* TODO query for title and content */
              var data = { 'trips': { 
                              'polyline_id': layer.id, 
                              'latlngs': alatlngs } };
              $.post('/{$_REQUEST.controller}/{$_REQUEST.id}/updatepolyline.json', data, function(result) {
                if (result.success) {
                  showFlashMessage('success', lang.save_successful);
                }
                else {
                  showFlashMessage('error', lang.save_error);
                }
              } );
            }
        } );
      } );

      map.on('draw:deleted', function (e) {
        var layers = e.layers;
        layers.eachLayer(function (layer) {

            if (layer.getLatLng) {
              /* marker destroy, got the id in it :) */
              var data = { 'trips': { 'marker_id': layer.id } };
              $.post('/{$_REQUEST.controller}/{$_REQUEST.id}/destroymarker.json', data, function(result) {
                if (result.success) {
                  removeMarker(layer.id);
                  showFlashMessage('success', lang.save_successful);
                }
                else {
                  showFlashMessage('error', lang.save_error);
                }
              } );
            }
            else {
              /* marker destroy, got the id in it :) */
              var data = { 'trips': { 'polyline_id': layer.id } };
              $.post('/{$_REQUEST.controller}/{$_REQUEST.id}/destroypolyline.json', data, function(result) {
                if (result.success) {
                  showFlashMessage('success', lang.save_successful);
                }
                else {
                  showFlashMessage('error', lang.save_error);
                }
              } );
            }
        } );
      } );

      map.on('popupopen', function(e) {
        /* enable the editor magic :) , i.e. enable the save button */
        $('form', e.popup._wrapper).on('submit', function(f) {
          f.preventDefault();
          var button = $(this).find('input.js-submit');
          button.attr('disabled','disabled').addClass('disabled');

          var data = { 'trips': {
                          'marker_id': $(this).data('id'),
                          'title': $(this).find('input.js-title').val(),
                          'content': $(this).find('textarea.js-content').val() } };
          $.post('/{$_REQUEST.controller}/{$_REQUEST.id}/updatemarker.json', data, function(result) {
            if (result.success) {
              map.closePopup();
              editMarker(data.trips.marker_id, data.trips.title, data.trips.content);
              showFlashMessage('success', lang.save_successful);
            }
            else {
              showFlashMessage('error', lang.save_error);
              button.removeAttr('disabled').removeClass('disabled');
            }
          } );
        });
        console.log(e);
      } );
      map.on('popupclose', function(e) {
        /* disable the save button and other events ... */
        $('form', e.popup._wrapper).off();
      } );
    </script>
  {/if}
{/strip}
