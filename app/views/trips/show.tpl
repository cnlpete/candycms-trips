{strip}
  <script src="{$_PATH.js.app}/leaflet.js"></script>
  <script src="{$_PATH.js.app}/leaflet.iconlabel.js"></script>
  <script src="{$_PATH.js.app}/leaflet.providers.js"></script>
  <script src="{$_PATH.js.app}/leaflet.markercluster.js"></script>
  {if $_SESSION.user.role >= 3}
    <p class='center'>
      <a href='/{$_REQUEST.controller}/create'>
        <i class='icon-plus'
           title='{$lang.global.create.entry}'></i>
        {$lang.trips.title.create}
      </a>
    </p>
  {/if}
  <header class='page-header'>
    <h2>
      <a href='{$trip.url}'>{$trip.title}</a>
      {if $_SESSION.user.role >= 3}
        <a href='{$trip.url_update}'>
          <i class='icon-pencil'
              title='{$lang.global.update.update}'></i>
        </a>
      {/if}
    </h2>
    <p>
      <time datetime='{$trip.start_date.w3c}' class='js-timeago'>
        {$trip.start_date.raw|date_format:$lang.global.time.format.date}
      </time>
      {if isset($trip.end_date)}
        &nbsp;-&nbsp;
        <time datetime='{$trip.end_date.w3c}' class='js-timeago'>
          {$trip.end_date.raw|date_format:$lang.global.time.format.date}
        </time>
      {/if}
      &nbsp;
      {$lang.global.by}
      &nbsp;
      <a href='{$trip.author.url}' rel='author'>{$trip.author.full_name}</a>
    </p>
  </header>
  {if isset($trip.teaser) && $trip.teaser}
    <p class='summary'>
      {$trip.teaser}
    </p>
  {/if}
  <div id="map" style="height: 400px;"></div>

  <script>
    L.Icon.Default.imagePath = '{$_PATH.img.app}/leaflet/';
    var map = new L.Map('map');
    
    var baseLayers = ["Stamen.Watercolor", "OpenStreetMap.DE"],
        overlays = ["OpenWeatherMap.Rain", "OpenWeatherMap.RainClassic", "OpenWeatherMap.Temperature"];

    var layerControl = L.control.layers.provided(baseLayers,overlays).addTo(map);

    /* center on the users location and add a marker */
    var userlocationLayer = new L.LayerGroup();

    map.locate();

    map.on('locationfound', onLocationFound);
    function onLocationFound(e) {
      console.log(e);

      /* TODO have a special marker for the users location (crsoss??) */
      var marker = new L.Marker(e.latlng);
      userlocationLayer.addLayer(marker);
      marker.bindPopup("Du bist vermutlich hier").openPopup();
      userlocationLayer.addLayer(new L.Circle(e.latlng, e.accuracy / 2));

      map.addLayer(userlocationLayer);
    }

    map.on('locationerror', onLocationError);
    function onLocationError(e) {
      console.log('some error occured with the localizing of the user');
    }

    /* add the trips polylines */
    function addPolyline(latlngs) {
      var polyline = new L.Polyline([], { color: '{$trip.color}' });
      map.addLayer(polyline);
      layerControl.addOverlay(polyline, "Pfad");

      for (k = 0; k < latlngs.length; k++)
        polyline.addLatLng(new L.LatLng( latlngs[k].lat , latlngs[k].lng ));

      return polyline.getBounds();
    }

    {foreach $trippolylines as $tp}
      map.fitBounds(addPolyline({$tp.latlngs}));
    {/foreach}

    /* zoom the map to some arbitrary point if no info is given yet */
    {if !$trippolylines}
      var kiel = new L.LatLng(54, 10);
      map.setView(kiel, 7);
    {/if}

    /* use custom icons, using leflet.iconlabel plugin */
    var markerIcon = L.Icon.Label.extend({
      options: {
        iconUrl: '{$_PATH.img.app}/leaflet/custom.marker-icon.png',
        shadowUrl: null,
        iconSize: new L.Point(24, 24),
        iconAnchor: new L.Point(0, 1),
        labelAnchor: new L.Point(24, 4),
        wrapperAnchor: new L.Point(12, 23),
      }
    });

    /* add all the markers */
    var markers = new Array();
    var markersLayer = new L.MarkerClusterGroup();
    {foreach $tripmarkers as $tm}
      /* TODO if it has a specified image, use it?! */

      markers["{$tm.title|escape}"] = new L.Marker(new L.LatLng( {$tm.lat} , {$tm.long} ),
          { icon: new markerIcon({ labelText: "{$tm.title|escape}" }) } );
      markersLayer.addLayer(markers['{$tm.title|escape}']);
      markers['{$tm.title|escape}'].bindPopup( '
        <h2>{$tm.title|escape}</h2>
        <time datetime="{$tm.date.w3c}" class="js-timeago">
          {$tm.date.raw|date_format:$lang.global.time.format.date}
        </time>
        <div>{$tm.content|strip|escape:'quotes'}</div>', { 'minWidth': 110 } );
    {/foreach}
    map.addLayer(markersLayer);
    layerControl.addOverlay(markersLayer, "Marker");

    map.on('popupopen', function(e) {
      $('time.js-timeago',e.popup._wrapper).timeago();
    } );
  </script>
{/strip}
