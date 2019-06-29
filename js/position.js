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
   * @param {Element|Document} xml
   * @returns {uPosition}
   */
  static fromXml(xml) {
    const position = new uPosition();
    position.id = uUtils.getAttributeAsInt(xml, 'id');
    position.latitude = uUtils.getNodeAsFloat(xml, 'latitude');
    position.longitude = uUtils.getNodeAsFloat(xml, 'longitude');
    position.altitude = uUtils.getNodeAsInt(xml, 'altitude'); // may be null
    position.speed = uUtils.getNodeAsInt(xml, 'speed'); // may be null
    position.bearing = uUtils.getNodeAsInt(xml, 'bearing'); // may be null
    position.accuracy = uUtils.getNodeAsInt(xml, 'accuracy'); // may be null
    position.provider = uUtils.getNode(xml, 'provider'); // may be null
    position.comments = uUtils.getNode(xml, 'comments'); // may be null
    position.username = uUtils.getNode(xml, 'username');
    position.trackname = uUtils.getNode(xml, 'trackname');
    position.trackid = uUtils.getNodeAsInt(xml, 'trackid');
    position.timestamp = uUtils.getNodeAsInt(xml, 'timestamp');
    position.distance = uUtils.getNodeAsInt(xml, 'distance');
    position.seconds = uUtils.getNodeAsInt(xml, 'seconds');
    position.totalDistance = 0;
    position.totalSeconds = 0;
    return position;
  }
}
