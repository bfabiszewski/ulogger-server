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

export default class uBinder {

  constructor() {
    /** @type {Map<string, uEvent>} */
    this.events = new Map();
  }

  /**
   * @param {string} type
   */
  addEvent(type) {
    this.events.set(type, new uEvent(type));
  }

  /**
   * @param {string} type
   * @param {(Object|Function)} listener
   */
  addEventListener(type, listener) {
    if (!this.events.has(type)) {
      this.addEvent(type);
    }
    if ((typeof listener === 'object') &&
      (typeof listener.handleEvent === 'function')) {
      listener = listener.handleEvent.bind(listener);
    }
    if (typeof listener !== 'function') {
      throw new Error(`Wrong listener type: ${typeof listener}`);
    }
    this.events.get(type).addListener(listener);
  }

  /**
   * @param {string} type
   * @param {(Object|Function)} listener
   */
  removeEventListener(type, listener) {
    if (this.events.has(type)) {
      if ((typeof listener === 'object') &&
        (typeof listener.handleEvent === 'function')) {
        listener = listener.handleEvent;
      }
      this.events.get(type).removeListener(listener);
    }
  }

  /**
   * @param {string} type
   * @param {*=} args
   */
  dispatchEvent(type, args) {
    if (this.events.has(type)) {
      this.events.get(type).dispatch(args);
    }
  }
}
