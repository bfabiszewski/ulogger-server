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
