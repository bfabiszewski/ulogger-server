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

/* eslint-disable func-style */
const stubObj = {};
const stubFn = function() {/* ignore */};
const stubFnObj = function() { return stubObj; };

export const setupGmapsStub = () => {
  // noinspection JSUnresolvedVariable
  window.google = {
    maps: {
      Animation: {
        BOUNCE: 1,
        DROP: 2
      },
      event: {
        addListener: stubFn,
        addListenerOnce: stubFn,
        removeListener: stubFn
      },
      Icon: stubFn,
      InfoWindow: stubFn,
      LatLng: function(lat, lng) {
        this.latitude = parseFloat(lat);
        this.longitude = parseFloat(lng);
      },
      LatLngBounds: function(sw, ne) {
        this.sw = sw;
        this.ne = ne;
      },
      Map: stubFn,
      MapTypeId: {
        HYBRID: 1,
        ROADMAP: 2,
        SATELLITE: 3,
        TERRAIN: 4
      },
      Marker: stubFn,
      Point: stubFnObj,
      Polyline: function(opts) {
        this.options = opts;
        this.path = [];
      }
    }
  };
  applyPrototypes(stubFn, stubObj);
};

export const applyPrototypes = () => {
  window.google.maps.InfoWindow.prototype.addListener = stubFn;
  window.google.maps.InfoWindow.prototype.close = stubFn;
  window.google.maps.InfoWindow.prototype.getMap = stubFn;
  window.google.maps.InfoWindow.prototype.open = stubFn;
  window.google.maps.InfoWindow.prototype.setContent = stubFn;
  window.google.maps.LatLng.prototype.lat = function () { return this.latitude; };
  window.google.maps.LatLng.prototype.lng = function () { return this.longitude; };
  window.google.maps.LatLngBounds.prototype.extend = stubFn;
  window.google.maps.LatLngBounds.prototype.getNorthEast = function () { return this.ne; };
  window.google.maps.LatLngBounds.prototype.getSouthWest = function () { return this.sw; };
  window.google.maps.Map.prototype.fitBounds = stubFn;
  window.google.maps.Map.prototype.getBounds = stubFn;
  window.google.maps.Map.prototype.getCenter = stubFn;
  window.google.maps.Map.prototype.getDiv = stubFn;
  window.google.maps.Map.prototype.getZoom = stubFn;
  window.google.maps.Map.prototype.setCenter = stubFn;
  window.google.maps.Map.prototype.setMapTypeId = stubFn;
  window.google.maps.Map.prototype.setOptions = stubFn;
  window.google.maps.Map.prototype.setZoom = stubFn;
  window.google.maps.Marker.prototype.addListener = stubFn;
  window.google.maps.Marker.prototype.getIcon = stubFn;
  window.google.maps.Marker.prototype.getPosition = stubFn;
  window.google.maps.Marker.prototype.setAnimation = stubFn;
  window.google.maps.Marker.prototype.setIcon = stubFn;
  window.google.maps.Marker.prototype.setMap = stubFn;
  window.google.maps.Polyline.prototype.getPath = function () { return this.path; };
  window.google.maps.Polyline.prototype.setMap = stubFn;
};

export const clear = () => {
  // noinspection JSAnnotator,JSUnresolvedVariable
  delete window.google;
};
