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
import uUtils from './utils.js';

/* global Chartist */

document.addEventListener('DOMContentLoaded', () => {
  Chart.onDomLoaded();
});

export default class Chart {
  /**
   * @param {uBinder} binder
   */
  constructor(binder) {
    binder.addEventListener(uEvent.MARKER_OVER, this);
    binder.addEventListener(uEvent.MARKER_SELECT, this);
    binder.addEventListener(uEvent.TRACK_READY, this);
    binder.addEventListener(uEvent.UI_READY, this);
    this._binder = binder;
    this._targetEl = null;
    this._points = null;
  }

  /**
   * @return {Array<{x: number, y: number}>}
   */
  get data() {
    return this._data;
  }

  render() {
    if (!this._targetEl) {
      return;
    }
    const chart = new Chartist.Line(this._targetEl, {
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
        Chartist.plugins.ctAxisTitle({
          axisY: {
            axisTitle: `${lang.strings['altitude']} (${config.unit_m})`,
            axisClass: 'ct-axis-title',
            offset: {
              x: 0,
              y: 20
            },
            textAnchor: 'middle',
            flipTitle: true
          }
        })
      ]
    });

    chart.on('created', () => {
      this._points = document.querySelectorAll('.ct-chart-line .ct-point');
      const len = this._points.length;
      for (let i = 0; i < len; i++) {
        ((id) => {
          this._points[id].addEventListener('click', () => {
            this._binder.dispatchEvent(uEvent.CHART_CLICKED, id);
          });
        })(i);
      }
      this._binder.dispatchEvent(uEvent.CHART_READY, len);
    });

    // need to update chart first time the container becomes visible
    if (!this.isVisible()) {
      const observer = new MutationObserver(() => {
        if (this.isVisible()) {
          // eslint-disable-next-line no-underscore-dangle
          this._targetEl.__chartist__.update();
          observer.disconnect();
        }
      });
      observer.observe(this._targetEl.parentNode, { attributes: true });
    }
  }

  isVisible() {
    return this._targetEl && this._targetEl.parentNode && this._targetEl.parentNode.style.display === 'block';
  }

  static onDomLoaded() {
    uUtils.addScript('js/lib/chartist.min.js', 'chartist_js', () => {
      uUtils.addScript('js/lib/chartist-plugin-axistitle.min.js', 'chartist_axistitle_js');
    });
    uUtils.addCss('css/chartist.min.css', 'chartist_css');
  }

  /**
   * @param {uEvent} event
   * @param {*=} args
   */
  handleEvent(event, args) {
    if (event.type === uEvent.TRACK_READY) {
      /** @type {uTrack} */
      const track = args;
      this._data = track.plotData;
      this.render()
    } else if (event.type === uEvent.UI_READY) {
      /** @type {uUI} */
      const ui = args;
      this._targetEl = ui.chart;
    } else if (event.type === uEvent.MARKER_OVER) {
      /** @type {number} */
      const pointId = args;
      if (pointId) {
        this.pointOver(pointId);
      } else {
        this.pointOut();
      }
    } else if (event.type === uEvent.MARKER_SELECT) {
      /** @type {number} */
      const pointId = args;
      if (pointId) {
        this.pointSelect(pointId);
      } else {
        this.pointUnselect();
      }
    }
  }

  pointOver(pointId) {
    if (this.isVisible()) {
      const point = this._points[pointId];
      point.classList.add('ct-point-hilight');
    }
  }

  pointOut() {
    this._targetEl.querySelectorAll('.ct-point-hilight').forEach((el) => el.classList.remove('ct-point-hilight'));
  }

  pointSelect(pointId) {
    if (this.isVisible()) {
      const point = this._points[pointId];
      point.classList.add('ct-point-selected');
    }
  }

  pointUnselect() {
    this._targetEl.querySelectorAll('.ct-point-selected').forEach((el) => el.classList.remove('ct-point-selected'));
  }
}
