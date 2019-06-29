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

import { auth, config } from './constants.js';
import TrackList from './tracklist.js';
import UserList from './userlist.js';
import uBinder from './binder.js';
import uChart from './chart.js';
import uEvent from './event.js';
import uMap from './map.js';
import uUI from './ui.js';


export const uLogger = {
  /** @type {?UserList} */
  userList: null,
  /** @type {?TrackList} */
  trackList: null
};

const binder = new uBinder();
binder.addEventListener(uEvent.PASSWORD, auth);
config.binder = binder;

new uMap(binder);
new uChart(binder);
new uUI(binder);

document.addEventListener('DOMContentLoaded', () => {
  uLogger.userList = new UserList('#user', binder);
  uLogger.trackList = new TrackList('#track', binder);
});
