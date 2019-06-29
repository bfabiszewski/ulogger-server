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

import { lang } from './constants.js';

/**
 * @typedef {Object} ModalResult
 * @property {boolean} cancelled Was dialog cancelled
 * @property {string} [action] Click action name
 * @property {Object} [data] Additional data
 */

/**
 * @callback ModalCallback
 * @param {ModalResult} result
 */

export default class uModal {

  /**
   * Builds modal dialog
   * Positive click handlers bound to elements with class 'button-resolve'.
   * Negative click handlers bound to elements with class 'button-reject'.
   * Optional attribute 'data-action' value is returned in {@link ModalResult.action}
   * @param {(string|Node|NodeList|Array.<Node>)} content
   */
  constructor(content) {
    const modal = document.createElement('div');
    modal.setAttribute('id', 'modal');
    const modalHeader = document.createElement('div');
    modalHeader.setAttribute('id', 'modal-header');
    const buttonClose = document.createElement('button');
    buttonClose.setAttribute('id', 'modal-close');
    buttonClose.setAttribute('type', 'button');
    buttonClose.setAttribute('class', 'button-reject');
    const img = document.createElement('img');
    img.setAttribute('src', 'images/close.svg');
    img.setAttribute('alt', lang.strings['close']);
    buttonClose.append(img);
    modalHeader.append(buttonClose);
    modal.append(modalHeader);
    const modalBody = document.createElement('div');
    modalBody.setAttribute('id', 'modal-body');
    if (typeof content === 'string') {
      modalBody.innerHTML = content;
    } else if (content instanceof NodeList || content instanceof Array) {
      for (const node of content) {
        modalBody.append(node);
      }
    } else {
      modalBody.append(content);
    }
    modal.append(modalBody);
    this._modal = modal;
    this.visible = false;
  }

  /**
   * @return {HTMLDivElement}
   */
  get modal() {
    return this._modal;
  }

  /**
   * Show modal dialog
   * @returns {Promise<ModalResult>}
   */
  show() {
    return new Promise((resolve) => {
      this.addListeners(resolve);
      if (!this.visible) {
        document.body.append(this._modal);
      }
    });
  }

  /**
   * Add listeners
   * @param {ModalCallback} resolve callback
   */
  addListeners(resolve) {
    this._modal.querySelectorAll('.button-resolve').forEach((el) => {
      el.addEventListener('click', () => {
        uModal.onClick(el, resolve, { cancelled: false, action: el.getAttribute('data-action') });
      });
    });
    this._modal.querySelectorAll('.button-reject').forEach((el) => {
      el.addEventListener('click', () => {
        uModal.onClick(el, resolve, { cancelled: true });
      });
    });
  }

  /**
   * On click action
   * Handles optional confirmation dialog
   * @param {Element} el Clicked element
   * @param {ModalCallback} resolve callback
   * @param {ModalResult} result
   */
  static onClick(el, resolve, result) {
    const confirm = el.getAttribute('data-confirm');
    let proceed = true;
    if (confirm) {
      proceed = this.isConfirmed(confirm);
    }
    if (proceed) {
      resolve(result);
    }
  }

  /**
   * Show confirmation dialog and return user decision
   * @param {string} message
   * @return {boolean} True if confirmed, false otherwise
   */
  static isConfirmed(message) {
    return confirm(message);
  }

  /**
   * Remove modal dialog
   */
  hide() {
    document.body.removeChild(this._modal);
    this.visible = false
  }
}
