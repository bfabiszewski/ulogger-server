/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import Http from './Http.js';
import Position from './Position.js';
import PositionSet from './PositionSet.js';
import User from './User.js';
import Utils from './Utils.js';

/**
 * Set of positions representing user's track
 * @class Track
 * @property {number} id
 * @property {string} name
 * @property {User} user
 * @property {Position[]} positions
 * @property {PlotData} plotData
 */
export default class Track extends PositionSet {

  /**
   * @param {number} id
   * @param {string} name
   * @param {User} user
   */
  constructor(id, name, user) {
    super();
    if (!Number.isSafeInteger(id) || id <= 0 || !name || !(user instanceof User)) {
      throw new Error('Invalid argument for track constructor');
    }
    this.id = id;
    this.name = name;
    this.user = user;
    this.plotData = [];
    this.maxId = 0;
    this.maxSpeed = 0;
    this.maxAltitude = null;
    this.minAltitude = null;
    this.totalMeters = 0;
    this.totalSeconds = 0;
    this.listItem(id, name);
  }

  setName(name) {
    this.name = name;
    this.listText = name;
  }

  clear() {
    super.clear();
    this.clearTrackCounters();
  }

  clearTrackCounters() {
    this.maxId = 0;
    this.maxSpeed = 0;
    this.maxAltitude = null;
    this.minAltitude = null;
    this.plotData.length = 0;
    this.totalMeters = 0;
    this.totalSeconds = 0;
  }

  /**
   * @param {Track} track
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
   * @return {boolean}
   */
  get hasAltitudes() {
    return this.maxAltitude !== null;
  }

  /**
   * @return {boolean}
   */
  get hasSpeeds() {
    return this.maxSpeed > 0;
  }

  /**
   * Get track data from json
   * @param {Object[]} posArr Positions data
   * @param {boolean=} isUpdate If true append to old data
   */
  fromJson(posArr, isUpdate = false) {
    let positions = [];
    if (isUpdate && this.hasPositions) {
      positions = this.positions;
    } else {
      this.clear();
    }
    for (const pos of posArr) {
      const position = Position.fromJson(pos);
      this.calculatePosition(position);
      positions.push(position);
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
    let url = `api/tracks/${this.id}/positions`;
    if (this.maxId) {
      url += `?afterId=${this.maxId}`;
    }
    return Http.get(url).then((_positions) => {
      this.fromJson(_positions, this.maxId > 0);
    });
  }

  /**
   * Fetch track with latest position of a user.
   * @param {User} user
   * @return {Promise<?Track, Error>}
   */
  static fetchLatest(user) {
    return Http.get(`api/users/${user.id}/position`)
      .then((_position) => {
        if (_position) {
          const id = Utils.getInteger(_position.trackId);
          const name = Utils.getString(_position.trackName);
          const track = new Track(id, name, user);
          track.fromJson([ _position ]);
          return track;
        }
        return null;
      })
      .catch((e) => {
        if (e.status === Http.ERROR_NOT_FOUND) {
          return null;
        }
        throw e;
      });
  }

  /**
   * Fetch tracks for given user
   * @throws
   * @param {User} user
   * @return {Promise<Track[], Error>}
   */
  static fetchList(user) {
    return Http.get(`api/users/${user.id}/tracks`).then(
      /**
       * @param {Array.<{id: number, name: string}>} _tracks
       * @return {Track[]}
       */
      (_tracks) => {
        const tracks = [];
        for (const track of _tracks) {
          const id = Utils.getInteger(track.id);
          const name = Utils.getString(track.name);
          tracks.push(new Track(id, name, user));
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
      const url = `api/tracks/${this.id}/export?format=${type}`;
      Utils.openUrl(url);
    }
  }

  /**
   * Imports tracks submitted with HTML form and returns imported tracks metadata
   * @param {HTMLFormElement} form
   * @param {User} user
   * @return {Promise<Track[], Error>}
   */
  static import(form, user) {
    return Http.post('api/tracks/import', form)
      .then(
        /**
         * @param {Array.<{id: number, name: string}>} _tracks
         * @return {Track[]}
         */
        (_tracks) => {
          const tracks = [];
          for (const track of _tracks) {
            const id = Utils.getInteger(track.id);
            const name = Utils.getString(track.name);
            tracks.push(new Track(id, name, user));
          }
          return tracks;
      });
  }

  delete() {
    return Http.delete(`api/tracks/${this.id}`);
  }

  saveMeta() {
    const data = {
      id: this.id,
      name: this.name,
      userId: this.user.id
    }
    return Http.put(`api/tracks/${this.id}`, data);
  }

  /**
   * @param {number} id
   * @return {Promise<{id: number, name: string, userId: number, comment: string|null}, Error>}
   */
  static getMeta(id) {
    return Http.get(`api/tracks/${id}`);
  }

  recalculatePositions() {
    this.clearTrackCounters();
    let previous = null;
    for (const position of this.positions) {
      position.meters = previous ? position.distanceTo(previous) : 0;
      position.seconds = previous ? position.secondsTo(previous) : 0;
      this.calculatePosition(position);
      previous = position;
    }
  }

  /**
   * Calculate position total counters and plot data
   * @param {Position} position
   */
  calculatePosition(position) {
    this.totalMeters += position.meters;
    this.totalSeconds += position.seconds;
    position.totalMeters = this.totalMeters;
    position.totalSeconds = this.totalSeconds;
    if (position.hasAltitude()) {
      this.plotData.push({ x: position.totalMeters, y: position.altitude });
      if (this.maxAltitude === null || position.altitude > this.maxAltitude) {
        this.maxAltitude = position.altitude;
      }
      if (this.minAltitude === null || position.altitude < this.minAltitude) {
        this.minAltitude = position.altitude;
      }
    }
    if (position.id > this.maxId) {
      this.maxId = position.id;
    }
    if (position.hasSpeed() && position.speed > this.maxSpeed) {
      this.maxSpeed = position.speed;
    }
  }
}
