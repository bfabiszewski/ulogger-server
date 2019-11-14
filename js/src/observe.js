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

/* eslint-disable no-underscore-dangle */
export default class uObserve {

  /**
   * Observe object's property or all properties if not specified.
   * On change call observer function.
   * observe(obj, prop, observer) observes given property prop;
   * observe(obj, observer) observes all properties of object obj.
   * @param {Object} obj
   * @param {(string|function)} p1
   * @param {function=} p2
   */
  static observe(obj, p1, p2) {
    if (typeof p2 === 'function') {
      this.observeProperty(obj, p1, p2);
    } else if (typeof p1 === 'function') {
        if (Array.isArray(obj)) {
          this.observeArray(obj, p1);
        } else {
          this.observeRecursive(obj, p1);
        }
    } else {
      throw new Error('Invalid arguments');
    }
  }

  /**
   * Observe object's proporty. On change call observer
   * @param {Object} obj
   * @param {?string} property
   * @param {function} observer
   */
  static observeProperty(obj, property, observer) {
    this.addObserver(obj, observer, property);
    if (!obj.hasOwnProperty('_values')) {
      Object.defineProperty(obj, '_values', { enumerable: false, configurable: false, value: [] });
    }
    obj._values[property] = obj[property];
    Object.defineProperty(obj, property, {
      get: () => obj._values[property],
      set: (newValue) => {
        if (obj._values[property] !== newValue) {
          obj._values[property] = newValue;
          uObserve.notify(obj._observers[property], newValue);
        }
        if (Array.isArray(obj[property])) {
          this.observeArray(obj[property], observer);
        }
      }
    });
    if (Array.isArray(obj[property])) {
      this.observeArray(obj[property], observer);
    }
  }

  /**
   * Recursively add observer to all properties
   * @param {Object} obj
   * @param {function} observer
   */
  static observeRecursive(obj, observer) {
    for (const prop in obj) {
      if (obj.hasOwnProperty(prop)) {
        uObserve.observeProperty(obj, prop, observer);
      }
    }
  }

  /**
   * Observe array
   * @param {Object} arr
   * @param {function} observer
   */
  static observeArray(arr, observer) {
    this.addObserver(arr, observer);
    [ 'pop', 'push', 'reverse', 'shift', 'sort', 'splice', 'unshift' ].forEach(
      (operation) => {
        const descriptor = Object.getOwnPropertyDescriptor(Array.prototype, operation);
        descriptor.value = function () {
          const result = Array.prototype[operation].apply(arr, arguments);
          uObserve.notify(arr._observers, arr);
          return result;
        };
        Object.defineProperty(arr, operation, descriptor);
      });
  }

  /**
   * Store observer in object
   * @param {Object} obj Object
   * @param {function} observer Observer
   * @param {string=} property Optional property
   */
  static addObserver(obj, observer, property) {
    if (!obj.hasOwnProperty('_observers')) {
      Object.defineProperty(obj, '_observers', {
        enumerable: false,
        configurable: false,
        value: (arguments.length === 3) ? [] : new Set()
      });
    }
    if (arguments.length === 3) {
      if (!obj._observers[property]) {
        obj._observers[property] = new Set();
      }
      obj._observers[property].add(observer);
    } else {
      obj._observers.add(observer);
    }
  }

  /**
   * Notify observers
   * @param {Set<function>} observers
   * @param {*} value
   */
  static notify(observers, value) {
    for (const observer of observers) {
      (async () => {
        await observer(value);
      })();
    }
  }
}
