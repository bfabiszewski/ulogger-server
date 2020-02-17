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

import Fixture from './helpers/fixture.js';
import MainViewModel from '../src/mainviewmodel.js';
import ViewModel from '../src/viewmodel.js';
import uState from '../src/state.js';

describe('MainViewModel tests', () => {

  const hiddenClass = 'menu-hidden';
  let vm;
  let state;
  let menuEl;
  let userMenuEl;
  let userButtonEl;
  let menuButtonEl;

  beforeEach((done) => {
    Fixture.load('main-authorized.html')
      .then(() => done())
      .catch((e) => done.fail(e));
  });

  beforeEach(() => {
    menuEl = document.querySelector('#menu');
    userMenuEl = document.querySelector('#user-menu');
    userButtonEl = document.querySelector('a[data-bind="onShowUserMenu"]');
    menuButtonEl = document.querySelector('#menu-button a');
    spyOn(window, 'addEventListener');
    spyOn(window, 'removeEventListener').and.callThrough();
    state = new uState();
    vm = new MainViewModel(state);
  });

  afterEach(() => {
    Fixture.clear();
  });

  it('should create instance', () => {
    expect(vm).toBeInstanceOf(ViewModel);
    expect(vm.state).toBe(state);
    expect(vm.menuEl).toBe(menuEl);
    expect(vm.userMenuEl).toBe(userMenuEl);
  });

  it('should hide side menu', (done) => {
    // given
    vm.init();
    // when
    menuButtonEl.click();
    // then
    setTimeout(() => {
      expect(menuEl.classList.contains(hiddenClass)).toBe(true);
      done();
    }, 100);
  });

  it('should show side menu', (done) => {
    // given
    menuEl.classList.add(hiddenClass);
    vm.init();
    // when
    menuButtonEl.click();
    // then
    setTimeout(() => {
      expect(menuEl.classList.contains(hiddenClass)).toBe(false);
      done();
    }, 100);
  });

  it('should hide user menu', (done) => {
    // given
    userMenuEl.classList.remove(hiddenClass);
    vm.init();
    // when
    userButtonEl.click();
    // then
    setTimeout(() => {
      expect(userMenuEl.classList.contains(hiddenClass)).toBe(true);
      done();
    }, 100);
  });

  it('should show user menu', (done) => {
    // given
    vm.init();
    // when
    userButtonEl.click();
    // then
    setTimeout(() => {
      expect(userMenuEl.classList.contains(hiddenClass)).toBe(false);
      expect(window.addEventListener).toHaveBeenCalledTimes(1);
      expect(window.addEventListener).toHaveBeenCalledWith('click', vm.hideUserMenuCallback, true);
      done();
    }, 100);
  });

  it('should hide user menu on window click', (done) => {
    // given
    userMenuEl.classList.remove(hiddenClass);
    window.addEventListener.and.callThrough();
    window.addEventListener('click', vm.hideUserMenuCallback, true);
    vm.init();
    // when
    document.body.click();
    // then
    setTimeout(() => {
      expect(userMenuEl.classList.contains(hiddenClass)).toBe(true);
      expect(window.removeEventListener).toHaveBeenCalledTimes(1);
      expect(window.removeEventListener).toHaveBeenCalledWith('click', vm.hideUserMenuCallback, true);
      done();
    }, 100);
  });

});
