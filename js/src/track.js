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

import uAjax from './ajax.js';
import uPosition from './position.js';
import uPositionSet from './positionset.js';
import uUser from './user.js';
import uUtils from './utils.js';

/**
 * Set of positions representing user's track
 * @class uTrack
 * @property {number} id
 * @property {string} name
 * @property {uUser} user
 * @property {uPosition[]} positions
 * @property {PlotData} plotData
 */
export default class uTrack extends uPositionSet {

  /**
   * @param {number} id
   * @param {string} name
   * @param {uUser} user
   */
  constructor(id, name, user) {
    super();
    if (!Number.isSafeInteger(id) || id <= 0 || !name || !(user instanceof uUser)) {
      throw new Error('Invalid argument for track constructor');
    }
    this.id = id;
    this.name = name;
    this.user = user;
    this.plotData = [];
    this.maxId = 0;
    this.listItem(id, name);
  }

  clear() {
    super.clear();
    this.maxId = 0;
    this.plotData.length = 0;
  }

  /**
   * @param {uTrack} track
   * @return {boolean}
   */
  isEqualTo(track) {
    return !!track && track.id === this.id;
  }

  /**
   * @return {boolean}
   */
  get hasPlotData() {
    return this.plotData.length > 0;
  }

  /**
   * Get track data from json
   * @param {Object[]} posArr Positions data
   * @param {boolean=} isUpdate If true append to old data
   */
  fromJson(posArr, isUpdate = false) {
    let totalMeters = 0;
    let totalSeconds = 0;
    let positions = [];
    if (isUpdate && this.hasPositions) {
      positions = this.positions;
      const last = positions[this.length - 1];
      totalMeters = last.totalMeters;
      totalSeconds = last.totalSeconds;
    } else {
      this.clear();
    }
    for (const pos of posArr) {
      const position = uPosition.fromJson(pos);
      totalMeters += position.meters;
      totalSeconds += position.seconds;
      position.totalMeters = totalMeters;
      position.totalSeconds = totalSeconds;
      positions.push(position);
      if (position.altitude != null) {
        this.plotData.push({ x: position.totalMeters, y: position.altitude });
      }
      if (position.id > this.maxId) {
        this.maxId = position.id;
      }
    }
    // update at the end to avoid observers update invidual points
    this.positions = positions;
  }

  /**
   * @param {number} id
   * @return {boolean}
   */
  isLastPosition(id) {
    return this.length > 0 && id === this.length - 1;
  }

  /**
   * @param {number} id
   * @return {boolean}
   */
  isFirstPosition(id) {
    return this.length > 0 && id === 0;
  }

  /**
   * Fetch track positions
   * @return {Promise<void, Error>}
   */
  fetchPositions() {
    const params = {
      userid: this.user.id,
      trackid: this.id
    };
    if (this.maxId) {
      params.afterid = this.maxId;
    }
    return uPositionSet.fetch(params).then((_positions) => {
      this.fromJson(_positions, params.afterid > 0);
    });
  }

  /**
   * Fetch track with latest position of a user.
   * @param {uUser} user
   * @return {Promise<?uTrack, Error>}
   */
  static fetchLatest(user) {
    return this.fetch({
      last: true,
      userid: user.id
    }).then((_positions) => {
      if (_positions.length) {
        const track = new uTrack(_positions[0].trackid, _positions[0].trackname, user);
        track.fromJson(_positions);
        return track;
      }
      return null;
    });
  }

  /**
   * Fetch tracks for given user
   * @throws
   * @param {uUser} user
   * @return {Promise<uTrack[], Error>}
   */
  static fetchList(user) {
    return uAjax.get('utils/gettracks.php', { userid: user.id }).then(
      /**
       * @param {Array.<{id: number, name: string}>} _tracks
       * @return {uTrack[]}
       */
      (_tracks) => {
        const tracks = [];
        for (const track of _tracks) {
          tracks.push(new uTrack(track.id, track.name, user));
        }
        return tracks;
    });
  }

  /**
   * Export to file
   * @param {string} type File type
   */
  export(type) {
    if (this.hasPositions) {
      const url = `utils/export.php?type=${type}&userid=${this.user.id}&trackid=${this.id}`;
      uUtils.openUrl(url);
    }
  }

  /**
   * Imports tracks submited with HTML form and returns last imported track id
   * @param {HTMLFormElement} form
   * @param {uUser} user
   * @return {Promise<uTrack[], Error>}
   */
  static import(form, user) {
    return uAjax.post('utils/import.php', form)
      .then(
        /**
         * @param {Array.<{id: number, name: string}>} _tracks
         * @return {uTrack[]}
         */
        (_tracks) => {
          const tracks = [];
          for (const track of _tracks) {
            tracks.push(new uTrack(track.id, track.name, user));
          }
          return tracks;
      });
  }

}
