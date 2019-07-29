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

import { config, lang } from './constants.js';
import uEvent from './event.js';
import { uLogger } from './ulogger.js';
import uUtils from './utils.js';

export default class uUI {

  /**
   * @param {uBinder} binder
   */
  constructor(binder) {
    this._binder = binder;
    binder.addEventListener(uEvent.CONFIG, this);
    binder.addEventListener(uEvent.CHART_READY, this);
    binder.addEventListener(uEvent.OPEN_URL, this);
    binder.addEventListener(uEvent.LOADER, this);
    binder.addEventListener(uEvent.TRACK_READY, this);
    document.addEventListener('DOMContentLoaded', () => { this.initUI(); });
    this.isLiveOn = false;
  }

  /**
   * Initialize uUI elements
   */
  initUI() {
    /** @type {HTMLElement} */
    this.menu = document.getElementById('menu');
    /** @type {?HTMLElement} */
    this.userMenu = document.getElementById('user-menu');
    /** @type {?HTMLElement} */
    this.userDropdown = document.getElementById('user-dropdown');
    /** @type {?HTMLElement} */
    this.userPass = document.getElementById('user-pass');
    // noinspection JSValidateTypes
    /** @type {?HTMLSelectElement} */
    this.userSelect = function () {
      const list = document.getElementsByName('user');
      if (list.length) { return list[0]; }
      return null;
    }();
    // noinspection JSValidateTypes
    /** @type {HTMLSelectElement} */
    this.trackSelect = document.getElementsByName('track')[0];
    // noinspection JSValidateTypes
    /** @type {HTMLSelectElement} */
    this.apiSelect = document.getElementsByName('api')[0];
    // noinspection JSValidateTypes
    /** @type {HTMLSelectElement} */
    this.langSelect = document.getElementsByName('lang')[0];
    // noinspection JSValidateTypes
    /** @type {HTMLSelectElement} */
    this.unitsSelect = document.getElementsByName('units')[0];
    /** @type {HTMLElement} */
    this.chart = document.getElementById('chart');
    /** @type {HTMLElement} */
    this.chartClose = document.getElementById('chart-close');
    /** @type {HTMLElement} */
    this.bottom = document.getElementById('bottom');
    /** @type {HTMLElement} */
    this.chartLink = document.getElementById('altitudes');
    /** @type {HTMLElement} */
    this.main = document.getElementById('main');
    /** @type {HTMLElement} */
    this.menuClose = document.getElementById('menu-close');
    /** @type {HTMLElement} */
    this.track = document.getElementById('track');
    /** @type {HTMLElement} */
    this.trackTitle = document.querySelector('label[for="track"]');
    /** @type {HTMLElement} */
    this.importTitle = document.getElementById('import') || null;
    /** @type {HTMLElement} */
    this.summary = document.getElementById('summary');
    /** @type {HTMLElement} */
    this.latest = document.getElementById('latest');
    /** @type {HTMLElement} */
    this.autoReload = document.getElementById('auto-reload');
    /** @type {HTMLElement} */
    this.forceReload = document.getElementById('force-reload');
    /** @type {HTMLElement} */
    this.interval = document.getElementById('interval');
    /** @type {HTMLElement} */
    this.setInterval = document.getElementById('set-interval');
    /** @type {HTMLElement} */
    this.exportKml = document.getElementById('export-kml');
    /** @type {HTMLElement} */
    this.exportGpx = document.getElementById('export-gpx');
    /** @type {?HTMLElement} */
    this.inputFile = document.getElementById('input-file');
    /** @type {HTMLElement} */
    this.importGpx = document.getElementById('import-gpx');
    /** @type {?HTMLElement} */
    this.addUser = document.getElementById('adduser');
    /** @type {?HTMLElement} */
    this.editUser = document.getElementById('edituser');
    /** @type {?HTMLElement} */
    this.editTrack = document.getElementById('edittrack');
    /** @type {HTMLElement} */
    this.map = document.getElementById('map-canvas');
    /** @type {HTMLElement} */
    this.head = document.getElementsByTagName('head')[0];

    if (this.userMenu) {
      this.userMenu.onclick = () => this.showUserMenu();
    }
    if (this.userPass) {
      this.userPass.onclick = () => {
        this.emit(uEvent.PASSWORD);
      }
    }
    this.hideUserMenu = this.hideUserMenu.bind(this);
    this.latest.onchange = () => uUI.toggleLatest();
    this.autoReload.onchange = () => this.toggleAutoReload();
    this.setInterval.onclick = () => this.setAutoReloadTime();
    this.forceReload.onclick = () => this.trackReload();
    this.chartLink.onclick = () => this.toggleChart();
    this.trackSelect.onchange = () => {
      const trackId = this.trackSelect.options[this.trackSelect.selectedIndex].value;
      this.emit(uEvent.CHANGE, { el: this.trackSelect, id: trackId });
    };
    this.userSelect.onchange = () => {
      const userId = this.userSelect.options[this.userSelect.selectedIndex].value;
      this.emit(uEvent.CHANGE, { el: this.userSelect, id: userId });
    };
    this.apiSelect.onchange = () => {
      const api = this.apiSelect.options[this.apiSelect.selectedIndex].value;
      this.emit(uEvent.API_CHANGE, api);
    };
    this.langSelect.onchange = () => {
      uUI.setLang(this.langSelect.options[this.langSelect.selectedIndex].value);
    };
    this.unitsSelect.onchange = () => {
      uUI.setUnits(this.unitsSelect.options[this.unitsSelect.selectedIndex].value);
    };
    this.exportKml.onclick = () => {
      this.emit(uEvent.EXPORT, 'kml');
    };
    this.exportGpx.onclick = () => {
      this.emit(uEvent.EXPORT, 'gpx');
    };
    if (this.inputFile) {
      this.inputFile.onchange = () => {
        const form = this.inputFile.parentElement;
        const sizeMax = form.elements['MAX_FILE_SIZE'].value;
        if (this.inputFile.files && this.inputFile.files.length === 1 && this.inputFile.files[0].size > sizeMax) {
          alert(uUtils.sprintf(lang.strings['isizefailure'], sizeMax));
          return;
        }
        this.emit(uEvent.IMPORT, form);
      };
      this.importGpx.onclick = () => {
        this.inputFile.click();
      };
    }
    if (this.addUser) {
      this.addUser.onclick = () => {
        this.emit(uEvent.ADD, this.userSelect);
      }
    }
    if (this.editUser) {
      this.editUser.onclick = () => {
        this.emit(uEvent.EDIT, this.userSelect);
      }
    }
    if (this.editTrack) {
      this.editTrack.onclick = () => {
        this.emit(uEvent.EDIT, this.trackSelect);
      }
    }
    this.menuClose.onclick = () => this.toggleSideMenu();
    this.chartClose.onclick = () => this.hideChart();
    this.emit(uEvent.UI_READY);
  }

