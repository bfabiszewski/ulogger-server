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

import uAjax from '../src/ajax.js';
import uAuth from '../src/auth.js';
import uConfig from '../src/config.js';
import { uInitializer } from '../src/initializer.js';
import uLang from '../src/lang.js';

describe('Initializer tests', () => {

  let initializer;
  let data;

  beforeEach(() => {
    data = {
      auth: {},
      config: {},
      lang: {}
    };
    initializer = new uInitializer();
    spyOn(initializer.auth, 'load');
    spyOn(initializer.config, 'load');
    spyOn(initializer.lang, 'init');
    spyOn(uAjax, 'get').and.returnValue(Promise.resolve(data));
  });

  it('should create instance', () => {
    expect(initializer.auth).toBeInstanceOf(uAuth);
    expect(initializer.config).toBeInstanceOf(uConfig);
    expect(initializer.lang).toBeInstanceOf(uLang);
  });

  it('should load data from server', (done) => {
    // when
    initializer.initialize().then(() => {
      // then
      expect(uAjax.get).toHaveBeenCalledWith('utils/getinit.php');
      expect(initializer.auth.load).toHaveBeenCalledWith(data.auth);
      expect(initializer.config.load).toHaveBeenCalledWith(data.config);
      expect(initializer.lang.init).toHaveBeenCalledWith(initializer.config, data.lang);
      done();
    }).catch((e) => done.fail(`reject callback called (${e})`));
  });

  it('should throw error on missing data.config', (done) => {
    // given
    delete data.config;
    // when
    initializer.initialize().then(() => {
      // then
      done.fail('resolve callback called');
    }).catch((e) => {
      expect(e).toEqual(jasmine.any(Error));
      done();
    });
  });

  it('should throw error on missing data.auth', (done) => {
    // given
    delete data.auth;
    // when
    initializer.initialize().then(() => {
      // then
      done.fail('resolve callback called');
    }).catch((e) => {
      expect(e).toEqual(jasmine.any(Error));
      done();
    });
  });

  it('should throw error on missing data.lang', (done) => {
    // given
    delete data.lang;
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
    uInitializer.waitForDom().then(() => {
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
    uInitializer.waitForDom().then(() => {
      // then
      done();
    }).catch((e) => done.fail(`reject callback called (${e})`));
  });

});
