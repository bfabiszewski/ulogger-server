/*
 * μlogger
 *
 * Copyright(C) 2020 Bartek Fabiszewski (www.fabiszewski.net)
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

import { lang as $, config } from './initializer.js';
import uTrack from './track.js';
import uUtils from './utils.js';

/**
 * @typedef {Object} PermalinkState
 * @property {string} title
 * @property {number|null} userId
 * @property {number|null} trackId
 * @property {string|null} mapApi
 * @property {MapParams|null} mapParams
 */

export default class uPermalink {

  /**
   * @param {uState} state
   */
  constructor(state) {
    this.state = state;
    this.skipPush = false;
  }

  /**
   * @return {uPermalink}
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
    return uPermalink.parse(window.location.hash);
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
      return uTrack.getMeta(trackId)
        .then((meta) => {
          const userId = meta.userId;
          const title = meta.name;
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
    if (!prevState || !uUtils.isDeepEqual(prevState, state)) {
      const hash = uPermalink.getHash(state);
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
