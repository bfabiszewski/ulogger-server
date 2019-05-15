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
