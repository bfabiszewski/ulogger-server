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

import { lang as $, auth, config } from './initializer.js';
import TrackDialogModel from './trackdialogmodel.js';
import ViewModel from './viewmodel.js';
import uObserve from './observe.js';
import uPositionSet from './positionset.js';
import uSelect from './select.js';
import uTrack from './track.js';
import uUtils from './utils.js';

/**
 * @class TrackViewModel
 */
export default class TrackViewModel extends ViewModel {

  /**
   * @param {uState} state
   */
  constructor(state) {
    super({
      /** @type {uTrack[]} */
      trackList: [],
      /** @type {string} */
      currentTrackId: '',
      /** @type {boolean} */
      showLatest: false,
      /** @type {boolean} */
      autoReload: false,
      /** @type {string} */
      inputFile: false,
      /** @type {string} */
      summary: false,
      // click handlers
      /** @type {function} */
      onReload: null,
      /** @type {function} */
      onExportGpx: null,
      /** @type {function} */
      onExportKml: null,
      /** @type {function} */
      onImportGpx: null,
      /** @type {function} */
      onTrackEdit: null
    });
    this.setClickHandlers();
    /** @type HTMLSelectElement */
    const listEl = document.querySelector('#track');
    this.importEl = document.querySelector('#input-file');
    this.editEl = this.getBoundElement('onTrackEdit');
    this.select = new uSelect(listEl);
    this.state = state;
    this.timerId = 0;
  }

  /**
   * @return {TrackViewModel}
   */
  init() {
    this.setObservers();
    this.bindAll();
    return this;
  }

  setObservers() {
    this.onChanged('trackList', (list) => { this.select.setOptions(list); });
    this.onChanged('currentTrackId', (listValue) => {
      this.onTrackSelect(listValue);
    });
    this.onChanged('inputFile', (file) => {
      if (file) { this.onImport(); }
    });
    this.onChanged('autoReload', (reload) => {
      this.autoReload(reload);
    });
    this.onChanged('showLatest', (showLatest) => {
      this.state.showLatest = showLatest;
      this.onReload(true);
    });
    this.state.onChanged('currentUser', (user) => {
      if (user) {
        this.loadTrackList();
        TrackViewModel.setMenuVisible(this.editEl, true);
      } else {
        this.model.currentTrackId = '';
        this.model.trackList = [];
        TrackViewModel.setMenuVisible(this.editEl, false);
      }
    });
    this.state.onChanged('currentTrack', (track) => {
      this.renderSummary();
      if (track) {
        uObserve.observe(track, 'positions', () => {
          this.renderSummary();
        });
      }
    });
    this.state.onChanged('showAllUsers', (showAll) => {
      if (showAll) {
        this.loadAllUsersPosition();
      }
    });
    config.onChanged('interval', () => {
      if (this.timerId) {
        this.stopAutoReload();
        this.startAutoReload();
      }
    });
  }

  setClickHandlers() {
    this.model.onReload = () => this.onReload();
    const exportCb = (type) => () => {
      if (this.state.currentTrack) {
        this.state.currentTrack.export(type);
      }
    };
    this.model.onExportGpx = exportCb('gpx');
    this.model.onExportKml = exportCb('kml');
    this.model.onImportGpx = () => this.importEl.click();
    this.model.onTrackEdit = () => this.showDialog();
  }

  /**
   * Reload or update track view
   * @param {boolean} clear Reload if true, update current track otherwise
   */
  onReload(clear = false) {
    if (this.state.showLatest) {
      if (this.state.showAllUsers) {
        this.loadAllUsersPosition();
      } else if (this.state.currentUser) {
        this.onUserLastPosition();
      }
    } else if (this.state.currentTrack instanceof uTrack) {
      this.onTrackUpdate(clear);
    } else if (this.state.currentTrack instanceof uPositionSet) {
      this.state.currentTrack = null;
    } else if (this.state.currentUser) {
      this.loadTrackList();
    }
  }

  /**
   * Handle import
   */
  onImport() {
    const form = this.importEl.parentElement;
    const sizeMax = form.elements['MAX_FILE_SIZE'].value;
    if (this.importEl.files && this.importEl.files.length === 1 && this.importEl.files[0].size > sizeMax) {
      uUtils.error($._('isizefailure', sizeMax));
      return;
    }
    if (!auth.isAuthenticated) {
      uUtils.error($._('notauthorized'));
      return;
    }
    uTrack.import(form, auth.user)
      .then((trackList) => {
        if (trackList.length) {
          if (trackList.length > 1) {
            alert($._('imultiple', trackList.length));
          }
          this.model.trackList = trackList.concat(this.model.trackList);
          this.model.currentTrackId = trackList[0].listValue;
        }
      })
      .catch((e) => uUtils.error(e, `${$._('actionfailure')}\n${e.message}`))
      .finally(() => {
        this.model.inputFile = '';
      });
  }

