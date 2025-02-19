/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import { lang as $, config } from './Initializer.js';
import Track from './Track.js';
import Utils from './Utils.js';

/**
 * @typedef {Object} PermalinkState
 * @property {string} title
 * @property {number|null} userId
 * @property {number|null} trackId
 * @property {string|null} mapApi
 * @property {MapParams|null} mapParams
 */

export default class Permalink {

  /**
   * @param {State} state
   */
  constructor(state) {
    this.state = state;
    this.skipPush = false;
  }

  /**
   * @return {Permalink}
   */
  init() {
    this.state.onChanged('mapParams', () => this.pushState());
    window.addEventListener('popstate', (event) => {
      if (event.state === null) {
        return;
      }
      const track = this.state.currentTrack;
      const user = this.state.currentUser;
      // remove elements that won't be updated
      const state = {
        title: event.state.title,
        userId: (user && user.id === event.state.userId) ? null : event.state.userId,
        trackId: (track && track.id === event.state.trackId) ? null : event.state.trackId,
        mapApi: config.mapApi === event.state.mapApi ? null : event.state.mapApi,
        mapParams: event.state.mapParams
      }
      this.onPop(state);
      this.skipPush = true;
    });
    return this;
  }

  /**
   * @return {Promise<?PermalinkState>}
   */
  static parseHash() {
    return Permalink.parse(window.location.hash);
  }

  /**
   * Parse URL hash string
   * @param {string} hash
   * @return {Promise<?PermalinkState>} Permalink state or null if not parsable
   */
  static parse(hash) {
    const parts = hash.replace('#', '').split('/');
    parts.reverse();
    const trackId = parseInt(parts.pop());
    if (!isNaN(trackId)) {
      let mapApi = 'openlayers';
      if (parts.pop() === 'g') {
        mapApi = 'gmaps';
      }
      let mapParams = null;
      if (parts.length >= 4) {
        mapParams = {};
        mapParams.center = [ parseFloat(parts.pop()), parseFloat(parts.pop()) ];
        mapParams.zoom = parseFloat(parts.pop());
        mapParams.rotation = parseFloat(parts.pop());
      }
      return Track.getMeta(trackId)
        .then((meta) => {
          const userId = Utils.getInteger(meta.userId);
          const title = Utils.getString(meta.name);
          return { title, userId, trackId, mapApi, mapParams };
        })
        .catch((e) => {
          console.log(`Ignoring unknown track ${trackId} ${e}`);
          return null;
        });
    }
    return Promise.resolve(null);
  }

  /**
   * @param {?PermalinkState} state
   */
  onPop(state) {
    console.log('popState: #' + (state ? `${state.trackId}/${state.mapApi}/${state.mapParams}` : ''));
    this.state.history = state;
    if (state) {
      document.title = `${$._('title')} ${state.title}`;
    }
  }

  /**
   * Push state into browser history
   */
  pushState() {
    if (this.skipPush) {
      this.skipPush = false;
      return;
    }
    if (this.state.currentUser === null || this.state.currentTrack === null) {
      return;
    }
    const state = this.getState();
    const prevState = window.history.state;
    if (!prevState || !Utils.isDeepEqual(prevState, state)) {
      const hash = Permalink.getHash(state);
      console.log(`pushState: ${hash} => ${state}`);
      window.history.pushState(state, state.title, hash);
      document.title = `${$._('title')} ${state.title}`;
    }
  }

  getState() {
    return {
      title: this.state.currentTrack.name,
      userId: this.state.currentUser.id,
      trackId: this.state.currentTrack.id,
      mapApi: config.mapApi,
      mapParams: this.state.mapParams
    };
  }

  /**
   * Get link hash
   * @param {PermalinkState} state
   * @return {string}
   */
  static getHash(state) {
    let hash = `#${state.trackId}/${state.mapApi.charAt(0)}`;
    if (state.mapParams) {
      hash += `/${state.mapParams.center[0]}/${state.mapParams.center[1]}`;
      hash += `/${state.mapParams.zoom}/${state.mapParams.rotation}`;
    }
    return hash;
  }
}
