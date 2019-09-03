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
   * @param {number} value
   */
  set factor_kmh(value) {
    this._factor_kmh = value;
  }

  /**
   * @return {string}
   */
  get unit_kmh() {
    return this._unit_kmh;
  }

  /**
   * @param {string} value
   */
  set unit_kmh(value) {
    this._unit_kmh = value;
  }

  /**
   * @return {number}
   */
  get factor_m() {
    return this._factor_m;
  }

  /**
   * @param {number} value
   */
  set factor_m(value) {
    this._factor_m = value;
  }

  /**
   * @return {string}
   */
  get unit_m() {
    return this._unit_m;
  }

  /**
   * @param {string} value
   */
  set unit_m(value) {
    this._unit_m = value;
  }

  /**
   * @return {number}
   */
  get factor_km() {
    return this._factor_km;
  }

  /**
   * @param {number} value
   */
  set factor_km(value) {
    this._factor_km = value;
  }

  /**
   * @return {string}
   */
  get unit_km() {
    return this._unit_km;
  }

  /**
   * @param {string} value
   */
  set unit_km(value) {
    this._unit_km = value;
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
