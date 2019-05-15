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
