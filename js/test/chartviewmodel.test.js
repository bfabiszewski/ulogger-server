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

import ChartViewModel from '../src/chartviewmodel.js';
import Chartist from 'chartist'
import Fixture from './helpers/fixture.js';
import TrackFactory from './helpers/trackfactory.js';
import ViewModel from '../src/viewmodel.js';
import { lang } from '../src/initializer.js';
import uObserve from '../src/observe.js';
import uState from '../src/state.js';
import uUtils from '../src/utils.js';

describe('ChartViewModel tests', () => {

  let state;
  /** @type {HTMLAnchorElement} */
  let buttonEl;
  /** @type {HTMLAnchorElement} */
  let closeEl;
  /** @type {HTMLDivElement} */
  let chartEl;
  /** @type {HTMLDivElement} */
  let chartContainerEl;
  let vm;
  let mockChart;
  let chartFixture;
  let chartData;
  let chartPointNodes;

  beforeEach((done) => {
    Fixture.load('main.html')
      .then(() => done())
      .catch((e) => done.fail(e));
  });

  beforeEach(() => {
    // language=XML
    chartFixture = `<svg xmlns:ct="http://gionkunz.github.com/chartist-js/ct" width="100%" height="100%" class="ct-chart-line">
                          <g class="ct-grids"/>
                          <g class="ct-series ct-series-a">
                              <path d="1" class="ct-area"/>
                              <path d="1" class="ct-line"/>
                              <line x1="50" y1="115" x2="50" y2="115" class="ct-point" ct:value="0,130"/>
                              <line x1="173" y1="158" x2="173" y2="158" class="ct-point" ct:value="48,104"/>
                              <line x1="286" y1="23" x2="286" y2="23" class="ct-point" ct:value="92,185"/>
                              <line x1="400" y1="23" x2="400" y2="23" class="ct-point" ct:value="136,185"/>
                              <line x1="657" y1="135" x2="657" y2="135" class="ct-point" ct:value="236,118"/>
                              <line x1="1046" y1="135" x2="1046" y2="135" class="ct-point" ct:value="387,118"/>
                          </g>
                          <g class="ct-labels"/>
                        </svg>`;
    chartData = [
      { x: 0, y: 130 },
      { x: 48, y: 104 },
      { x: 92, y: 185 },
      { x: 136, y: 185 },
      { x: 236, y: 118 },
      { x: 387, y: 118 }
    ];
    chartEl = document.querySelector('#chart');
    chartContainerEl = document.querySelector('#bottom');
    buttonEl = document.querySelector('#altitudes');
    closeEl = document.querySelector('#chart-close');
    const chartRendered = uUtils.nodeFromHtml(chartFixture);
    chartPointNodes = chartRendered.querySelectorAll('.ct-series .ct-point');
    state = new uState();
    vm = new ChartViewModel(state);
    spyOn(lang, 'unit');
    spyOn(lang, '_').and.returnValue('{placeholder}');
    mockChart = jasmine.createSpyObj('mockChart', {
      'on': { /* ignored */ },
      'update': { /* ignored */ }
    });
    spyOn(Chartist, 'Line').and.returnValue(mockChart);
  });

  afterEach(() => {
    Fixture.clear();
    uObserve.unobserveAll(lang);
  });

  it('should create instance', () => {
    // then
    expect(vm).toBeInstanceOf(ViewModel);
    expect(vm.state).toBe(state);
    expect(vm.chartElement).toBe(chartEl);
    expect(vm.chartContainer).toBe(chartContainerEl);
    expect(vm.chart).toBe(null);
    expect(vm.data).toEqual([]);
  });

  it('should initialize chart, set and bind observers', () => {
    // given
    spyOn(vm, 'chartSetup');
    spyOn(vm, 'setObservers');
    spyOn(vm, 'bindAll');
    // when
    vm.init();
    // then
    expect(vm.chartSetup).toHaveBeenCalledTimes(1);
    expect(vm.setObservers).toHaveBeenCalledTimes(1);
    expect(vm.bindAll).toHaveBeenCalledTimes(1);
  });

  it('should set up chart', () => {
    // given
    spyOn(uUtils, 'addCss');
    // when
    vm.chartSetup();
    // then
    expect(uUtils.addCss).toHaveBeenCalledWith('css/chartist.min.css', 'chartist_css');
    expect(Chartist.Line).toHaveBeenCalledWith(chartEl, jasmine.any(Object), jasmine.any(Object));
    expect(mockChart.on).toHaveBeenCalledWith('created', jasmine.any(Function));
  });

  it('should add click listeners to all chart points on created', () => {
    // given
    chartEl.insertAdjacentHTML('afterbegin', chartFixture);
    vm.data = chartData;
    spyOn(EventTarget.prototype, 'addEventListener');
    // when
    vm.onCreated();
    // then
    expect(EventTarget.prototype.addEventListener).toHaveBeenCalledTimes(chartData.length);
    expect(EventTarget.prototype.addEventListener).toHaveBeenCalledWith('click', jasmine.any(Function));
    expect(vm.chartPoints).toEqual(chartPointNodes);
  });

  it('should render chart on non-empty track and show altitudes button', () => {
    // given
    spyOn(vm, 'render');
    const positions = [
      TrackFactory.getPosition({ id: 1, latitude: 2, longitude: 3, altitude: 4 }),
      TrackFactory.getPosition({ id: 2, latitude: 3, longitude: 4, altitude: 5 })
    ];
    state.currentTrack = null;
    vm.model.buttonVisible = false;
    buttonEl.style.visibility = 'hidden';
    // when
    vm.setObservers();
    state.currentTrack = TrackFactory.getTrack(positions);
    // then
    expect(vm.render).toHaveBeenCalledTimes(1);
    expect(vm.model.buttonVisible).toBe(true);
    expect(buttonEl.style.visibility).toBe('visible');
  });

  it('should render chart on null track and hide altitudes button', () => {
    // given
    spyOn(vm, 'render');
    const positions = [
      TrackFactory.getPosition({ id: 1, latitude: 2, longitude: 3, altitude: 4 }),
      TrackFactory.getPosition({ id: 2, latitude: 3, longitude: 4, altitude: 5 })
    ];
    state.currentTrack = TrackFactory.getTrack(positions);
    vm.model.buttonVisible = true;
    buttonEl.style.visibility = 'visible';
    // when
    vm.setObservers();
    state.currentTrack = null;
    // then
    expect(vm.render).toHaveBeenCalledTimes(1);
    expect(vm.model.buttonVisible).toBe(false);
    expect(buttonEl.style.visibility).toBe('hidden');
  });

  it('should render chart on empty track and hide altitudes button', () => {
    // given
    spyOn(vm, 'render');
    state.currentTrack = TrackFactory.getTrack(2);
    vm.model.buttonVisible = true;
    buttonEl.style.visibility = 'visible';
    // when
    vm.setObservers();
    state.currentTrack = TrackFactory.getTrack(0);
    // then
    expect(vm.render).toHaveBeenCalledTimes(1);
    expect(vm.model.buttonVisible).toBe(false);
    expect(buttonEl.style.visibility).toBe('hidden');
  });

  it('should render button visible', () => {
    // given
    vm.model.buttonVisible = false;
    buttonEl.style.visibility = 'hidden';
    // when
    vm.setObservers();
    vm.model.buttonVisible = true;
    // then
    expect(buttonEl.style.visibility).toBe('visible');
  });

  it('should render button hidden', () => {
    // given
    vm.model.buttonVisible = true;
    buttonEl.style.visibility = 'visible';
    // when
    vm.setObservers();
    vm.model.buttonVisible = false;
    // then
    expect(buttonEl.style.visibility).toBe('hidden');
  });

  it('should render chart container visible and render chart', () => {
    // given
    spyOn(vm, 'render');
    vm.model.chartVisible = false;
    chartContainerEl.style.display = 'none';
    // when
    vm.setObservers();
    vm.model.chartVisible = true;
    // then
    expect(vm.render).toHaveBeenCalledTimes(1);
    expect(chartContainerEl.style.display).toBe('block');
  });

  it('should render chart container hidden', () => {
    // given
    spyOn(vm, 'render');
    vm.model.chartVisible = true;
    chartContainerEl.style.display = 'block';
    // when
    vm.setObservers();
    vm.model.chartVisible = false;
    // then
    expect(vm.render).not.toHaveBeenCalled();
    expect(chartContainerEl.style.display).toBe('none');
  });

  it('should render chart on non-empty track and chart visible', () => {
    // given
    const positions = [
      TrackFactory.getPosition({ id: 1, latitude: 2, longitude: 3, altitude: 4 }),
      TrackFactory.getPosition({ id: 2, latitude: 3, longitude: 4, altitude: 5 })
    ];
    const track = TrackFactory.getTrack(positions);
    state.currentTrack = track;
    vm.model.chartVisible = true;
    vm.data = null;
    vm.chartSetup();
    // when
    vm.render();
    // then
    expect(mockChart.update).toHaveBeenCalledTimes(1);
    expect(mockChart.update.calls.mostRecent().args[0].series[0]).toBe(track.plotData);
    expect(vm.data).toBe(track.plotData);
  });

  it('should not render chart on same track and chart visible', () => {
    // given
    const positions = [
      TrackFactory.getPosition({ id: 1, latitude: 2, longitude: 3, altitude: 4 }),
      TrackFactory.getPosition({ id: 2, latitude: 3, longitude: 4, altitude: 5 })
    ];
    const track = TrackFactory.getTrack(positions);
    state.currentTrack = track;
    vm.model.chartVisible = true;
    vm.data = track.plotData;
    vm.chartSetup();
    // when
    vm.render();
    // then
    expect(mockChart.update).not.toHaveBeenCalled();
    expect(vm.data).toBe(track.plotData);
  });

  it('should render empty chart on empty track and hide chart container', () => {
    // given
    const track = TrackFactory.getTrack(0);
    state.currentTrack = track;
    vm.model.chartVisible = true;
    vm.data = chartData;
    vm.chartSetup();
    // when
    vm.render();
    // then
    expect(mockChart.update).toHaveBeenCalledTimes(1);
    expect(mockChart.update.calls.mostRecent().args[0].series[0]).toEqual(track.plotData);
    expect(vm.data).toEqual(track.plotData);
    expect(vm.model.chartVisible).toBe(false);
  });

  it('should render empty chart on null track and hide chart container', () => {
    // given
    state.currentTrack = null;
    vm.model.chartVisible = true;
    vm.data = chartData;
    vm.chartSetup();
    // when
    vm.render();
    // then
    expect(mockChart.update).toHaveBeenCalledTimes(1);
    expect(mockChart.update.calls.mostRecent().args[0].series[0]).toEqual([]);
    expect(vm.data).toEqual([]);
    expect(vm.model.chartVisible).toBe(false);
  });

  it('should hilight chart point', () => {
    // given
    vm.model.chartVisible = true;
    vm.chartPoints = chartPointNodes;
    const pointId = 0;
    /** @type {SVGLineElement} */
    const point = chartPointNodes[pointId];
    // when
    vm.onPointOver(pointId);
    // then
    expect(point.classList.contains('ct-point-hilight')).toBe(true);
  });

  it('should remove hilight from all points', () => {
    // given
    vm.model.chartVisible = true;
    vm.chartPoints = chartPointNodes;
    const pointId = 0;
    /** @type {SVGLineElement} */
    const point = chartPointNodes[pointId];
    point.classList.add('ct-point-hilight');
    // when
    vm.onPointOut();
    // then
    expect(point.classList.contains('ct-point-hilight')).toBe(false);
  });

  it('should select chart point', () => {
    // given
    vm.model.chartVisible = true;
    vm.chartPoints = chartPointNodes;
    const pointId = 0;
    /** @type {SVGLineElement} */
    const point = chartPointNodes[pointId];
    // when
    vm.onPointSelect(pointId);
    // then
    expect(point.classList.contains('ct-point-selected')).toBe(true);
  });

  it('should remove selection from all points', () => {
    // given
    vm.model.chartVisible = true;
    vm.chartPoints = chartPointNodes;
    const pointId = 0;
    /** @type {SVGLineElement} */
    const point = chartPointNodes[pointId];
    point.classList.add('ct-point-selected');
    // when
    vm.onPointUnselect();
    // then
    expect(point.classList.contains('ct-point-selected')).toBe(false);
  });

  it('should show chart on button click', (done) => {
    // given
    spyOn(vm, 'renderContainer');
    vm.model.chartVisible = false;
    // when
    vm.bindAll();
    vm.setObservers();
    buttonEl.click();
    // then
    setTimeout(() => {
      expect(vm.model.chartVisible).toBe(true);
      done();
    }, 100);
  });

  it('should hide chart on button click', (done) => {
    // given
    spyOn(vm, 'renderContainer');
    vm.model.chartVisible = true;
    // when
    vm.bindAll();
    vm.setObservers();
    buttonEl.click();
    // then
    setTimeout(() => {
      expect(vm.model.chartVisible).toBe(false);
      done();
    }, 100);
  });

  it('should hide chart on close click', (done) => {
    // given
    spyOn(vm, 'renderContainer');
    vm.model.chartVisible = true;
    // when
    vm.bindAll();
    vm.setObservers();
    closeEl.click();
    // then
    setTimeout(() => {
      expect(vm.model.chartVisible).toBe(false);
      done();
    }, 100);
  });

});
