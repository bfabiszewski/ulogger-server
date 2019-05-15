import { config } from './constants.js';
import uAjax from './ajax.js';
import uData from './data.js';
import uEvent from './event.js';
import uPosition from './position.js';

/**
 * @class uTrack
 * @extends {uData}
 * @property {number} id
 * @property {string} name
 * @property {uUser} user
 * @property {?uPosition[]} positions
 * @property {?Array<{x: number, y: number}>} plotData
 */
export default class uTrack extends uData {

  /**
   * @param {number} id
   * @param {string} name
   * @param {uUser} user
   */
  constructor(id, name, user) {
    super(id, name, 'id', 'name');
    this._user = user;
    this._positions = null;
    this._plotData = null;
    this._maxId = 0;
    this._onlyLatest = false;
  }

  /**
   * @return {?uPosition[]}
   */
  get positions() {
    return this._positions;
  }

  /**
   * @param {uUser} user
   */
  set user(user) {
    this._user = user;
  }

  /**
   * @return {uUser}
   */
  get user() {
    return this._user;
  }

  /**
   * @param {boolean} value
   */
  set onlyLatest(value) {
    this._onlyLatest = value;
  }

  clear() {
    this._positions = null;
    this._plotData = null;
  }

  /**
   * Get track data from xml
   * @param {XMLDocument} xml
   * @param {boolean} isUpdate
   */
  fromXml(xml, isUpdate) {
    let positions = [];
    let plotData = [];
    let totalDistance = 0;
    let totalSeconds = 0;
    if (isUpdate && this._positions) {
      positions = this._positions;
      plotData = this._plotData;
      totalDistance = positions[positions.length - 1].totalDistance;
      totalSeconds = positions[positions.length - 1].totalSeconds;
    }
    const xmlPos = xml.getElementsByTagName('position');
    for (xml of xmlPos) {
      const position = uPosition.fromXml(xml);
      totalDistance += position.distance;
      totalSeconds += position.seconds;
      position.totalDistance = totalDistance;
      position.totalSeconds = totalSeconds;
      positions.push(position);
      if (position.altitude != null) {
        plotData.push({ x: position.totalDistance, y: position.altitude * config.factor_m });
      }
      if (position.id > this._maxId) {
        this._maxId = position.id;
      }
    }
    this._positions = positions;
    this._plotData = plotData;
  }

  /**
   * @return {?Array<{x: number, y: number}>}
   */
  get plotData() {
    return this._plotData;
  }

  /**
   * @return {number}
   */
  get length() {
    return this._positions ? this._positions.length : 0;
  }

  /**
   * @return {boolean}
   */
  get hasPositions() {
    return this._positions !== null;
  }

  /**
   * @throws
   * @return {Promise<void>}
   */
  fetch() {
    const data = {
      userid: this._user.id
    };
    let isUpdate = this.hasPositions;
    if (config.showLatest) {
      data.last = 1;
      isUpdate = false;
    } else {
      data.trackid = this.id;
    }
    if (this._onlyLatest !== config.showLatest) {
      this._onlyLatest = config.showLatest;
      isUpdate = false;
    } else {
      data.afterid = this._maxId;
    }
    return uAjax.get('utils/getpositions.php', data, {
      // loader: ui.trackTitle
      }).then((xml) => {
      this.fromXml(xml, isUpdate);
      return this.render();
    });
  }

  /**
   *
   * @param {string} action
   * @return {Promise<void>}
   */
  update(action) {
      return uAjax.post('utils/handletrack.php',
        {
          action: action,
          trackid: this.id,
          trackname: this.name
        });
  }

  render() {
    this.emit(uEvent.TRACK_READY);
  }

  /**
   * Export to file
   * @param {string} type File type
   */
  export(type) {
    const url = `utils/export.php?type=${type}&userid=${this._user.id}&trackid=${this.id}`;
    this.emit(uEvent.OPEN_URL, url);
  }
}
