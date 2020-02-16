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
 * @property {number} meters Distance to previous position
 * @property {number} seconds Time difference to previous position
 * @property {number} totalMeters Distance to first position
 * @property {number} totalSeconds Time difference to first position
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
    position.speed = uUtils.getFloat(pos.speed, true); // may be null
    position.bearing = uUtils.getInteger(pos.bearing, true); // may be null
    position.accuracy = uUtils.getInteger(pos.accuracy, true); // may be null
    position.provider = uUtils.getString(pos.provider, true); // may be null
    position.comment = uUtils.getString(pos.comment, true); // may be null
    position.image = uUtils.getString(pos.image, true); // may be null
    position.username = uUtils.getString(pos.username);
    position.trackname = uUtils.getString(pos.trackname);
    position.trackid = uUtils.getInteger(pos.trackid);
    position.timestamp = uUtils.getInteger(pos.timestamp);
    position.meters = uUtils.getInteger(pos.meters);
    position.seconds = uUtils.getInteger(pos.seconds);
    position.totalMeters = 0;
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

  get calculatedSpeed() {
    return this.seconds ? this.meters / this.seconds : 0;
  }

  get totalSpeed() {
    return this.totalSeconds ? this.totalMeters / this.totalSeconds : 0;
  }

  /**
   * @return {Promise<void, Error>}
   */
  delete() {
    return uPosition.update({
      action: 'delete',
      posid: this.id
    });
  }

  /**
   * @return {Promise<void, Error>}
   */
  save() {
    return uPosition.update({
      action: 'update',
      posid: this.id,
      comment: this.comment
    });
  }

  /**
   * Save track data
   * @param {Object} data
   * @return {Promise<void, Error>}
   */
  static update(data) {
    return uAjax.post('utils/handleposition.php', data);
  }

  /**
   * Calculate distance to target point using haversine formula
   * @param {uPosition} target
   * @return {number} Distance in meters
   */
  distanceTo(target) {
    const lat1 = uUtils.deg2rad(this.latitude);
    const lon1 = uUtils.deg2rad(this.longitude);
    const lat2 = uUtils.deg2rad(target.latitude);
    const lon2 = uUtils.deg2rad(target.longitude);
    const latD = lat2 - lat1;
    const lonD = lon2 - lon1;
    const bearing = 2 * Math.asin(Math.sqrt((Math.sin(latD / 2) ** 2) + Math.cos(lat1) * Math.cos(lat2) * (Math.sin(lonD / 2) ** 2)));
    return bearing * 6371000;
  }

  /**
   * Calculate time elapsed since target point
   * @param {uPosition} target
   * @return {number} Number of seconds
   */
  secondsTo(target) {
    return this.timestamp - target.timestamp;
  }

}
