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

import * as gmApi from './mapapi/api_gmaps.js';
import * as olApi from './mapapi/api_openlayers.js';
import { config, lang } from './constants.js';
import uEvent from './event.js';
import { uLogger } from './ulogger.js';
import uUtils from './utils.js';

/**
 * @typedef {Object} uMap.api
 * @memberOf uMap
 * @type {Object}
 * @property {string} name
 * @property {function(uBinder, HTMLElement)} init
 * @property {function} cleanup
 * @property {function(uTrack, boolean)} displayTrack
 * @property {function} clearMap
 * @property {function(number)} animateMarker
 * @property {function} getBounds
 * @property {function} zoomToExtent
 * @property {function} zoomToBounds
 * @property {function} updateSize
 */

/**
 * @class uMap
 * @property {number} loadTime
 * @property {?Array<number>} savedBounds
 * @property {?(gmApi|olApi)} api
 * @property {?HTMLElement} mapElement
 */
export default class uMap {

  /**
   * @param {uBinder} binder
   */
  constructor(binder) {
    binder.addEventListener(uEvent.API_CHANGE, this);
    binder.addEventListener(uEvent.CHART_CLICKED, this);
    binder.addEventListener(uEvent.TRACK_READY, this);
    binder.addEventListener(uEvent.UI_READY, this);
    this.loadTime = 0;
    this.savedBounds = null;
    this.api = null;
    this.mapElement = null;
    this.lastTrackId = null;
    this._binder = binder;
    this.track = null;
  }

  /**
   * Dynamic change of map api
   * @param {string=} apiName API name
   */
  loadMapAPI(apiName) {
    if (apiName) {
      config.mapapi = apiName;
      try {
        this.savedBounds = this.api.getBounds();
      } catch (e) {
        this.savedBounds = null;
      }
      this.api.cleanup();
    }
    if (config.mapapi === 'gmaps') {
      this.api = gmApi;
    } else {
      this.api = olApi;
    }
    this.waitAndInit();
  }

  /**
   * Try to initialize map engine
   */
  waitAndInit() {
    // wait till main api loads
    if (this.loadTime > 10000) {
      this.loadTime = 0;
      alert(uUtils.sprintf(lang.strings['apifailure'], config.mapapi));
      return;
    }
    try {
      this.api.init(this._binder, this.mapElement);
    } catch (e) {
      setTimeout(() => {
        this.loadTime += 50;
        this.waitAndInit();
      }, 50);
      return;
    }
    this.loadTime = 0;
    if (this.savedBounds) {
      this.api.zoomToBounds(this.savedBounds);
    }
    uLogger.trackList.onChange();
    // save current api as default
    uUtils.setCookie('api', config.mapapi, 30);
  }

  /**
   *
   * @param {uEvent} event
   * @param {*=} args
   */
  handleEvent(event, args) {
    if (event.type === uEvent.TRACK_READY) {
      /** @type {uTrack} */
      const track = args;
      this.api.clearMap();
      const onlyReload = track.id !== this.lastTrackId;
      this.api.displayTrack(track, onlyReload);
      this.lastTrackId = track.id;
    } else if (event.type === uEvent.UI_READY) {
      /** @type {uUI} */
      const ui = args;
      this.mapElement = ui.map;
      this.loadMapAPI();
    } else if (event.type === uEvent.API_CHANGE) {
      /** @type {string} */
      const api = args;
      this.loadMapAPI(api);
    } else if (event.type === uEvent.CHART_CLICKED) {
      /** @type {number} */
      const id = args;
      this.api.animateMarker(id);
    }
  }
}
