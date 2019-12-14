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
import uListItem from './listitem.js';
import uTrack from './track.js';

/**
 * @class uUser
 * @property {number} id
 * @property {string} login
 * @property {string} [password]
 */
export default class uUser extends uListItem {
  /**
   * @param {number} id
   * @param {string} login
   */
  constructor(id, login) {
    super();
    if (!Number.isSafeInteger(id) || id <= 0) {
      throw new Error('Invalid argument for user constructor');
    }
    this.id = id;
    this.login = login;
    this.listItem(id, login);
  }

  /**
   * @return {Promise<uTrack, Error>}
   */
  fetchLastPosition() {
    return uTrack.fetchLatest(this);
  }

  /**
   * @throws
   * @return {Promise<uUser[], Error>}
   */
  static fetchList() {
    return uAjax.get('utils/getusers.php').then((_users) => {
      const users = [];
      for (const user of _users) {
        users.push(new uUser(user.id, user.login));
      }
      return users;
    });
  }
}
