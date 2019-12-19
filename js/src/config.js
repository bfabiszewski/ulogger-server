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

import uObserve from './observe.js';

/**
 * @class uConfig
 * @property {number} interval;
 * @property {string} units
 * @property {string} mapApi
 * @property {?string} gkey
 * @property {Object<string, string>} olLayers
 * @property {number} initLatitude
 * @property {number} initLongitude
 * @property {RegExp} passRegex
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
    this.gkey = null;
    this.olLayers = {};
    this.initLatitude = 52.23;
    this.initLongitude = 21.01;
    this.passRegex = new RegExp('(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.{12,})');
    this.strokeWeight = 2;
    this.strokeColor = '#ff0000';
    this.strokeOpacity = 1;
    // marker colors
    this.colorNormal = '#fff';
    this.colorStart = '#55b500';
    this.colorStop = '#ff6a00';
    this.colorExtra = '#ccc';
    this.colorHilite = '#feff6a';
    this.initUnits();
  }

  initUnits() {
    if (this.units === 'imperial') {
      this.factorSpeed = 0.62; // to mph
      this.unitSpeed = 'unitmph';
      this.factorDistance = 3.28; // to feet
      this.unitDistance = 'unitft';
      this.factorDistanceMajor = 0.62; // to miles
      this.unitDistanceMajor = 'unitmi';
    } else if (this.units === 'nautical') {
      this.factorSpeed = 0.54; // to knots
      this.unitSpeed = 'unitkt';
      this.factorDistance = 1; // meters
      this.unitDistance = 'unitm';
      this.factorDistanceMajor = 0.54; // to nautical miles
      this.unitDistanceMajor = 'unitnm';
    } else {
      this.factorSpeed = 1;
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
        if (data.hasOwnProperty(property) && this.hasOwnProperty(property)) {
          this[property] = data[property];
        }
      }
      if (data.passRegex) {
        const re = data.passRegex;
        this.passRegex = new RegExp(re.substr(1, re.length - 2));
      }
      this.initUnits();
    }
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
}
