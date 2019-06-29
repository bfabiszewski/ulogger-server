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
import uData from './data.js';
import uUtils from './utils.js';

/**
 * @class uUser
 * @extends {uData}
 * @property {number} id
 * @property {string} login
 * @property {string} [password]
 */
export default class uUser extends uData {
  /**
   * @param {number} id
   * @param {string} login
   */
  constructor(id, login) {
    super(id, login, 'id', 'login');
  }

  /**
   *
   * @param {string} action
   * @return {Promise<uUser>}
   */
  update(action) {
    const pass = this.password;
    // don't store password in class property
    delete this.password;
    return uAjax.post('utils/handleuser.php',
      {
        action: action,
        login: this.login,
        pass: pass
      }).then((xml) => {
        if (action === 'add') {
          this.id = uUtils.getNodeAsInt(xml, 'userid');
        }
        return this;
    });
  }

  /**
   * @param {string} password
   * @param {string} oldPassword
   * @return {Promise<void>}
   */
  changePass(password, oldPassword) {
    return uAjax.post('utils/changepass.php',
      {
        login: this.login,
        pass: password,
        oldpass: oldPassword
      });
  }
}
