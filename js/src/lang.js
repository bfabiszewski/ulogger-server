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

import uUtils from './utils.js';

export default class uLang {
  constructor() {
    this.strings = {};
    this.config = null;
  }

  /**
   * @param {uConfig} config
   * @param {Object<string, string>} data
   */
  init(config, data) {
    this.config = config;
    if (data) {
      /** @type {Object<string, string>} */
      this.strings = data;
    }
  }

  /**
   * @param {string} name
   * @param {...(string|number)=} params Optional parameters
   * @return {string}
   */
  _(name, ...params) {
    if (typeof this.strings[name] === 'undefined') {
      throw new Error('Unknown localized string');
    }
    if (params.length) {
      return uUtils.sprintf(this.strings[name], ...params);
    }
    return this.strings[name];
  }

  /**
   * @param {string} name
   * @return {string}
   */
  unit(name) {
    const unitName = this.config[name];
    if (typeof this.config[name] === 'undefined') {
      throw new Error('Unknown localized unit');
    }
    return this._(unitName);
  }

  /**
   * Get speed converted to locale units
   * @param {number} ms Speed in meters per second
   * @param {boolean} withUnit
   * @return {(number|string)} String when with unit
   */
  getLocaleSpeed(ms, withUnit) {
    const value = Math.round(ms * this.config.factorSpeed * 100) / 100;
    if (withUnit) {
      return `${value.toLocaleString(this.config.lang)} ${this.unit('unitSpeed')}`;
    }
    return value;
  }

  /**
   * Get distance converted to locale units
   * @param {number} m Distance in meters
   * @param {boolean} withUnit
   * @return {(number|string)} String when with unit
   */
  getLocaleDistanceMajor(m, withUnit) {
    const value = Math.round(m * this.config.factorDistanceMajor / 10) / 100;
    if (withUnit) {
      return `${value.toLocaleString(this.config.lang)} ${this.unit('unitDistanceMajor')}`
    }
    return value;
  }

  /**
   * @param {number} m Distance in meters
   * @param {boolean} withUnit
   * @return {(number|string)} String when with unit
   */
  getLocaleDistance(m, withUnit) {
    const value = Math.round(m * this.config.factorDistance * 100) / 100;
    if (withUnit) {
      return `${value.toLocaleString(this.config.lang)} ${this.unit('unitDistance')}`;
    }
    return value;
  }

  /**
   * @param {number} m Distance in meters
   * @param {boolean} withUnit
   * @return {(number|string)} String when with unit
   */
  getLocaleAltitude(m, withUnit) {
    return this.getLocaleDistance(m, withUnit);
  }

  /**
   * @param {number} m Distance in meters
   * @param {boolean} withUnit
   * @return {(number|string)} String when with unit
   */
  getLocaleAccuracy(m, withUnit) {
    return this.getLocaleDistance(m, withUnit);
  }

  /**
   * @param {number} s Duration in seconds
   * @return {string} Formatted to (d) h:m:s
   */
  getLocaleDuration(s) {
    const d = Math.floor(s / 86400);
    const h = Math.floor((s % 86400) / 3600);
    const m = Math.floor(((s % 86400) % 3600) / 60);
    s = ((s % 86400) % 3600) % 60;
    return ((d > 0) ? (`${d} ${this.unit('unitDay')} `) : '') +
      ((`00${h}`).slice(-2)) + ':' + ((`00${m}`).slice(-2)) + ':' + ((`00${s}`).slice(-2)) + '';
  }

  /**
   * @param {uPosition} pos
   * @return {string}
   */
  getLocaleCoordinates(pos) {
    return `${this.coordStr(pos.longitude, true)} ${this.coordStr(pos.latitude, false)}`;
  }

  /**
   * @param {number} pos
   * @param {boolean} isLon
   * @return {string}
   */
  coordStr(pos, isLon) {
    const ipos = Math.trunc(pos);
    const dec = Math.abs((pos - ipos) * 60);
    let dir;

    if (isLon) {
      dir = pos < 0 ? 'W' : 'E';
    } else {
      dir = pos < 0 ? 'S' : 'N';
    }
    return `${Math.abs(ipos).toLocaleString(this.config.lang)}°${dec.toLocaleString(this.config.lang, { maximumFractionDigits: 2 })}'${dir}`;
  }

  getLocalePassRules() {
    let rulesStr = '';
    if (this.config.passLenMin > 0) {
      rulesStr = uUtils.sprintf(this._('passlenmin') + '\n', this.config.passLenMin);
    }
    if (this.config.passStrength > 0 && this.config.passStrength < 4) {
      rulesStr += this._(`passrules_${this.config.passStrength}`);
    }
    return rulesStr;
  }

  /**
   * Get languages list { langCode: langName }
   * @return {Object.<string, string>}
   */
  getLangList() {
    return this.strings['langArr'];
  }
}
