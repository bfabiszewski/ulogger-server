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
   * @param {(string|ObserveCallback)} p1
   * @param {ObserveCallback=} p2
   */
  static observe(obj, p1, p2) {
    if (typeof obj !== 'object' || obj === null) {
      throw new Error('Invalid argument: invalid object');
    }
    if (typeof p2 === 'function') {
      this.observeProperty(obj, p1, p2);
    } else if (typeof p1 === 'function') {
      this.observeRecursive(obj, p1);
    } else {
      throw new Error('Invalid argument for observe');
    }
  }

  /**
   * Notify callback
   * @callback ObserveCallback
   * @param {*} value
   */

  /**
   * Notify observers
   * @param {Set<ObserveCallback>} observers
   * @param {*} value
   */
  static notify(observers, value) {
    for (const observer of observers) {
      (async () => {
        await observer(value);
      })();
    }
  }

  /**
   * Trigger notify of property observers
   * @param {Object} obj
   * @param {string} property
   */
  static forceUpdate(obj, property) {
    const value = obj._values[property];
    const observers = obj._observers[property];
    this.notify(observers, value);
  }

  /**
   * Check if object property is observed;
   * Optionally check if it is observed by given observer
   * @param {Object} obj
   * @param {string} property
   * @param {Function=} observer
   * @return {boolean}
   */
  static isObserved(obj, property, observer) {
    if (typeof obj !== 'object' || obj === null || !obj.hasOwnProperty(property)) {
      return false;
    }
    const isObserved = !!(obj._observers && obj._observers[property] && obj._observers[property].size > 0);
    if (isObserved && observer) {
      return obj._observers[property].has(observer);
    }
    return isObserved;
  }

  /**
   * Set observed property value without notifying observers
   * @param {Object} obj
   * @param {string} property
   * @param {*} value
   */
  static setSilently(obj, property, value) {
    if (!obj.hasOwnProperty(property)) {
      throw new Error(`Invalid argument: object does not have property "${property}"`);
    }
    if (this.isObserved(obj, property)) {
      obj._values[property] = value;
      if (Array.isArray(obj[property])) {
        for (const obs of obj._observers[property]) {
          this.observeArray(obj[property], obs);
        }
      }
    } else {
      obj[property] = value;
    }
  }

  /**
   * Observe object's property. On change call observer
   * @param {Object} obj
   * @param {string} property
   * @param {ObserveCallback} observer
   */
  static observeProperty(obj, property, observer) {
    if (!obj.hasOwnProperty(property)) {
      throw new Error(`Invalid argument: object does not have property "${property}"`);
    }
    if (this.isObserved(obj, property, observer)) {
      throw new Error(`Observer already registered for property ${property}`);
    }
    this.addObserver(obj, observer, property);
    if (!obj.hasOwnProperty('_values')) {
      Object.defineProperty(obj, '_values', { enumerable: false, configurable: false, value: {} });
    }
    obj._values[property] = obj[property];
    Object.defineProperty(obj, property, {
      get: () => obj._values[property],
      set: (newValue) => {
        if (obj._values[property] !== newValue) {
          obj._values[property] = newValue;
          console.log(`${property} = ` + (Array.isArray(newValue) && newValue.length ? `[${newValue[0]}, …](${newValue.length})` : newValue));
          uObserve.notify(obj._observers[property], newValue);
        }
        if (Array.isArray(obj[property])) {
          this.observeArray(obj[property], obj._observers[property]);
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
   * @param {ObserveCallback} observer
   */
  static observeRecursive(obj, observer) {
    if (Array.isArray(obj)) {
      this.observeArray(obj, observer);
    } else {
      for (const prop in obj) {
        if (obj.hasOwnProperty(prop)) {
          uObserve.observeProperty(obj, prop, observer);
        }
      }
    }
  }

  /**
   * Observe array
   * @param {Object} arr
   * @param {(ObserveCallback|Set<ObserveCallback>)} observer
   */
  static observeArray(arr, observer) {
    if (observer instanceof Set) {
      for (const obs of observer) {
        this.addObserver(arr, obs);
      }
    } else {
      this.addObserver(arr, observer);
    }
    this.overrideArrayPrototypes(arr, arguments);
  }

  /**
   * Store observer in object
   * @param {Object} obj Object
   * @param {ObserveCallback} observer Observer
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
   * Remove observer from object's property or all it's properties
   * unobserve(obj, prop, observer) unobserves given property prop;
   * unobserve(obj, observer) unobserves all properties of object obj.
   * @param {Object} obj
   * @param {(string|ObserveCallback)} p1
   * @param {ObserveCallback=} p2
   */
  static unobserve(obj, p1, p2) {
    if (typeof p2 === 'function') {
      this.unobserveProperty(obj, p1, p2);
    } else if (typeof p1 === 'function') {
      if (Array.isArray(obj)) {
        this.unobserveArray(obj, p1);
      } else {
        this.unobserveRecursive(obj, p1);
      }
    } else {
      throw new Error('Invalid argument for unobserve');
    }
  }

  /**
   * Remove all observers from object's property or all it's properties
   * unobserveAll(obj, prop) removes all observes from given property prop;
   * unobserveAll(obj) removes all observers from all properties of object obj.
   * @param {Object} obj
   * @param {string=} property
   */
  static unobserveAll(obj, property) {
    if (arguments.length === 1) {
      for (const prop in obj) {
        if (obj.hasOwnProperty(prop)) {
          this.unobserveAll(obj, prop);
        }
      }
    } else if (this.isObserved(obj, property)) {
      console.log(`Removing all observers for ${property}…`);
      if (Array.isArray(obj[property])) {
        this.restoreArrayPrototypes(obj[property]);
      } else if (typeof obj[property] === 'object' && obj[property] !== null) {
        for (const prop in obj[property]) {
          if (obj[property].hasOwnProperty(prop)) {
            this.unobserveAll(obj[property], prop);
          }
        }
      }
      delete obj._observers[property];
      delete obj[property];
      obj[property] = obj._values[property];
      delete obj._values[property];
    }
  }

  /**
   * Remove observer from object's property
   * @param {Object} obj
   * @param {?string} property
   * @param {ObserveCallback} observer
   */
  static unobserveProperty(obj, property, observer) {
    if (Array.isArray(obj[property])) {
      this.unobserveArray(obj[property], observer);
    }
    this.removeObserver(obj, observer, property);
    if (!obj._observers[property].size) {
      delete obj[property];
      obj[property] = obj._values[property];
      delete obj._values[property];
    }
  }

  /**
   * Recursively remove observers from all properties
   * @param {Object} obj
   * @param {ObserveCallback} observer
   */
  static unobserveRecursive(obj, observer) {
    for (const prop in obj) {
      if (obj.hasOwnProperty(prop)) {
        uObserve.unobserveProperty(obj, prop, observer);
      }
    }
  }

  /**
   * Remove observer from array
   * @param {Object} arr
   * @param {ObserveCallback} observer
   */
  static unobserveArray(arr, observer) {
    this.removeObserver(arr, observer);
    if (!arr._observers.size) {
      this.restoreArrayPrototypes(arr);
    }
  }

  /**
   * @param {Object} arr
   */
  static overrideArrayPrototypes(arr) {
    [ 'pop', 'push', 'reverse', 'shift', 'sort', 'splice', 'unshift' ].forEach(
      (operation) => {
        const descriptor = Object.getOwnPropertyDescriptor(Array.prototype, operation);
        if (!arr.hasOwnProperty(operation)) {
          descriptor.value = function () {
            const result = Array.prototype[operation].apply(arr, arguments);
            console.log(`[${operation}] ` + (arr.length ? `[${arr[0]}, …](${arr.length})` : arr));
            uObserve.notify(arr._observers, arr);
            return result;
          };
          Object.defineProperty(arr, operation, descriptor);
        }
      });
  }

  /**
   * @param {Object} arr
   */
  static restoreArrayPrototypes(arr) {
    [ 'pop', 'push', 'reverse', 'shift', 'sort', 'splice', 'unshift' ].forEach(
      (operation) => {
        delete arr[operation];
      });
  }

  /**
   * Remove observer from object's property
   * @param {Object} obj Object
   * @param {string} property Optional property
   * @param {ObserveCallback} observer Observer
   */
  static removeObserver(obj, observer, property) {
    if (!obj.hasOwnProperty('_observers')) {
      return;
    }
    let observers;
    if (arguments.length === 3) {
      if (!obj._observers[property]) {
        return;
      }
      observers = obj._observers[property];
      console.log(`Removing observer for ${property}…`);
    } else {
      observers = obj._observers;
      console.log('Removing observer for object…');
    }
    observers.forEach((obs) => {
      if (obs === observer) {
        console.log('Removed');
        observers.delete(obs);
      }
    });
  }
}
