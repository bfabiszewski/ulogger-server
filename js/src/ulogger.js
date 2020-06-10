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

import { lang as $, config, initializer, uInitializer } from './initializer.js';
import ChartViewModel from './chartviewmodel.js';
import ConfigViewModel from './configviewmodel.js';
import MainViewModel from './mainviewmodel.js';
import MapViewModel from './mapviewmodel.js';
import TrackViewModel from './trackviewmodel.js';
import UserViewModel from './userviewmodel.js';
import uAlert from './alert.js';
import uPermalink from './permalink.js';
import uSpinner from './spinner.js';
import uState from './state.js';

const domReady = uInitializer.waitForDom();
const initReady = initializer.initialize();
const initLink = uPermalink.parseHash();

Promise.all([ domReady, initReady, initLink ])
  .then((result) => {
    start(result[2]);
  })
  .catch((msg) => uAlert.error(`${$._('actionfailure')}\n${msg}`));

/**
 * @param {?Object} linkState
 */
function start(linkState) {
  const state = new uState();
  const permalink = new uPermalink(state);
  const spinner = new uSpinner(state);
  const mainVM = new MainViewModel(state);
  const userVM = new UserViewModel(state);
  const trackVM = new TrackViewModel(state);
  const mapVM = new MapViewModel(state);
  const chartVM = new ChartViewModel(state);
  const configVM = new ConfigViewModel(state);
  permalink.init().onPop(linkState);
  spinner.init();
  mainVM.init();
  userVM.init();
  trackVM.init();
  mapVM.init().loadMapAPI(config.mapApi);
  chartVM.init();
  configVM.init();

  mapVM.onChanged('markerOver', (id) => {
    if (id !== null) {
      chartVM.onPointOver(id);
    } else {
      chartVM.onPointOut();
    }
  });
  mapVM.onChanged('markerSelect', (id) => {
    if (id !== null) {
      chartVM.onPointSelect(id);
    } else {
      chartVM.onPointUnselect();
    }
  });
  chartVM.onChanged('pointSelected', (id) => {
    if (id !== null) {
      mapVM.api.animateMarker(id);
    }
  });
}
