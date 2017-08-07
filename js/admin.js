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

function addUser() {
  var form = '<form id="userForm" method="post" onsubmit="submitUser(\'add\'); return false">';
  form += '<label><b>' + lang['username'] + '</b></label><input type="text" placeholder="' + lang['usernameenter'] + '" name="login" required>';
  form += '<label><b>' + lang['password'] + '</b></label><input type="password" placeholder="' + lang['passwordenter'] + '" name="pass" required>';
  form += '<label><b>' + lang['passwordrepeat'] + '</b></label><input type="password" placeholder="' + lang['passwordenter'] + '" name="pass2" required>';
  form += '<div class="buttons"><button type="button" onclick="removeModal()">' + lang['cancel'] + '</button><button type="submit">' + lang['submit'] + '</button></div>';
  form += '</form>';
  showModal(form);
}

function editUser() {
  var userForm = document.getElementsByName('user')[0];
  var userLogin = (userForm !== undefined) ? userForm.options[userForm.selectedIndex].text : auth;
  if (userLogin == auth) {
    alert(lang['selfeditwarn']);
    return;
  }
  var message = '<div style="float:left">' + sprintf(lang['editinguser'], '<b>' + htmlEncode(userLogin) + '</b>') + '</div>';
  message += '<div class="red-button"><b><a href="javascript:void(0);" onclick="submitUser(\'delete\'); return false">' + lang['deluser'] + '</a></b></div>';
  message += '<div style="clear: both; padding-bottom: 1em;"></div>';

  var form = '<form id="userForm" method="post" onsubmit="submitUser(\'update\'); return false">';
  form += '<input type="hidden" name="login" value="' + htmlEncode(userLogin) + '">';
  form += '<label><b>' + lang['password'] + '</b></label><input type="password" placeholder="' + lang['passwordenter'] + '" name="pass" required>';
  form += '<label><b>' + lang['passwordrepeat'] + '</b></label><input type="password" placeholder="' + lang['passwordenter'] + '" name="pass2" required>';
  form += '<div class="buttons"><button type="button" onclick="removeModal()">' + lang['cancel'] + '</button><button type="submit">' + lang['submit'] + '</button></div>';
  form += '</form>';
  showModal(message + form);
}

function confirmedDelete(login) {
  return confirm(sprintf(lang['userdelwarn'], '"' + login + '"'));
}

function submitUser(action) {
  var form = document.getElementById('userForm');
  var login = form.elements['login'].value.trim();
  if (!login) {
      alert(lang['allrequired']);
      return;
  }
  var pass = null;
  var pass2 = null;
  if (action != 'delete') {
    pass = form.elements['pass'].value;
    pass2 = form.elements['pass2'].value;
    if (!pass || !pass2) {
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
  } else {
    if (!confirmedDelete(login)) {
      return;
    }
  }
  var xhr = getXHR();
  xhr.onreadystatechange = function() {
    if (xhr.readyState == 4) {
      var error = true;
      var message = "";
      if (xhr.status == 200) {
        var xml = xhr.responseXML;
        if (xml) {
          var root = xml.getElementsByTagName('root');
          if (root.length && getNode(root[0], 'error') == 0) {
            removeModal();
            alert(lang['actionsuccess']);
            if (action == 'delete') {
              // select current user in users form
              var f = document.getElementsByName('user')[0];
              f.remove(f.selectedIndex);
              selectUser(f);
            }
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
  xhr.open('POST', 'utils/handleuser.php', true);
  xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  var params = 'action=' + action + '&login=' + encodeURIComponent(login) + '&pass=' + encodeURIComponent(pass);
  params = params.replace(/%20/g, '+');
  xhr.send(params);
  return;
}