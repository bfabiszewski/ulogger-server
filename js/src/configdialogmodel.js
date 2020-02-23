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

import { lang as $, config } from './initializer.js';
import ViewModel from './viewmodel.js';
import uDialog from './dialog.js';
import uLayer from './layer.js';
import uLayerCollection from './layercollection.js';
import uSelect from './select.js';
import uUtils from './utils.js';

/**
 * @class ConfigDialogModel
 */
export default class ConfigDialogModel extends ViewModel {

  constructor() {
    super({
      interval: config.interval,
      units: config.units,
      lang: config.lang,
      mapApi: config.mapApi,
      googleKey: config.googleKey,
      layerId: config.olLayers.getPriorityLayer(),
      layers: new uLayerCollection(new uLayer(0, 'OpenStreetMap', '', 0), ...config.olLayers),
      layerName: null,
      layerUrl: null,
      initLatitude: config.initLatitude,
      initLongitude: config.initLongitude,
      requireAuth: config.requireAuth,
      publicTracks: config.publicTracks,
      passStrength: config.passStrength,
      passLenMin: config.passLenMin,
      strokeWeight: config.strokeWeight,
      strokeColor: config.strokeColor,
      strokeOpacity: config.strokeOpacity,
      colorNormal: config.colorNormal,
      colorStart: config.colorStart,
      colorStop: config.colorStop,
      colorExtra: config.colorExtra,
      colorHilite: config.colorHilite
    });
    this.model.onCancel = () => this.onCancel();
    this.model.onSave = () => this.onSave();
    this.model.onLayerUpdate = () => this.onLayerUpdate();
    this.model.onLayerCancel = () => this.onLayerCancel();
    this.model.onLayerEdit = () => this.onLayerEdit();
    this.model.onLayerDelete = () => this.onLayerDelete();
    this.model.onLayerAdd = () => this.onLayerAdd();
  }

  init() {
    const html = this.getHtml();
    this.dialog = new uDialog(html);
    this.dialog.show();
    this.bindAll(this.dialog.element);
    this.toggleEditEl = this.getBoundElement('onLayerEdit').parentNode;
    this.layerEditEl = this.getBoundElement('layerName').parentNode;
    this.layerSelect = new uSelect(this.getBoundElement('layerId'));
    this.layerSelect.setOptions(this.model.layers, this.currentLayer().listValue);
    this.setPublicTracksActivity(this.model.requireAuth);
    this.onChanged('layerId', (listValue) => {
      const layer = this.model.layers.get(listValue);
      this.model.layerName = layer ? layer.name : '';
      this.model.layerUrl = layer ? layer.url : '';
      this.toggleEditVisible();
    });
    this.onChanged('layers', (list) => this.layerSelect.setOptions(list));
    this.onChanged('requireAuth', (value) => {
      this.setPublicTracksActivity(value);
    });
  }

  setPublicTracksActivity(value) {
    if (value) {
      this.getBoundElement('publicTracks').disabled = false;
    } else {
      this.model.publicTracks = true;
      this.getBoundElement('publicTracks').disabled = true;
    }
  }

  onCancel() {
    this.dialog.destroy();
  }

  onSave() {
    if (this.validate()) {
      this.model.layers.setPriorityLayer(this.model.layerId);
      config.save(this.model)
        .then(() => this.dialog.destroy())
        .catch((e) => { uUtils.error(e, `${$._('actionfailure')}\n${e.message}`); });
    }
  }

  /**
   * Validate form
   * @return {boolean} True if valid
   */
  validate() {
    const form = this.dialog.element.querySelector('form');
    return form.checkValidity();
  }

  toggleEditVisible() {
    if (this.model.layerId > 0) {
      this.toggleEditEl.style.visibility = 'visible';
    } else {
      this.toggleEditEl.style.visibility = 'hidden';
      this.hideEditElement();
    }
  }

  onLayerDelete() {
    this.model.layers.delete(this.model.layerId);
    this.model.layerId = 0;
  }

  onLayerEdit() {
    if (this.layerEditEl.style.display === 'none') {
      this.showEditElement();
    } else {
      this.hideEditElement();
    }
  }

  onLayerUpdate() {
    if (!this.model.layerName || !this.model.layerUrl) {
      return;
    }
    if (this.model.layerId === -1) {
      this.model.layers.addNewLayer(this.model.layerName, this.model.layerUrl);
    } else {
      const layer = this.currentLayer();
      layer.setName(this.model.layerName);
      layer.setUrl(this.model.layerUrl);
    }
    this.hideEditElement();
    this.layerSelect.setOptions(this.model.layers);
  }

  onLayerCancel() {
    this.hideEditElement();
    this.layerSelect.setOptions(this.model.layers);
  }

  onLayerAdd() {
    this.model.layerId = -1;
    this.onLayerEdit();
  }

  hideEditElement() {
    this.layerEditEl.style.display = 'none';
  }

  showEditElement() {
    this.layerEditEl.style.display = 'block';
  }

  currentLayer() {
    return this.model.layers.get(this.model.layerId);
  }

