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

export default class uAjax {

  /**
   * Perform POST HTTP request
   * @alias ajax
   */
  static post(url, data, options) {
    const params = options || {};
    params.method = 'POST';
    return this.ajax(url, data, params);
  }

  /**
   * Perform GET HTTP request
   * @alias ajax
   */
  static get(url, data, options) {
    const params = options || {};
    params.method = 'GET';
    return this.ajax(url, data, params);
  }

  /**
   * Perform ajax HTTP request
   * @param {string} url Request URL
   * @param {Object|HTMLFormElement} [data] Optional request parameters: key/value pairs or form element
   * @param {Object} [options] Optional options
   * @param {string} [options.method='GET'] Optional query method, default 'GET'
   * @return {Promise<Document, string>}
   */
  static ajax(url, data, options) {
    const params = [];
    data = data || {};
    options = options || {};
    let method = options.method || 'GET';
    const xhr = new XMLHttpRequest();
    return new Promise((resolve, reject) => {
      xhr.onreadystatechange = function () {
        if (xhr.readyState !== 4) { return; }
        let message = '';
        let error = true;
        if (xhr.status === 200) {
          const xml = xhr.responseXML;
          if (xml) {
            const root = xml.getElementsByTagName('root');
            if (root.length && uUtils.getNode(root[0], 'error') !== '1') {
              if (resolve && typeof resolve === 'function') {
                resolve(xml);
              }
              error = false;
            } else if (root.length) {
              const errorMsg = uUtils.getNode(root[0], 'message');
              if (errorMsg) {
                message = errorMsg;
              }
            }
          }
        }
        if (error && reject && typeof reject === 'function') {
          reject(message);
        }
      };
      let body = null;
      if (data instanceof HTMLFormElement) {
        // noinspection JSCheckFunctionSignatures
        body = new FormData(data);
        method = 'POST';
      } else {
        for (const key in data) {
          if (data.hasOwnProperty(key)) {
            params.push(key + '=' + encodeURIComponent(data[key]));
          }
        }
        body = params.join('&');
        body = body.replace(/%20/g, '+');
      }
      if (method === 'GET' && params.length) {
        url += '?' + body;
        body = null;
      }
      xhr.open(method, url, true);
      if (method === 'POST' && !(data instanceof HTMLFormElement)) {
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
      }
      xhr.send(body);
    });
  }
}
