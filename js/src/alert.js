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
   * @property {boolean} [hasButton] Optional can be closed by button click, default true when autoClose not set
   * @property {string} [id] Optional box id
   * @property {string} [class] Optional box class
   * @property {boolean} [fixed=false] Optional set fixed position, default false
   */

  /**
   * Builds alert box
   * @param {string} message
   * @param {AlertOptions} [options] Optional options
   */
  constructor(message, options = {}) {
    this.autoClose = options.autoClose || 0;
    this.hasButton = typeof options.hasButton !== 'undefined' ? options.hasButton : this.autoClose === 0
    this.fixedPosition = options.fixed || false;
    const html = `<div class="alert"><span>${message}</span></div>`;
    /** @var HTMLElement */
    this.box = uUtils.nodeFromHtml(html);
    if (options.id) {
      this.box.id = options.id;
    }
    if (options.class) {
      this.box.classList.add(options.class);
    }
    if (this.hasButton) {
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
    if (!this.fixedPosition) {
      const top = uAlert.getPosition();
      if (top) {
        this.box.style.top = `${top}px`;
      }
    }
    document.body.appendChild(this.box);
    setTimeout(() => {
      if (this.box) {
        this.box.classList.add('in');
      }
    }, 50);
  }

  destroy() {
    if (this.closeHandle) {
      clearTimeout(this.closeHandle);
      this.closeHandle = null;
    }
    if (this.box && document.body.contains(this.box)) {
      const element = this.box;
      requestAnimationFrame(() => {
        element.classList.add('out');
        setTimeout(() => {
          element.remove();
        }, 1000);
      });
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

  static spinner() {
    return this.show('', { class: 'spinner', hasButton: false, fixed: true });
  }

}
