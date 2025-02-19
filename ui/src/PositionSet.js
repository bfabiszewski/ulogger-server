/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import Http from './Http.js';
import ListItem from './ListItem.js';
import Position from './Position.js';

/**
 * Set of unrelated positions
 * @class PositionSet
 * @property {Position[]} positions
 */
export default class PositionSet extends ListItem {

  constructor() {
    super();
    this.positions = [];
  }

  clear() {
    this.positions.length = 0;
  }

  /**
   * @return {number}
   */
  get length() {
    return this.positions.length;
  }

  /**
   * @return {boolean}
   */
  get hasPositions() {
    return this.positions.length > 0;
  }

  // eslint-disable-next-line no-unused-vars,class-methods-use-this
  isLastPosition(id) {
    return true;
  }

  // eslint-disable-next-line no-unused-vars,class-methods-use-this
  isFirstPosition(id) {
    return true;
  }

  /**
   * Get track data from json
   * @param {Object[]} posArr Positions data
   * @param {boolean=} isUpdate If true append to old data
   */
  fromJson(posArr, isUpdate = false) {
    let positions = [];
    if (isUpdate) {
      positions = this.positions;
    } else {
      this.clear();
    }
    for (const pos of posArr) {
      positions.push(Position.fromJson(pos));
    }
    // update at the end to avoid observers update individual points
    this.positions = positions;
  }

  /**
   * Fetch latest position of each user.
   * @return {Promise<void, Error>}
   */
  fetchLatest() {
    this.clear();
    return Http.get('api/users/position')
      .then((_positions) => {
        this.fromJson(_positions);
      })
      .catch((e) => {
        if (e.status === Http.ERROR_NOT_FOUND) {
          return null;
        }
        throw e;
      });
  }

  /**
   * Fetch latest position of each user.
   * @return {Promise<?PositionSet, Error>}
   */
  static fetchLatest() {
    const set = new PositionSet();
    return set.fetchLatest().then(() => {
      if (set.length) {
        return set;
      }
      return null;
    });
  }

}
