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

/**
 * @abstract
 */
export default class uData {
  /**
   * @param {number} key
   * @param {string} value
   * @param {string} keyProperty
   * @param {string} valueProperty
   */
  // eslint-disable-next-line max-params
  constructor(key, value, keyProperty, valueProperty) {
    this[keyProperty] = key;
    this[valueProperty] = value;
    Object.defineProperty(this, 'key', {
      get() {
        return this[keyProperty];
      }
    });
    Object.defineProperty(this, 'value', {
      get() {
        return this[valueProperty];
      }
    });
  }

  /**
   * @param {uBinder} binder
   */
  set binder(binder) {
    this._binder = binder;
  }

  /**
   * @returns {uBinder}
   */
  get binder() {
    return this._binder;
  }

  /**
   * Dispatch event
   * @param {string} type
   * @param {*=} args Defaults to this
   */
  emit(type, args) {
    const data = args || this;
    this.binder.dispatchEvent(type, data);
  }
}
