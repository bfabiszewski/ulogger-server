/*
 * μlogger
 *
 * Copyright(C) 2020 Bartek Fabiszewski (www.fabiszewski.net)
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

import { lang as $ } from './initializer.js';
import ViewModel from './viewmodel.js';
import uDialog from './dialog.js';
import uObserve from './observe.js';
import uUtils from './utils.js';

/**
 * @class PositionDialogModel
 */
export default class PositionDialogModel extends ViewModel {

  /**
   * @param {uState} state
   * @param {number} positionIndex
   */
  constructor(state, positionIndex) {
    super({
      onPositionDelete: null,
      onPositionUpdate: null,
      onCancel: null,
      comment: ''
    });
    this.state = state;
    this.positionIndex = positionIndex;
    this.position = this.state.currentTrack.positions[positionIndex];
    this.model.onPositionDelete = () => this.onPositionDelete();
    this.model.onPositionUpdate = () => this.onPositionUpdate();
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
    return `<div class="red-button button-resolve"><b><a data-bind="onPositionDelete">${$._('delposition')}</a></b></div>
      <div>${$._('editingposition', this.positionIndex + 1, `<b>${uUtils.htmlEncode(this.position.trackname)}</b>`)}</div>
      <div style="clear: both; padding-bottom: 1em;"></div>
      <form id="positionForm">
        <label><b>${$._('comment')}</b></label><br>
        <textarea style="width:100%;" maxlength="255" rows="5" placeholder="${$._('comment')}" name="comment" data-bind="comment">${this.position.hasComment() ? uUtils.htmlEncode(this.position.comment) : ''}</textarea>
        <div class="buttons">
          <button class="button-reject" data-bind="onCancel" type="button">${$._('cancel')}</button>
          <button class="button-resolve" data-bind="onPositionUpdate" type="submit">${$._('submit')}</button>
        </div>
      </form>`;
  }

  onPositionDelete() {
    if (uDialog.isConfirmed($._('positiondelwarn', this.positionIndex + 1, uUtils.htmlEncode(this.position.trackname)))) {
      this.position.delete()
        .then(() => {
          const track = this.state.currentTrack;
          this.state.currentTrack = null;
          track.positions.splice(this.positionIndex, 1);
          track.recalculatePositions();
          this.state.currentTrack = track;
          this.dialog.destroy();
        }).catch((e) => { uUtils.error(e, `${$._('actionfailure')}\n${e.message}`); });
    }
  }

  onPositionUpdate() {
    if (this.validate()) {
      this.position.comment = this.model.comment;
      this.position.save()
        .then(() => {
          uObserve.forceUpdate(this.state, 'currentTrack');
          this.dialog.destroy()
        })
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
    return this.model.comment !== this.position.comment;
  }
}
