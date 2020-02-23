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
import UserDialogModel from '../src/userdialogmodel.js';
import uDialog from '../src/dialog.js';
import uObserve from '../src/observe.js';
import uState from '../src/state.js';
import uUser from '../src/user.js';

describe('UserDialogModel tests', () => {

  let dm;
  let mockVM;
  let authUser;
  let newUser;
  let dialogType;

  beforeEach(() => {
    config.reinitialize();
    config.interval = 10;
    lang.init(config);
    authUser = new uUser(3, 'authUser');
    newUser = new uUser(2, 'newUser');
    auth.user = authUser;
    spyOn(lang, '_').and.returnValue('{placeholder}');
    mockVM = { state: new uState(), onUserDeleted: {}, onUserAdded: {} };
    dialogType = 'add';
    dm = new UserDialogModel(mockVM, dialogType);
    dm.user = new uUser(1, 'testUser');
    spyOn(dm.user, 'delete').and.returnValue(Promise.resolve());
    spyOn(dm.user, 'setPassword').and.returnValue(Promise.resolve());
    spyOn(dm.user, 'modify').and.callThrough();
    spyOn(uUser, 'update').and.returnValue(Promise.resolve());
    spyOn(auth.user, 'setPassword').and.returnValue(Promise.resolve());
    spyOn(uUser, 'add').and.returnValue(Promise.resolve(newUser));
    spyOn(config, 'validPassStrength').and.returnValue(true);
    spyOn(window, 'alert');
  });

  afterEach(() => {
    document.body.innerHTML = '';
    uObserve.unobserveAll(lang);
    auth.user = null;
  });

  it('should create instance with parent view model as parameter', () => {
    expect(dm).toBeDefined();
    expect(dm.userVM).toBe(mockVM);
    expect(dm.type).toBe(dialogType);
  });

  it('should show add dialog for current user', () => {
    // given
    dm.type = 'add';
    // when
    dm.init();
    // then
    expect(document.querySelector('#modal')).toBeInstanceOf(HTMLDivElement);
    expect(dm.dialog.element.querySelector("[data-bind='onUserAdd']")).toBeInstanceOf(HTMLButtonElement);
  });

  it('should show edit dialog for current user', () => {
    // given
    dm.type = 'edit';
    // when
    dm.init();
    // then
    expect(document.querySelector('#modal')).toBeInstanceOf(HTMLDivElement);
    expect(dm.dialog.element.querySelector("[data-bind='onUserUpdate']")).toBeInstanceOf(HTMLButtonElement);
    expect(dm.dialog.element.querySelector("[data-bind='onUserDelete']")).toBeInstanceOf(HTMLAnchorElement);
  });

  it('should show password change dialog for current user', () => {
    // given
    dm.type = 'pass';
    // when
    dm.init();
    // then
    expect(document.querySelector('#modal')).toBeInstanceOf(HTMLDivElement);
    expect(dm.dialog.element.querySelector("[data-bind='onPassChange']")).toBeInstanceOf(HTMLButtonElement);
    expect(dm.dialog.element.querySelector("[data-bind='onUserDelete']")).toBe(null);
  });

  it('should show confirmation dialog on user delete button click', (done) => {
    // given
    spyOn(uDialog, 'isConfirmed').and.returnValue(false);
    dm.type = 'edit';
    dm.init();
    const button = dm.dialog.element.querySelector("[data-bind='onUserDelete']");
    // when
    button.click();
    // then
    setTimeout(() => {
      expect(uDialog.isConfirmed).toHaveBeenCalledTimes(1);
      done();
    }, 100);
  });

  it('should delete user and hide dialog on confirmation dialog accepted', (done) => {
    // given
    spyOn(uDialog, 'isConfirmed').and.returnValue(true);
    spyOn(mockVM, 'onUserDeleted');
    dm.type = 'edit';
    dm.init();
    const button = dm.dialog.element.querySelector("[data-bind='onUserDelete']");
    // when
    button.click();
    // then
    setTimeout(() => {
      expect(dm.user.delete).toHaveBeenCalledTimes(1);
      expect(mockVM.onUserDeleted).toHaveBeenCalledTimes(1);
      expect(document.querySelector('#modal')).toBe(null);
      done();
    }, 100);
  });

  it('should update user password and hide edit dialog on positive button clicked', (done) => {
    // given
    spyOn(dm, 'validate').and.returnValue(true);
    dm.type = 'edit';
    dm.init();
    const button = dm.dialog.element.querySelector("[data-bind='onUserUpdate']");
    const passVisibility = dm.dialog.element.querySelector("[data-bind='passVisibility']");
    const passEl = dm.dialog.element.querySelector("[data-bind='password']");
    const newPassword = 'newpass';
    // when
    passVisibility.checked = true;
    passVisibility.dispatchEvent(new Event('change'));
    passEl.value = newPassword;
    passEl.dispatchEvent(new Event('change'));
    button.click();
    // then
    setTimeout(() => {
      expect(dm.user.modify).toHaveBeenCalledTimes(1);
      expect(dm.user.modify).toHaveBeenCalledWith(dm.model.admin, newPassword);
      expect(document.querySelector('#modal')).toBe(null);
      done();
    }, 100);
  });

  it('should toggle password input fields visibility on user edit form', (done) => {
    // given
    dm.type = 'edit';
    dm.init();
    const passInput = dm.getBoundElement('passInput');
    const passVisibility = dm.dialog.element.querySelector("[data-bind='passVisibility']");

    expect(passInput.style.display).toBe('none');
    // when
    passVisibility.checked = true;
    passVisibility.dispatchEvent(new Event('change'));
    // then
    setTimeout(() => {
      expect(passInput.style.display).toBe('block');
      // when
      passVisibility.checked = false;
      passVisibility.dispatchEvent(new Event('change'));
      // then
      setTimeout(() => {
        expect(passInput.style.display).toBe('none');
        done();
      }, 100);
      done();
    }, 100);
  });

  it('should update user admin status and hide edit dialog on positive button clicked', (done) => {
    // given
    spyOn(dm, 'validate').and.returnValue(true);
    dm.type = 'edit';
    dm.init();
    const button = dm.dialog.element.querySelector("[data-bind='onUserUpdate']");
    const adminEl = dm.dialog.element.querySelector("[data-bind='admin']");
    const isAdmin = true;
    // when
    adminEl.checked = isAdmin;
    adminEl.dispatchEvent(new Event('change'));
    button.click();
    // then
    setTimeout(() => {
      expect(dm.user.modify).toHaveBeenCalledTimes(1);
      expect(dm.user.modify).toHaveBeenCalledWith(isAdmin, null);
      expect(dm.user.isAdmin).toBeTrue();
      expect(document.querySelector('#modal')).toBe(null);
      done();
    }, 100);
  });

  it('should update other session user password and hide pass dialog on positive button clicked', (done) => {
    // given
    spyOn(dm, 'validate').and.returnValue(true);
    dm.type = 'pass';
    dm.init();
    const button = dm.dialog.element.querySelector("[data-bind='onPassChange']");
    const passEl = dm.dialog.element.querySelector("[data-bind='password']");
    const passOldEl = dm.dialog.element.querySelector("[data-bind='oldPassword']");
    const newPassword = 'newpass';
    const oldPassword = 'oldpass';
    // when
    passEl.value = newPassword;
    passEl.dispatchEvent(new Event('change'));
    passOldEl.value = oldPassword;
    passOldEl.dispatchEvent(new Event('change'));
    button.click();
    // then
    setTimeout(() => {
      expect(auth.user.setPassword).toHaveBeenCalledTimes(1);
      expect(auth.user.setPassword).toHaveBeenCalledWith(newPassword, oldPassword);
      expect(document.querySelector('#modal')).toBe(null);
      done();
    }, 100);
  });

  it('should add user and hide edit dialog on positive button clicked', (done) => {
    // given
    spyOn(dm, 'validate').and.returnValue(true);
    spyOn(mockVM, 'onUserAdded');
    dm.type = 'add';
    dm.init();
    const button = dm.dialog.element.querySelector("[data-bind='onUserAdd']");
    const loginEl = dm.dialog.element.querySelector("[data-bind='login']");
    const passEl = dm.dialog.element.querySelector("[data-bind='password']");
    const newPassword = 'newpass';
    // when
    loginEl.value = newUser.login;
    loginEl.dispatchEvent(new Event('change'));
    passEl.value = newPassword;
    passEl.dispatchEvent(new Event('change'));
    button.click();
    // then
    setTimeout(() => {
      expect(uUser.add).toHaveBeenCalledTimes(1);
      expect(uUser.add).toHaveBeenCalledWith(newUser.login, newPassword, false);
      expect(mockVM.onUserAdded).toHaveBeenCalledWith(newUser);
      expect(document.querySelector('#modal')).toBe(null);
      done();
    }, 100);
  });

  it('should hide dialog on negative button clicked', (done) => {
    // given
    dm.init();
    const button = dm.dialog.element.querySelector("[data-bind='onCancel']");
    // when
    button.click();
    // then
    setTimeout(() => {
      expect(document.querySelector('#modal')).toBe(null);
      done();
    }, 100);
  });

  it('should return true on add user dialog validate', () => {
    // given
    dm.type = 'add';
    dm.model.login = 'test';
    dm.model.password = 'password';
    dm.model.password2 = 'password';
    // when
    const result = dm.validate();
    // then
    expect(result).toBe(true);
    expect(window.alert).not.toHaveBeenCalled();
  });

  it('should return false on add user dialog empty login', () => {
    // given
    dm.type = 'add';
    dm.model.login = '';
    dm.model.password = 'password';
    dm.model.password2 = 'password';
    // when
    const result = dm.validate();
    // then
    expect(result).toBe(false);
    expect(window.alert).toHaveBeenCalledTimes(1);
  });

  it('should return false on password change dialog empty old password', () => {
    // given
    dm.type = 'pass';
    dm.model.login = 'test';
    dm.model.password = 'password';
    dm.model.password2 = 'password';
    dm.model.oldPassword = '';
    // when
    const result = dm.validate();
    // then
    expect(result).toBe(false);
    expect(window.alert).toHaveBeenCalledTimes(1);
  });

  it('should return false on add user dialog passwords not match', () => {
    // given
    dm.model.login = 'test';
    dm.model.passVisibility = true;
    dm.model.password = 'password';
    dm.model.password2 = 'password2';
    // when
    const result = dm.validate();
    // then
    expect(result).toBe(false);
    expect(window.alert).toHaveBeenCalledTimes(1);
  });

  it('should return true and ignore passwords on add user dialog passwords hidden', () => {
    // given
    dm.model.login = 'test';
    dm.model.passVisibility = false;
    dm.model.password = 'password';
    dm.model.password2 = 'password2';
    // when
    const result = dm.validate();
    // then
    expect(result).toBe(true);
    expect(window.alert).toHaveBeenCalledTimes(0);
  });

  it('should test password regex on dialog validate', () => {
    // given
    const password = 'password';
    dm.model.login = 'test';
    dm.model.passVisibility = true;
    dm.model.password = password;
    dm.model.password2 = password;
    // when
    dm.validate();
    // then
    expect(config.validPassStrength).toHaveBeenCalledWith(password);
  });
});
