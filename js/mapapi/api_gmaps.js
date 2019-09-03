/*
 * μlogger
 *
 * Copyright(C) 2019 Bartek Fabiszewski (www.fabiszewski.net)
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

import { config, lang } from '../constants.js';
import uEvent from '../event.js';
import uUI from '../ui.js';
import uUtils from '../utils.js';

// google maps
/**
 * Google Maps API module
 * @module gmApi
 * @implements {uMap.api}
 */

/** @type {google.maps.Map} */
let map = null;
/** @type {uBinder} */
let binder = null;
/** @type {google.maps.Polyline[]} */
const polies = [];
/** @type {google.maps.Marker[]} */
const markers = [];
/** @type {google.maps.InfoWindow[]} */
const popups = [];
/** @type {google.maps.InfoWindow} */
let openPopup = null;
/** @type {google.maps.PolylineOptions} */
let polyOptions = null;
/** @type {google.maps.MapOptions} */
let mapOptions = null;
/** @type {number} */
let timeoutHandle = 0;
const name = 'gmaps';
let isLoaded = false;
let authError = false;

/**
 * Initialize map
 * @param {uBinder} b
 * @param {HTMLElement} el
 */
function init(b, el) {

  binder = b;

  const url = '//maps.googleapis.com/maps/api/js?' + ((config.gkey != null) ? ('key=' + config.gkey + '&') : '') + 'callback=gm_loaded';
  uUtils.addScript(url, 'mapapi_gmaps');
  if (!isLoaded) {
    throw new Error('Google Maps API not ready');
  }
  start(el);
}

/**
 * Start map engine when loaded
 * @param {HTMLElement} el
 */
function start(el) {
  if (authError) {
    window.gm_authFailure();
    return;
  }
  google.maps.visualRefresh = true;
  // noinspection JSValidateTypes
  polyOptions = {
    strokeColor: config.strokeColor,
    strokeOpacity: config.strokeOpacity,
    strokeWeight: config.strokeWeight
  };
  // noinspection JSValidateTypes
  mapOptions = {
    center: new google.maps.LatLng(config.init_latitude, config.init_longitude),
    zoom: 8,
    mapTypeId: google.maps.MapTypeId.ROADMAP,
    scaleControl: true
  };
  map = new google.maps.Map(el, mapOptions);
}

/**
 * Clean up API
 */
function cleanup() {
  polies.length = 0;
  markers.length = 0;
  popups.length = 0;
  polyOptions = null;
  mapOptions = null;
  openPopup = null;
  if (map && map.getDiv()) {
    map.getDiv().innerHTML = '';
  }
  map = null;
}

/**
 * Display track
 * @param {uTrack} track
 * @param {boolean} update Should fit bounds if true
 */
function displayTrack(track, update) {
  if (!track) {
    return;
  }
  // init polyline
  const poly = new google.maps.Polyline(polyOptions);
  poly.setMap(map);
  const path = poly.getPath();
  const latlngbounds = new google.maps.LatLngBounds();
  let i = 0;
  for (const position of track.positions) {
    // set marker
    setMarker(i++, track);
    // update polyline
    const coordinates = new google.maps.LatLng(position.latitude, position.longitude);
    if (track.continuous) {
      path.push(coordinates);
    }
    latlngbounds.extend(coordinates);
  }
  if (update) {
    map.fitBounds(latlngbounds);
    if (i === 1) {
      // only one point, zoom out
      const zListener =
        google.maps.event.addListenerOnce(map, 'bounds_changed', function () {
          if (this.getZoom()) {
            this.setZoom(15);
          }
        });
      setTimeout(function () { google.maps.event.removeListener(zListener) }, 2000);
    }
  }
  polies.push(poly);
}

/**
 * Clear map
 */
function clearMap() {
  if (polies) {
    for (let i = 0; i < polies.length; i++) {
      polies[i].setMap(null);
    }
  }
  if (markers) {
    for (let i = 0; i < markers.length; i++) {
      // google.maps.event.removeListener(popups[i].listener);
      google.maps.event.clearInstanceListeners(popups[i]);
      popups[i].setMap(null);
      markers[i].setMap(null);
    }
  }
  markers.length = 0;
  polies.length = 0;
  popups.length = 0;
}

/**
 * @param {string} fill Fill color
 * @param {boolean} isLarge Is large icon
 * @param {boolean} isExtra Is styled with extra mark
 * @return {google.maps.Icon}
 */
