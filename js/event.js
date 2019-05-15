/* eslint-disable lines-between-class-members */
/**
 * class uEvent
 * property {string} type
 * property {Set<Function>} listeners
 */
export default class uEvent {
  /**
   * @param {string} type
   */
  constructor(type) {
    /** type {string} */
    this.type = type;
    /** type {Set<Function>} */
    this.listeners = new Set();
  }

  static get ADD() { return 'µAdd'; }
  static get API_CHANGE() { return 'µApiChange'; }
  static get CHART_READY() { return 'µChartReady'; }
  static get CONFIG() { return 'µConfig'; }
  static get EDIT() { return 'µEdit'; }
  static get EXPORT() { return 'µExport'; }
  static get OPEN_URL() { return 'µOpen'; }
  static get IMPORT() { return 'µImport'; }
  static get PASSWORD() { return 'µPassword'; }
  static get TRACK_READY() { return 'µTrackReady'; }
  static get UI_READY() { return 'µUiReady'; }

  /**
   * @param {Function} listener
   */
  addListener(listener) {
    this.listeners.add(listener);
  }

  /**
   * @param {Function} listener
   */
  removeListener(listener) {
    this.listeners.delete(listener);
  }

  /**
   * @param {*=} args
   */
  dispatch(args) {
    for (const listener of this.listeners) {
      (async () => {
        console.log(`${this.type}: ${args.constructor.name} => ${listener.name}`);
        await listener(this, args);
      })();
    }
  }
}
