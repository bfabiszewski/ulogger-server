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

import { config, lang } from './constants.js';
import uModal from './modal.js';
import uUtils from './utils.js';

export default class UserDialog {

  /**
   * @param {string} type: edit, add, pass
   * @param {uUser=} user Update existing user if supplied
   */
  constructor(type, user) {
    this.type = type;
    this.user = user;
    this.dialog = new uModal(this.getHtml());
    this.form = this.dialog.modal.querySelector('#userForm');
    this.form.onsubmit = () => false;
  }

  /**
   * @return {string}
   */
  getHtml() {
    let deleteButton = '';
    let header = '';
    let action;
    let fields;
    switch (this.type) {
      case 'add':
        action = 'add';
        header = `<label><b>${lang.strings['username']}</b></label>
        <input type="text" placeholder="${lang.strings['usernameenter']}" name="login" required>`;
        fields = `<label><b>${lang.strings['password']}</b></label>
        <input type="password" placeholder="${lang.strings['passwordenter']}" name="pass" required>
        <label><b>${lang.strings['passwordrepeat']}</b></label>
        <input type="password" placeholder="${lang.strings['passwordenter']}" name="pass2" required>`;
        break;
      case 'edit':
        action = 'update';
        deleteButton = `<div style="float:left">${uUtils.sprintf(lang.strings['editinguser'], `<b>${uUtils.htmlEncode(this.user.login)}</b>`)}</div>
        <div class="red-button button-resolve" data-action="delete" data-confirm="${uUtils.sprintf(lang.strings['userdelwarn'], uUtils.htmlEncode(this.user.login))}"><b><a>${lang.strings['deluser']}</a></b></div>
        <div style="clear: both; padding-bottom: 1em;"></div>`;
        fields = `<label><b>${lang.strings['password']}</b></label>
        <input type="password" placeholder="${lang.strings['passwordenter']}" name="pass" required>
        <label><b>${lang.strings['passwordrepeat']}</b></label>
        <input type="password" placeholder="${lang.strings['passwordenter']}" name="pass2" required>`;
        break;
      case 'pass':
        action = 'update';
        fields = `<label><b>${lang.strings['oldpassword']}</b></label>
        <input type="password" placeholder="${lang.strings['passwordenter']}" name="oldpass" required>
        <label><b>${lang.strings['newpassword']}</b></label>
        <input type="password" placeholder="${lang.strings['passwordenter']}" name="pass" required>
        <label><b>${lang.strings['newpasswordrepeat']}</b></label>
        <input type="password" placeholder="${lang.strings['passwordenter']}" name="pass2" required>`;
        break;
      default:
        throw new Error(`Unknown dialog type: ${this.type}`);
    }
    return `${deleteButton}
      <form id="userForm">
        ${header}
        ${fields}
        <div class="buttons">
          <button class="button-reject" type="button">${lang.strings['cancel']}</button>
          <button class="button-resolve" type="submit" data-action="${action}">${lang.strings['submit']}</button>
        </div>
      </form>`;
  }

  /**
   * Show edit user dialog
   * @see {uModal}
   * @returns {Promise<ModalResult>}
   */
  show() {
    return new Promise((resolve) => {
      this.resolveModal(resolve);
    });
  }

  /**
   * @param {ModalCallback} resolve
   */
  resolveModal(resolve) {
    this.dialog.show().then((result) => {
      if (result.cancelled) {
        return this.hide();
      }
      if (result.action === 'update' || result.action === 'add') {
        if (!this.validate()) {
          return this.resolveModal(resolve);
        }
        result.data = this.getData();
      }
      return resolve(result);
    });
  }

  /**
   * Hide dialog
   */
  hide() {
    this.dialog.hide();
  }

  /**
   * Get data from track form
   * @return {boolean|{login: string, password: string, oldPassword: ?string}}
   */
  getData() {
    let login;
    if (this.type === 'add') {
      login = this.form.elements['login'].value.trim();
    } else {
      login = this.user.login;
    }
    let oldPass = null;
    if (this.type === 'pass') {
      oldPass = this.form.elements['oldpass'].value.trim();
    }
    const pass = this.form.elements['pass'].value.trim();
    return { login: login, password: pass, oldPassword: oldPass };
  }

  /**
   * Validate form
   * @return {boolean} True if valid
   */
  validate() {
    if (this.type === 'add') {
      const login = this.form.elements['login'].value.trim();
      if (!login) {
        alert(lang.strings['allrequired']);
        return false;
      }
    } else if (this.type === 'pass') {
      const oldPass = this.form.elements['oldpass'].value.trim();
      if (!oldPass) {
        alert(lang.strings['allrequired']);
        return false;
      }
    }
    const pass = this.form.elements['pass'].value.trim();
    const pass2 = this.form.elements['pass2'].value.trim();
    if (!pass || !pass2) {
      alert(lang.strings['allrequired']);
      return false;
    }
    if (pass !== pass2) {
      alert(lang.strings['passnotmatch']);
      return false;
    }
    if (!config.pass_regex.test(pass)) {
      alert(lang.strings['passlenmin'] + '\n' + lang.strings['passrules']);
      return false;
    }
    return true;
  }

}
