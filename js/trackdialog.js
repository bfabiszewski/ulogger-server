/* μlogger
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

import { lang } from './constants.js';
import uModal from './modal.js';
import uUtils from './utils.js';

export default class TrackDialog {

  /**
   * @param {uTrack} track
   */
  constructor(track) {
    this.track = track;
    const html = `<div style="float:left">${uUtils.sprintf(lang.strings['editingtrack'], `<b>${uUtils.htmlEncode(this.track.name)}</b>`)}</div>
      <div class="red-button button-resolve" data-action="delete" data-confirm="${uUtils.sprintf(lang.strings['trackdelwarn'], uUtils.htmlEncode(this.track.name))}"><b><a>${lang.strings['deltrack']}</a></b></div>
      <div style="clear: both; padding-bottom: 1em;"></div>
      <form id="trackForm">
        <label><b>${lang.strings['trackname']}</b></label>
        <input type="text" placeholder="${lang.strings['trackname']}" name="trackname" value="${uUtils.htmlEncode(this.track.name)}" required>
        <div class="buttons">
          <button class="button-reject" type="button">${lang.strings['cancel']}</button>
          <button class="button-resolve" type="submit" data-action="update">${lang.strings['submit']}</button>
        </div>
      </form>`;
    this.dialog = new uModal(html);
    this.form = this.dialog.modal.querySelector('#trackForm');
    this.form.onsubmit = () => false;

  }

  /**
   * Show edit track dialog
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
      if (result.action === 'update') {
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
   * @return {{name: string}}
   */
  getData() {
    const trackName = this.form.elements['trackname'].value.trim();
    return { name: trackName };
  }

  /**
   * Validate form
   * @return {boolean} True if valid
   */
  validate() {
    const trackName = this.form.elements['trackname'].value.trim();
    if (trackName === this.track.name) {
      return false;
    }
    if (!trackName) {
      alert(lang.strings['allrequired']);
      return false;
    }
    return true;
  }

}
