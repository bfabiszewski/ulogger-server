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

export default class uUtils {

  /**
   * Set cookie
   * @param {string} name
   * @param {(string|number)} value
   * @param {number=} days
   */
  static setCookie(name, value, days) {
    let expires = '';
    if (days) {
      const date = new Date();
      date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
      expires = `; expires=${date.toUTCString()}`;
    }
    document.cookie = `ulogger_${name}=${value}${expires}; path=/`;
  }

  /**
   * sprintf, naive approach, only %s, %d supported
   * @param {string} fmt String
   * @param {...(string|number)=} params Optional parameters
   * @returns {string}
   */
  static sprintf(fmt, params) { // eslint-disable-line no-unused-vars
    const args = Array.prototype.slice.call(arguments);
    const format = args.shift();
    let i = 0;
    return format.replace(/%%|%s|%d/g, (match) => {
      if (match === '%%') {
        return '%';
      }
      return (typeof args[i] != 'undefined') ? args[i++] : match;
    });
  }

  /**
   * Add script tag
   * @param {string} url attribute
   * @param {string} id attribute
   * @param {Function=} onload
   */
  static addScript(url, id, onload) {
    if (id && document.getElementById(id)) {
      return;
    }
    const tag = document.createElement('script');
    tag.type = 'text/javascript';
    tag.src = url;
    if (id) {
      tag.id = id;
    }
    tag.async = true;
    if (onload instanceof Function) {
      tag.onload = onload;
    }

    document.getElementsByTagName('head')[0].appendChild(tag);
  }

  /**
   * Encode string for HTML
   * @param {string} s
   * @returns {string}
   */
  static htmlEncode(s) {
    return s.replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  /**
   * Convert hex string and opacity to an rgba string
   * @param {string} hex
   * @param {number} opacity
   * @returns {string}
   */
  static hexToRGBA(hex, opacity) {
    return 'rgba(' + (hex = hex.replace('#', ''))
      .match(new RegExp('(.{' + hex.length / 3 + '})', 'g'))
      .map((l) => parseInt(hex.length % 2 ? l + l : l, 16))
      .concat(opacity || 1).join(',') + ')';
  }

  /**
   * Add link tag with type css
   * @param {string} url attribute
   * @param {string} id attribute
   */
  static addCss(url, id) {
    if (id && document.getElementById(id)) {
      return;
    }
    const tag = document.createElement('link');
    tag.type = 'text/css';
    tag.rel = 'stylesheet';
    tag.href = url;
    if (id) {
      tag.id = id;
    }
    document.getElementsByTagName('head')[0].appendChild(tag);
  }

  /**
   * @param {string} html HTML representing a single element
   * @return {Node}
   */
  static nodeFromHtml(html) {
    const template = document.createElement('template');
    template.innerHTML = html;
    return template.content.firstChild;
  }

  /**
   * @param {string} html HTML representing a single element
   * @return {NodeList}
   */
  static nodesFromHtml(html) {
    const template = document.createElement('template');
    template.innerHTML = html;
    return template.content.childNodes;
  }

  /**
   *
   * @param {NodeList} nodeList
   * @param {string} selector
   * @return {?Element}
   */
  static querySelectorInList(nodeList, selector) {
    for (const node of nodeList) {
      if (node instanceof HTMLElement) {
        const el = node.querySelector(selector);
        if (el) {
          return el;
        }
      }
    }
    return null;
  }

  /**
   * Get value of first XML child node with given name
   * @param {(Element|XMLDocument)} node
   * @param {string} name Node name
   * @returns {?string} Node value or null if not found
   */
  static getNode(node, name) {
    const el = node.getElementsByTagName(name);
    if (el.length) {
      const children = el[0].childNodes;
      if (children.length) {
        return children[0].nodeValue;
      }
    }
    return null;
  }

  /**
   * Get value of first XML child node with given name
   * @param {(Element|XMLDocument)} node
   * @param {string} name Node name
   * @returns {?number} Node value or null if not found
   */
  static getNodeAsFloat(node, name) {
    const str = uUtils.getNode(node, name);
    if (str != null) {
      return parseFloat(str);
    }
    return null;
  }

  /**
   * Get value of first XML child node with given name
   * @param {(Element|XMLDocument)} node
   * @param {string} name Node name
   * @returns {?number} Node value or null if not found
   */
  static getNodeAsInt(node, name) {
    const str = uUtils.getNode(node, name);
    if (str != null) {
      return parseInt(str);
    }
    return null;
  }

  /**
   * Get value of first XML child node with given name
   * @param {(Element|XMLDocument)} node
   * @param {string} name Node name
   * @returns {Object<string, string>} Node value or null if not found
   */
  static getNodesArray(node, name) {
    const el = node.getElementsByTagName(name);
    if (el.length) {
      const obj = {};
      const children = el[0].childNodes;
      for (const child of children) {
        if (child.nodeType === Node.ELEMENT_NODE) {
          obj[child.nodeName] = child.firstChild ? child.firstChild.nodeValue : '';
        }
      }
      return obj;
    }
    return null;
  }

  /**
   * Get value of first XML child node with given name
   * @param {(Element|XMLDocument)} node
   * @param {string} name Node name
   * @returns {?number} Node value or null if not found
   */
  static getAttributeAsInt(node, name) {
    const str = node.getAttribute(name);
    if (str != null) {
      return parseInt(str);
    }
    return null;
  }

  /**
   * Format date to date, time and time zone strings
   * Simplify zone name, eg.
   * date: 2017-06-14, time: 11:42:19, zone: GMT+2 CEST
   * @param {Date} date
   * @return {{date: string, time: string, zone: string}}
   */
  static getTimeString(date) {
    let timeZone = '';
    const dateStr = `${date.getFullYear()}-${(`0${date.getMonth() + 1}`).slice(-2)}-${(`0${date.getDate()}`).slice(-2)}`;
    const timeStr = date.toTimeString().replace(/^\s*([^ ]+)([^(]*)(\([^)]*\))*/,
      // eslint-disable-next-line max-params
      (_, hours, zone, dst) => {
        if (zone) {
          timeZone = zone.replace(/(0(?=[1-9]00))|(00\b)/g, '');
          if (dst && (/[A-Z]/).test(dst)) {
            timeZone += dst.match(/\b[A-Z]+/g).join('');
          }
        }
        return hours;
      });
    return { date: dateStr, time: timeStr, zone: timeZone };
  }
}

// seconds to (d) H:M:S
Number.prototype.toHMS = function () {
  let s = this;
  const d = Math.floor(s / 86400);
  const h = Math.floor((s % 86400) / 3600);
  const m = Math.floor(((s % 86400) % 3600) / 60);
  s = ((s % 86400) % 3600) % 60;
  return ((d > 0) ? (d + ' d ') : '') + (('00' + h).slice(-2)) + ':' + (('00' + m).slice(-2)) + ':' + (('00' + s).slice(-2)) + '';
};

// meters to km
Number.prototype.toKm = function () {
  return Math.round(this / 10) / 100;
};

// m/s to km/h
Number.prototype.toKmH = function () {
  return Math.round(this * 3600 / 10) / 100;
};
