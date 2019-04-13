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

// google maps
/** @namespace */
var uLogger = uLogger || {};
/** @namespace */
uLogger.mapAPI = uLogger.mapAPI || {};
/** @namespace */
uLogger.mapAPI.gmaps = (function(ns) {

  /** @type {google.maps.Map} */
  var map;
  /** @type {google.maps.Polyline[]} */
  var polies = [];
  /** @type {google.maps.Marker[]} */
  var markers = [];
  /** @type {google.maps.InfoWindow[]} */
  var popups = [];
  /** @type {google.maps.InfoWindow} */
  var popup;
  /** @type {google.maps.PolylineOptions} */
  var polyOptions;
  /** @type {google.maps.MapOptions} */
  var mapOptions;
  /** @type {number} */
  var timeoutHandle;
  var name = 'gmaps';
  var isLoaded = false;
  var authError = false;

  /**
   * Initialize map
   */
  function init() {
    var url = '//maps.googleapis.com/maps/api/js?' + ((ns.config.gkey != null) ? ('key=' + ns.config.gkey + '&') : '') + 'callback=uLogger.mapAPI.gmaps.setLoaded';
    ns.addScript(url, 'mapapi_gmaps');
    if (!isLoaded) {
      throw new Error("Google Maps API not ready");
    }
    start();
  }

  /**
   * Start map engine when loaded
   */
  function start() {
    if (authError) {
      gm_authFailure();
      return;
    }
    google.maps.visualRefresh = true;
    // noinspection JSValidateTypes
    polyOptions = {
      strokeColor: ns.config.strokeColor,
      strokeOpacity: ns.config.strokeOpacity,
      strokeWeight: ns.config.strokeWeight
    };
    // noinspection JSValidateTypes
    mapOptions = {
      center: new google.maps.LatLng(ns.config.init_latitude, ns.config.init_longitude),
      zoom: 8,
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      scaleControl: true
    };
    map = new google.maps.Map(ns.ui.map, mapOptions);
  }

  /**
   * Clean up API
   */
  function cleanup() {
    polies = [];
    markers = [];
    popups = [];
    map = null;
    polyOptions = null;
    mapOptions = null;
    popup = null;
    ns.clearMapCanvas();
  }

  /**
   * Display track
   * @param {HTMLCollection} positions XML element
   * @param {boolean} update Should fit bounds if true
   */
  function displayTrack(positions, update) {
    var totalMeters = 0;
    var totalSeconds = 0;
    // init polyline
    var poly = new google.maps.Polyline(polyOptions);
    poly.setMap(map);
    var path = poly.getPath();
    var latlngbounds = new google.maps.LatLngBounds();
    var posLen = positions.length;
    for (var i = 0; i < posLen; i++) {
      var p = ns.parsePosition(positions[i], i);
      totalMeters += p.distance;
      totalSeconds += p.seconds;
      p.totalMeters = totalMeters;
      p.totalSeconds = totalSeconds;
      // set marker
      setMarker(p, i, posLen);
      // update polyline
      var coordinates = new google.maps.LatLng(p.latitude, p.longitude);
      path.push(coordinates);
      latlngbounds.extend(coordinates);
    }
    if (update) {
      map.fitBounds(latlngbounds);
      if (i === 1) {
        // only one point, zoom out
        var zListener =
          google.maps.event.addListenerOnce(map, 'bounds_changed', function () {
            if (this.getZoom()) {
              this.setZoom(15);
            }
          });
        setTimeout(function () { google.maps.event.removeListener(zListener) }, 2000);
      }
    }
    polies.push(poly);

    ns.updateSummary(p.timestamp, totalMeters, totalSeconds);
    if (p.tid !== ns.config.trackid) {
      ns.config.trackid = p.tid;
      ns.setTrack(ns.config.trackid);
    }
    ns.updateChart();
  }

  /**
   * Clear map
   */
  function clearMap() {
    if (polies) {
      for (var i = 0; i < polies.length; i++) {
        polies[i].setMap(null);
      }
    }
    if (markers) {
      for (var j = 0; j < markers.length; j++) {
        google.maps.event.removeListener(popups[j].listener);
        popups[j].setMap(null);
        markers[j].setMap(null);
      }
    }
    markers.length = 0;
    polies.length = 0;
    popups.lentgth = 0;
  }

  /**
   * Set marker
   * @param {uLogger.Position} pos
   * @param {number} id
   * @param {number} posLen
   */
  function setMarker(pos, id, posLen) {
    // marker
    // noinspection JSCheckFunctionSignatures
    var marker = new google.maps.Marker({
      position: new google.maps.LatLng(pos.latitude, pos.longitude),
      title: (new Date(pos.timestamp * 1000)).toLocaleString(),
      map: map
    });
    if (ns.isLatest()) {
      marker.setIcon('images/marker-red.png');
    } else if (id === 0) {
      marker.setIcon('images/marker-green.png');
    } else if (id === posLen - 1) {
      marker.setIcon('images/marker-red.png');
    } else {
      marker.setIcon('images/marker-white.png');
    }
    // popup
    var content = ns.getPopupHtml(pos, id, posLen);
    popup = new google.maps.InfoWindow();
    // noinspection JSUndefinedPropertyAssignment
    popup.listener = google.maps.event.addListener(marker, 'click', (function (_marker, _content) {
      return function () {
        popup.setContent(_content);
        popup.open(map, _marker);
        ns.chartShowPosition(id);
      }
    })(marker, content));
    markers.push(marker);
    popups.push(popup);
  }

  /**
   * Add listener on chart to show position on map
   * @param {google.visualization.LineChart} chart
   * @param {google.visualization.DataTable} data
   */
  function addChartEvent(chart, data) {
    google.visualization.events.addListener(chart, 'select', function () {
      if (popup) { popup.close(); clearTimeout(timeoutHandle); }
      var selection = chart.getSelection()[0];
      if (selection) {
        var id = data.getValue(selection.row, 0) - 1;
        var icon = markers[id].getIcon();
        markers[id].setIcon('images/marker-gold.png');
        timeoutHandle = setTimeout(function () { markers[id].setIcon(icon); }, 2000);
      }
    });
  }

  /**
   * Get map bounds
   * eg. ((52.20105108685229, 20.789387865580238), (52.292069558807135, 21.172192736185707))
   * @returns {number[]} Bounds
   */
  function getBounds() {
    var bounds = map.getBounds();
    var lat_sw = bounds.getSouthWest().lat();
    var lon_sw = bounds.getSouthWest().lng();
    var lat_ne = bounds.getNorthEast().lat();
    var lon_ne = bounds.getNorthEast().lng();
    return [lon_sw, lat_sw, lon_ne, lat_ne];
  }

  /**
   * Zoom to track extent
   */
  function zoomToExtent() {
    var latlngbounds = new google.maps.LatLngBounds();
    for (var i = 0; i < markers.length; i++) {
      var coordinates = new google.maps.LatLng(markers[i].position.lat(), markers[i].position.lng());
      latlngbounds.extend(coordinates);
    }
    map.fitBounds(latlngbounds);
  }

  /**
   * Zoom to bounds
   * @param {number[]} bounds
   */
  function zoomToBounds(bounds) {
    var sw = new google.maps.LatLng(bounds[1], bounds[0]);
    var ne = new google.maps.LatLng(bounds[3], bounds[2]);
    var latLngBounds = new google.maps.LatLngBounds(sw, ne);
    map.fitBounds(latLngBounds);
  }

  /**
   * Update size
   */
  function updateSize() {
    // ignore for google API
  }

  return {
    name: name,
    init: init,
    setLoaded: function () { isLoaded = true; },
    cleanup: cleanup,
    displayTrack: displayTrack,
    clearMap: clearMap,
    setMarker: setMarker,
    addChartEvent: addChartEvent,
    getBounds: getBounds,
    zoomToExtent: zoomToExtent,
    zoomToBounds: zoomToBounds,
    updateSize: updateSize
  }

})(uLogger);

/**
 * Callback for Google Maps API
 * It will be called when authentication fails
 */
function gm_authFailure() {
  uLogger.mapAPI.gmaps.authError = true;
  var message = uLogger.sprintf(uLogger.lang.strings['apifailure'], 'Google Maps');
  message += '<br><br>' + uLogger.lang.strings['gmauthfailure'];
  message += '<br><br>' + uLogger.lang.strings['gmapilink'];
  uLogger.ui.showModal(message);
}
