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

import uUtils from './utils.js';

/**
 * @class uPosition
 * @property {number} id
 * @property {number} latitude
 * @property {number} longitude
 * @property {?number} altitude
 * @property {?number} speed
 * @property {?number} bearing
 * @property {?number} accuracy
 * @property {?string} provider
 * @property {?string} comment
 * @property {?string} image
 * @property {string} username
 * @property {string} trackname
 * @property {number} trackid
 * @property {number} timestamp
 * @property {number} distance
 * @property {number} seconds
 * @property {number} totalDistance
 * @property {number} totalSeconds
 */
export default class uPosition {

  /**
   * @throws On invalid input
   * @param {Object} pos
   * @returns {uPosition}
   */
  static fromJson(pos) {
    const position = new uPosition();
    position.id = uUtils.getInteger(pos.id);
    position.latitude = uUtils.getFloat(pos.latitude);
    position.longitude = uUtils.getFloat(pos.longitude);
    position.altitude = uUtils.getInteger(pos.altitude, true); // may be null
    position.speed = uUtils.getInteger(pos.speed, true); // may be null
    position.bearing = uUtils.getInteger(pos.bearing, true); // may be null
    position.accuracy = uUtils.getInteger(pos.accuracy, true); // may be null
    position.provider = uUtils.getString(pos.provider, true); // may be null
    position.comment = uUtils.getString(pos.comment, true); // may be null
    position.image = uUtils.getString(pos.image, true); // may be null
    position.username = uUtils.getString(pos.username);
    position.trackname = uUtils.getString(pos.trackname);
    position.trackid = uUtils.getInteger(pos.trackid);
    position.timestamp = uUtils.getInteger(pos.timestamp);
    position.distance = uUtils.getInteger(pos.distance);
    position.seconds = uUtils.getInteger(pos.seconds);
    position.totalDistance = 0;
    position.totalSeconds = 0;
    return position;
  }

  /**
   * @return {boolean}
   */
  hasComment() {
    return (this.comment != null && this.comment.length > 0);
  }

  /**
   * @return {boolean}
   */
  hasImage() {
    return (this.image != null && this.image.length > 0);
  }
}
