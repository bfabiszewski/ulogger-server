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

import uLayer from './layer.js';

export default class uLayerCollection extends Array {

  /**
   * Create new layer in layers array
   * @param {string} name
   * @param {string} url
   * @param {number} priority
   */
  // eslint-disable-next-line max-params
  addNewLayer(name, url, priority = 0) {
    this.addLayer(this.getMaxId() + 1, name, url, priority);
  }

  /**
   * @param {number} id
   * @param {string} name
   * @param {string} url
   * @param {number} priority
   */
  // eslint-disable-next-line max-params
  addLayer(id, name, url, priority = 0) {
    this.push(new uLayer(id, name, url, priority));
  }

  /**
   * @param {number} id
   */
  delete(id) {
    const index = this.map((o) => o.id).indexOf(id);
    this.splice(index, 1);
  }

  /**
   * @param {number|string} id Id or listValue
   */
  get(id) {
    if (typeof id === 'string') {
      return this.find((o) => o.listValue === id);
    }
    return this.find((o) => o.id === id);
  }

  /**
   * Return max id from layers array
   * @return {number}
   */
  getMaxId() {
    return Math.max(...this.map((o) => o.id), 0);
  }

  /**
   * @param {number} id
   */
  setPriorityLayer(id) {
    for (const layer of this) {
      if (layer.id > 0 && layer.id === id) {
        layer.priority = 1;
      } else {
        layer.priority = 0;
      }
    }
  }

  /**
   * Return id of first layer with priority
   * @return {number}
   */
  getPriorityLayer() {
    for (const layer of this) {
      if (layer.priority > 0) {
        return layer.id;
      }
    }
    return 0;
  }

  /**
   * Load from array
   * @param {Array} layers
   */
  load(layers) {
    this.length = 0;
    for (const layer of layers) {
      if (layer.id > 0) {
        this.addLayer(layer.id, layer.name, layer.url, layer.priority);
      }
    }
  }

}

