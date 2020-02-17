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

const baseUrl = '/base/test/fixtures/';

export default class Fixture {

  static load(url) {
    return this.get(url).then((fixture) => {
      document.body.insertAdjacentHTML('afterbegin', fixture);
    });
  }

  static clear() {
    document.body.innerHTML = '';
  }

  /**
   * @param {string} url
   * @return {Promise<string, Error>}
   */
  static get(url) {
    url = baseUrl + url;
    const xhr = new XMLHttpRequest();
    return new Promise((resolve, reject) => {
      xhr.onreadystatechange = () => {
        if (xhr.readyState === XMLHttpRequest.DONE) {
          if (xhr.status === 200) {
            resolve(xhr.responseText);
          } else {
            reject(new Error(`HTTP error ${xhr.status}`));
          }
        }
      };
      xhr.open('GET', url, true);
      xhr.send();
    });
  }
}
