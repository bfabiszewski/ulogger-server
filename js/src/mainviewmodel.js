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

import ViewModel from './viewmodel.js';

const hiddenClass = 'menu-hidden';

export default class MainViewModel extends ViewModel {

  /**
   * @param {uState} state
   */
  constructor(state) {
    super({
      onMenuToggle: null,
      onShowUserMenu: null
    });
    this.state = state;
    this.model.onMenuToggle = () => this.toggleSideMenu();
    this.model.onShowUserMenu = () => this.toggleUserMenu();
    this.hideUserMenuCallback = (e) => this.hideUserMenu(e);
    this.menuEl = document.querySelector('#menu');
    this.userMenuEl = document.querySelector('#user-menu');
  }

  /**
   * @return {MainViewModel}
   */
  init() {
    this.bindAll();
    return this;
  }

  toggleSideMenu() {
    if (this.menuEl.classList.contains(hiddenClass)) {
      this.menuEl.classList.remove(hiddenClass);
    } else {
      this.menuEl.classList.add(hiddenClass);
    }
  }

  /**
   * Toggle user menu visibility
   */
  toggleUserMenu() {
    if (this.userMenuEl.classList.contains(hiddenClass)) {
      this.userMenuEl.classList.remove(hiddenClass);
      window.addEventListener('click', this.hideUserMenuCallback, true);
    } else {
      this.userMenuEl.classList.add(hiddenClass);
    }
  }

  /**
   * Click listener callback to hide user menu
   * @param {MouseEvent} event
   */
  hideUserMenu(event) {
    const el = event.target;
    this.userMenuEl.classList.add(hiddenClass);
    window.removeEventListener('click', this.hideUserMenuCallback, true);
    if (el.parentElement.id !== 'user-menu') {
      event.stopPropagation();
    }
  }

}