  /**
   * @return {string}
   */
  getHtml() {
    let langOptions = '';
    for (const [ langCode, langName ] of Object.entries($.getLangList())) {
      langOptions += `<option value="${langCode}"${this.model.lang === langCode ? ' selected' : ''}>${langName}</option>`;
    }
    let unitOptions = '';
    for (const units of [ 'metric', 'imperial', 'nautical' ]) {
      unitOptions += `<option value="${units}"${this.model.units === units ? ' selected' : ''}>${$._(units)}</option>`;
    }
    let layerOptions = '';
    for (const layer of this.model.layers) {
      layerOptions += `<option value="${layer.id}"${layer.priority > 0 ? ' selected' : ''}>${layer.name}</option>`;
    }
    return `<div><img style="vertical-align: bottom; margin-right: 10px;" src="images/settings.svg" alt="${$._('settings')}"> <b>${$._('editingconfig')}</b></div>
      <div style="clear: both; padding-bottom: 1em;"></div>
      <form id="configForm">
        <label><b>${$._('language')}</b>
        <select data-bind="lang">
          ${langOptions}
        </select></label>
        <label><b>${$._('units')}</b>
        <select data-bind="units">
          ${unitOptions}
        </select></label>
        <label><b>${$._('api')}</b>
        <select data-bind="mapApi">
          <option value="openlayers"${this.model.mapApi === 'openlayers' ? ' selected' : ''}>OpenLayers</option>
          <option value="gmaps"${this.model.mapApi === 'gmaps' ? ' selected' : ''}>Google Maps</option>
        </select></label>
        <label><b>${$._('ollayers')}</b>
        <select data-bind="layerId">
          ${layerOptions}
        </select>
        <a data-bind="onLayerAdd"><img src="images/add.svg" alt="${$._('add')}"></a> 
        <span style="visibility: hidden;">
        <a data-bind="onLayerEdit"><img src="images/edit.svg" alt="${$._('edit')}"></a> 
        <a data-bind="onLayerDelete"><img src="images/delete.svg" alt="${$._('delete')}"></a>
        </span></label>
        <div style="display: none; text-align: center;">
          <input type="text" maxlength="50" placeholder="${$._('layername')}" data-bind="layerName">
          <input type="text" maxlength="255" placeholder="${$._('layerurl')}" data-bind="layerUrl">
          <button class="button-resolve" data-bind="onLayerUpdate" type="submit">${$._('submit')}</button>
          <button class="button-reject" data-bind="onLayerCancel" type="button">${$._('cancel')}</button>
        </div>
        <label><b>${$._('interval')}</b>
        <input type="number" data-bind="interval" min="1" value="${this.model.interval}" required></label>
        <label><b>${$._('longitude')}</b>
        <input type="number" data-bind="longitude" min="-180" max="180" step="0.01" value="${this.model.initLongitude}" required></label>
        <label><b>${$._('latitude')}</b>
        <input type="number" data-bind="latitude" min="-90" max="90" step="0.01" value="${this.model.initLatitude}" required></label>
        <label><b>${$._('googlekey')}</b>
        <input type="text" data-bind="googleKey" value="${this.model.googleKey}"></label>
        <label><b>${$._('passlength')}</b>
        <input type="number" data-bind="passLenMin" min="1" value="${this.model.passLenMin}" required></label>
        <label><b>${$._('passstrength')}</b>
        <select data-bind="passStrength">
          <option value="0"${this.model.passStrength === 0 ? ' selected' : ''}>password</option>
          <option value="1"${this.model.passStrength === 1 ? ' selected' : ''}>paSsword</option>
          <option value="2"${this.model.passStrength === 2 ? ' selected' : ''}>paSsword1</option>
          <option value="3"${this.model.passStrength === 3 ? ' selected' : ''}>paSsword1#</option>
        </select></label>
        <label><b>${$._('requireauth')}</b>
        <input type="checkbox" data-bind="requireAuth"${this.model.requireAuth ? ' checked' : ''}></label>
        <label><b>${$._('publictracks')}</b>
        <input type="checkbox" data-bind="publicTracks"${this.model.publicTracks ? ' checked' : ''}></label>
        <label><b>${$._('strokeweight')}</b>
        <input type="number" data-bind="strokeWeight" min="1" value="${this.model.strokeWeight}" required></label>
        <label><b>${$._('strokeopacity')}</b>
        <input type="number" data-bind="strokeOpacity" min="0" max="1" step="0.01" value="${this.model.strokeOpacity}" required></label>
        <label><b>${$._('strokecolor')}</b>
        <input type="color" data-bind="strokeColor" pattern="#[0-9a-f]{6}" maxlength="7" value="${this.model.strokeColor}" required></label>
        <label><b>${$._('colornormal')}</b>
        <input type="color" data-bind="strokeColor" pattern="#[0-9a-f]{6}" maxlength="7" value="${this.model.colorNormal}" required></label>
        <label><b>${$._('colorstart')}</b>
        <input type="color" data-bind="strokeColor" pattern="#[0-9a-f]{6}" maxlength="7" value="${this.model.colorStart}" required></label>
        <label><b>${$._('colorstop')}</b>
        <input type="color" data-bind="strokeColor" pattern="#[0-9a-f]{6}" maxlength="7" value="${this.model.colorStop}" required></label>
        <label><b>${$._('colorextra')}</b>
        <input type="color" data-bind="strokeColor" pattern="#[0-9a-f]{6}" maxlength="7" value="${this.model.colorExtra}" required></label>
        <label><b>${$._('colorhilite')}</b>
        <input type="color" data-bind="strokeColor" pattern="#[0-9a-f]{6}" maxlength="7" value="${this.model.colorHilite}" required></label>
        <div class="buttons">
          <button class="button-reject" data-bind="onCancel" type="button">${$._('cancel')}</button>
          <button class="button-resolve" data-bind="onSave" type="submit">${$._('submit')}</button>
        </div>
      </form>`;
  }
}
