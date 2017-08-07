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

function editTrack() {
  var userForm = document.getElementsByName('user')[0];
  var trackUser = (userForm !== undefined) ? userForm.options[userForm.selectedIndex].text : auth;
  if (trackUser != auth && !admin) {
    alert(lang['owntrackswarn']);
    return;
  }
  var trackForm = document.getElementsByName('track')[0];
  if (trackForm.selectedIndex < 0) {
    return;
  }
  var trackId = trackForm.options[trackForm.selectedIndex].value;
  var trackName = trackForm.options[trackForm.selectedIndex].text;
  var message = '<div style="float:left">' + sprintf(lang['editingtrack'], '<b>' + htmlEncode(trackName) + '</b>') + '</div>';
  message += '<div class="red-button"><b><a href="javascript:void(0);" onclick="submitTrack(\'delete\'); return false">' + lang['deltrack'] + '</a></b></div>';
  message += '<div style="clear: both; padding-bottom: 1em;"></div>';

  var form = '<form id="trackForm" method="post" onsubmit="submitTrack(\'update\'); return false">';
  form += '<input type="hidden" name="trackid" value="' + trackId + '">';
  form += '<label><b>' + lang['trackname'] + '</b></label><input type="text" placeholder="' + lang['trackname'] + '" name="trackname" value="' + htmlEncode(trackName) + '" required>';
  form += '<div class="buttons"><button type="button" onclick="removeModal()">' + lang['cancel'] + '</button><button type="submit">' + lang['submit'] + '</button></div>';
  form += '</form>';
  showModal(message + form);
}

function confirmedDelete(name) {
  return confirm(sprintf(lang['trackdelwarn'], '"' + name + '"'));
}

function submitTrack(action) {
  var form = document.getElementById('trackForm');
  var trackId = parseInt(form.elements['trackid'].value);
  var trackName = form.elements['trackname'].value.trim();
  if (isNaN(trackId)) {
      alert(lang['allrequired']);
      return;
  }
  if (action != 'delete') {
    if (!trackName) {
      alert(lang['allrequired']);
      return;
    }
  } else {
    if (!confirmedDelete(trackName)) {
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
            var f = document.getElementsByName('track')[0];
            if (action == 'delete') {
              // select current track in tracks form
              f.remove(f.selectedIndex);
              clearMap();
              selectTrack(f);
            } else {
              f.options[f.selectedIndex].innerHTML = htmlEncode(trackName);
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
  xhr.open('POST', 'utils/handletrack.php', true);
  xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
  var params = 'action=' + action + '&trackid=' + trackId + '&trackname=' + encodeURIComponent(trackName);
  params = params.replace(/%20/g, '+');
  xhr.send(params);
  return;
}