function getMarkerIcon(fill, isLarge, isExtra) {
  return {
    anchor: new google.maps.Point(15, 35),
    url: uUI.getSvgSrc(fill, isLarge, isExtra)
  };
}

/**
 * Set marker
 * @param {uTrack} track
 * @param {number} id
 */
function setMarker(id, track) {
  // marker
  const position = track.positions[id];
  const posLen = track.length;
  // noinspection JSCheckFunctionSignatures
  const marker = new google.maps.Marker({
    position: new google.maps.LatLng(position.latitude, position.longitude),
    title: (new Date(position.timestamp * 1000)).toLocaleString(),
    map: map
  });
  const isExtra = position.hasComment() || position.hasImage();
  let icon = getMarkerIcon(isExtra ? config.colorExtra : config.colorNormal, false, isExtra);
  if (id === posLen - 1) {
    icon = getMarkerIcon(config.colorStop, true, isExtra);
  } else if (id === 0) {
    icon = getMarkerIcon(config.colorStart, true, isExtra);
  }
  marker.setIcon(icon);
  // popup
  const popup = new google.maps.InfoWindow();

  marker.addListener('click',
    ((i) => () => {
    popup.setContent(uUI.getPopupHtml(i));
    popup.open(map, marker);
    binder.dispatchEvent(uEvent.MARKER_SELECT, i);
    openPopup = popup;
    popup.addListener('closeclick', () => {
      binder.dispatchEvent(uEvent.MARKER_SELECT);
      google.maps.event.clearListeners(popup, 'closeclick');
      openPopup = null;
    });
  })(id));
  marker.addListener('mouseover',
    ((i) => () => {
      binder.dispatchEvent(uEvent.MARKER_OVER, i);
    })(id));
  marker.addListener('mouseout',
    () => {
      binder.dispatchEvent(uEvent.MARKER_OVER);
    });

  markers.push(marker);
  popups.push(popup);
}

function animateMarker(id) {
  if (openPopup) {
    openPopup.close();
    clearTimeout(timeoutHandle);
  }
  const icon = markers[id].getIcon();
  markers[id].setIcon(getMarkerIcon(config.colorHilite, false, false));
  markers[id].setAnimation(google.maps.Animation.BOUNCE);
  timeoutHandle = setTimeout(() => {
    markers[id].setIcon(icon);
    markers[id].setAnimation(null);
  }, 2000);
}

/**
 * Get map bounds
 * eg. ((52.20105108685229, 20.789387865580238), (52.292069558807135, 21.172192736185707))
 * @returns {number[]} Bounds
 */
function getBounds() {
  const bounds = map.getBounds();
  const lat_sw = bounds.getSouthWest().lat();
  const lon_sw = bounds.getSouthWest().lng();
  const lat_ne = bounds.getNorthEast().lat();
  const lon_ne = bounds.getNorthEast().lng();
  return [ lon_sw, lat_sw, lon_ne, lat_ne ];
}

/**
 * Zoom to track extent
 */
function zoomToExtent() {
  const latlngbounds = new google.maps.LatLngBounds();
  for (let i = 0; i < markers.length; i++) {
    const coordinates = new google.maps.LatLng(markers[i].position.lat(), markers[i].position.lng());
    latlngbounds.extend(coordinates);
  }
  map.fitBounds(latlngbounds);
}

/**
 * Zoom to bounds
 * @param {number[]} bounds
 */
function zoomToBounds(bounds) {
  const sw = new google.maps.LatLng(bounds[1], bounds[0]);
  const ne = new google.maps.LatLng(bounds[3], bounds[2]);
  const latLngBounds = new google.maps.LatLngBounds(sw, ne);
  map.fitBounds(latLngBounds);
}

/**
 * Update size
 */
function updateSize() {
  // ignore for google API
}

function setAuthError() { authError = true; }
function setLoaded() { isLoaded = true; }

export {
  name,
  init,
  cleanup,
  displayTrack,
  clearMap,
  animateMarker,
  getBounds,
  zoomToExtent,
  zoomToBounds,
  updateSize
}


/**
 * Callback for Google Maps API
 * It will be called when authentication fails
 */
window.gm_authFailure = function () {
  setAuthError();
  let message = uUtils.sprintf(lang.strings['apifailure'], 'Google Maps');
  message += '<br><br>' + lang.strings['gmauthfailure'];
  message += '<br><br>' + lang.strings['gmapilink'];
  uUI.resolveModal(message);
};

/**
 * Callback for Google Maps API
 * It will be called when API is loaded
 */
window.gm_loaded = function () {
  setLoaded();
};