  /**
   * Handle track change
   * @param {string} listValue Track list selected option
   */
  onTrackSelect(listValue) {
    /** @type {(uTrack|undefined)} */
    const track = this.model.trackList.find((_track) => _track.listValue === listValue);
    if (!track) {
      this.state.currentTrack = null;
    } else if (!track.isEqualTo(this.state.currentTrack)) {
      track.fetchPositions().then(() => {
        console.log(`currentTrack id: ${track.id}, loaded ${track.length} positions`);
        this.state.currentTrack = track;
        if (this.model.showLatest) {
          this.model.showLatest = false;
        }
      })
        .catch((e) => { uUtils.error(e, `${$._('actionfailure')}\n${e.message}`); });
    }
  }

  /**
   * Handle track update
   * @param {boolean=} clear
   */
  onTrackUpdate(clear) {
    if (clear) {
      this.state.currentTrack.clear();
    }
    this.state.currentTrack.fetchPositions()
      .catch((e) => { uUtils.error(e, `${$._('actionfailure')}\n${e.message}`); });
  }

  /**
   * Handle user last position request
   */
  onUserLastPosition() {
    this.state.currentUser.fetchLastPosition()
      .then((_track) => {
        if (_track) {
          if (!this.model.trackList.find((listItem) => listItem.listValue === _track.listValue)) {
            this.model.trackList.unshift(_track);
          }
          this.state.currentTrack = _track;
          this.model.currentTrackId = _track.listValue;
        }
      })
      .catch((e) => { uUtils.error(e, `${$._('actionfailure')}\n${e.message}`); });
  }

  /**
   * Handle last position of all users request
   */
  loadAllUsersPosition() {
    uPositionSet.fetchLatest()
      .then((_track) => {
        if (_track) {
          this.model.trackList = [];
          this.model.currentTrackId = '';
          this.state.currentTrack = _track;
        }
      })
      .catch((e) => { uUtils.error(e, `${$._('actionfailure')}\n${e.message}`); });
  }

  loadTrackList() {
    uTrack.fetchList(this.state.currentUser)
      .then((_tracks) => {
        this.model.trackList = _tracks;
        if (_tracks.length) {
          if (this.state.showLatest) {
            this.onUserLastPosition();
          } else {
            // autoload first track in list
            this.model.currentTrackId = _tracks[0].listValue;
          }
        } else {
          this.model.currentTrackId = '';
        }
      })
      .catch((e) => { uUtils.error(e, `${$._('actionfailure')}\n${e.message}`); });
  }

  showDialog() {
    const vm = new TrackDialogModel(this);
    vm.init();
  }

  onTrackDeleted() {
    const index = this.model.trackList.indexOf(this.state.currentTrack);
    this.state.currentTrack = null;
    if (index !== -1) {
      this.model.trackList.splice(index, 1);
      if (this.model.trackList.length) {
        this.model.currentTrackId = this.model.trackList[index].listValue;
      } else {
        this.model.currentTrackId = '';
      }
    }
  }

  /**
   * @param {boolean} start
   */
  autoReload(start) {
    if (start) {
      this.startAutoReload();
    } else {
      this.stopAutoReload();
    }
  }

  startAutoReload() {
    this.timerId = setInterval(() => this.onReload(), config.interval * 1000);
  }

  stopAutoReload() {
    clearInterval(this.timerId);
    this.timerId = 0;
    this.model.autoReload = false;
  }

  /**
   * @param {HTMLElement} el
   * @param {boolean} visible
   */
  static setMenuVisible(el, visible) {
    if (el) {
      if (visible) {
        el.classList.remove('menu-hidden');
      } else {
        el.classList.add('menu-hidden');
      }
    }
  }

  renderSummary() {
    if (!this.state.currentTrack || !this.state.currentTrack.hasPositions) {
      this.model.summary = '';
      return;
    }
    const last = this.state.currentTrack.positions[this.state.currentTrack.length - 1];

    if (this.state.showLatest) {
      const today = new Date();
      const date = new Date(last.timestamp * 1000);
      const dateTime = uUtils.getTimeString(date);
      const dateString = (date.toDateString() !== today.toDateString()) ? `${dateTime.date}<br>` : '';
      const timeString = `${dateTime.time}<span style="font-weight:normal">${dateTime.zone}</span>`;
      this.model.summary = `
        <div class="menu-title">${$._('latest')}:</div>
        ${dateString}
        ${timeString}`;
    } else {
      this.model.summary = `
        <div class="menu-title">${$._('summary')}</div>
        <div><img class="icon" alt="${$._('tdistance')}" title="${$._('tdistance')}" src="images/distance.svg"> ${$.getLocaleDistanceMajor(last.totalMeters, true)}</div>
        <div><img class="icon" alt="${$._('ttime')}" title="${$._('ttime')}" src="images/time.svg"> ${$.getLocaleDuration(last.totalSeconds)}</div>`;
    }
  }

}
