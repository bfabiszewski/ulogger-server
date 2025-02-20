/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import Http from './Http.js';
import User from './User.js';

export default class Session {

  /** @var {?User} */
  #user;

  constructor() {
    this.init();
  }

  init() {
    this.#user = null;
  }

  /**
   * @param {string} login
   * @param {string} password
   * @return {Promise<Object, Error>}
   */
  login(login, password) {
    return Http.post('api/session', {
      login, password
    }).then((data) => this.load(data));
  }

  logout() {
    this.init();
    return Http.delete('api/session');
  }

  /**
   * @param {User} user
   */
  set user(user) {
    if (user) {
      this.#user = user;
    } else {
      this.#user = null;
    }
  }

  /**
   * @param {boolean} isAdmin
   */
  set isAdmin(isAdmin) {
    if (!this.#user) {
      throw new Error('No authenticated user');
    }
    this.#user.isAdmin = isAdmin;
  }

  /**
   * @return {boolean}
   */
  get isAdmin() {
    return this.#user !== null && this.#user.isAdmin;
  }

  /**
   * @return {boolean}
   */
  get isAuthenticated() {
    return this.#user !== null;
  }

  /**
   * @return {?User}
   */
  get user() {
    return this.#user;
  }

  /**
   * Load auth state from data object
   * @param {Object} data
   * @param {User} data.user
   * @param {boolean} data.isAuthenticated
   */
  load(data) {
    if (data) {
      if (data.isAuthenticated && data.user) {
        this.user = new User(data.user.id, data.user.login, data.user.isAdmin);
      }
    }
  }
}
