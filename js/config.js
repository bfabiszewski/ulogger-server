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

import uEvent from './event.js';

export default class uConfig {

  constructor() {
    this.inititialize();
  }

  inititialize() {
    this.interval = 10;
    this.units = 'metric';
    this.mapapi = 'openlayers';
    this.gkey = null;
    this.ol_layers = {};
    this.init_latitude = 52.23;
    this.init_longitude = 21.01;
    this.pass_regex = new RegExp('(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.{12,})');
    this.strokeWeight = 2;
    this.strokeColor = '#ff0000';
    this.strokeOpacity = 1;
    this.showLatest = false;
    // marker colors
    this.colorNormal = '#fff';
    this.colorStart = '#55b500';
    this.colorStop = '#ff6a00';
    this.colorExtra = '#ccc';
    this.colorHilite = '#feff6a';
  }

  /**
   *
   * @param {uBinder} binder
   */
  set binder(binder) {
    this._binder = binder;
  }

  /**
   * Dispatch event
   * @param {string} property
   */
  notify(property) {
    if (this._binder) {
      this._binder.dispatchEvent(uEvent.CONFIG, property);
    }
  }

  /**
   * @return {number}
   */
  get interval() {
    return this._interval;
  }

  /**
   * @param {number} value
   */
  set interval(value) {
    this._interval = value;
  }

  /**
   * @return {string}
   */
  get units() {
    return this._units;
  }

  /**
   * @param {string} value
   */
  set units(value) {
    this._units = value;
    if (this._units === 'imperial') {
      this._factor_kmh = 0.62; // to mph
      this._unit_kmh = 'mph';
      this._factor_m = 3.28; // to feet
      this._unit_m = 'ft';
      this._factor_km = 0.62; // to miles
      this._unit_km = 'mi';
    } else if (this._units === 'nautical') {
      this._factor_kmh = 0.54; // to knots
      this._unit_kmh = 'kt';
      this._factor_m = 1; // meters
      this._unit_m = 'm';
      this._factor_km = 0.54; // to nautical miles
      this._unit_km = 'nm';
    } else {
      this._factor_kmh = 1;
      this._unit_kmh = 'km/h';
      this._factor_m = 1;
      this._unit_m = 'm';
      this._factor_km = 1;
      this._unit_km = 'km';
    }
  }

  /**
   * @return {string}
   */
  get mapapi() {
    return this._mapapi;
  }

  /**
   * @param {string} value
   */
  set mapapi(value) {
    this._mapapi = value;
  }

  /**
   * @return {?string}
   */
  get gkey() {
    return this._gkey;
  }

  /**
   * @param {?string} value
   */
  set gkey(value) {
    this._gkey = value;
  }

  /**
   * @return {Object.<string, string>}
   */
  get ol_layers() {
    return this._ol_layers;
  }

  /**
   * @param {Object.<string, string>} value
   */
  set ol_layers(value) {
    this._ol_layers = value;
  }

  /**
   * @return {number}
   */
  get init_latitude() {
    return this._init_latitude;
  }

  /**
   * @param {number} value
   */
  set init_latitude(value) {
    this._init_latitude = value;
  }

  /**
   * @return {number}
   */
  get init_longitude() {
    return this._init_longitude;
  }

  /**
   * @param {number} value
   */
  set init_longitude(value) {
    this._init_longitude = value;
  }

  /**
   * @return {RegExp}
   */
  get pass_regex() {
    return this._pass_regex;
  }

  /**
   * @param {RegExp} value
   */
  set pass_regex(value) {
    this._pass_regex = value;
  }

  /**
   * @return {number}
   */
  get strokeWeight() {
    return this._strokeWeight;
  }

  /**
   * @param {number} value
   */
  set strokeWeight(value) {
    this._strokeWeight = value;
  }

  /**
   * @return {string}
   */
  get strokeColor() {
    return this._strokeColor;
  }

  /**
   * @param {string} value
   */
  set strokeColor(value) {
    this._strokeColor = value;
  }

  /**
   * @return {number}
   */
  get strokeOpacity() {
    return this._strokeOpacity;
  }

  /**
   * @param {number} value
   */
  set strokeOpacity(value) {
    this._strokeOpacity = value;
  }

  /**
   * @return {number}
   */
  get factor_kmh() {
    return this._factor_kmh;
  }

  /**
   * @return {string}
   */
  get unit_kmh() {
    return this._unit_kmh;
  }

  /**
   * @return {number}
   */
  get factor_m() {
    return this._factor_m;
  }

  /**
   * @return {string}
   */
  get unit_m() {
    return this._unit_m;
  }

  /**
   * @return {number}
   */
  get factor_km() {
    return this._factor_km;
  }

  /**
   * @return {string}
   */
  get unit_km() {
    return this._unit_km;
  }

  /**
   * @return {boolean}
   */
  get showLatest() {
    return this._showLatest;
  }

  /**
   * @param {boolean} value
   */
  set showLatest(value) {
    if (this._showLatest !== value) {
      this._showLatest = value;
      this.notify('showLatest');
    }
  }

  /**
   * @return {string}
   */
  get colorNormal() {
    return this._colorNormal;
  }

  /**
   * @param {string} value
   */
  set colorNormal(value) {
    this._colorNormal = value;
  }

  /**
   * @return {string}
   */
  get colorStart() {
    return this._colorStart;
  }

  /**
   * @param {string} value
   */
  set colorStart(value) {
    this._colorStart = value;
  }

  /**
   * @return {string}
   */
  get colorStop() {
    return this._colorStop;
  }

  /**
   * @param {string} value
   */
  set colorStop(value) {
    this._colorStop = value;
  }

  /**
   * @return {string}
   */
  get colorExtra() {
    return this._colorExtra;
  }

  /**
   * @param {string} value
   */
  set colorExtra(value) {
    this._colorExtra = value;
  }

  /**
   * @return {string}
   */
  get colorHilite() {
    return this._colorHilite;
  }

  /**
   * @param {string} value
   */
  set colorHilite(value) {
    this._colorHilite = value;
  }
}
