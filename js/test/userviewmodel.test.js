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
import UserViewModel from '../src/userviewmodel.js';
import ViewModel from '../src/viewmodel.js';
import uObserve from '../src/observe.js';
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

  beforeEach(() => {
    const fixture = `<div id="fixture">
                      <div class="section">
                        <label for="user">user</label>
                        <select id="user" data-bind="currentUserId" name="user"></select>
                      </div>
                    </div>`;
    document.body.insertAdjacentHTML('afterbegin', fixture);
    userEl = document.querySelector('#user');
    config.initialize();
    lang.init(config);
    lang.strings['suser'] = 'select user';
    lang.strings['allusers'] = 'all users';
    state = new uState();
    user1 = new uUser(1, 'user1');
    user2 = new uUser(2, 'user2');
    users = [ user1, user2 ];
  });

  afterEach(() => {
    document.body.removeChild(document.querySelector('#fixture'));
    auth.user = null;
  });

  it('should create instance with state as parameter and load user list and select first user on list', (done) => {
    // given
    spyOn(uUser, 'fetchList').and.returnValue(Promise.resolve(users));
    // when
    const vm = new UserViewModel(state);
    // then
    setTimeout(() => {
      expect(vm).toBeInstanceOf(ViewModel);
      expect(vm.select.element).toBeInstanceOf(HTMLSelectElement);
      expect(vm.state).toBe(state);
      expect(vm.model.userList.length).toBe(users.length);
      expect(userEl.value).toBe(user1.listValue);
      expect(userEl.options.length).toBe(users.length + 1);
      expect(userEl.options[1].selected).toBe(true);
      expect(userEl.options[1].value).toBe(user1.listValue);
      done();
    }, 100);
  });

  it('should create instance with state as parameter and load user list and select authorized user on list', (done) => {
    // given
    spyOn(uUser, 'fetchList').and.returnValue(Promise.resolve(users));
    // when
    auth.user = user2;
    const vm = new UserViewModel(state);
    // then
    setTimeout(() => {
      expect(vm).toBeInstanceOf(ViewModel);
      expect(vm.select.element).toBeInstanceOf(HTMLSelectElement);
      expect(vm.state).toBe(state);
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
    spyOn(UserViewModel.prototype, 'init');
    const vm = new UserViewModel(state);
    uObserve.setSilently(state, 'currentUser', user1);
    uObserve.setSilently(vm.model, 'userList', users);
    uObserve.setSilently(vm.model, 'currentUserId', user1.listValue);
    const options = '<option selected value="1">user1</option><option value="2">user2</option>';
    userEl.insertAdjacentHTML('beforeend', options);
    const optLength = userEl.options.length;
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
    spyOn(UserViewModel.prototype, 'init');
    const vm = new UserViewModel(state);
    uObserve.setSilently(state, 'currentUser', user1);
    uObserve.setSilently(state, 'showAllUsers', false);
    uObserve.setSilently(vm.model, 'userList', users);
    uObserve.setSilently(vm.model, 'currentUserId', user1.listValue);
    const options = `<option value="${uSelect.allValue}">all users</option><option selected value="1">user1</option><option value="2">user2</option>`;
    userEl.insertAdjacentHTML('beforeend', options);
    const optLength = userEl.options.length;
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
    spyOn(UserViewModel.prototype, 'init');
    const vm = new UserViewModel(state);
    uObserve.setSilently(state, 'currentUser', user1);
    uObserve.setSilently(state, 'showAllUsers', false);
    uObserve.setSilently(vm.model, 'userList', users);
    uObserve.setSilently(vm.model, 'currentUserId', user1.listValue);
    const options = '<option selected value="1">user1</option><option value="2">user2</option>';
    userEl.insertAdjacentHTML('beforeend', options);
    const optLength = userEl.options.length;
    const listLength = vm.model.userList.length;
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
    spyOn(UserViewModel.prototype, 'init');
    const vm = new UserViewModel(state);
    uObserve.setSilently(state, 'currentUser', user1);
    uObserve.setSilently(state, 'showAllUsers', false);
    uObserve.setSilently(state, 'showLatest', true);
    uObserve.setSilently(vm.model, 'userList', users);
    uObserve.setSilently(vm.model, 'currentUserId', user1.listValue);
    const options = `<option value="${uSelect.allValue}">all users</option><option selected value="1">user1</option><option value="2">user2</option>`;
    userEl.insertAdjacentHTML('beforeend', options);
    const optLength = userEl.options.length;
    const listLength = vm.model.userList.length;
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

});
