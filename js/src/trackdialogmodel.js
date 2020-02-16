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

import { lang as $ } from '../src/initializer.js';
import ViewModel from './viewmodel.js';
import uDialog from './dialog.js';
import uUtils from './utils.js';

export default class TrackDialogModel extends ViewModel {

  /**
   * @param {TrackViewModel} viewModel
   */
  constructor(viewModel) {
    super({
      onTrackDelete: null,
      onTrackUpdate: null,
      onCancel: null,
      trackname: ''
    });
    this.track = viewModel.state.currentTrack;
    this.trackVM = viewModel;
    this.model.onTrackDelete = () => this.onTrackDelete();
    this.model.onTrackUpdate = () => this.onTrackUpdate();
    this.model.onCancel = () => this.onCancel();
  }

  init() {
    const html = this.getHtml();
    this.dialog = new uDialog(html);
    this.dialog.show();
    this.bindAll(this.dialog.element);
  }

  /**
   * @return {string}
   */
  getHtml() {
    return `<div class="red-button button-resolve"><b><a data-bind="onTrackDelete">${$._('deltrack')}</a></b></div>
      <div>${$._('editingtrack', `<b>${uUtils.htmlEncode(this.track.name)}</b>`)}</div>
      <div style="clear: both; padding-bottom: 1em;"></div>
      <form id="trackForm">
        <label><b>${$._('trackname')}</b></label>
        <input type="text" placeholder="${$._('trackname')}" name="trackname" data-bind="trackname" value="${uUtils.htmlEncode(this.track.name)}" required>
        <div class="buttons">
          <button class="button-reject" data-bind="onCancel" type="button">${$._('cancel')}</button>
          <button class="button-resolve" data-bind="onTrackUpdate" type="submit">${$._('submit')}</button>
        </div>
      </form>`;
  }

  onTrackDelete() {
    if (uDialog.isConfirmed($._('trackdelwarn', uUtils.htmlEncode(this.track.name)))) {
      this.track.delete().then(() => {
        this.trackVM.onTrackDeleted();
        this.dialog.destroy();
      }).catch((e) => { uUtils.error(e, `${$._('actionfailure')}\n${e.message}`); });
    }
  }

  onTrackUpdate() {
    if (this.validate()) {
      this.track.setName(this.model.trackname);
      this.track.saveMeta()
        .then(() => this.dialog.destroy())
        .catch((e) => { uUtils.error(e, `${$._('actionfailure')}\n${e.message}`); });
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
    if (this.model.trackname === this.track.name) {
      return false;
    }
    if (!this.model.trackname) {
      alert($._('allrequired'));
      return false;
    }
    return true;
  }
}
