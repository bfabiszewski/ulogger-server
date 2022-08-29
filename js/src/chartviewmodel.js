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

import { AutoScaleAxis, LineChart } from 'chartist';
import { lang as $ } from './initializer.js';
import ViewModel from './viewmodel.js';
import ctAxisTitle from 'chartist-plugin-axistitle';
import uObserve from './observe.js';
import uUtils from './utils.js';

/**
 * @typedef {Object} PlotPoint
 * @property {number} x
 * @property {number} y
 */
/**
 * @typedef {PlotPoint[]} PlotData
 */

// FIXME: Chartist is not suitable for large data sets
const LARGE_DATA = 1000;
export default class ChartViewModel extends ViewModel {
  /**
   * @param {uState} state
   */
  constructor(state) {
    super({
      pointSelected: null,
      chartVisible: false,
      buttonVisible: false,
      onChartToggle: null,
      onMenuToggle: null
    });
    this.state = state;
    /** @type {PlotData} */
    this.data = [];
    /** @type {?LineChart} */
    this.chart = null;
    /** @type {?NodeListOf<SVGLineElement>} */
    this.chartPoints = null;
    /** @type {HTMLDivElement} */
    this.chartElement = document.querySelector('#chart');
    /** @type {HTMLDivElement} */
    this.chartContainer = this.chartElement.parentElement;
    /** @type {HTMLAnchorElement} */
    this.buttonElement = document.querySelector('#altitudes');
  }

  /**
   * @return {ChartViewModel}
   */
  init() {
    this.chartSetup();
    this.setObservers();
    this.bindAll();
    return this;
  }

  chartSetup() {
    uUtils.addCss('css/dist/chartist.css', 'chartist_css');
    this.chart = ChartViewModel.getChart(this.chartElement, this.data);
    this.chart.on('created', () => this.onCreated());
  }

  static getChart(element, data) {
    return new LineChart(element, {
      series: [ data ]
    }, {
      lineSmooth: true,
      showArea: true,
      axisX: {
        type: AutoScaleAxis,
        onlyInteger: true,
        showLabel: false
      },
      plugins: [
        ctAxisTitle({
          axisY: {
            axisTitle: `${$._('altitude')} (${$.unit('unitDistance')} ${$.unit('unitAltitude')})`,
            axisClass: 'ct-axis-title',
            offset: {
              x: 0,
              y: 11
            },
            textAnchor: 'middle',
            flipTitle: true
          }
        })
      ]
    });
  }

  onCreated() {
    if (this.data.length && this.data.length <= LARGE_DATA) {
      this.chartPoints = document.querySelectorAll('.ct-series .ct-point');
      const len = this.chartPoints.length;
      for (let id = 0; id < len; id++) {
        this.chartPoints[id].addEventListener('click', () => {
          this.model.pointSelected = id;
        });
      }
    }
  }

  setObservers() {
    this.state.onChanged('currentTrack', (track) => {
      if (track) {
        uObserve.observe(track, 'positions', () => {
          this.onTrackUpdate(track, true);
        });
      }
      this.onTrackUpdate(track);
    });
    this.onChanged('buttonVisible', (visible) => this.renderButton(visible));
    this.onChanged('chartVisible', (visible) => this.renderContainer(visible));
    this.model.onChartToggle = () => {
      this.model.chartVisible = !this.model.chartVisible;
    };
    this.model.onMenuToggle = () => {
      if (this.model.chartVisible) {
        this.chart.update();
      }
    };
  }

  /**
   * @param {?uTrack} track
   * @param {boolean=} update
   */
  onTrackUpdate(track, update = false) {
    this.render(track, update);
    this.model.buttonVisible = !!track && track.hasPlotData;
  }

  /**
   * @param {boolean} isVisible
   */
  renderContainer(isVisible) {
    if (isVisible) {
      this.chartContainer.style.display = 'block';
      this.render(this.state.currentTrack);
    } else {
      this.chartContainer.style.display = 'none';
    }
  }

  /**
   * @param {boolean} isVisible
   */
  renderButton(isVisible) {
    if (isVisible) {
      this.buttonElement.classList.remove('menu-hidden');
    } else {
      this.buttonElement.classList.add('menu-hidden');
    }
  }

  /**
   * @param {?uTrack} track
   * @param {boolean=} update
   */
  render(track, update = false) {
    let data = [];
    if (track && track.hasPlotData && this.model.chartVisible) {
      data = track.plotData;
    } else {
      this.model.chartVisible = false;
    }
    if (update || this.data !== data) {
      console.log(`Chart${update ? ' forced' : ''} update (${data.length})`);
      this.data = data;
      const options = {
        lineSmooth: (data.length <= LARGE_DATA)
      };
      this.chart.update({ series: [ data ] }, options, true);
    }
  }

  /**
   * @param {number} pointId
   * @param {string} $className
   */
  pointAddClass(pointId, $className) {
    if (this.model.chartVisible && this.chartPoints && this.chartPoints.length > pointId) {
      const point = this.chartPoints[pointId];
      point.classList.add($className);
    }
  }

  /**
   * @param {string} $className
   */
  pointsRemoveClass($className) {
    if (this.model.chartVisible && this.chartPoints) {
      this.chartPoints.forEach((el) => el.classList.remove($className));
    }
  }

  /**
   * @param {number} pointId
   */
  onPointOver(pointId) {
    this.pointAddClass(pointId, 'ct-point-hilight');
  }

  onPointOut() {
    this.pointsRemoveClass('ct-point-hilight');
  }

  /**
   * @param {number} pointId
   */
  onPointSelect(pointId) {
    this.pointAddClass(pointId, 'ct-point-selected');
  }

  onPointUnselect() {
    this.pointsRemoveClass('ct-point-selected');
  }
}
