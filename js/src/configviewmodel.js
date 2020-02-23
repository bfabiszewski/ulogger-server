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

import { lang as $, config } from './initializer.js';
import ConfigDialogModel from './configdialogmodel.js';
import ViewModel from './viewmodel.js';
import uUtils from './utils.js';

/**
 * @class ConfigViewModel
 */
export default class ConfigViewModel extends ViewModel {
  /**
   * @param {uState} state
   */
  constructor(state) {
    super(config);
    this.state = state;
    this.model.onSetInterval = () => this.setAutoReloadInterval();
    this.model.onConfigEdit = () => this.showConfigDialog();
  }

  /**
   * @return {ConfigViewModel}
   */
  init() {
    this.setObservers();
    this.bindAll();
    return this;
  }

  setObservers() {
    this.onChanged('mapApi', (api) => {
      uUtils.setCookie('api', api);
    });
    this.onChanged('lang', (_lang) => {
      uUtils.setCookie('lang', _lang);
      ConfigViewModel.reload();
    });
    this.onChanged('units', (units) => {
      uUtils.setCookie('units', units);
      ConfigViewModel.reload();
    });
    this.onChanged('interval', (interval) => {
      uUtils.setCookie('interval', interval);
    });
  }

  static reload() {
    window.location.reload();
  }

  setAutoReloadInterval() {
    const interval = parseInt(prompt($._('newinterval')));
    if (!isNaN(interval) && interval !== this.model.interval) {
      this.model.interval = interval;
    }
  }

  showConfigDialog() {
    const vm = new ConfigDialogModel(this);
    vm.init();
  }
}
