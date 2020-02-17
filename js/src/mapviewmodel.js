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
import GoogleMapsApi from './mapapi/api_gmaps.js';
import OpenLayersApi from './mapapi/api_openlayers.js';
import PositionDialogModel from './positiondialogmodel.js';
import ViewModel from './viewmodel.js';
import uDialog from './dialog.js';
import uObserve from './observe.js';
import uUtils from './utils.js';

/**
 * @typedef {Object} MapViewModel.api
 * @interface
 * @memberOf MapViewModel
 * @type {Object}
 * @property {function(MapViewModel)} init
 * @property {function} cleanup
 * @property {function(uTrack, boolean)} displayTrack
 * @property {function} clearMap
 * @property {function(number)} animateMarker
 * @property {function} getBounds
 * @property {function} zoomToExtent
 * @property {function} zoomToBounds
 * @property {function} updateSize
 */

/**
 * @class MapViewModel
 */
export default class MapViewModel extends ViewModel {
  /**
   * @param {uState} state
   */
  constructor(state) {
    super({
      /** @type {?number} */
      markerOver: null,
      /** @type {?number} */
      markerSelect: null,
      // click handler
      onMenuToggle: null
    });
    this.model.onMenuToggle = () => this.onMapResize();
    this.state = state;
    /** @type HTMLElement */
    this.mapElement = document.querySelector('#map-canvas');
    this.savedBounds = null;
    this.api = null;
  }

  /**
   * @return {MapViewModel}
   */
  init() {
    this.bindAll();
    this.setObservers();
    return this;
  }

  /**
   * Dynamic change of map api
   * @param {string} apiName API name
   */
  loadMapAPI(apiName) {
    if (this.api) {
      try {
        this.savedBounds = this.api.getBounds();
      } catch (e) {
        this.savedBounds = null;
      }
      this.api.cleanup();
    }
    this.api = this.getApi(apiName);
    this.api.init()
      .then(() => this.onReady())
      .catch((e) => {
        let txt = $._('apifailure', apiName);
        if (e && e.message) {
          txt += ` (${e.message})`;
        }
        uUtils.error(e, txt);
        config.mapApi = (apiName === 'gmaps') ? 'openlayers' : 'gmaps';
      });
  }

  /**
   * @param {string} apiName
   * @return {OpenLayersApi|GoogleMapsApi}
   */
  getApi(apiName) {
    return apiName === 'gmaps' ? new GoogleMapsApi(this) : new OpenLayersApi(this);
  }

  onReady() {
    if (this.savedBounds) {
      this.api.zoomToBounds(this.savedBounds);
    }
    if (this.state.currentTrack) {
      this.api.displayTrack(this.state.currentTrack, this.savedBounds === null);
    }
  }

  setObservers() {
    config.onChanged('mapApi', (mapApi) => {
      this.loadMapAPI(mapApi);
    });
    this.state.onChanged('currentTrack', (track) => {
      if (!this.api) {
        return;
      }
      this.api.clearMap();
      if (track) {
        uObserve.observe(track, 'positions', () => {
          this.api.displayTrack(track, false);
          this.api.zoomToExtent();
        });
        this.api.displayTrack(track, true);
      }
    });
  }

