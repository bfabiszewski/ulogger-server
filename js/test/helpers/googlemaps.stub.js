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

/* eslint-disable func-style,lines-between-class-members,class-methods-use-this,max-classes-per-file */
export const setupGmapsStub = () => {
  // noinspection JSUnresolvedVariable,JSConstantReassignment,JSUnusedGlobalSymbols
  window.google = {
    maps: {
      Animation: {
        BOUNCE: 1,
        DROP: 2
      },
      event: {
        addListener: () => {/* ignore */},
        addListenerOnce: () => {/* ignore */},
        removeListener: () => {/* ignore */},
        clearListeners: () => {/* ignore */}
      },
      Icon: class Icon {/* ignore */},
      InfoWindow: class InfoWindow {
        addListener() {/* ignore */}
        open() {/* ignore */}
        close() {/* ignore */}
        getMap() {/* ignore */}
        setContent() {/* ignore */}
      },
      LatLng: class LatLng {
        constructor(lat, lng) {
          this.latitude = parseFloat(lat);
          this.longitude = parseFloat(lng);
        }
        lat() { return this.latitude; }
        lng() { return this.longitude; }
      },
      LatLngBounds: class LatLngBounds {
        constructor(sw, ne) {
          this.sw = sw;
          this.ne = ne;
        }
        contains() {/* ignore */}
        extend() {/* ignore */}
        getNorthEast() { return this.ne; }
        getSouthWest() { return this.sw; }
      },
      Map: class Map {
        fitBounds() {/* ignore */}
        getBounds() {/* ignore */}
        getCenter() {/* ignore */}
        getDiv() {/* ignore */}
        getZoom() {/* ignore */}
        setCenter() {/* ignore */}
        setMapTypeId() {/* ignore */}
        setOptions() {/* ignore */}
        setZoom() {/* ignore */}
      },
      MapTypeId: {
        HYBRID: 1,
        ROADMAP: 2,
        SATELLITE: 3,
        TERRAIN: 4
      },
      Marker: class Marker {
        addListener() {/* ignore */}
        getIcon() {/* ignore */}
        getPosition() {/* ignore */}
        setAnimation() {/* ignore */}
        setIcon() {/* ignore */}
        setMap() {/* ignore */}
      },
      Point: class Point {/* ignore */},
      Polyline: class Polyline {
        constructor(opts) {
          this.options = opts;
          this.path = [];
        }
        getPath() { return this.path; }
        setMap() {/* ignore */}
      }
    }
  };
};

export const clear = () => {
  // noinspection JSAnnotator,JSUnresolvedVariable,JSConstantReassignment
  delete window.google;
};
