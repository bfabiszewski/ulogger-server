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

import { lang as $ } from './initializer.js';

export default class uDialog {

  /**
   * Builds modal dialog
   * @param {(string|Node|NodeList|Array.<Node>)} content
   */
  constructor(content) {
    const dialog = document.createElement('div');
    dialog.setAttribute('id', 'modal');
    const dialogHeader = document.createElement('div');
    dialogHeader.setAttribute('id', 'modal-header');
    const buttonClose = document.createElement('button');
    buttonClose.setAttribute('id', 'modal-close');
    buttonClose.setAttribute('type', 'button');
    buttonClose.setAttribute('class', 'button-reject');
    buttonClose.setAttribute('data-bind', 'onCancel');
    const img = document.createElement('img');
    img.setAttribute('src', 'images/close.svg');
    img.setAttribute('alt', $._('close'));
    buttonClose.append(img);
    dialogHeader.append(buttonClose);
    const dialogBody = document.createElement('div');
    dialogBody.setAttribute('id', 'modal-body');
    if (typeof content === 'string') {
      dialogBody.innerHTML = content;
    } else if (content instanceof NodeList || content instanceof Array) {
      for (const node of content) {
        dialogBody.append(node);
      }
    } else {
      dialogBody.append(content);
    }
    dialogBody.prepend(dialogHeader);
    dialog.append(dialogBody);
    this.element = dialog;
    this.visible = false;
  }

  /**
   * Show modal dialog
   */
  show() {
    if (!this.visible) {
      document.body.append(this.element);
      this.visible = true;
    }
  }

  /**
   * Remove modal dialog
   */
  destroy() {
    document.body.removeChild(this.element);
    this.visible = false
  }

  /**
   * Show confirmation dialog and return user decision
   * @param {string} message
   * @return {boolean} True if confirmed, false otherwise
   */
  static isConfirmed(message) {
    return confirm(message);
  }
}
