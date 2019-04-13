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
(function (ns) {

  /**
   * Show change password dialog
   */
  function changePass() {
    var form = '<form id="passForm" method="post" onsubmit="uLogger.submitPass(); return false">';
    form += '<label><b>' + ns.lang.strings['oldpassword'] + '</b></label><input type="password" placeholder="' + ns.lang.strings['passwordenter'] + '" name="oldpass" required>';
    form += '<label><b>' + ns.lang.strings['newpassword'] + '</b></label><input type="password" placeholder="' + ns.lang.strings['passwordenter'] + '" name="pass" required>';
    form += '<label><b>' + ns.lang.strings['newpasswordrepeat'] + '</b></label><input type="password" placeholder="' + ns.lang.strings['passwordenter'] + '" name="pass2" required>';
    form += '<button type="button" onclick="uLogger.ui.removeModal()">' + ns.lang.strings['cancel'] + '</button><button type="submit">' + ns.lang.strings['submit'] + '</button>';
    form += '</form>';
    ns.ui.showModal(form);
  }

  /**
   * Submit password form
   */
  function submitPass() {
    var form = document.getElementById('passForm');
    var oldpass = form.elements['oldpass'].value;
    var pass = form.elements['pass'].value;
    var pass2 = form.elements['pass2'].value;
    if (!oldpass || !pass || !pass2) {
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

    ns.post('utils/changepass.php',
      {
        oldpass: oldpass,
        pass: pass
      },
      {
        success: function () {
          ns.ui.removeModal();
          alert(ns.lang.strings['actionsuccess']);
        },
        fail: function (message) {
          alert(ns.lang.strings['actionfailure'] + '\n' + message);
        }
      });
  }

  // exports
  ns.changePass = changePass;
  ns.submitPass = submitPass;

})(uLogger);
