/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import Http from './Http.js';
import ListItem from './ListItem.js';
import Track from './Track.js';
import Utils from './Utils';

/**
 * @class User
 * @property {number} id
 * @property {string} login
 * @property {boolean} isAdmin
 */
export default class User extends ListItem {
  /**
   * @param {number} id
   * @param {string} login
   * @param {boolean=} isAdmin
   */
  constructor(id, login, isAdmin = false) {
    super();
    if (!Number.isSafeInteger(id) || id <= 0) {
      throw new Error('Invalid argument for user constructor');
    }
    this.id = id;
    this.login = login;
    this.isAdmin = isAdmin;
    this.listItem(id, login);
  }

  /**
   * @param {User} user
   * @return {boolean}
   */
  isEqualTo(user) {
    return !!user && user.id === this.id;
  }

  /**
   * @return {Promise<Track, Error>}
   */
  fetchLastPosition() {
    return Track.fetchLatest(this);
  }

  /**
   * @throws
   * @return {Promise<User[], Error>}
   */
  static fetchList() {
    return Http.get('api/users').then((_users) => {
      const users = [];
      for (const user of _users) {
        const id = Utils.getInteger(user.id);
        const login = Utils.getString(user.login);
        const isAdmin = Utils.getBoolean(user.isAdmin);
        users.push(new User(id, login, isAdmin));
      }
      return users;
    });
  }

  delete() {
    return Http.delete(`api/users/${this.id}`);
  }

  /**
   *
   * @param {string} login
   * @param {string} password
   * @param {boolean} isAdmin
   * @return {Promise<User>}
   */
  static add(login, password, isAdmin) {
    return Http.post('api/users', { login, password, isAdmin })
      .then((user) => {
        const userId = Utils.getInteger(user.id);
        const userLogin = Utils.getString(user.login);
        const userIsAdmin = Utils.getBoolean(user.isAdmin);
        return new User(userId, userLogin, userIsAdmin);
      });
  }

  /**
   * @param {string} password New password
   * @param {string} oldPassword Current password
   * @return {Promise<void, Error>}
   */
  setPassword(password, oldPassword) {
    return Http.put(`api/users/${this.id}/password`, { password, oldPassword });
  }

  /**
   * @param {boolean} isAdmin
   * @param {string|null} password
   * @return {Promise<void, Error>}
   */
  modify(isAdmin, password = null) {
    const data = {
      id: this.id,
      login: this.login,
      isAdmin: isAdmin
    };
    if (password) {
      data.password = password;
    }
    return Http.put(`api/users/${this.id}`, data)
      .then(() => { this.isAdmin = isAdmin; });
  }

}
