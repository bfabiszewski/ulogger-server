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

import uAuth from './auth.js';
import uConfig from './config.js';
import uUser from './user.js';
import uUtils from './utils.js';

class uConstants {

  constructor() {
    this.auth = {};
    this.config = {};
    this.lang = {};
    if (!this.loaded) {
      this.initialize();
    }
  }

  /**
   * @return {?XMLDocument}
   */
  static fetch() {
    let xml = null;
    const request = new XMLHttpRequest();
    request.open('GET', 'utils/getconstants.php', false);
    request.send(null);
    if (request.status === 200) {
      xml = request.responseXML;
    }
    return xml;
  }

  initialize() {
    const xml = uConstants.fetch();
    if (xml) {
      this.initAuth(xml);
      this.initConfig(xml);
      this.initLang(xml);
      this.loaded = true;
    }
  }

  /**
   * @param {XMLDocument} xml
   */
  initAuth(xml) {
    this.auth = new uAuth();
    const authNode = xml.getElementsByTagName('auth');
    if (authNode.length) {
      const isAuthenticated = uUtils.getNodeAsInt(authNode[0], 'isAuthenticated') === 1;
      if (isAuthenticated) {
        const id = uUtils.getNodeAsInt(authNode[0], 'userId');
        const login = uUtils.getNode(authNode[0], 'userLogin');
        this.auth.user = new uUser(id, login);
        this.auth.isAdmin = uUtils.getNodeAsInt(authNode[0], 'isAdmin') === 1;
      }
    }
  }

  /**
   * @param {XMLDocument} xml
   */
  initLang(xml) {
    const langNode = xml.getElementsByTagName('lang');
    if (langNode.length) {
      /** @type {Object<string, string>} */
      this.lang.strings = uUtils.getNodesArray(langNode[0], 'strings');
    }
  }

  /**
   * @param {XMLDocument} xml
   */
  initConfig(xml) {
    this.config = new uConfig();
    const configNode = xml.getElementsByTagName('config');
    if (configNode.length) {
      this.config.interval = uUtils.getNodeAsInt(configNode[0], 'interval');
      this.config.units = uUtils.getNode(configNode[0], 'units');
      this.config.mapapi = uUtils.getNode(configNode[0], 'mapapi');
      this.config.gkey = uUtils.getNode(configNode[0], 'gkey');
      this.config.ol_layers = uUtils.getNodesArray(configNode[0], 'ol_layers');
      this.config.init_latitude = uUtils.getNodeAsFloat(configNode[0], 'init_latitude');
      this.config.init_longitude = uUtils.getNodeAsFloat(configNode[0], 'init_longitude');
      const re = uUtils.getNode(configNode[0], 'pass_regex');
      this.config.pass_regex = new RegExp(re.substr(1, re.length - 2));
      this.config.strokeWeight = uUtils.getNodeAsInt(configNode[0], 'strokeWeight');
      this.config.strokeColor = uUtils.getNode(configNode[0], 'strokeColor');
      this.config.strokeOpacity = uUtils.getNodeAsInt(configNode[0], 'strokeOpacity');
      this.config.factor_kmh = 1;
      this.config.unit_kmh = 'km/h';
      this.config.factor_m = 1;
      this.config.unit_m = 'm';
      this.config.factor_km = 1;
      this.config.unit_km = 'km';
      if (this.config.units === 'imperial') {
        this.config.factor_kmh = 0.62; // to mph
        this.config.unit_kmh = 'mph';
        this.config.factor_m = 3.28; // to feet
        this.config.unit_m = 'ft';
        this.config.factor_km = 0.62; // to miles
        this.config.unit_km = 'mi';
      } else if (this.config.units === 'nautical') {
        this.config.factor_kmh = 0.54; // to knots
        this.config.unit_kmh = 'kt';
        this.config.factor_m = 1; // meters
        this.config.unit_m = 'm';
        this.config.factor_km = 0.54; // to nautical miles
        this.config.unit_km = 'nm';
      }
      this.config.showLatest = false;
      // marker colors
      this.config.colorNormal = '#fff';
      this.config.colorStart = '#55b500';
      this.config.colorStop = '#ff6a00';
      this.config.colorExtra = '#ccc';
      this.config.colorHilite = '#feff6a';
    }
  }
}

const constants = new uConstants();
/** @type {uConfig} */
export const config = constants.config;
/** @type {{strings: Object<string, string>}} */
export const lang = constants.lang;
/** @type {uAuth} */
export const auth = constants.auth;
