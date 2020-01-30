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

import { lang as $ } from './initializer.js';
import Chartist from 'chartist'
import ViewModel from './viewmodel.js';
import ctAxisTitle from 'chartist-plugin-axistitle';
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
    /** @type {?Chartist.Line} */
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
    uUtils.addCss('css/chartist.min.css', 'chartist_css');
    this.chart = new Chartist.Line(this.chartElement, {
      series: [ this.data ]
    }, {
      lineSmooth: true,
      showArea: true,
      axisX: {
        type: Chartist.AutoScaleAxis,
        onlyInteger: true,
        showLabel: false
      },
      plugins: [
        ctAxisTitle({
          axisY: {
            axisTitle: `${$._('altitude')} (${$.unit('unitDistance')})`,
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

    this.chart.on('created', () => this.onCreated());
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
      this.render();
      this.model.buttonVisible = !!track && track.hasPlotData;
    });
    this.onChanged('buttonVisible', (visible) => this.renderButton(visible));
    this.onChanged('chartVisible', (visible) => this.renderContainer(visible));
    this.model.onChartToggle = () => {
      this.model.chartVisible = !this.model.chartVisible;
    };
    this.model.onMenuToggle = () => {
      this.chart.update();
    };
  }

  /**
   * @param {boolean} isVisible
   */
  renderContainer(isVisible) {
    if (isVisible) {
      this.chartContainer.style.display = 'block';
      this.render();
    } else {
      this.chartContainer.style.display = 'none';
    }
  }

  /**
   * @param {boolean} isVisible
   */
  renderButton(isVisible) {
    if (isVisible) {
      this.buttonElement.style.visibility = 'visible';
    } else {
      this.buttonElement.style.visibility = 'hidden';
    }
  }

  render() {
    let data = [];
    if (this.state.currentTrack && this.state.currentTrack.hasPlotData && this.model.chartVisible) {
      data = this.state.currentTrack.plotData;
    } else {
      this.model.chartVisible = false;
    }
    if (this.data !== data) {
      console.log(`Chart update (${data.length})`);
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
    if (this.model.chartVisible && this.chartPoints.length > pointId) {
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
