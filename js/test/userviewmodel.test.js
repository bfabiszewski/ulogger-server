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

import { auth, config, lang } from '../src/initializer.js';
import Fixture from './helpers/fixture.js';
import UserViewModel from '../src/userviewmodel.js';
import ViewModel from '../src/viewmodel.js';
import uSelect from '../src/select.js';
import uState from '../src/state.js';
import uUser from '../src/user.js';

describe('UserViewModel tests', () => {

  let state;
  let user1;
  let user2;
  let users;
  /** @type {HTMLSelectElement} */
  let userEl;
  let userEditEl;
  let userAddEl;
  let userPassEl;
  let vm;

  beforeEach((done) => {
    Fixture.load('main-authorized.html')
      .then(() => done())
      .catch((e) => done.fail(e));
  });

  beforeEach(() => {
    userEl = document.querySelector('#user');
    userEditEl = document.querySelector('#edituser');
    userAddEl = document.querySelector('#adduser');
    userPassEl = document.querySelector('#user-pass');
    config.reinitialize();
    lang.init(config);
    lang.strings['suser'] = 'select user';
    lang.strings['allusers'] = 'all users';
    state = new uState();
    user1 = new uUser(1, 'user1');
    user2 = new uUser(2, 'user2');
    users = [ user1, user2 ];
    vm = new UserViewModel(state);
  });

  afterEach(() => {
    Fixture.clear();
    auth.user = null;
  });

  it('should create instance with state as parameter', () => {
    expect(vm).toBeInstanceOf(ViewModel);
    expect(vm.select.element).toBeInstanceOf(HTMLSelectElement);
    expect(vm.state).toBe(state);
    expect(userEl.value).toBe('0');
    expect(userEl.options.length).toBe(1);
    expect(userEl.options[0].selected).toBe(true);
    expect(userEl.options[0].value).toBe('0');
  });

  it('should load user list and select first user on list', (done) => {
    // given
    spyOn(uUser, 'fetchList').and.returnValue(Promise.resolve(users));
    // when
    vm.init();
    // then
    setTimeout(() => {
      expect(vm.model.userList.length).toBe(users.length);
      expect(userEl.value).toBe(user1.listValue);
      expect(userEl.options.length).toBe(users.length + 1);
      expect(userEl.options[1].selected).toBe(true);
      expect(userEl.options[1].value).toBe(user1.listValue);
      done();
    }, 100);
  });

  it('should load user list and select authorized user on list', (done) => {
    // given
    spyOn(uUser, 'fetchList').and.returnValue(Promise.resolve(users));
    // when
    auth.user = user2;
    vm.init();
    // then
    setTimeout(() => {
      expect(vm.model.userList.length).toBe(users.length);
      expect(userEl.value).toBe(user2.listValue);
      expect(userEl.options.length).toBe(users.length + 1);
      expect(userEl.options[2].selected).toBe(true);
      expect(userEl.options[2].value).toBe(user2.listValue);
      done();
    }, 100);
  });

  it('should change current user on user list option selected', (done) => {
    // given
    state.currentUser = user1;
    vm.model.userList = users;
    vm.model.currentUserId = user1.listValue;
    const options = '<option selected value="1">user1</option><option value="2">user2</option>';
    userEl.insertAdjacentHTML('beforeend', options);
    const optLength = userEl.options.length;
    vm.setObservers(state);
    vm.bindAll();
    // when
    userEl.value = user2.listValue;
    userEl.dispatchEvent(new Event('change'));
    // then
    setTimeout(() => {
      expect(state.currentUser).toBe(user2);
      expect(userEl.options.length).toBe(optLength);
      expect(userEl.value).toBe(user2.listValue);
      expect(userEl.options[2].selected).toBe(true);
      expect(userEl.options[2].value).toBe(user2.listValue);
      done();
    }, 100);
  });

  it('should set showAllUsers state on "all users" option selected', (done) => {
    // given
    state.currentUser = user1;
    state.showAllUsers = false;
    vm.model.userList = users;
    vm.model.currentUserId = user1.listValue;
    const options = `<option value="${uSelect.allValue}">all users</option><option selected value="1">user1</option><option value="2">user2</option>`;
    userEl.insertAdjacentHTML('beforeend', options);
    const optLength = userEl.options.length;
    vm.setObservers(state);
    vm.bindAll();
    // when
    userEl.value = uSelect.allValue;
    userEl.dispatchEvent(new Event('change'));
    // then
    setTimeout(() => {
      expect(state.showAllUsers).toBe(true);
      expect(state.currentUser).toBe(null);
      expect(userEl.value).toBe(uSelect.allValue);
      expect(userEl.options.length).toBe(optLength);
      expect(userEl.options[1].selected).toBe(true);
      expect(userEl.options[1].value).toBe(uSelect.allValue);
      done();
    }, 100);
  });

  it('should add "all users" option when "showLatest" state is set', (done) => {
    // given
    state.currentUser = user1;
    state.showAllUsers = false;
    vm.model.userList = users;
    vm.model.currentUserId = user1.listValue;
    const options = '<option selected value="1">user1</option><option value="2">user2</option>';
    userEl.insertAdjacentHTML('beforeend', options);
    const optLength = userEl.options.length;
    const listLength = vm.model.userList.length;
    vm.setObservers(state);
    vm.bindAll();
    // when
    state.showLatest = true;
    // then
    setTimeout(() => {
      expect(state.showAllUsers).toBe(false);
      expect(state.currentUser).toBe(user1);
      expect(vm.select.hasAllOption).toBe(true);
      expect(userEl.value).toBe(user1.listValue);
      expect(userEl.options.length).toBe(optLength + 1);
      expect(vm.model.userList.length).toBe(listLength);
      expect(userEl.options[1].selected).toBe(false);
      expect(userEl.options[1].value).toBe(uSelect.allValue);
      expect(userEl.options[2].selected).toBe(true);
      expect(userEl.options[2].value).toBe(user1.listValue);
      done();
    }, 100);
  });

  it('should remove "all users" option when "showLatest" state is unset', (done) => {
    // given
    state.currentUser = user1;
    state.showAllUsers = false;
    state.showLatest = true;
    vm.model.userList = users;
    vm.model.currentUserId = user1.listValue;
    const options = `<option value="${uSelect.allValue}">all users</option><option selected value="1">user1</option><option value="2">user2</option>`;
    userEl.insertAdjacentHTML('beforeend', options);
    const optLength = userEl.options.length;
    const listLength = vm.model.userList.length;
    vm.setObservers(state);
    vm.bindAll();
    // when
    state.showLatest = false;
    // then
    setTimeout(() => {
      expect(state.showAllUsers).toBe(false);
      expect(state.currentUser).toBe(user1);
      expect(vm.select.hasAllOption).toBe(false);
      expect(userEl.value).toBe(user1.listValue);
      expect(userEl.options.length).toBe(optLength - 1);
      expect(vm.model.userList.length).toBe(listLength);
      expect(userEl.options[1].selected).toBe(true);
      expect(userEl.options[1].value).toBe(user1.listValue);
      done();
    }, 100);
  });

  it('should show user edit dialog on button click', (done) => {
    // given
    spyOn(vm, 'showDialog');
    // when
    vm.bindAll();
    userEditEl.click();
    // then
    setTimeout(() => {
      expect(vm.showDialog).toHaveBeenCalledWith('edit');
      done();
    }, 100);
  });

  it('should show user add dialog on button click', (done) => {
    // given
    spyOn(vm, 'showDialog');
    // when
    vm.bindAll();
    userAddEl.click();
    // then
    setTimeout(() => {
      expect(vm.showDialog).toHaveBeenCalledWith('add');
      done();
    }, 100);
  });

  it('should show password change dialog on button click', (done) => {
    // given
    spyOn(vm, 'showDialog');
    // when
    vm.bindAll();
    userPassEl.click();
    // then
    setTimeout(() => {
      expect(vm.showDialog).toHaveBeenCalledWith('pass');
      done();
    }, 100);
  });

  it('should add new user to user list in alphabetic order', () => {
    // given
    user1 = new uUser(1, 'b');
    user2 = new uUser(2, 'a');
    vm.model.userList = [ user1 ];
    // when
    vm.onUserAdded(user2);
    // then
    expect(vm.model.userList.length).toBe(2);
    expect(vm.model.userList[0]).toBe(user2);
  });

  it('should remove current user from user list and set new current user id', () => {
    // given
    vm.model.userList = [ user1, user2 ];
    vm.state.currentUser = user1;
    vm.model.currentUserId = user1.listValue;
    // when
    vm.onUserDeleted();
    // then
    expect(vm.model.userList.length).toBe(1);
    expect(vm.model.currentUserId).toBe(user2.listValue);
    expect(vm.state.currentUser).toBe(null);
  });

  it('should remove last remaining element from user list and set empty user id', () => {
    // given
    vm.model.userList = [ user1 ];
    vm.state.currentUser = user1;
    vm.model.currentUserId = user1.listValue;
    // when
    vm.onUserDeleted();
    // then
    expect(vm.model.userList.length).toBe(0);
    expect(vm.model.currentUserId).toBe('0');
    expect(vm.state.currentUser).toBe(null);
  });

  it('show hide element', () => {
    // given
    const element = document.createElement('div');
    // when
    UserViewModel.setMenuVisible(element, false);
    // then
    expect(element.classList.contains('menu-hidden')).toBe(true);
  });

  it('show shown hidden element', () => {
    // given
    const element = document.createElement('div');
    element.classList.add('menu-hidden');
    // when
    UserViewModel.setMenuVisible(element, true);
    // then
    expect(element.classList.contains('menu-hidden')).toBe(false);
  });

});
