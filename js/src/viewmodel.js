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

import uObserve from './observe.js';

/**
 * @class ViewModel
 * @property {Object} model
 */
export default class ViewModel {

  /**
   * @param {Object} model
   */
  constructor(model) {
    this._model = model;
    this.root = document;
  }

  /**
   * @return {Object}
   */
  get model() {
    return this._model;
  }

  /**
   * Apply bindings for model properties
   * @param {HTMLElement=} root Root element
   */
  bindAll(root = document) {
    this.root = root;
    for (const key in this._model) {
      if (this._model.hasOwnProperty(key)) {
        this.bind(key);
      }
    }
  }

  /**
   * Creates bidirectional binding between model property and DOM element.
   * For input elements model property value change triggers change in DOM element and vice versa.
   * In case of anchor or button element binding is one way. Model property is callback that will receive click event.
   * @param {string} key
   */
  bind(key) {
    const dataProp = 'bind';
    const observers = this.root.querySelectorAll(`[data-${dataProp}]`);
    observers.forEach(/** @param {HTMLElement} element */ (element) => {
      const name = element.dataset[dataProp];
      if (name === key) {
        if (element instanceof HTMLInputElement ||
          element instanceof HTMLSelectElement ||
          element instanceof HTMLTextAreaElement) {
          this.onChangeBind(element, key);
        } else if (element instanceof HTMLAnchorElement ||
          element instanceof HTMLButtonElement) {
          this.onClickBind(element, key);
        } else {
          this.viewUpdateBind(element, key);
        }
      }
    });
  }

  /**
   * One way bind: view element click event to view model event handler
   * @param {HTMLAnchorElement} element
   * @param {string} key
   */
  onClickBind(element, key) {
    element.addEventListener('click', (event) => {
      if (typeof this._model[key] !== 'function') {
        throw new Error(`Property ${key} is not a callback`);
      }
      this._model[key](event);
      event.preventDefault();
    });
  }

  /**
   * Two way bind: view element change event to view model property
   * @param {(HTMLInputElement|HTMLSelectElement)} element
   * @param {string} key
   */
  onChangeBind(element, key) {
    let prop = 'value';
    let getVal = (val) => val;
    if (element.type === 'checkbox') {
      prop = 'checked';
      getVal = (val) => !!val;
    }
    element.addEventListener('change', () => {
      this._model[key] = element[prop];
    });
    uObserve.observe(this.model, key, (val) => {
      val = getVal(val);
      if (element[prop] !== val) {
        element[prop] = val;
      }
    });
  }

  /**
   * One way bind: view model property to view element content
   * @param {HTMLElement} element
   * @param {string} key
   */
  viewUpdateBind(element, key) {
    uObserve.observe(this.model, key, (content) => {
      if (element.innerHTML !== content) {
        element.innerHTML = content;
      }
    });
  }

  /**
   * @param {string} property
   * @param {ObserveCallback} callback
   */
  onChanged(property, callback) {
    uObserve.observe(this.model, property, callback);
  }

  /**
   * @param {string} property
   * @param {ObserveCallback} callback
   */
  unsubscribe(property, callback) {
    uObserve.unobserve(this.model, property, callback);
  }

  getBoundElement(property) {
    return this.root.querySelector(`[data-bind='${property}']`);
  }
}