  trackReload() {
    uUI.emitDom(this.trackSelect, 'change');
  }

  userReload() {
    uUI.emitDom(this.userSelect, 'change');
  }

  /**
   * Toggle auto-reload
   */
  toggleAutoReload() {
    if (this.isLiveOn) {
      this.stopAutoReload();
    } else {
      this.startAutoReload();
    }
  }

  startAutoReload() {
    this.isLiveOn = true;
    this.liveInterval = setInterval(() => {
      this.trackReload();
    }, config.interval * 1000);
  }

  stopAutoReload() {
    this.isLiveOn = false;
    clearInterval(this.liveInterval);
  }

  /**
   * Set new interval from user dialog
   */
  setAutoReloadTime() {
    const i = parseInt(prompt(lang.strings['newinterval']));
    if (!isNaN(i) && i !== config.interval) {
      config.interval = i;
      this.interval.innerHTML = config.interval.toString();
      // if live tracking on, reload with new interval
      if (this.isLiveOn) {
        this.stopAutoReload();
        this.startAutoReload();
      }
      // save current state as default
      uUtils.setCookie('interval', config.interval, 30);
    }
  }

  /**
   * Toggle side menu
   */
  toggleSideMenu() {
    if (this.menuClose.innerHTML === '»') {
      this.menu.style.width = '0';
      this.main.style.marginRight = '0';
      this.menuClose.style.right = '0';
      this.menuClose.innerHTML = '«';
    } else {
      this.menu.style.width = '165px';
      this.main.style.marginRight = '165px';
      this.menuClose.style.right = '165px';
      this.menuClose.innerHTML = '»';
    }
    uUI.emitDom(window, 'resize');
  }

  /**
   * Dispatch event at specified target
   * @param {(Element|Document|Window)} el Target element
   * @param {string} event Event name
   */
  static emitDom(el, event) {
    el.dispatchEvent(new Event(event));
  }

  /**
   * Dispatch event
   * @param {string} type
   * @param {*=} args Defaults to this
   */
  emit(type, args) {
    const data = args || this;
    this._binder.dispatchEvent(type, data);
  }

