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


import uPosition from '../../src/position.js';
import uPositionSet from '../../src/positionset.js';
import uTrack from '../../src/track.js';
import uUser from '../../src/user.js';

export default class TrackFactory {

  /**
   * @template T
   * @param {number} length
   * @param {T} type
   * @param {Object} params
   * @return {T}
   */
  static getSet(length = 2, type, params) {
    let track;
    if (type === uTrack) {
      track = new uTrack(params.id, params.name, params.user);
    } else {
      track = new uPositionSet();
    }
    if (length) {
      track.positions = [];
      let lat = 21.01;
      let lon = 52.23;
      for (let i = 0; i < length; i++) {
        track.positions.push(this.getPosition(i + 1, lat, lon));
        lat += 0.5;
        lon += 0.5;
      }
    }
    return track;
  }

  /**
   * @param {number=} length
   * @param {{ id: number, name: string, user: uUser }=} params
   * @return {uTrack}
   */
  static getTrack(length = 2, params) {
    params = params || {};
    params.id = params.id || 1;
    params.name = params.name || 'test track';
    params.user = params.user || new uUser(1, 'testUser');
    return this.getSet(length, uTrack, params);
  }

  /**
   * @param {number} length
   * @return {uPositionSet}
   */
  static getPositionSet(length = 2) {
    return this.getSet(length, uPositionSet);
  }

  /**
   * @param {number=} id
   * @param {number=} latitude
   * @param {number=} longitude
   * @return {uPosition}
   */
  static getPosition(id = 1, latitude = 52.23, longitude = 21.01) {
    const position = new uPosition();
    position.id = id;
    position.latitude = latitude;
    position.longitude = longitude;
    position.altitude = null;
    position.speed = null;
    position.bearing = null;
    position.timestamp = 1;
    position.accuracy = null;
    position.provider = null;
    position.comment = null;
    position.image = null;
    position.username = 'testUser';
    position.trackid = 1;
    position.trackname = 'test track';
    position.meters = 0;
    position.seconds = 0;
    return position;
  }
}
