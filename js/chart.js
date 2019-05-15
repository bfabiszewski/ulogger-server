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
    binder.addEventListener(uEvent.UI_READY, this);
    binder.addEventListener(uEvent.TRACK_READY, this);
    this._binder = binder;
    this._targetEl = null;
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
      const points = document.querySelectorAll('.ct-chart-line .ct-point');
      for (let i = 0; i < points.length; i++) {
        ((id) => {
          points[id].addEventListener('click', () => {
            /** @todo trigger marker action */
            console.log(id);
          });
        })(i);
      }
      this._binder.dispatchEvent('chart ready', points.length);
    });

    // need to update chart first time the container becomes visible
    if (this._targetEl.parentNode.style.display !== 'block') {
      const observer = new MutationObserver(() => {
        if (this._targetEl.parentNode.style.display === 'block') {
          // eslint-disable-next-line no-underscore-dangle
          this._targetEl.__chartist__.update();
          observer.disconnect();
        }
      });
      observer.observe(this._targetEl.parentNode, { attributes: true });
    }

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
    }
  }

}
