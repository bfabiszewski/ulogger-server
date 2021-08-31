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

import { lang as $, config } from '../initializer.js';
import MapViewModel from '../mapviewmodel.js';
import uAlert from '../alert.js';
import uTrack from '../track.js';
import uUtils from '../utils.js';

// google maps
/**
 * Google Maps API
 * @class GoogleMapsApi
 * @implements {MapViewModel.api}
 */
export default class GoogleMapsApi {

  /**
   * @param {MapViewModel} vm
   */
  constructor(vm) {
    /** @type {google.maps.Map} */
    this.map = null;
    /** @type {MapViewModel} */
    this.viewModel = vm;
    /** @type {google.maps.Polyline[]} */
    this.polies = [];
    /** @type {google.maps.Marker[]} */
    this.markers = [];
    /** @type {google.maps.InfoWindow} */
    this.popup = null;
    /** @type {number} */
    this.timeoutHandle = 0;
  }

  /**
   * Load and initialize api scripts
   * @return {Promise<void, Error>}
   */
  init() {
    const params = `?${(config.googleKey) ? `key=${config.googleKey}&` : ''}callback=gm_loaded`;
    const gmReady = Promise.all([
      GoogleMapsApi.onScriptLoaded(),
      uUtils.loadScript(`https://maps.googleapis.com/maps/api/js${params}`, 'mapapi_gmaps', GoogleMapsApi.loadTimeoutMs)
    ]);
    return gmReady.then(() => this.initMap());
  }

  /**
   * Listen to Google Maps callbacks
   * @return {Promise<void, Error>}
   */
  static onScriptLoaded() {
    const timeout = uUtils.timeoutPromise(GoogleMapsApi.loadTimeoutMs);
    const gmInitialize = new Promise((resolve, reject) => {
      window.gm_loaded = () => {
        GoogleMapsApi.gmInitialized = true;
        resolve();
      };
      window.gm_authFailure = () => {
        GoogleMapsApi.authError = true;
        let message = $._('apifailure', 'Google Maps');
        message += '<br><br>' + $._('gmauthfailure');
        message += '<br><br>' + $._('gmapilink');
        if (GoogleMapsApi.gmInitialized) {
          uAlert.error(message);
        }
        reject(new Error(message));
      };
      if (GoogleMapsApi.authError) {
        window.gm_authFailure();
      }
      if (GoogleMapsApi.gmInitialized) {
        window.gm_loaded();
      }
    });
    return Promise.race([ gmInitialize, timeout ]);
  }

  /**
   * Start map engine when loaded
   */
  initMap() {
    const mapOptions = {
      center: new google.maps.LatLng(config.initLatitude, config.initLongitude),
      zoom: 8,
      mapTypeId: google.maps.MapTypeId.TERRAIN,
      scaleControl: true,
      controlSize: 30
    };
    // noinspection JSCheckFunctionSignatures
    this.map = new google.maps.Map(this.viewModel.mapElement, mapOptions);
    this.popup = new google.maps.InfoWindow();
    this.popup.addListener('closeclick', () => {
      this.popupClose();
    });
    this.saveState = () => {
      this.viewModel.state.mapParams = this.getState();
    };
  }

  /**
   * Clean up API
   */
  cleanup() {
    this.polies.length = 0;
    this.markers.length = 0;
    this.popup = null;
    if (this.map && this.map.getDiv()) {
      this.map.getDiv().innerHTML = '';
    }
    this.map = null;
  }

