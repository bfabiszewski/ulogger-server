/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import Http from './Http.js';
import Utils from './Utils.js';

/**
 * @class Position
 * @property {number} id
 * @property {number} latitude
 * @property {number} longitude
 * @property {?number} altitude
 * @property {?number} speed
 * @property {?number} bearing
 * @property {?number} accuracy
 * @property {?string} provider
 * @property {?string} comment
 * @property {boolean} hasImage
 * @property {string} userName
 * @property {string} trackName
 * @property {number} trackId
 * @property {number} timestamp
 * @property {number} meters Distance to previous position
 * @property {number} seconds Time difference to previous position
 * @property {number} totalMeters Distance to first position
 * @property {number} totalSeconds Time difference to first position
 */
export default class Position {

  /**
   * @throws On invalid input
   * @param {Object} pos
   * @returns {Position}
   */
  static fromJson(pos) {
    const position = new Position();
    position.id = Utils.getInteger(pos.id);
    position.latitude = Utils.getFloat(pos.latitude);
    position.longitude = Utils.getFloat(pos.longitude);
    position.altitude = Utils.getInteger(pos.altitude, true); // may be null
    position.speed = Utils.getFloat(pos.speed, true); // may be null
    position.bearing = Utils.getInteger(pos.bearing, true); // may be null
    position.accuracy = Utils.getInteger(pos.accuracy, true); // may be null
    position.provider = Utils.getString(pos.provider, true); // may be null
    position.comment = Utils.getString(pos.comment, true); // may be null
    position.hasImage = Utils.getBoolean(pos.hasImage);
    position.userName = Utils.getString(pos.userName);
    position.trackName = Utils.getString(pos.trackName);
    position.trackId = Utils.getInteger(pos.trackId);
    position.timestamp = Utils.getInteger(pos.timestamp);
    position.meters = Utils.getInteger(pos.meters);
    position.seconds = Utils.getInteger(pos.seconds);
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
  hasSpeed() {
    return this.speed != null;
  }

  /**
   * @return {boolean}
   */
  hasAltitude() {
    return this.altitude != null;
  }

  /**
   * @return {?string}
   */
  getImagePath() {
    return this.hasImage ? `api/positions/${this.id}/image` : null;
  }

  /**
   * Get total speed in m/s
   * @return {number}
   */
  get totalSpeed() {
    return this.totalSeconds ? this.totalMeters / this.totalSeconds : 0;
  }

  /**
   * @return {Promise<void, Error>}
   */
  delete() {
    return Http.delete(`/api/positions/${this.id}`);
  }

  /**
   * @return {Promise<void, Error>}
   */
  save() {
    return Http.put(`/api/positions/${this.id}`, this);
  }

  /**
   * @return {Promise<void, Error>}
   */
  imageDelete() {
    return Http.delete(`/api/positions/${this.id}/image`)
      .then(() => { this.hasImage = false; });
  }

  /**
   * @param {File} imageFile
   * @return {Promise<void, Error>}
   */
  imageAdd(imageFile) {
    const data = new FormData();
    data.append('imageUpload', imageFile);
    return Http.post(`/api/positions/${this.id}/image`, data).then(
      /**
       * @param {Object} result
       * @param {boolean} result.hasImage
       */
      (result) => { this.hasImage = result.hasImage; });
  }

  /**
   * Calculate distance to target point using haversine formula
   * @param {Position} target
   * @return {number} Distance in meters
   */
  distanceTo(target) {
    const lat1 = Utils.deg2rad(this.latitude);
    const lon1 = Utils.deg2rad(this.longitude);
    const lat2 = Utils.deg2rad(target.latitude);
    const lon2 = Utils.deg2rad(target.longitude);
    const latD = lat2 - lat1;
    const lonD = lon2 - lon1;
    const bearing = 2 * Math.asin(Math.sqrt((Math.sin(latD / 2) ** 2) + Math.cos(lat1) * Math.cos(lat2) * (Math.sin(lonD / 2) ** 2)));
    return bearing * 6371000;
  }

  /**
   * Calculate time elapsed since target point
   * @param {Position} target
   * @return {number} Number of seconds
   */
  secondsTo(target) {
    return this.timestamp - target.timestamp;
  }

}
