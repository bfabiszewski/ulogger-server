import * as gmApi from './mapapi/api_gmaps.js';
import * as olApi from './mapapi/api_openlayers.js';
import { config, lang } from './constants.js';
import uEvent from './event.js';
import { uLogger } from './ulogger.js';
import uUtils from './utils.js';

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
    binder.addEventListener(uEvent.TRACK_READY, this);
    binder.addEventListener(uEvent.UI_READY, this);
    binder.addEventListener(uEvent.API_CHANGE, this);
    this.loadTime = 0;
    this.savedBounds = null;
    this.api = null;
    this.mapElement = null;
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
      this.api.init(this.mapElement);
    } catch (e) {
      setTimeout(() => {
        this.loadTime += 50;
        this.waitAndInit();
      }, 50);
      return;
    }
    this.loadTime = 0;
    let update = 1;
    if (this.savedBounds) {
      this.api.zoomToBounds(this.savedBounds);
      update = 0;
    }
    // if (latest && isSelectedAllUsers()) {
    //   loadLastPositionAllUsers();
    // } else {
    //   loadTrack(ns.userId, ns.trackId, update);
    uLogger.trackList.onChange();
    // }
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
      const track = args;
      this.api.clearMap();
      /** @todo use update */
      const update = 1;
      this.api.displayTrack(track, update);
    } else if (event.type === uEvent.UI_READY) {
      /** @type {uUI} */
      const ui = args;
      this.mapElement = ui.map;
      this.loadMapAPI();
    } else if (event.type === uEvent.API_CHANGE) {
      /** @type {string} */
      const api = args;
      this.loadMapAPI(api);
    }
  }
}