  /**
   * Is chart visible
   * @returns {boolean}
   */
  isChartVisible() {
    return this.bottom.style.display === 'block';
  }

  /**
   * Show chart
   */
  showChart() {
    this.bottom.style.display = 'block';
  }

  /**
   * Hide chart
   */
  hideChart() {
    this.bottom.style.display = 'none';
  }

  /**
   * Toggle chart visibility
   */
  toggleChart() {
    if (this.isChartVisible()) {
      this.hideChart();
    } else {
      this.showChart();
    }
  }

  /**
   * Animate element text
   * @param {HTMLElement} el
   */
  static setLoader(el) {
    const str = el.textContent;
    el.innerHTML = '';
    for (const c of str) {
      el.innerHTML += `<span class="loader">${c}</span>`;
    }
  }

  /**
   * Stop animation
   * @param {HTMLElement} el
   */
  static removeLoader(el) {
    el.innerHTML = el.textContent;
  }

  /**
   * Get popup html
   * @param {number} id Position ID
   * @returns {string}
   */
  static getPopupHtml(id) {
    const pos = uLogger.trackList.current.positions[id];
    const count = uLogger.trackList.current.positions.length;
    let date = '–––';
    let time = '–––';
    if (pos.timestamp > 0) {
      const parts = uUtils.getTimeString(new Date(pos.timestamp * 1000));
      date = parts.date;
      time = `${parts.time}<span class="smaller">${parts.zone}</span>`;
    }
    let provider = '';
    if (pos.provider === 'gps') {
      provider = ` (<img class="icon" alt="${lang.strings['gps']}" title="${lang.strings['gps']}"  src="images/gps_dark.svg">)`;
    } else if (pos.provider === 'network') {
      provider = ` (<img class="icon" alt="${lang.strings['network']}" title="${lang.strings['network']}"  src="images/network_dark.svg">)`;
    }
    let stats = '';
    if (!config.showLatest) {
      stats =
        `<div id="pright">
        <img class="icon" alt="${lang.strings['track']}" src="images/stats_blue.svg" style="padding-left: 3em;"><br>
        <img class="icon" alt="${lang.strings['ttime']}" title="${lang.strings['ttime']}" src="images/time_blue.svg"> ${pos.totalSeconds.toHMS()}<br>
        <img class="icon" alt="${lang.strings['aspeed']}" title="${lang.strings['aspeed']}" src="images/speed_blue.svg"> ${(pos.totalSeconds > 0) ? ((pos.totalDistance / pos.totalSeconds).toKmH() * config.factor_kmh).toFixed() : 0} ${config.unit_kmh}<br>
        <img class="icon" alt="${lang.strings['tdistance']}" title="${lang.strings['tdistance']}" src="images/distance_blue.svg"> ${(pos.totalDistance.toKm() * config.factor_km).toFixed(2)} ${config.unit_km}<br>
        </div>`;
    }
    return `<div id="popup">
        <div id="pheader">
        <div><img alt="${lang.strings['user']}" title="${lang.strings['user']}" src="images/user_dark.svg"> ${uUtils.htmlEncode(pos.username)}</div>
        <div><img alt="${lang.strings['track']}" title="${lang.strings['track']}" src="images/route_dark.svg"> ${uUtils.htmlEncode(pos.trackname)}</div>
        </div>
        <div id="pbody">
        ${(pos.hasComment()) ? `<div id="pcomments">${uUtils.htmlEncode(pos.comment)}</div>` : ''}
        ${(pos.hasImage()) ? `<div id="pimage"><img src="uploads/${pos.image}" alt="image"></div>` : ''}
        <div id="pleft">
        <img class="icon" alt="${lang.strings['time']}" title="${lang.strings['time']}" src="images/calendar_dark.svg"> ${date}<br>
        <img class="icon" alt="${lang.strings['time']}" title="${lang.strings['time']}" src="images/clock_dark.svg"> ${time}<br>
        ${(pos.speed != null) ? `<img class="icon" alt="${lang.strings['speed']}" title="${lang.strings['speed']}" src="images/speed_dark.svg">${pos.speed.toKmH() * config.factor_kmh} ${config.unit_kmh}<br>` : ''}
        ${(pos.altitude != null) ? `<img class="icon" alt="${lang.strings['altitude']}" title="${lang.strings['altitude']}" src="images/altitude_dark.svg">${(pos.altitude * config.factor_m).toFixed()} ${config.unit_m}<br>` : ''}
        ${(pos.accuracy != null) ? `<img class="icon" alt="${lang.strings['accuracy']}" title="${lang.strings['accuracy']}" src="images/accuracy_dark.svg">${(pos.accuracy * config.factor_m).toFixed()} ${config.unit_m}${provider}<br>` : ''}
        </div>${stats}</div>
        <div id="pfooter">${uUtils.sprintf(lang.strings['pointof'], id + 1, count)}</div>
        </div>`;
  }

