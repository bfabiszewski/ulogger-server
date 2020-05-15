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


import uAlert from './alert.js';

export default class uSpinner {

  constructor(state) {
    this.spinner = null;
    this.state = state;
  }

  init() {
    this.state.onChanged('activeJobs', (jobs) => {
      if (jobs > 0) {
        if (!this.spinner) {
          this.spinner = uAlert.spinner();
        }
      } else if (this.spinner) {
        this.spinner.destroy();
        this.spinner = null;
      }
    });
  }
}
