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
   * Show edit track dialog
   */
  function editTrack() {
    var userForm = ns.ui.userSelect;
    var trackUser = (userForm) ? userForm.options[userForm.selectedIndex].text : ns.config.auth;
    if (trackUser !== ns.config.auth && !ns.config.admin) {
      alert(ns.lang.strings['owntrackswarn']);
      return;
    }
    var trackForm = ns.ui.trackSelect;
    if (trackForm.selectedIndex < 0) {
      return;
    }
    var trackId = trackForm.options[trackForm.selectedIndex].value;
    var trackName = trackForm.options[trackForm.selectedIndex].text;
    var message = '<div style="float:left">' + ns.sprintf(ns.lang.strings['editingtrack'], '<b>' + ns.htmlEncode(trackName) + '</b>') + '</div>';
    message += '<div class="red-button"><b><a href="javascript:void(0);" onclick="uLogger.submitTrack(\'delete\'); return false">' + ns.lang.strings['deltrack'] + '</a></b></div>';
    message += '<div style="clear: both; padding-bottom: 1em;"></div>';

    var form = '<form id="trackForm" method="post" onsubmit="uLogger.submitTrack(\'update\'); return false">';
    form += '<input type="hidden" name="trackid" value="' + trackId + '">';
    form += '<label><b>' + ns.lang.strings['trackname'] + '</b></label><input type="text" placeholder="' + ns.lang.strings['trackname'] + '" name="trackname" value="' + ns.htmlEncode(trackName) + '" required>';
    form += '<div class="buttons"><button type="button" onclick="uLogger.ui.removeModal()">' + ns.lang.strings['cancel'] + '</button><button type="submit">' + ns.lang.strings['submit'] + '</button></div>';
    form += '</form>';
    ns.ui.showModal(message + form);
  }

  /**
   * Show confirmation dialog
   * @param {string} name
   * @returns {boolean} True if confirmed
   */
  function confirmedDelete(name) {
    return confirm(ns.sprintf(ns.lang.strings['trackdelwarn'], '"' + name + '"'));
  }

  /**
   * Submit form dialog
   * @param action
   */
  function submitTrack(action) {
    var form = document.getElementById('trackForm');
    var trackId = parseInt(form.elements['trackid'].value);
    var trackName = form.elements['trackname'].value.trim();
    if (isNaN(trackId)) {
      alert(ns.lang.strings['allrequired']);
      return;
    }
    if (action !== 'delete') {
      if (!trackName) {
        alert(ns.lang.strings['allrequired']);
        return;
      }
    } else if (!confirmedDelete(trackName)) {
      return;
    }

    ns.post('utils/handletrack.php',
      {
        action: action,
        trackid: trackId,
        trackname: trackName
      },
      {
        success: function () {
          ns.ui.removeModal();
          alert(ns.lang.strings['actionsuccess']);
          var el = ns.ui.trackSelect;
          if (action === 'delete') {
            el.remove(el.selectedIndex);
            ns.map.clearMap();
            ns.selectTrack();
          } else {
            el.options[el.selectedIndex].innerHTML = ns.htmlEncode(trackName);
          }
        },
        fail: function (message) {
          alert(ns.lang.strings['actionfailure'] + '\n' + message);
        }
      });
  }

  ns.editTrack = editTrack;
  ns.submitTrack = submitTrack;

})(uLogger);
