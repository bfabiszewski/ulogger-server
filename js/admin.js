/* μlogger
 *
 * Copyright(C) 2017 Bartek Fabiszewski (www.fabiszewski.net)
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

/** @namespace */
var uLogger = window.uLogger || {};
(function (ul) {

  /**
   * @typedef uLogger.admin
   * @memberOf uLogger
   * @type {Object}
   * @property {function} addUser
   * @property {function} editUser
   * @property {function} submitUser
   */
  ul.admin = (function (ns) {

    /**
     * Show add user dialog
     */
    function addUser() {
      var form = '<form id="userForm" method="post" onsubmit="uLogger.admin.submitUser(\'add\'); return false">';
      form += '<label><b>' + ns.lang.strings['username'] + '</b></label><input type="text" placeholder="' + ns.lang.strings['usernameenter'] + '" name="login" required>';
      form += '<label><b>' + ns.lang.strings['password'] + '</b></label><input type="password" placeholder="' + ns.lang.strings['passwordenter'] + '" name="pass" required>';
      form += '<label><b>' + ns.lang.strings['passwordrepeat'] + '</b></label><input type="password" placeholder="' + ns.lang.strings['passwordenter'] + '" name="pass2" required>';
      form += '<div class="buttons"><button type="button" onclick="uLogger.ui.removeModal()">' + ns.lang.strings['cancel'] + '</button><button type="submit">' + ns.lang.strings['submit'] + '</button></div>';
      form += '</form>';
      ns.ui.showModal(form);
    }

    /**
     * Show edit user dialog
     */
    function editUser() {
      var userForm = ns.ui.userSelect;
      var userLogin = (userForm) ? userForm.options[userForm.selectedIndex].text : ns.config.auth;
      if (userLogin === ns.config.auth) {
        alert(ns.lang.strings['selfeditwarn']);
        return;
      }
      var message = '<div style="float:left">' + ns.sprintf(ns.lang.strings['editinguser'], '<b>' + ns.htmlEncode(userLogin) + '</b>') + '</div>';
      message += '<div class="red-button"><b><a href="javascript:void(0);" onclick="uLogger.admin.submitUser(\'delete\'); return false">' + ns.lang.strings['deluser'] + '</a></b></div>';
      message += '<div style="clear: both; padding-bottom: 1em;"></div>';

      var form = '<form id="userForm" method="post" onsubmit="uLogger.admin.submitUser(\'update\'); return false">';
      form += '<input type="hidden" name="login" value="' + ns.htmlEncode(userLogin) + '">';
      form += '<label><b>' + ns.lang.strings['password'] + '</b></label><input type="password" placeholder="' + ns.lang.strings['passwordenter'] + '" name="pass" required>';
      form += '<label><b>' + ns.lang.strings['passwordrepeat'] + '</b></label><input type="password" placeholder="' + ns.lang.strings['passwordenter'] + '" name="pass2" required>';
      form += '<div class="buttons"><button type="button" onclick="uLogger.ui.removeModal()">' + ns.lang.strings['cancel'] + '</button><button type="submit">' + ns.lang.strings['submit'] + '</button></div>';
      form += '</form>';
      ns.ui.showModal(message + form);
    }

    /**
     * Show confirmation dialog
     * @param {string} login
     * @returns {boolean} True if confirmed
     */
    function confirmedDelete(login) {
      return confirm(ns.sprintf(ns.lang.strings['userdelwarn'], '"' + login + '"'));
    }

    /**
     * Submit user form
     * @param {string} action Add, delete, update
     */
    function submitUser(action) {
      var form = document.getElementById('userForm');
      var login = form.elements['login'].value.trim();
      if (!login) {
        alert(ns.lang.strings['allrequired']);
        return;
      }
      var pass = null;
      var pass2 = null;
      if (action !== 'delete') {
        pass = form.elements['pass'].value;
        pass2 = form.elements['pass2'].value;
        if (!pass || !pass2) {
          alert(ns.lang.strings['allrequired']);
          return;
        }
        if (pass !== pass2) {
          alert(ns.lang.strings['passnotmatch']);
          return;
        }
        if (!ns.config.pass_regex.test(pass)) {
          alert(ns.lang.strings['passlenmin'] + '\n' + ns.lang.strings['passrules']);
          return;
        }
      } else if (!confirmedDelete(login)) {
        return;
      }

      ns.post('utils/handleuser.php',
        {
          action: action,
          login: login,
          pass: pass
        },
        {
          success: function () {
            ns.ui.removeModal();
            alert(ns.lang.strings['actionsuccess']);
            if (action === 'delete') {
              var f = ns.ui.userSelect;
              f.remove(f.selectedIndex);
              ns.selectUser(f);
            }
          },
          fail: function (message) {
            alert(ns.lang.strings['actionfailure'] + '\n' + message);
          }
        });
    }

    // noinspection JSUnusedGlobalSymbols
    return {
      addUser: addUser,
      editUser: editUser,
      submitUser: submitUser
    }

  })(ul);

})(uLogger);