  /**
   * Display track
   * @param {uPositionSet} track
   * @param {boolean} update Should fit bounds if true
   * @return {Promise.<void>}
   */
  displayTrack(track, update) {
    if (!track || !track.hasPositions) {
      return Promise.resolve();
    }
    google.maps.event.clearListeners(this.map, 'idle');
    const promise = new Promise((resolve) => {
      google.maps.event.addListenerOnce(this.map, 'tilesloaded', () => {
        console.log('tilesloaded');
        if (this.map) {
          this.saveState();
          this.map.addListener('idle', this.saveState);
        }
        resolve();
      })
    });
    // init polyline
    const polyOptions = {
      strokeColor: config.strokeColor,
      strokeOpacity: config.strokeOpacity,
      strokeWeight: config.strokeWeight
    };
    // noinspection JSCheckFunctionSignatures
    let poly;
    const latlngbounds = new google.maps.LatLngBounds();
    if (this.polies.length) {
      poly = this.polies[0];
      for (let i = 0; i < this.markers.length; i++) {
        latlngbounds.extend(this.markers[i].getPosition());
      }
    } else {
      poly = new google.maps.Polyline(polyOptions);
      poly.setMap(this.map);
      this.polies.push(poly);
    }
    const path = poly.getPath();
    let start = this.markers.length;
    if (start > 0) {
      this.removePoint(--start);
    }
    for (let i = start; i < track.length; i++) {
      // set marker
      this.setMarker(i, track);
      // update polyline
      const position = track.positions[i];
      const coordinates = new google.maps.LatLng(position.latitude, position.longitude);
      if (track instanceof uTrack) {
        path.push(coordinates);
      }
      latlngbounds.extend(coordinates);
    }
    if (update) {
      this.map.fitBounds(latlngbounds);
      if (track.length === 1) {
        // only one point, zoom out
        const zListener =
          google.maps.event.addListenerOnce(this.map, 'bounds_changed', function () {
            if (this.getZoom()) {
              this.setZoom(15);
            }
          });
        setTimeout(() => {
          google.maps.event.removeListener(zListener);
        }, 2000);
      }
    }
    return promise;
  }

  /**
   * Clear map
   */
  clearMap() {
    if (this.polies) {
      for (let i = 0; i < this.polies.length; i++) {
        this.polies[i].setMap(null);
      }
    }
    if (this.markers) {
      for (let i = 0; i < this.markers.length; i++) {
        this.markers[i].setMap(null);
      }
    }
    if (this.popup.getMap()) {
      this.popupClose();
    }
    this.popup.setContent('');
    this.markers.length = 0;
    this.polies.length = 0;
  }

  /**
   * @param {string} fill Fill color
   * @param {boolean} isLarge Is large icon
   * @param {boolean} isExtra Is styled with extra mark
   * @return {google.maps.Icon}
   */
  static getMarkerIcon(fill, isLarge, isExtra) {
    // noinspection JSValidateTypes
    return {
      anchor: new google.maps.Point(15, 35),
      url: MapViewModel.getSvgSrc(fill, isLarge, isExtra)
    };
  }

  /**
   * Set marker
   * @param {uPositionSet} track
   * @param {number} id
   */
  setMarker(id, track) {
    // marker
    const position = track.positions[id];
    // noinspection JSCheckFunctionSignatures
    const marker = new google.maps.Marker({
      position: new google.maps.LatLng(position.latitude, position.longitude),
      title: (new Date(position.timestamp * 1000)).toLocaleString(),
      map: this.map
    });
    const isExtra = position.hasComment() || position.hasImage();
    let icon;
    if (track.isLastPosition(id)) {
      icon = GoogleMapsApi.getMarkerIcon(config.colorStop, true, isExtra);
    } else if (track.isFirstPosition(id)) {
      icon = GoogleMapsApi.getMarkerIcon(config.colorStart, true, isExtra);
    } else {
      icon = GoogleMapsApi.getMarkerIcon(isExtra ? config.colorExtra : config.colorNormal, false, isExtra);
    }
    marker.setIcon(icon);

    marker.addListener('click', () => {
      this.popupOpen(id, marker);
    });
    marker.addListener('mouseover', () => {
      this.viewModel.model.markerOver = id;
    });
    marker.addListener('mouseout', () => {
      this.viewModel.model.markerOver = null;
    });

    this.markers.push(marker);
  }

  /**
   * @param {number} id
   */
  removePoint(id) {
    if (this.markers.length > id) {
      this.markers[id].setMap(null);
      this.markers.splice(id, 1);
      if (this.polies.length) {
        this.polies[0].getPath().removeAt(id);
      }
      if (this.viewModel.model.markerSelect === id) {
        this.popupClose();
      }
    }
  }

  /**
   * Open popup on marker with given id
   * @param {number} id
   * @param {google.maps.Marker} marker
   */
  popupOpen(id, marker) {
    this.popup.setContent(this.viewModel.getPopupElement(id));
    this.popup.open(this.map, marker);
    this.viewModel.model.markerSelect = id;
  }

