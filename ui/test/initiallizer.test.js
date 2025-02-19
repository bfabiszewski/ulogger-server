/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import Config from '../src/Config.js';
import Http from '../src/Http.js';
import HttpError from '../src/HttpError';
import { Initializer } from '../src/Initializer.js';
import Locale from '../src/Locale.js';
import Session from '../src/Session.js';

describe('Initializer tests', () => {

  let initializer;
  const auth = {};
  const config = {};
  const lang = {};

  beforeEach(() => {
    initializer = new Initializer();
    spyOn(initializer.auth, 'load');
    spyOn(initializer.config, 'load');
    spyOn(initializer.lang, 'init');
    spyOn(Http, 'get')
      .withArgs('api/session').and.resolveTo(auth)
      .withArgs('api/config').and.resolveTo(config)
      .withArgs('api/locales').and.resolveTo(lang);
  });

  it('should create instance', () => {
    expect(initializer.auth).toBeInstanceOf(Session);
    expect(initializer.config).toBeInstanceOf(Config);
    expect(initializer.lang).toBeInstanceOf(Locale);
  });

  it('should load data from server', (done) => {
    // when
    initializer.initialize().then(() => {
      // then
      expect(Http.get).toHaveBeenCalledTimes(3);
      expect(Http.get.calls.allArgs()).toEqual([ [ 'api/session' ], [ 'api/config' ], [ 'api/locales' ] ]);
      expect(initializer.auth.load).toHaveBeenCalledWith(auth);
      expect(initializer.config.load).toHaveBeenCalledWith(config);
      expect(initializer.lang.init).toHaveBeenCalledWith(initializer.config, lang);
      done();
    }).catch((e) => done.fail(`reject callback called (${e})`));
  });

  it('should throw error on missing config', (done) => {
    // given
    Http.get.withArgs('api/config').and.rejectWith(new HttpError('server error', 500));
    // when
    initializer.initialize().then(() => {
      // then
      done.fail('resolve callback called');
    }).catch((e) => {
      expect(e).toEqual(jasmine.any(Error));
      done();
    });
  });

  it('should throw error on missing auth', (done) => {
    // given
    Http.get.withArgs('api/session').and.rejectWith(new HttpError('server error', 500));
    // when
    initializer.initialize().then(() => {
      // then
      done.fail('resolve callback called');
    }).catch((e) => {
      expect(e).toEqual(jasmine.any(Error));
      done();
    });
  });

  it('should not throw error on unauthorized session', (done) => {
    // given
    Http.get.withArgs('api/session').and.rejectWith(new HttpError('unauthorized', 401));
    // when
    initializer.initialize().then(() => {
      // then
      done();
    }).catch((e) => done.fail(`reject callback called (${e})`));
  });

  it('should throw error on missing locales', (done) => {
    // given
    Http.get.withArgs('api/locales').and.rejectWith(new HttpError('server error', 500));
    // when
    initializer.initialize().then(() => {
      // then
      done.fail('resolve callback called');
    }).catch((e) => {
      expect(e).toEqual(jasmine.any(Error));
      done();
    });
  });

  it('should resolve on DOMContentLoaded event', (done) => {
    // given
    spyOnProperty(document, 'readyState').and.returnValue('loading');
    // when
    Initializer.waitForDom().then(() => {
      // then
      console.log(document.readyState);
      done();
    }).catch((e) => done.fail(`reject callback called (${e})`));

    document.dispatchEvent(new Event('DOMContentLoaded'));
  });

  it('should resolve on DOM ready', (done) => {
    // given
    spyOnProperty(document, 'readyState').and.returnValue('complete');
    // when
    Initializer.waitForDom().then(() => {
      // then
      done();
    }).catch((e) => done.fail(`reject callback called (${e})`));
  });

});
