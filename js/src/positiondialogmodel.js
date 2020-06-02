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

import { lang as $, config } from './initializer.js';
import ViewModel from './viewmodel.js';
import uAlert from './alert.js';
import uDialog from './dialog.js';
import uObserve from './observe.js';
import uUtils from './utils.js';

const hiddenClass = 'hidden';

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
      comment: null,
      image: null,
      onImageDelete: null
    });
    this.state = state;
    this.positionIndex = positionIndex;
    this.position = this.state.currentTrack.positions[positionIndex];
    this.model.comment = this.position.hasComment() ? this.position.comment : '';
    this.model.image = this.position.image;
    this.model.onPositionDelete = () => this.onPositionDelete();
    this.model.onPositionUpdate = () => this.onPositionUpdate();
    this.model.onCancel = () => this.onCancel();
    this.model.onImageDelete = () => this.onImageDelete();
    this.onChanged('image', (image) => {
      if (image && image !== this.position.image) { this.readImage(); }
    });
  }

  init() {
    const html = this.getHtml();
    this.dialog = new uDialog(html);
    this.dialog.show();
    this.bindAll(this.dialog.element);
    this.previewEl = this.getBoundElement('imagePreview');
    this.fileEl = this.getBoundElement('image');
    this.imageDeleteEl = this.getBoundElement('onImageDelete');
    this.initReader();
  }

  initReader() {
    this.reader = new FileReader();
    this.reader.addEventListener('load', () => {
      this.showThumbnail();
    }, false);
    this.reader.addEventListener('error', () => {
      this.model.image = this.position.image;
    }, false);
  }

  readImage() {
    const file = this.fileEl.files[0];
    if (file) {
      if (file.size > config.uploadMaxSize) {
        uAlert.error($._('isizefailure', config.uploadMaxSize));
        this.model.image = this.position.image;
        return;
      }
      this.reader.readAsDataURL(file);
    }
  }

  showThumbnail() {
    this.previewEl.onload = () => this.toggleImage();
    this.previewEl.onerror = () => {
      uAlert.error($._('iuploadfailure'));
      this.model.image = this.position.image;
    };
    this.previewEl.src = this.reader.result;
  }

  /**
   * Toggle image visibility
   */
  toggleImage() {
    if (this.previewEl.classList.contains(hiddenClass)) {
      this.previewEl.classList.remove(hiddenClass);
      this.imageDeleteEl.classList.remove(hiddenClass);
      this.fileEl.classList.add(hiddenClass);
    } else {
      this.previewEl.classList.add(hiddenClass);
      this.imageDeleteEl.classList.add(hiddenClass);
      this.fileEl.classList.remove(hiddenClass);
    }
  }

  onImageDelete() {
    this.model.image = null;
    this.toggleImage();
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
        <textarea style="width:100%;" maxlength="255" rows="5" placeholder="${$._('comment')}" name="comment" 
        data-bind="comment" autofocus>${uUtils.htmlEncode(this.model.comment)}</textarea>
        <br><br>
        <label><b>${$._('image')}</b></label><br>
        <input type="file" name="image" data-bind="image" accept="image/png, image/jpeg, image/gif, image/bmp"${this.position.hasImage() ? ' class="hidden"' : ''}>
        <img style="max-width:50px; max-height:50px" data-bind="imagePreview" ${this.position.hasImage() ? `src="${this.position.getImagePath()}"` : 'class="hidden"'}>
        <a data-bind="onImageDelete" ${this.position.hasImage() ? '' : ' class="hidden"'}>${$._('delimage')}</a>
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
        }).catch((e) => { uAlert.error(`${$._('actionfailure')}\n${e.message}`, e); });
    }
  }

  /**
   * @return {Promise<void>}
   */
  updateImage() {
    let promise = Promise.resolve();
    if (this.model.image !== this.position.image) {
      if (this.model.image === null) {
        promise = this.position.imageDelete();
      } else {
        promise = this.position.imageAdd(this.fileEl.files[0]);
      }
    }
    return promise;
  }

  onPositionUpdate() {
    this.model.comment.trim();
    if (this.validate()) {
      this.position.comment = this.model.comment;
      this.updateImage()
        .then(() => this.position.save())
        .then(() => {
          uObserve.forceUpdate(this.state, 'currentTrack');
          this.dialog.destroy()
        })
        .catch((e) => { uAlert.error(`${$._('actionfailure')}\n${e.message}`, e); });
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
    return !(this.model.comment === this.position.comment && this.model.image === this.position.image);

  }
}
