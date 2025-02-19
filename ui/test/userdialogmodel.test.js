/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import { auth, config, lang } from '../src/Initializer.js';
import Alert from '../src/Alert.js';
import Dialog from '../src/Dialog.js';
import Http from '../src/Http';
import Observer from '../src/Observer.js';
import State from '../src/State.js';
import User from '../src/User.js';
import UserDialogModel from '../src/models/UserDialogModel.js';

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
    authUser = new User(3, 'authUser');
    newUser = new User(2, 'newUser');
    auth.user = authUser;
    spyOn(lang, '_').and.returnValue('{placeholder}');
    mockVM = { state: new State(), onUserDeleted: {}, onUserAdded: {} };
    dialogType = 'add';
    dm = new UserDialogModel(mockVM, dialogType);
    dm.user = new User(1, 'testUser');
    spyOn(dm.user, 'delete').and.resolveTo();
    spyOn(dm.user, 'setPassword').and.resolveTo();
    spyOn(dm.user, 'modify').and.resolveTo();
    // spyOn(User, 'update').and.resolveTo();
    spyOn(auth.user, 'setPassword').and.resolveTo();
    spyOn(User, 'add').and.resolveTo(newUser);
    spyOn(config, 'validPassStrength').and.returnValue(true);
    spyOn(Alert, 'error');
  });

  afterEach(() => {
    document.body.innerHTML = '';
    Observer.unobserveAll(lang);
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
    spyOn(Dialog, 'isConfirmed').and.returnValue(false);
    dm.type = 'edit';
    dm.init();
    const button = dm.dialog.element.querySelector("[data-bind='onUserDelete']");
    // when
    button.click();
    // then
    setTimeout(() => {
      expect(Dialog.isConfirmed).toHaveBeenCalledTimes(1);
      done();
    }, 100);
  });

  it('should delete user and hide dialog on confirmation dialog accepted', (done) => {
    // given
    spyOn(Dialog, 'isConfirmed').and.returnValue(true);
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
    }, 100);
  });

  it('should update user admin status and hide edit dialog on positive button clicked', (done) => {
    // given
    spyOn(dm, 'validate').and.returnValue(true);
    dm.user.modify.and.callThrough();
    spyOn(Http, 'put').and.resolveTo();
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
      expect(User.add).toHaveBeenCalledTimes(1);
      expect(User.add).toHaveBeenCalledWith(newUser.login, newPassword, false);
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
    expect(Alert.error).not.toHaveBeenCalled();
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
    expect(Alert.error).toHaveBeenCalledTimes(1);
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
    expect(Alert.error).toHaveBeenCalledTimes(1);
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
    expect(Alert.error).toHaveBeenCalledTimes(1);
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
    expect(Alert.error).toHaveBeenCalledTimes(0);
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
