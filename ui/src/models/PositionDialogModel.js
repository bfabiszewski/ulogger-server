/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import { lang as $, config } from '../Initializer.js';
import Alert from '../Alert.js';
import Dialog from '../Dialog.js';
import Observer from '../Observer.js';
import Utils from '../Utils.js';
import ViewModel from '../ViewModel.js';

const hiddenClass = 'hidden';

/**
 * @class PositionDialogModel
 */
export default class PositionDialogModel extends ViewModel {

  /**
   * @param {State} state
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
    this.model.image = this.position.getImagePath();
    this.model.onPositionDelete = () => this.onPositionDelete();
    this.model.onPositionUpdate = () => this.onPositionUpdate();
    this.model.onCancel = () => this.onCancel();
    this.model.onImageDelete = () => this.onImageDelete();
    this.onChanged('image', (image) => {
      if (image && image !== this.position.getImagePath()) { this.readImage(); }
    });
  }

  init() {
    const html = this.getHtml();
    this.dialog = new Dialog(html);
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
      this.model.image = this.position.getImagePath();
    }, false);
  }

  readImage() {
    const file = this.fileEl.files[0];
    if (file) {
      if (file.size > config.uploadMaxSize) {
        Alert.error($._('isizefailure', config.uploadMaxSize));
        this.model.image = this.position.getImagePath();
        return;
      }
      this.reader.readAsDataURL(file);
    }
  }

  showThumbnail() {
    this.previewEl.onload = () => this.toggleImage();
    this.previewEl.onerror = () => {
      Alert.error($._('iuploadfailure'));
      this.model.image = this.position.getImagePath();
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
      <div>${$._('editingposition', this.positionIndex + 1, `<b>${Utils.htmlEncode(this.position.trackName)}</b>`)}</div>
      <div style="clear: both; padding-bottom: 1em;"></div>
      <form id="positionForm">
        <label><b>${$._('comment')}</b></label><br>
        <textarea style="width:100%;" maxlength="255" rows="5" placeholder="${$._('comment')}" name="comment" 
        data-bind="comment" autofocus>${Utils.htmlEncode(this.model.comment)}</textarea>
        <br><br>
        <label><b>${$._('image')}</b></label><br>
        <input type="file" name="image" data-bind="image" accept="image/png, image/jpeg, image/gif, image/bmp"${this.position.hasImage ? ' class="hidden"' : ''}>
        <img alt="${$._('image')}" style="max-width:50px; max-height:50px;" data-bind="imagePreview" ${this.position.hasImage ? `src="${this.position.getImagePath()}"` : 'class="hidden"'}>
        <a data-bind="onImageDelete" ${this.position.hasImage ? '' : ' class="hidden"'}>${$._('delimage')}</a>
        <div class="buttons">
          <button class="button-reject" data-bind="onCancel" type="button">${$._('cancel')}</button>
          <button class="button-resolve" data-bind="onPositionUpdate" type="submit">${$._('submit')}</button>
        </div>
      </form>`;
  }

  onPositionDelete() {
    if (Dialog.isConfirmed($._('positiondelwarn', this.positionIndex + 1, Utils.htmlEncode(this.position.trackName)))) {
      this.position.delete()
        .then(() => {
          const track = this.state.currentTrack;
          this.state.currentTrack = null;
          track.positions.splice(this.positionIndex, 1);
          track.recalculatePositions();
          this.state.currentTrack = track;
          this.dialog.destroy();
        }).catch((e) => { Alert.error(`${$._('actionfailure')}\n${e.message}`, e); });
    }
  }

  /**
   * @return {Promise<void>}
   */
  updateImage() {
    let promise = Promise.resolve();
    if (this.model.image !== this.position.getImagePath()) {
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
        .then(() => {
          this.position.hasImage = this.model.image !== null;
          return this.position.save();
        })
        .then(() => {
          Observer.forceUpdate(this.state, 'currentTrack');
          this.dialog.destroy()
        })
        .catch((e) => { Alert.error(`${$._('actionfailure')}\n${e.message}`, e); });
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
    return !(this.model.comment === this.position.comment && this.model.image === this.position.getImagePath());
  }
}
