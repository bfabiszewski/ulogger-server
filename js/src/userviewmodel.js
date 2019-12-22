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

import { lang as $, auth } from './initializer.js';
import ViewModel from './viewmodel.js';
import uSelect from './select.js';
import uUser from './user.js';
import uUtils from './utils.js';

/**
 * @class UserViewModel
 */
export default class UserViewModel extends ViewModel {

  /**
   * @param {uState} state
   */
  constructor(state) {
    super({
      /** @type {uUser[]} */
      userList: [],
      /** @type {string} */
      currentUserId: '0'
    });
    /** @type HTMLSelectElement */
    const listEl = document.querySelector('#user');
    this.select = new uSelect(listEl, $._('suser'), `- ${$._('allusers')} -`);
    this.state = state;
  }

  /**
   * @return {UserViewModel}
   */
  init() {
    this.setObservers(this.state);
    this.bindAll();
    uUser.fetchList()
      .then((_users) => {
      this.model.userList = _users;
      if (_users.length) {
        let userId = _users[0].listValue;
        if (auth.isAuthenticated) {
          const user = this.model.userList.find((_user) => _user.listValue === auth.user.listValue);
          if (user) {
            userId = user.listValue;
          }
        }
        this.model.currentUserId = userId;
      }
    })
      .catch((e) => { uUtils.error(e, `${$._('actionfailure')}\n${e.message}`); });
    return this;
  }

  /**
   * @param {uState} state
   */
  setObservers(state) {
    this.onChanged('userList', (list) => {
      this.select.setOptions(list);
    });
    this.onChanged('currentUserId', (listValue) => {
      this.state.showAllUsers = listValue === uSelect.allValue;
      this.state.currentUser = this.model.userList.find((_user) => _user.listValue === listValue) || null;
    });
    state.onChanged('showLatest', (showLatest) => {
      if (showLatest) {
        this.select.showAllOption();
      } else {
        this.select.hideAllOption();
      }
    });
  }

}
