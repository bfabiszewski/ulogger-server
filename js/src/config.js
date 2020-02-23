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

import uAjax from './ajax.js';
import uLayerCollection from './layercollection.js';
import uObserve from './observe.js';

/**
 * @class uConfig
 * @property {number} interval;
 * @property {string} units
 * @property {string} lang
 * @property {string} mapApi
 * @property {string} googleKey
 * @property {uLayerCollection} olLayers
 * @property {number} initLatitude
 * @property {number} initLongitude
 * @property {number} initLongitude
 * @property {boolean} requireAuth
 * @property {boolean} publicTracks
 * @property {number} passStrength
 * @property {number} passLenMin
 * @property {number} strokeWeight
 * @property {string} strokeColor
 * @property {number} strokeOpacity
 * @property {boolean} showLatest
 * @property {string} colorNormal
 * @property {string} colorStart
 * @property {string} colorStop
 * @property {string} colorExtra
 * @property {string} colorHilite
 */
export default class uConfig {

  constructor() {
    this.initialize();
  }

  initialize() {
    this.interval = 10;
    this.units = 'metric';
    this.lang = 'en';
    this.mapApi = 'openlayers';
    this.googleKey = '';
    this.olLayers = new uLayerCollection();
    this.initLatitude = 52.23;
    this.initLongitude = 21.01;
    this.requireAuth = true;
    this.publicTracks = false;
    this.passStrength = 2;
    this.passLenMin = 10;
    this.strokeWeight = 2;
    this.strokeColor = '#ff0000';
    this.strokeOpacity = 1;
    // marker colors
    this.colorNormal = '#ffffff';
    this.colorStart = '#55b500';
    this.colorStop = '#ff6a00';
    this.colorExtra = '#cccccc';
    this.colorHilite = '#feff6a';
    this.initUnits();
  }

  initUnits() {
    if (this.units === 'imperial') {
      this.factorSpeed = 2.237; // m/s to mph
      this.unitSpeed = 'unitmph';
      this.factorDistance = 3.28; // m to feet
      this.unitDistance = 'unitft';
      this.factorDistanceMajor = 0.621; // km to miles
      this.unitDistanceMajor = 'unitmi';
    } else if (this.units === 'nautical') {
      this.factorSpeed = 1.944; // m/s to kt
      this.unitSpeed = 'unitkt';
      this.factorDistance = 1; // meters
      this.unitDistance = 'unitm';
      this.factorDistanceMajor = 0.54; // km to nautical miles
      this.unitDistanceMajor = 'unitnm';
    } else {
      this.factorSpeed = 3.6; // m/s to km/h
      this.unitSpeed = 'unitkmh';
      this.factorDistance = 1;
      this.unitDistance = 'unitm';
      this.factorDistanceMajor = 1;
      this.unitDistanceMajor = 'unitkm';
    }
    this.unitDay = 'unitday';
  }

  /**
   * Load config values from data object
   * @param {Object} data
   */
  load(data) {
    if (data) {
      for (const property in data) {
        if (property === 'layers') {
          this.olLayers.load(data[property]);
        } else if (data.hasOwnProperty(property) && this.hasOwnProperty(property)) {
          this[property] = data[property];
        }
      }
      this.initUnits();
    }
  }

  /**
   * Save config values from data object
   * @param {Object} data
   */
  save(data) {
    this.load(data);
    data = Object.keys(this)
      .filter((key) => typeof this[key] !== 'function')
      .reduce((obj, key) => {
        obj[key] = this[key];
        return obj;
      }, {});
    return uAjax.post('utils/saveconfig.php', data);
  }

  reinitialize() {
    uObserve.unobserveAll(this);
    this.initialize();
  }

  /**
   * @param {string} property
   * @param {ObserveCallback} callback
   */
  onChanged(property, callback) {
    uObserve.observe(this, property, callback);
  }


  /**
   * @param {string} password
   * @return {boolean}
   */
  validPassStrength(password) {
    return this.getPassRegExp().test(password);
  }

  /**
   * Set password validation regexp
   * @return {RegExp}
   */
  getPassRegExp() {
    let regex = '';
    if (this.passStrength > 0) {
      // lower and upper case
      regex += '(?=.*[a-z])(?=.*[A-Z])';
    }
    if (this.passStrength > 1) {
      // digits
      regex += '(?=.*[0-9])';
    }
    if (this.passStrength > 2) {
      // not latin, not digits
      regex += '(?=.*[^a-zA-Z0-9])';
    }
    if (this.passLenMin > 0) {
      regex += `(?=.{${this.passLenMin},})`;
    }
    if (regex.length === 0) {
      regex = '.*';
    }
    return new RegExp(regex);
  }
}
