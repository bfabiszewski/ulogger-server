/* μlogger
 *
 * Copyright(C) 2017 Bartek Fabiszewski (www.fabiszewski.net)
 *
 * This is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see <http://www.gnu.org/licenses/>.
 */

// openlayers
var map;
var layerTrack;
var layerMarkers;
var wgs84;
var mercator;
var loadedAPI = 'openlayers';

function init() {
  wgs84 = new OpenLayers.Projection('EPSG:4326');   // from WGS 1984
  mercator = new OpenLayers.Projection('EPSG:900913'); // to Mercator
  var options = {
    controls: [
      new OpenLayers.Control.ArgParser(), // default
      new OpenLayers.Control.Attribution(), // default
      new OpenLayers.Control.LayerSwitcher(),
      new OpenLayers.Control.Navigation(), // default
      new OpenLayers.Control.PanZoomBar(),// do we need it?
      new OpenLayers.Control.ScaleLine()
    ]
  };
  map = new OpenLayers.Map('map-canvas', options);
  // default layer: OpenStreetMap
  var osm = new OpenLayers.Layer.OSM();
  map.addLayer(osm);

  // add extra layers
  for (var layerName in ol_layers) {
    if (ol_layers.hasOwnProperty(layerName)) {
      var layerUrl = ol_layers[layerName];
      var ol_layer = new OpenLayers.Layer.OSM(
        layerName,
        urlFromOL3(layerUrl),
        { tileOptions: { crossOriginKeyword: null } }
      );
      map.addLayer(ol_layer);
    }
  }

  var position = new OpenLayers.LonLat(init_longitude, init_latitude).transform(wgs84, mercator);
  var zoom = 8;
  map.setCenter(position, zoom);

  // init layers
  layerTrack = new OpenLayers.Layer.Vector('Track');
  layerMarkers = new OpenLayers.Layer.Markers('Markers');
}

function cleanup() {
  map = undefined;
  layerTrack = undefined;
  layerMarkers = undefined;
  wgs84 = undefined;
  mercator = undefined;
  document.getElementById("map-canvas").innerHTML = '';
}

function displayTrack(xml, update) {
  altitudes = {};
  var totalMeters = 0;
  var totalSeconds = 0;
  var points = [];
  var latlngbounds = new OpenLayers.Bounds();
  var positions = xml.getElementsByTagName('position');
  var posLen = positions.length;
  for (var i = 0; i < posLen; i++) {
    var p = parsePosition(positions[i], i);
    totalMeters += p.distance;
    totalSeconds += p.seconds;
    p['totalMeters'] = totalMeters;
    p['totalSeconds'] = totalSeconds;
    // set marker
    setMarker(p, i, posLen);
    // update polyline
    var point = new OpenLayers.Geometry.Point(p.longitude, p.latitude).transform(wgs84, mercator);
    latlngbounds.extend(point);
    points.push(point);
  }
  var lineString = new OpenLayers.Geometry.LineString(points);
  var lineStyle = { strokeColor: '#FF0000', strokeOpacity: 1, strokeWidth: 2 };
  var lineFeature = new OpenLayers.Feature.Vector(lineString, null, lineStyle);
  layerTrack.addFeatures([lineFeature]);
  map.addLayer(layerTrack);
  map.addLayer(layerMarkers);
  if (update) {
    map.zoomToExtent(latlngbounds);
    if (i == 1) {
      // only one point, zoom out
      map.zoomOut();
    }
  }

  updateSummary(p.timestamp, totalMeters, totalSeconds);
  if (p.tid != trackid) {
    trackid = p.tid;
    setTrack(trackid);
  }
  if (document.getElementById('bottom').style.display == 'block') {
    // update altitudes chart
    chart.clearChart();
    displayChart();
  }
}

function clearMap() {
  if (layerTrack) {
    layerTrack.removeAllFeatures();
  }
  if (layerMarkers) {
    layerMarkers.clearMarkers();
  }
}

function setMarker(p, i, posLen) {
  // marker
  var lonLat = new OpenLayers.LonLat(p.longitude, p.latitude).transform(wgs84, mercator);
  var size = new OpenLayers.Size(16, 25);
  var offset = new OpenLayers.Pixel(-(size.w / 2), -size.h);
  if (latest == 1) { var icon = new OpenLayers.Icon('images/marker-red.png', size, offset); }
  else if (i == 0) { var icon = new OpenLayers.Icon('images/marker-green.png', size, offset); }
  else if (i == posLen - 1) { var icon = new OpenLayers.Icon('images/marker-red.png', size, offset); }
  else {
    offset = new OpenLayers.Pixel(-(size.w / 2), -size.h);
    var icon = new OpenLayers.Icon('images/marker-white.png', size, offset);
  }
  var marker = new OpenLayers.Marker(lonLat, icon);
  layerMarkers.addMarker(marker);

  // popup
  var content = getPopupHtml(p, i, posLen);
  marker.events.register("mousedown", marker, (function () {
    return function () {
      // remove popups
      if (map.popups.length > 0) {
        for (var j = map.popups.length - 1; j >= 0; j--) {
          map.removePopup(map.popups[j])
        };
      }
      // show popup
      var popup = new OpenLayers.Popup.FramedCloud("popup_" + (i + 1), lonLat, null, content, icon, true);
      map.addPopup(popup);
      if (document.getElementById('bottom').style.display == 'block') {
        var index = 0;
        for (var key in altitudes) {
          if (altitudes.hasOwnProperty(key) && key == i) {
            chart.setSelection([{ row: index, column: null }]);
            break;
          }
          index++;
        }
      }
    }
  })());
}

function addChartEvent(chart, data) {
  google.visualization.events.addListener(chart, 'select', function () {
    var selection = chart.getSelection()[0];
    if (selection) {
      var id = data.getValue(selection.row, 0) - 1;
      var marker = layerMarkers.markers[id];
      var url = marker.icon.url;
      marker.setUrl('images/marker-gold.png');
      altTimeout = setTimeout(function () { marker.setUrl(url); }, 2000);
    }
  });
}
//20.597985430276808,52.15547181298076,21.363595171488573,52.33750879522563
function getBounds() {
  var bounds = map.getExtent().transform(mercator, wgs84).toArray();
  var lon_sw = bounds[0];
  var lat_sw = bounds[1];
  var lon_ne = bounds[2];
  var lat_ne = bounds[3];
  return [lon_sw, lat_sw, lon_ne, lat_ne];
}

function zoomToBounds(b) {
  var bounds = new OpenLayers.Bounds(b).transform(wgs84, mercator);
  map.zoomToExtent(bounds);
}

function urlFromOL3(url) {
  url = url.replace(/\{([xyz])\}/g, "$${$1}");
  return expandUrl(url);
}

// backported from openlayers 3
function expandUrl(url) {
  var urls = [];
  var match = /\{([a-z])-([a-z])\}/.exec(url);
  if (match) {
    // char range
    var startCharCode = match[1].charCodeAt(0);
    var stopCharCode = match[2].charCodeAt(0);
    for (var charCode = startCharCode; charCode <= stopCharCode; ++charCode) {
      urls.push(url.replace(match[0], String.fromCharCode(charCode)));
    }
    return urls;
  }
  match = match = /\{(\d+)-(\d+)\}/.exec(url);
  if (match) {
    // number range
    var stop = parseInt(match[2], 10);
    for (var i = parseInt(match[1], 10); i <= stop; i++) {
      urls.push(url.replace(match[0], i.toString()));
    }
    return urls;
  }
  urls.push(url);
  return urls;
}


