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
import uState from './state.js';

const domReady = uInitializer.waitForDom();
const initReady = initializer.initialize();

Promise.all([ domReady, initReady ])
  .then(() => {
    start();
  })
  .catch((msg) => alert(`${$._('actionfailure')}\n${msg}`));


function start() {
  const state = new uState();

  const mainVM = new MainViewModel(state);
  const userVM = new UserViewModel(state);
  const trackVM = new TrackViewModel(state);
  const mapVM = new MapViewModel(state);
  const chartVM = new ChartViewModel(state);
  const configVM = new ConfigViewModel(state);
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