  /**
   * Update track summary
   * @param {number} timestamp
   * @param {number=} totalDistance Total distance (m)
   * @param {number=} totalTime Total time (s)
   */
  updateSummary(timestamp, totalDistance, totalTime) {
    if (config.showLatest) {
      const today = new Date();
      const date = new Date(timestamp * 1000);
      let dateString = '';
      if (date.toDateString() !== today.toDateString()) {
        dateString = `${date.getFullYear()}-${(`0${date.getMonth() + 1}`).slice(-2)}-${(`0${date.getDate()}`).slice(-2)}<br>`;
      }
      let timeString = date.toTimeString();
      let offset;
      if ((offset = timeString.indexOf(' ')) >= 0) {
        timeString = `${timeString.substr(0, offset)} <span style="font-weight:normal">${timeString.substr(offset + 1)}</span>`;
      }
      this.summary.innerHTML = `
        <div class="menu-title">${lang.strings['latest']}:</div>
        ${dateString}
        ${timeString}`;
    } else {
      this.summary.innerHTML = `
        <div class="menu-title">${lang.strings['summary']}</div>
        <div><img class="icon" alt="${lang.strings['tdistance']}" title="${lang.strings['tdistance']}" src="images/distance.svg"> ${(totalDistance.toKm() * config.factor_km).toFixed(2)} ${config.unit_km}</div>
        <div><img class="icon" alt="${lang.strings['ttime']}" title="${lang.strings['ttime']}" src="images/time.svg"> ${totalTime.toHMS()}</div>`;
    }
  }

  /**
   * Clear map canvas
   */
  clearMapCanvas() {
    this.map.innerHTML = '';
  }

  /**
   * Toggle user menu visibility
   */
  showUserMenu() {
    if (this.userDropdown.style.display === 'block') {
      this.userDropdown.style.display = 'none';
    } else {
      this.userDropdown.style.display = 'block';
      window.addEventListener('click', this.hideUserMenu, true);
    }
  }

  /**
   * Click listener callback to hide user menu
   * @param {MouseEvent} e
   */
  hideUserMenu(e) {
    const parent = e.target.parentElement;
    this.userDropdown.style.display = 'none';
    window.removeEventListener('click', this.hideUserMenu, true);
    if (!parent.classList.contains('dropdown')) {
      e.stopPropagation();
    }
  }

  /**
   * Remove HTML element
   * @param {string} id Element ID
   */
  static removeElementById(id) {
    const tag = document.getElementById(id);
    if (tag && tag.parentNode) {
      tag.parentNode.removeChild(tag);
    }
  }

  /**
   *
   * @param {(Event|uEvent)} event
   * @param {*=} args
   */
  handleEvent(event, args) {
    if (event.type === uEvent.CHART_READY) {
      // toggle chart link
      const hasPoints = args > 0;
      if (hasPoints) {
        this.chartLink.style.visibility = 'visible';
      } else {
        this.chartLink.style.visibility = 'hidden';
      }
    } else if (event.type === uEvent.TRACK_READY) {
      /** @type {uTrack} */
      const track = args;
      if (track.hasPositions) {
        const position = track.positions[track.positions.length - 1];
        this.updateSummary(position.timestamp, position.totalDistance, position.totalSeconds);
      }
    } else if (event.type === uEvent.OPEN_URL) {
      window.location.assign(args);
    } else if (event.type === uEvent.CONFIG) {
      if (args === 'showLatest') {
        this.latest.checked = config.showLatest;
      }
    } else if (event.type === uEvent.LOADER) {
      const el = args.action === 'track' ? this.trackTitle : this.importTitle;
      if (args.on) {
        uUI.setLoader(el);
      } else {
        uUI.removeLoader(el);
      }
    }
  }

  /**
   * Set language
   * @param {string} languageCode Language code
   */
  static setLang(languageCode) {
    uUtils.setCookie('lang', languageCode, 30);
    uUI.reload();
  }

  /**
   * Set units
   * @param {string} unitCode New units
   */
  static setUnits(unitCode) {
    uUtils.setCookie('units', unitCode, 30);
    uUI.reload();
  }

  static reload() {
    window.location.reload();
  }

  static toggleLatest() {
    config.showLatest = !config.showLatest;
  }
}
