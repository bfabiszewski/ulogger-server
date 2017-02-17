/* μlogger
 *
 * Copyright(C) 2017 Bartek Fabiszewski (www.fabiszewski.net)
 *
 * This is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Library General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */

function showModal(contentHTML) {
  var div = document.createElement("div");
  div.setAttribute("id", "modal");
  div.innerHTML = '<div id="modal-header"><button type="button" onclick="removeModal()">&times;</button></div><div id="modal-body"></div>';
  document.body.appendChild(div);
  var modalBody = document.getElementById('modal-body');
  modalBody.innerHTML = contentHTML;
}

function removeModal() {
  document.body.removeChild(document.getElementById('modal'));
}

function addUser() {
  var form = '<form id="userForm" method="post" onsubmit="submitUser(); return false">';
  form += '<label><b>User name</b></label><input type="text" placeholder="Enter user login" name="login" required>';
  form += '<label><b>Password</b></label><input type="password" placeholder="Enter Password" name="pass" required>';
  form += '<label><b>Repeat password</b></label><input type="password" placeholder="Enter Password" name="pass2" required>';
  form += '<button type="button" onclick="removeModal()">Cancel</button><button type="submit">Submit</button>';
  form += '</form>';
  showModal(form);
}

function submitUser() {
  var form = document.getElementById('userForm');
  var login = form.elements['login'].value;
  var pass = form.elements['pass'].value;
  var pass2 = form.elements['pass2'].value;
  if (!login || !pass || !pass2) {
    alert("All fields are required");
    return;
  }
  if (pass != pass2) {
    alert("Passwords don't match");
    return;
  }
  var xhr = getXHR();
  xhr.onreadystatechange = function() {
    if (xhr.readyState==4 && xhr.status==200) {
      var xml = xhr.responseXML;
      var message = "";
      if (xml) {
        var root =  xml.getElementsByTagName('root');
        if (root.length && getNode(root[0], 'error') == 0) {
          removeModal();
          alert("User successfully added");
          return;
        }
        errorMsg = getNode(root[0], 'message');
        if (errorMsg) { message = errorMsg; }
      }
      alert("Something went wrong\n" + message);
      xhr = null;
    }
  }
  xhr.open('POST', 'adduser.php', true);
  xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  xhr.send('login=' + login + '&pass=' + pass);
  return;
}