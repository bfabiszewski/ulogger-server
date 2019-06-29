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

import { auth, config, lang } from './constants.js';
import UserDialog from './userdialog.js';
import uList from './list.js';
import { uLogger } from './ulogger.js';
import uUser from './user.js';

/**
 * @class UserList
 * @extends {uList<uUser>}
 */
export default class UserList extends uList {

  /**
   * @param {string} selector
   * @param {uBinder} binder
   */
  constructor(selector, binder) {
    super(selector, binder, uUser);
    super.hasHead = true;
    super.allValue = `- ${lang.strings['allusers']} -`;
    super.headValue = lang.strings['suser'];
  }

  /**
   * @override
   */
  onChange() {
    if (config.showLatest) {
      if (this.isSelectedAllOption) {
        uLogger.trackList.fetchLatest();
      } else {
        uLogger.trackList.fetch()
          .then(() => uLogger.trackList.fetchLatest());
      }
    } else {
      uLogger.trackList.fetch();
    }
  }

  /**
   * @override
   */
  onConfigChange(property) {
    if (property === 'showLatest') {
      if (config.showLatest && this.data.length > 1) {
        this.showAllOption = true;
      } else if (!config.showLatest && this.showAllOption) {
        this.showAllOption = false;
      }
    }
  }

  /**
   * @override
   */
  onEdit() {
    if (this.isSelectedAllOption) {
      return;
    }
    if (this.current) {
      if (this.current.login === auth.user.login) {
        alert(lang.strings['selfeditwarn']);
        return;
      }
      this.editUser();
    }
  }

  /**
   * @param {UserDialog=} modal
   */
  editUser(modal) {
    const dialog = modal || new UserDialog('edit', this.current);
    dialog.show()
      .then((result) => {
        switch (result.action) {
          case 'update':
            // currently only password
            this.current.password = result.data.password;
            return this.current.update('update');
          case 'delete':
            return this.current.update('delete').then(() => this.remove(this.current.id));
          default:
            break;
        }
        throw new Error();
      })
      .then(() => {
        alert(lang.strings['actionsuccess']);
        dialog.hide();
      })
      .catch((msg) => {
          alert(`${lang.strings['actionfailure']}\n${msg}`);
          this.editUser(dialog);
      });
  }

  /**
   * @override
   */
  onAdd() {
    this.addUser();
  }

  /**
   * @param {UserDialog=} modal
   */
  addUser(modal) {
    const dialog = modal || new UserDialog('add');
    dialog.show()
      .then((result) => {
        const newUser = new uUser(0, result.data.login);
        newUser.password = result.data.password;
        return newUser.update('add')
      })
      .then((user) => {
        alert(lang.strings['actionsuccess']);
        this.add(user);
        dialog.hide();
      })
      .catch((msg) => {
        alert(`${lang.strings['actionfailure']}\n${msg}`);
        this.addUser(dialog);
      });
  }

}
