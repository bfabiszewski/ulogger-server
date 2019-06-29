/* μlogger
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
  static get CHART_CLICKED() { return 'µChartClicked'; }
  static get CONFIG() { return 'µConfig'; }
  static get CHANGE() { return 'µChange'; }
  static get CHART_READY() { return 'µChartReady'; }
  static get EDIT() { return 'µEdit'; }
  static get EXPORT() { return 'µExport'; }
  static get IMPORT() { return 'µImport'; }
  static get LOADER() { return 'µLoader'; }
  static get MARKER_OVER() { return 'µMarkerOver'; }
  static get MARKER_SELECT() { return 'µMarkerSelect'; }
  static get OPEN_URL() { return 'µOpen'; }
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
        console.log(`${this.type}: ${args ? args.constructor.name : ''}`);
        await listener(this, args);
      })();
    }
  }
}
