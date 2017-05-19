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

function changePass() {
  var form = '<form id="passForm" method="post" onsubmit="submitPass(); return false">';
  form += '<label><b>' + lang['oldpassword'] + '</b></label><input type="password" placeholder="' + lang['passwordenter'] + '" name="oldpass" required>';
  form += '<label><b>' + lang['newpassword'] + '</b></label><input type="password" placeholder="' + lang['passwordenter'] + '" name="pass" required>';
  form += '<label><b>' + lang['newpasswordrepeat'] + '</b></label><input type="password" placeholder="' + lang['passwordenter'] + '" name="pass2" required>';
  form += '<button type="button" onclick="removeModal()">' + lang['cancel'] + '</button><button type="submit">' + lang['submit'] + '</button>';
  form += '</form>';
  showModal(form);
}

function submitPass() {
  var form = document.getElementById('passForm');
  var oldpass = form.elements['oldpass'].value;
  var pass = form.elements['pass'].value;
  var pass2 = form.elements['pass2'].value;
  if (!oldpass || !pass || !pass2) {
    alert(lang['allrequired']);
    return;
  }
  if (pass != pass2) {
    alert(lang['passnotmatch']);
    return;
  }
  if (!pass_regex.test(pass)) {
    alert(lang['passlenmin'] + '\n' + lang['passrules']);
    return;
  }

  var xhr = getXHR();
  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4) {
      var error = true;
      var message = "";
      if (xhr.status == 200) {
        var xml = xhr.responseXML;
        if (xml) {
          var root = xml.getElementsByTagName('root');
          if (root.length && getNode(root[0], 'error') == 0) {
            removeModal();
            alert(lang["actionsuccess"]);
            error = false;
          } else if (root.length) {
            errorMsg = getNode(root[0], 'message');
            if (errorMsg) { message = errorMsg; }
          }
        }
      }
      if (error) {
        alert(lang['actionfailure'] + '\n' + message);
      }
      xhr = null;
    }
  }
  xhr.open('POST', 'utils/changepass.php', true);
  xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  var params = 'oldpass=' + encodeURIComponent(oldpass) + '&pass=' + encodeURIComponent(pass);
  params = params.replace(/%20/g, '+');
  xhr.send(params);
  return;
}