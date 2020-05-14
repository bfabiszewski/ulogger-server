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

import uLayer from '../src/layer.js';
import uLayerCollection from '../src/layercollection.js';

describe('LayerCollection tests', () => {

  let layers;
  const testId = 5;
  const testName = 'test name';
  const testUrl = 'https://layer.url';
  const testPriority = 0;

  beforeEach(() => {
    layers = new uLayerCollection();
  });

  it('should create instance', () => {
    // then
    expect(layers).toBeInstanceOf(Array);
    expect(layers).toBeInstanceOf(uLayerCollection);
  });

  it('should add new layer', () => {
    // when
    layers.addNewLayer(testName, testUrl, testPriority);
    layers.addNewLayer(`${testName}2`, `${testUrl}2`, testPriority + 1);
    // then
    expect(layers.length).toBe(2);
    expect(layers[0]).toBeInstanceOf(uLayer);
    expect(layers[0].id).toBe(1);
    expect(layers[0].name).toBe(testName);
    expect(layers[0].url).toBe(testUrl);
    expect(layers[0].priority).toBe(testPriority);
    expect(layers[1].id).toBe(2);
  });

  it('should add layer', () => {
    // when
    layers.addLayer(testId, testName, testUrl, testPriority);
    // then
    expect(layers.length).toBe(1);
    expect(layers[0]).toBeInstanceOf(uLayer);
    expect(layers[0].id).toBe(testId);
    expect(layers[0].name).toBe(testName);
    expect(layers[0].url).toBe(testUrl);
    expect(layers[0].priority).toBe(testPriority);
  });

  it('should delete layer by id', () => {
    // given
    layers.addLayer(testId, testName, testUrl, testPriority);
    layers.addLayer(testId + 1, testName, testUrl, testPriority);

    expect(layers.length).toBe(2);
    // when
    layers.delete(testId);

    // then
    expect(layers.length).toBe(1);
    expect(layers[0].id).toBe(testId + 1);
  });

  it('should get layer by id (numeric)', () => {
    // when
    layers.addLayer(testId, testName, testUrl, testPriority);
    layers.addLayer(testId + 1, testName, testUrl, testPriority);
    // then
    expect(layers.get(testId).id).toBe(testId);
  });

  it('should get max id of all layers in array', () => {
    // when
    layers.addLayer(testId + 1, testName, testUrl, testPriority);
    layers.addLayer(testId, testName, testUrl, testPriority);
    // then
    expect(layers.getMaxId()).toBe(testId + 1);
  });

  it('should set priority layer by id', () => {
    // given
    layers.addLayer(testId + 1, testName, testUrl, testPriority);
    layers.addLayer(testId, testName, testUrl, testPriority);
    // when
    layers.setPriorityLayer(testId);
    // then
    expect(layers[0].priority).toBe(0);
    expect(layers[1].priority).toBe(1);
    expect(layers.getPriorityLayer()).toBe(testId);
  });

  it('should load layers from array', () => {
    // given
    const arr = [ { id: testId, name: testName, url: testUrl, priority: testPriority } ];
    // when
    layers.load(arr);
    // then
    expect(layers.length).toBe(1);
    expect(layers[0]).toBeInstanceOf(uLayer);
    expect(layers[0].id).toBe(testId);
    expect(layers[0].name).toBe(testName);
    expect(layers[0].url).toBe(testUrl);
    expect(layers[0].priority).toBe(testPriority);
  });

});
