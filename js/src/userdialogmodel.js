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

import { lang as $, auth, config } from './initializer.js';
import ViewModel from './viewmodel.js';
import uDialog from './dialog.js';
import uUser from './user.js';
import uUtils from './utils.js';

export default class UserDialogModel extends ViewModel {

  /**
   * @param {UserViewModel} viewModel
   * @param {string} type
   */
  constructor(viewModel, type) {
    super({
      onUserDelete: null,
      onUserUpdate: null,
      onUserAdd: null,
      onCancel: null,
      login: null,
      password: null,
      password2: null,
      oldPassword: null
    });
    this.user = viewModel.state.currentUser;
    this.type = type;
    this.userVM = viewModel;
    this.model.onUserDelete = () => this.onUserDelete();
    this.model.onUserUpdate = () => this.onUserUpdate();
    this.model.onUserAdd = () => this.onUserAdd();
    this.model.onCancel = () => this.onCancel();
  }

  init() {
    const html = this.getHtml();
    this.dialog = new uDialog(html);
    this.dialog.show();
    this.bindAll(this.dialog.element);
  }

  onUserDelete() {
    if (uDialog.isConfirmed($._('userdelwarn', uUtils.htmlEncode(this.user.login)))) {
      this.user.delete().then(() => {
        this.userVM.onUserDeleted();
        this.dialog.destroy();
      }).catch((e) => { uUtils.error(e, `${$._('actionfailure')}\n${e.message}`); });
    }
  }

  onUserUpdate() {
    if (this.validate()) {
      const user = this.type === 'pass' ? auth.user : this.user;
      user.setPassword(this.model.password, this.model.oldPassword)
        .then(() => this.dialog.destroy())
        .catch((e) => { uUtils.error(e, `${$._('actionfailure')}\n${e.message}`); });
    }
  }

  onUserAdd() {
    if (this.validate()) {
      uUser.add(this.model.login, this.model.password).then((user) => {
        this.userVM.onUserAdded(user);
        this.dialog.destroy();
      }).catch((e) => { uUtils.error(e, `${$._('actionfailure')}\n${e.message}`); });
    }
  }

  onCancel() {
    this.dialog.destroy();
  }

  /**
   * Validate form
   * @return {boolean} True if valid
   */
  validate() {
    if (this.type === 'add') {
      if (!this.model.login) {
        alert($._('allrequired'));
        return false;
      }
    } else if (this.type === 'pass') {
      if (!this.model.oldPassword) {
        alert($._('allrequired'));
        return false;
      }
    }
    if (!this.model.password || !this.model.password2) {
      alert($._('allrequired'));
      return false;
    }
    if (this.model.password !== this.model.password2) {
      alert($._('passnotmatch'));
      return false;
    }
    if (!config.passRegex.test(this.model.password)) {
      alert($._('passlenmin') + '\n' + $._('passrules'));
      return false;
    }
    return true;
  }

  /**
   * @return {string}
   */
  getHtml() {
    let deleteButton = '';
    let header = '';
    let observer;
    let fields;
    switch (this.type) {
      case 'add':
        observer = 'onUserAdd';
        header = `<label><b>${$._('username')}</b></label>
        <input type="text" placeholder="${$._('usernameenter')}" name="login" data-bind="login" required>`;
        fields = `<label><b>${$._('password')}</b></label>
        <input type="password" placeholder="${$._('passwordenter')}" name="password" data-bind="password" required>
        <label><b>${$._('passwordrepeat')}</b></label>
        <input type="password" placeholder="${$._('passwordenter')}" name="password2" data-bind="password2" required>`;
        break;
      case 'edit':
        observer = 'onUserUpdate';
        deleteButton = `<div style="float:left">${$._('editinguser', `<b>${uUtils.htmlEncode(this.user.login)}</b>`)}</div>
        <div class="red-button button-resolve"><b><a data-bind="onUserDelete">${$._('deluser')}</a></b></div>
        <div style="clear: both; padding-bottom: 1em;"></div>`;
        fields = `<label><b>${$._('password')}</b></label>
        <input type="password" placeholder="${$._('passwordenter')}" name="password" data-bind="password" required>
        <label><b>${$._('passwordrepeat')}</b></label>
        <input type="password" placeholder="${$._('passwordenter')}" name="password2" data-bind="password2" required>`;
        break;
      case 'pass':
        observer = 'onUserUpdate';
        fields = `<label><b>${$._('oldpassword')}</b></label>
        <input type="password" placeholder="${$._('passwordenter')}" name="old-password" data-bind="oldPassword" required>
        <label><b>${$._('newpassword')}</b></label>
        <input type="password" placeholder="${$._('passwordenter')}" name="password" data-bind="password" required>
        <label><b>${$._('newpasswordrepeat')}</b></label>
        <input type="password" placeholder="${$._('passwordenter')}" name="password2" data-bind="password2" required>`;
        break;
      default:
        throw new Error(`Unknown dialog type: ${this.type}`);
    }
    return `${deleteButton}
      <form id="userForm">
        ${header}
        ${fields}
        <div class="buttons">
          <button class="button-reject" type="button" data-bind="onCancel">${$._('cancel')}</button>
          <button class="button-resolve" type="submit" data-bind="${observer}">${$._('submit')}</button>
        </div>
      </form>`;
  }

}
