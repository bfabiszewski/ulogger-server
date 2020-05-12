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

import uUtils from './utils.js';

export default class uAlert {

  /**
   * @typedef {Object} AlertOptions
   * @property {number} [autoClose=0] Optional autoclose delay time in ms, default 0 – no autoclose
   * @property {string} [id] Optional box id
   * @property {string} [class] Optional box class
   */

  /**
   * Builds alert box
   * @param {string} message
   * @param {AlertOptions} [options] Optional options
   */
  constructor(message, options = {}) {
    this.autoClose = options.autoClose || 0;
    const html = `<div class="alert"><span>${message}</span></div>`;
    this.box = uUtils.nodeFromHtml(html);
    if (options.id) {
      this.box.id = options.id;
    }
    if (options.class) {
      this.box.classList.add(options.class);
    }
    if (this.autoClose === 0) {
      const button = document.createElement('button');
      button.setAttribute('type', 'button');
      button.textContent = '×';
      button.onclick = () => this.destroy();
      this.box.appendChild(button);
    }
    this.closeHandle = null;
  }

  /**
   * Calculate new box top offset
   * @return {number} Top offset
   */
  static getPosition() {
    const boxes = document.querySelectorAll('.alert');
    const lastBox = boxes[boxes.length - 1];
    let position = 0;
    if (lastBox) {
      const maxPosition = document.body.clientHeight - 100;
      position = lastBox.getBoundingClientRect().bottom;
      if (position > maxPosition) {
        position = maxPosition;
      }
    }
    return position;
  }

  render() {
    const top = uAlert.getPosition();
    if (top) {
      this.box.style.top = `${top}px`;
    }
    document.body.appendChild(this.box);
  }

  destroy() {
    if (this.closeHandle) {
      clearTimeout(this.closeHandle);
      this.closeHandle = null;
    }
    if (this.box) {
      if (document.body.contains(this.box)) {
        document.body.removeChild(this.box);
      }
      this.box = null;
    }
  }

  /**
   * Show alert box
   * @param {string} message
   * @param {AlertOptions} [options] Optional options
   * @return uAlert
   */
  static show(message, options) {
    const box = new uAlert(message, options);
    box.render();
    if (box.autoClose) {
      box.closeHandle = setTimeout(() => box.destroy(), box.autoClose);
    }
    return box;
  }

  /**
   * Show alert error box
   * @param {string} message
   * @param {Error=} e Optional error to be logged to console
   * @return uAlert
   */
  static error(message, e) {
    if (e instanceof Error) {
      console.error(`${e.name}: ${e.message} (${e.stack})`);
    }
    return this.show(message, { class: 'error' });
  }

  /**
   * Show alert toast box
   * @param {string} message
   * @return uAlert
   */
  static toast(message) {
    return this.show(message, { class: 'toast', autoClose: 10000 });
  }

}
