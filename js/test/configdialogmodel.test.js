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

import { config, lang } from '../src/initializer.js';
import ConfigDialogModel from '../src/configdialogmodel.js';
import uLayer from '../src/layer.js';
import uLayerCollection from '../src/layercollection.js';
import uObserve from '../src/observe.js';

describe('ConfigDialogModel tests', () => {

  let cm;
  let layers;

  beforeEach(() => {
    config.reinitialize();
    lang.init(config);
    spyOn(lang, '_').and.callFake((arg) => arg);
    cm = new ConfigDialogModel();
    layers = new uLayerCollection(new uLayer(0, 'layer0', '', 0), new uLayer(1, 'layer1', '', 0));
    cm.model.layers = layers;
  });

  afterEach(() => {
    document.body.innerHTML = '';
    uObserve.unobserveAll(lang);
  });

  it('should show config dialog', () => {
    // when
    cm.init();
    // then
    expect(document.querySelector('#modal')).toBeInstanceOf(HTMLDivElement);
  });

  const testElements = [
    'interval', 'units', 'lang', 'mapApi', 'googleKey', 'layerName', 'layerId', 'layerUrl', 'initLatitude', 'initLongitude',
    'requireAuth', 'publicTracks', 'passStrength', 'passLenMin', 'strokeWeight', 'strokeColor', 'strokeOpacity',
    'colorNormal', 'colorStart', 'colorStop', 'colorExtra', 'colorHilite', 'uploadMaxSizeMB'
  ];
  testElements.forEach((name) => {
    it(`should trigger model property change for ${name}`, (done) => {
      // given
      cm.init();
      const element = cm.dialog.element.querySelector(`[data-bind=${name}]`);
      // when
      if (element instanceof HTMLSelectElement) {
        const opt = document.createElement('option');
        opt.value = `__${name}__test`;
        opt.text = `__${name}__test`;
        element.add(opt, null);
        element.value = `__${name}__test`;
      } else if (element.type === 'checkbox') {
        element.checked = !element.checked;
      } else if (element.type === 'number') {
        element.value = Math.random().toString();
      } else {
        element.value = `__${name}__test`;
      }
      element.dispatchEvent(new Event('change'));
      // then
      setTimeout(() => {
        expect(cm.model[name]).not.toBe('');
        if (element.type === 'checkbox') {
          expect(cm.model[name]).toBe(element.checked);
        } else {
          expect(cm.model[name]).toBe(element.value);
        }
        done();
      }, 100);
    });
  });

  it('should update uploadMaxSize when uploadMaxSizeMB is changed', (done) => {
    // given
    cm.init();
    const element = cm.dialog.element.querySelector('[data-bind=uploadMaxSizeMB]');
    const valueMB = 2;
    // when
    element.value = valueMB;
    element.dispatchEvent(new Event('change'));
    // then
    setTimeout(() => {
      expect(cm.model.uploadMaxSize).toBe(valueMB * 1024 * 1024);
      done();
    }, 100);
  });

  it('should show layer edit on add button click', (done) => {
    // given
    cm.init();
    const button = cm.getBoundElement('onLayerAdd');
    cm.layerEditEl.style.display = 'none';
    // when
    button.click();
    // then
    setTimeout(() => {
      expect(cm.layerEditEl.style.display).toBe('block');
      expect(cm.model.layerId).toBe('-1');
      done();
    }, 100);
  });

  it('should hide visible layer edit on add button click', (done) => {
    // given
    cm.init();
    const button = cm.getBoundElement('onLayerAdd');
    cm.onLayerAdd();
    // when
    button.click();
    // then
    setTimeout(() => {
      expect(cm.layerEditEl.style.display).toBe('none');
      expect(cm.model.layerId).toBe('-1');
      done();
    }, 100);
  });

  it('should save config on positive button clicked', (done) => {
    // given
    spyOn(cm, 'validate').and.returnValue(true);
    spyOn(config, 'save').and.resolveTo();
    cm.init();
    const button = cm.dialog.element.querySelector("[data-bind='onSave']");
    // when
    button.click();
    // then
    setTimeout(() => {
      expect(cm.validate).toHaveBeenCalledTimes(1);
      expect(config.save).toHaveBeenCalledTimes(1);
      expect(document.querySelector('#modal')).toBe(null);
      done();
    }, 100);
  });

  it('should set priority layer on save', (done) => {
    // given
    spyOn(cm, 'validate').and.returnValue(true);
    spyOn(config, 'save').and.resolveTo();
    cm.init();
    cm.model.layerId = '1';
    const button = cm.dialog.element.querySelector("[data-bind='onSave']");
    // when
    button.click();
    // then
    setTimeout(() => {
      expect(cm.model.layers[1].priority).toBe(1);
      done();
    }, 100);
  });

  it('should hide dialog on negative button clicked', (done) => {
    // given
    cm.init();
    const button = cm.dialog.element.querySelector("[data-bind='onCancel']");
    // when
    button.click();
    // then
    setTimeout(() => {
      expect(document.querySelector('#modal')).toBe(null);
      done();
    }, 100);
  });

  it('should show edit on non-default layer select', (done) => {
    // given
    cm.model.layers = new uLayerCollection(new uLayer(0, 'layer0', '', 0), new uLayer(1, 'layer1', '', 0));
    cm.init();
    const element = cm.getBoundElement('layerId');
    // when
    element.value = '1';
    element.dispatchEvent(new Event('change'));
    // then
    setTimeout(() => {
      expect(cm.toggleEditEl.style.visibility).toBe('visible');
      done();
    }, 100);
  });

  it('should not show edit on default layer select', (done) => {
    // given
    cm.init();
    const element = cm.getBoundElement('layerId');
    // when
    element.value = '0';
    element.dispatchEvent(new Event('change'));
    // then
    setTimeout(() => {
      expect(cm.toggleEditEl.style.visibility).toBe('hidden');
      done();
    }, 100);
  });

  it('should delete layer on anchor click', (done) => {
    // given
    cm.init();
    const button = cm.dialog.element.querySelector("[data-bind='onLayerDelete']");
    const element = cm.getBoundElement('layerId');

    expect(layers.length).toBe(2);
    // when
    element.value = '1';
    element.dispatchEvent(new Event('change'));
    setTimeout(() => {
      button.click();
      setTimeout(() => {
        // then
        expect(layers.length).toBe(1);
        expect(layers[0].id).toBe(0);
        expect(cm.model.layerId).toBe('0');
        done();
      }, 100);
    }, 100);
  });

  it('should add layer on anchor click', (done) => {
    // given
    cm.init();
    const addBtn = cm.dialog.element.querySelector("[data-bind='onLayerAdd']");
    const updateBtn = cm.dialog.element.querySelector("[data-bind='onLayerUpdate']");
    const nameEl = cm.dialog.element.querySelector("[data-bind='layerName']");
    const urlEl = cm.dialog.element.querySelector("[data-bind='layerUrl']");

    expect(layers.length).toBe(2);
    // when
    addBtn.click();
    setTimeout(() => {
      nameEl.value = 'test name';
      nameEl.dispatchEvent(new Event('change'));
      urlEl.value = 'test url';
      urlEl.dispatchEvent(new Event('change'));
      updateBtn.click();
      setTimeout(() => {
        // then
        expect(layers.length).toBe(3);
        expect(layers[2].id).toBe(2);
        done();
      }, 100);
    }, 100);
  });

  it('should update layer on anchor click', (done) => {
    // given
    cm.init();
    const addBtn = cm.dialog.element.querySelector("[data-bind='onLayerAdd']");
    const updateBtn = cm.dialog.element.querySelector("[data-bind='onLayerUpdate']");
    const nameEl = cm.dialog.element.querySelector("[data-bind='layerName']");
    const urlEl = cm.dialog.element.querySelector("[data-bind='layerUrl']");
    const layerEl = cm.getBoundElement('layerId');

    expect(layers.length).toBe(2);
    // when
    addBtn.click();
    setTimeout(() => {
      layerEl.value = '1';
      layerEl.dispatchEvent(new Event('change'));
      nameEl.value = 'test name';
      nameEl.dispatchEvent(new Event('change'));
      urlEl.value = 'test url';
      urlEl.dispatchEvent(new Event('change'));
      updateBtn.click();
      setTimeout(() => {
        // then
        expect(layers.length).toBe(2);
        expect(layers[1].id).toBe(1);
        expect(layers[1].name).toBe('test name');
        expect(layers[1].url).toBe('test url');
        done();
      }, 100);
    }, 100);
  });

  it('should cancel layer edit on anchor click', (done) => {
    // given
    cm.init();
    const addBtn = cm.dialog.element.querySelector("[data-bind='onLayerAdd']");
    const cancelBtn = cm.dialog.element.querySelector("[data-bind='onLayerCancel']");
    const nameEl = cm.dialog.element.querySelector("[data-bind='layerName']");
    const urlEl = cm.dialog.element.querySelector("[data-bind='layerUrl']");
    const layerEl = cm.getBoundElement('layerId');
    const layersCount = layers.length;
    const layerId = 1;
    const layerName = layers[1].name;
    const layerUrl = layers[1].url;

    expect(layers.length).toBe(2);
    // when
    addBtn.click();
    setTimeout(() => {
      layerEl.value = layerId.toString();
      layerEl.dispatchEvent(new Event('change'));
      nameEl.value = 'test name';
      nameEl.dispatchEvent(new Event('change'));
      urlEl.value = 'test url';
      urlEl.dispatchEvent(new Event('change'));
      cancelBtn.click();
      setTimeout(() => {
        // then
        expect(layers.length).toBe(layersCount);
        expect(layers[1].id).toBe(layerId);
        expect(layers[1].name).toBe(layerName);
        expect(layers[1].url).toBe(layerUrl);
        done();
      }, 100);
    }, 100);
  });

});