  /**
   * Get popup html
   * @param {number} id Position index
   * @returns {HTMLDivElement}
   */
   getPopupElement(id) {
    const pos = this.state.currentTrack.positions[id];
    const count = this.state.currentTrack.length;
    const user = this.state.currentTrack.user;
    const isEditable = auth.user && (auth.isAdmin || auth.user === user);
    let date = '–––';
    let time = '–––';
    if (pos.timestamp > 0) {
      const dateTime = uUtils.getTimeString(new Date(pos.timestamp * 1000));
      date = dateTime.date;
      time = `${dateTime.time}<span class="smaller">${dateTime.zone}</span>`;
    }
    let provider = '';
    if (pos.provider === 'gps') {
      provider = ` <img class="icon" alt="${$._('gps')}" title="${$._('gps')}"  src="images/gps_dark.svg">`;
    } else if (pos.provider === 'network') {
      provider = ` <img class="icon" alt="${$._('network')}" title="${$._('network')}"  src="images/network_dark.svg">`;
    }
    let editLink = '';
    if (isEditable) {
      editLink = `<a id="editposition" class="menu-link" data-bind="onUserAdd">${$._('editposition')}</a>`;
    }
    let stats = '';
    if (!this.state.showLatest) {
      stats =
        `<div id="pright">
        <img class="icon" alt="${$._('track')}" src="images/stats_blue.svg" style="margin-left: 3em;"><br>
        <img class="icon" alt="${$._('ttime')}" title="${$._('ttime')}" src="images/time_blue.svg"> ${$.getLocaleDuration(pos.totalSeconds)}<br>
        <img class="icon" alt="${$._('aspeed')}" title="${$._('aspeed')}" src="images/speed_blue.svg"> ${$.getLocaleSpeed(pos.totalSpeed, true)}<br>
        <img class="icon" alt="${$._('tdistance')}" title="${$._('tdistance')}" src="images/distance_blue.svg"> ${$.getLocaleDistanceMajor(pos.totalMeters, true)}<br>
        </div>`;
    }
    const html =
       `<div id="pheader">
        <div><img alt="${$._('user')}" title="${$._('user')}" src="images/user_dark.svg"> ${uUtils.htmlEncode(pos.username)}</div>
        <div><img alt="${$._('track')}" title="${$._('track')}" src="images/route_dark.svg"> ${uUtils.htmlEncode(pos.trackname)}</div>
        </div>
        <div id="pbody">
        ${(pos.hasComment()) ? `<div id="pcomments">${uUtils.htmlEncode(pos.comment).replace(/\n/, '<br>')}</div>` : ''}
        ${(pos.hasImage()) ? `<div id="pimage"><img src="uploads/${pos.image}" alt="image"></div>` : ''}
        <div id="pleft">
        <img class="icon" alt="${$._('time')}" title="${$._('time')}" src="images/calendar_dark.svg"> ${date}<br>
        <img class="icon" alt="${$._('time')}" title="${$._('time')}" src="images/clock_dark.svg"> ${time}<br>
        ${(pos.speed !== null) ? `<img class="icon" alt="${$._('speed')}" title="${$._('speed')}" src="images/speed_dark.svg">${$.getLocaleSpeed(pos.speed, true)}<br>` : ''}
        ${(pos.altitude !== null) ? `<img class="icon" alt="${$._('altitude')}" title="${$._('altitude')}" src="images/altitude_dark.svg">${$.getLocaleAltitude(pos.altitude, true)}<br>` : ''}
        ${(pos.accuracy !== null) ? `<img class="icon" alt="${$._('accuracy')}" title="${$._('accuracy')}" src="images/accuracy_dark.svg">${$.getLocaleAccuracy(pos.accuracy, true)}${provider}<br>` : ''}
        ${(pos.bearing !== null) ? `<img class="icon" alt="${$._('bearing')}" title="${$._('bearing')}" src="images/bearing.svg" style="transform: rotate(${pos.bearing}deg) scale(1.2);">${pos.bearing}°<br>` : ''}
        <img class="icon" alt="${$._('position')}" title="${$._('position')}" src="images/position.svg">${$.getLocaleCoordinates(pos)}<br>
        </div>${stats}</div>
        <div id="pfooter"><div>${$._('pointof', id + 1, count)}</div><div>${editLink}</div></div>`;
    const node = document.createElement('div');
    node.setAttribute('id', 'popup');
    node.innerHTML = html;
    if (pos.hasImage()) {
      const image = node.querySelector('#pimage img');
      image.onclick = () => {
        const modal = new uDialog(`<img src="uploads/${pos.image}" alt="image">`);
        const closeEl = modal.element.querySelector('#modal-close');
        closeEl.onclick = () => modal.destroy();
        modal.element.classList.add('image');
        modal.show();
      }
    }
    if (isEditable) {
      const edit = node.querySelector('#editposition');
      edit.onclick = () => {
        const vm = new PositionDialogModel(this.state, id);
        vm.init();
      }
    }
    return node;
  }

  /**
   * Get SVG marker path
   * @param {boolean} isLarge Large marker with hole if true
   * @return {string}
   */
  static getMarkerPath(isLarge) {
    const markerHole = 'M15,34.911c0,0,0.359-3.922,1.807-8.588c0.414-1.337,1.011-2.587,2.495-4.159' +
      'c1.152-1.223,3.073-2.393,3.909-4.447c1.681-6.306-3.676-9.258-8.211-9.258c-4.536,0-9.893,2.952-8.211,9.258' +
      'c0.836,2.055,2.756,3.225,3.91,4.447c1.484,1.572,2.08,2.822,2.495,4.159C14.64,30.989,15,34.911,15,34.911z M18,15.922' +
      'c0,1.705-1.342,3.087-2.999,3.087c-1.657,0-3-1.382-3-3.087c0-1.704,1.343-3.086,3-3.086C16.658,12.836,18,14.218,18,15.922z';
    const marker = 'M14.999,34.911c0,0,0.232-1.275,1.162-4.848c0.268-1.023,0.652-1.98,1.605-3.184' +
      'c0.742-0.937,1.975-1.832,2.514-3.404c1.082-4.828-2.363-7.088-5.281-7.088c-2.915,0-6.361,2.26-5.278,7.088' +
      'c0.538,1.572,1.771,2.468,2.514,3.404c0.953,1.203,1.337,2.16,1.604,3.184C14.77,33.635,14.999,34.911,14.999,34.911z';
    return isLarge ? markerHole : marker;
  }

  /**
   * Get marker extra mark
   * @param {boolean} isLarge
   * @return {string}
   */
  static getMarkerExtra(isLarge) {
    const offset1 = isLarge ? 'M26.074,13.517' : 'M23.328,20.715';
    const offset2 = isLarge ? 'M28.232,10.942' : 'M25.486,18.141';
    return `<path fill="none" stroke="red" stroke-width="2" d="${offset1}c0-3.961-3.243-7.167-7.251-7.167"/>
            <path fill="none" stroke="red" stroke-width="2" d="${offset2}c-0.5-4.028-3.642-7.083-7.724-7.542"/>`;
  }

  /**
   * Get inline SVG source
   * @param {string} fill
   * @param {boolean=} isLarge
   * @param {boolean=} isExtra
   * @return {string}
   */
  static getSvgSrc(fill, isLarge, isExtra) {
    const svg = `<svg viewBox="0 0 30 35" width="30px" height="35px" xmlns="http://www.w3.org/2000/svg">
      <g><path stroke="black" fill="${fill}" d="${MapViewModel.getMarkerPath(isLarge)}"/>${isExtra ? MapViewModel.getMarkerExtra(isLarge) : ''}</g></svg>`;
    return `data:image/svg+xml,${encodeURIComponent(svg)}`;
  }

  onMapResize() {
    if (this.api) {
      this.api.updateSize();
    }
  }

}
