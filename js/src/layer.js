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

import uListItem from './listitem.js';

export default class uLayer extends uListItem {

  /**
   * @param {number} id
   * @param {string} name
   * @param {string} url
   * @param {number} priority
   */
  // eslint-disable-next-line max-params
  constructor(id, name, url, priority) {
    super();
    this.id = id;
    this.name = name;
    this.url = url;
    this.priority = priority;
    this.listItem(id, name);
  }

  /**
   * @param {string} name
   */
  setName(name) {
    this.name = name;
    this.listItem(this.id, this.name);
  }

  /**
   * @param {string} url
   */
  setUrl(url) {
    this.url = url;
  }

}
