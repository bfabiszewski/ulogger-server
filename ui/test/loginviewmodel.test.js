/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import { auth, config, lang } from '../src/Initializer.js';
import Fixture from './helpers/fixture.js';
import LoginViewModel from '../src/models/LoginViewModel';
import Router from '../src/Router.js';
import State from '../src/State.js';
import ViewModel from '../src/ViewModel.js';

describe('LoginViewModel tests', () => {

  let state;
  let vm;

  beforeEach((done) => {
    Fixture.load('main.html')
      .then(() => done())
      .catch((e) => done.fail(e));
  });

  beforeEach(() => {
    config.reinitialize();
    lang.init(config);
    spyOn(lang, '_').and.returnValue('{placeholder}');
    state = new State();
    vm = new LoginViewModel(state);
  });

  afterEach(() => {
    Fixture.clear();
  });

  it('should create instance with state as parameter', () => {
    expect(vm).toBeInstanceOf(ViewModel);
    expect(vm.state).toBe(state);
  });

  it('should initialize instance and load html with cancel button', () => {
    // given
    expect(document.querySelector('#login')).toBeNull();
    config.requireAuthentication = false;
    // when
    vm.init();
    // then
    expect(document.querySelector('#login')).toBeInstanceOf(HTMLDivElement);
    expect(document.querySelector('#cancel')).toBeInstanceOf(HTMLButtonElement);
  });

  it('should initialize instance and load html without cancel button', () => {
    // given
    expect(document.querySelector('#login')).toBeNull();
    config.requireAuthentication = true;
    // when
    vm.init();
    // then
    expect(document.querySelector('#login')).toBeInstanceOf(HTMLDivElement);
    expect(document.querySelector('#cancel')).toBeNull();
  });

  it('should login when button clicked', (done) => {
    // given
    spyOn(auth, 'login').and.resolveTo();
    spyOn(Router, 'initView');
    const login = 'testLogin';
    const password = 'testPassword';
    // when
    vm.init();
    setTimeout(() => {
      document.querySelector('#login-user').value = login;
      document.querySelector('#login-user').dispatchEvent(new Event('change'));
      document.querySelector('#login-pass').value = password;
      document.querySelector('#login-pass').dispatchEvent(new Event('change'));
      document.querySelector('#login-button').click();
      setTimeout(() => {
        // then
        expect(auth.login).toHaveBeenCalledWith(login, password);
        expect(Router.initView).toHaveBeenCalledTimes(1);
        done();
      }, 100);
    }, 100);
  });

  it('should not submit when button clicked and fields missing', (done) => {
    // given
    spyOn(auth, 'login').and.resolveTo();
    spyOn(Router, 'initView');
    const login = 'testLogin';
    // when
    vm.init();
    setTimeout(() => {
      document.querySelector('#login-user').value = login;
      document.querySelector('#login-user').dispatchEvent(new Event('change'));
      document.querySelector('#login-button').click();
      setTimeout(() => {
        // then
        expect(auth.login).toHaveBeenCalledTimes(0);
        expect(Router.initView).toHaveBeenCalledTimes(0);
        done();
      }, 100);
    }, 100);
  });

  it('should show error on auth failure', (done) => {
    // given
    spyOn(auth, 'login').and.rejectWith(new Error('testMessage'));
    spyOn(Router, 'initView');
    const login = 'testLogin';
    const password = 'testPassword';
    // when
    vm.init();
    setTimeout(() => {
      document.querySelector('#login-user').value = login;
      document.querySelector('#login-user').dispatchEvent(new Event('change'));
      document.querySelector('#login-pass').value = password;
      document.querySelector('#login-pass').dispatchEvent(new Event('change'));
      document.querySelector('#login-button').click();
      setTimeout(() => {
        // then
        expect(auth.login).toHaveBeenCalledWith(login, password);
        expect(Router.initView).toHaveBeenCalledTimes(0);
        expect(vm.model.error).toBe('{placeholder}');
        done();
      }, 100);
    }, 100);
  });

  it('should reload view on cancel click', (done) => {
    // given
    spyOn(auth, 'login');
    spyOn(Router, 'initView');
    config.requireAuthentication = false;
    // when
    vm.init();
    setTimeout(() => {
      document.querySelector('#cancel').click();
      setTimeout(() => {
        // then
        expect(auth.login).toHaveBeenCalledTimes(0);
        expect(Router.initView).toHaveBeenCalledTimes(1);
        done();
      }, 100);
    }, 100);
  });

});
