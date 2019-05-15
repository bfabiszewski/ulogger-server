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