  /**
   * Close popup
   */
  popupClose() {
    this.viewModel.model.markerSelect = null;
    this.popup.close();
  }

  /**
   * Animate marker
   * @param id Marker sequential id
   */
  animateMarker(id) {
    if (this.popup.getMap()) {
      this.popupClose();
      clearTimeout(this.timeoutHandle);
    }
    const icon = this.markers[id].getIcon();
    this.markers[id].setIcon(GoogleMapsApi.getMarkerIcon(config.colorHilite, false, false));
    this.markers[id].setAnimation(google.maps.Animation.BOUNCE);
    this.timeoutHandle = setTimeout(() => {
      this.markers[id].setIcon(icon);
      this.markers[id].setAnimation(null);
    }, 2000);
  }

  /**
   * Get map bounds
   * @returns {number[]} Bounds [ lon_sw, lat_sw, lon_ne, lat_ne ]
   */
  getBounds() {
    const bounds = this.map.getBounds();
    const lat_sw = bounds.getSouthWest().lat();
    const lon_sw = bounds.getSouthWest().lng();
    const lat_ne = bounds.getNorthEast().lat();
    const lon_ne = bounds.getNorthEast().lng();
    return [ lon_sw, lat_sw, lon_ne, lat_ne ];
  }

  /**
   * Zoom to track extent
   */
  zoomToExtent() {
    const bounds = new google.maps.LatLngBounds();
    for (let i = 0; i < this.markers.length; i++) {
      bounds.extend(this.markers[i].getPosition());
    }
    this.map.fitBounds(bounds);
  }

  /**
   * Zoom to bounds
   * @param {number[]} bounds [ lon_sw, lat_sw, lon_ne, lat_ne ]
   */
  zoomToBounds(bounds) {
    const sw = new google.maps.LatLng(bounds[1], bounds[0]);
    const ne = new google.maps.LatLng(bounds[3], bounds[2]);
    const latLngBounds = new google.maps.LatLngBounds(sw, ne);
    this.map.fitBounds(latLngBounds);
  }

  /**
   * Is given position within viewport
   * @param {number} id
   * @return {boolean}
   */
  isPositionVisible(id) {
    if (id >= this.markers.length) {
      return false;
    }
    return this.map.getBounds().contains(this.markers[id].getPosition());
  }

  /**
   * Center to given position
   * @param {number} id
   */
  centerToPosition(id) {
    if (id < this.markers.length) {
      this.map.setCenter(this.markers[id].getPosition());
    }
  }

  /**
   * Update size
   */
  // eslint-disable-next-line class-methods-use-this
  updateSize() {
    // ignore for google API
  }

  /**
   * Set default track style
   */
  // eslint-disable-next-line class-methods-use-this
  setTrackDefaultStyle() {
    // ignore for google API
  }

  /**
   * Set gradient style for given track property and scale
   * @param {uTrack} track
   * @param {string} property
   * @param {{ minValue: number, maxValue: number, minColor: number[], maxColor: number[] }} scale
   */
  // eslint-disable-next-line class-methods-use-this,no-unused-vars
  setTrackGradientStyle(track, property, scale) {
    // ignore for google API
  }

    static get loadTimeoutMs() {
    return 10000;
  }

  /**
   * Set map state
   * Note: ignores rotation
   * @param {MapParams} state
   */
  updateState(state) {
    this.map.setCenter({ lat: state.center[0], lng: state.center[1] });
    this.map.setZoom(state.zoom);
  }

  /**
   * Get map state
   * Note: ignores rotation
   * @return {MapParams|null}
   */
  getState() {
    if (this.map) {
      const center = this.map.getCenter();
      return {
        center: [ center.lat(), center.lng() ],
        zoom: this.map.getZoom(),
        rotation: 0
      };
    }
    return null;
  }

  // eslint-disable-next-line class-methods-use-this
  saveState() {/* empty */}
}

/** @type {boolean} */
GoogleMapsApi.authError = false;
/** @type {boolean} */
GoogleMapsApi.gmInitialized = false;
