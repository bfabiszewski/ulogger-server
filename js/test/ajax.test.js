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

describe('Ajax tests', () => {

  const url = 'http://ulogger.test/';
  const validResponse = { id: 1 };
  const invalidResponse = 'invalid';
  const errorResponse = { error: true, message: 'response error' };
  const form = document.createElement('form');
  const input = document.createElement('input');
  input.type = 'text';
  input.name = 'p1';
  input.value = 'test';
  form.appendChild(input);

  beforeEach(() => {
    spyOn(XMLHttpRequest.prototype, 'open').and.callThrough();
    spyOn(XMLHttpRequest.prototype, 'setRequestHeader').and.callThrough();
    spyOn(XMLHttpRequest.prototype, 'send');
    spyOnProperty(XMLHttpRequest.prototype, 'readyState').and.returnValue(XMLHttpRequest.DONE);
  });

  it('should make POST request', () => {
    // when
    uAjax.post(url).catch(() => { /* ignore */ });
    // then
    expect(XMLHttpRequest.prototype.setRequestHeader).toHaveBeenCalledWith('Content-type', 'application/x-www-form-urlencoded');
    expect(XMLHttpRequest.prototype.open).toHaveBeenCalledWith('POST', url, true);
  });

  it('should make GET request', () => {
    // when
    uAjax.get(url).catch(() => { /* ignore */ });
    // then
    expect(XMLHttpRequest.prototype.setRequestHeader).not.toHaveBeenCalled();
    expect(XMLHttpRequest.prototype.open).toHaveBeenCalledWith('GET', url, true);
  });

  it('should make GET request with parameters', () => {
    // when
    uAjax.get(url, { p1: 1, p2: 'test' }).catch(() => { /* ignore */ });
    // then
    expect(XMLHttpRequest.prototype.open).toHaveBeenCalledWith('GET', `${url}?p1=1&p2=test`, true);
    expect(XMLHttpRequest.prototype.send).toHaveBeenCalledWith(null);
  });

  it('should make POST request with parameters', () => {
    // when
    uAjax.post(url, { p1: 1, p2: 'test' }).catch(() => { /* ignore */ });
    // then
    expect(XMLHttpRequest.prototype.open).toHaveBeenCalledWith('POST', url, true);
    expect(XMLHttpRequest.prototype.send).toHaveBeenCalledWith('p1=1&p2=test');
  });

  it('should make POST request with form data', () => {
    // when
    uAjax.post(url, form).catch(() => { /* ignore */ });
    // then
    expect(XMLHttpRequest.prototype.setRequestHeader).not.toHaveBeenCalled();
    expect(XMLHttpRequest.prototype.open).toHaveBeenCalledWith('POST', url, true);
    expect(XMLHttpRequest.prototype.send).toHaveBeenCalledWith(new FormData(form));
  });

  it('should make GET request with form data', () => {
    // when
    uAjax.get(url, form).catch(() => { /* ignore */ });
    // then
    expect(XMLHttpRequest.prototype.setRequestHeader).not.toHaveBeenCalled();
    expect(XMLHttpRequest.prototype.open).toHaveBeenCalledWith('GET', `${url}?p1=test`, true);
    expect(XMLHttpRequest.prototype.send).toHaveBeenCalledWith(null);
  });

  it('should make successful request and return value', (done) => {
    // when
    spyOnProperty(XMLHttpRequest.prototype, 'status').and.returnValue(200);
    spyOnProperty(XMLHttpRequest.prototype, 'responseText').and.returnValue(JSON.stringify(validResponse));
    // then
    uAjax.get(url)
      .then((result) => {
      expect(result).toEqual(validResponse);
      done();
    })
      .catch((e) => done.fail(`reject callback called (${e})`));
  });

  it('should make successful request and return error with message', (done) => {
    // when
    spyOnProperty(XMLHttpRequest.prototype, 'status').and.returnValue(200);
    spyOnProperty(XMLHttpRequest.prototype, 'responseText').and.returnValue(JSON.stringify(errorResponse));
    // then
    uAjax.get(url)
      .then(() => done.fail('resolve callback called'))
      .catch((e) => {
        expect(e.message).toBe(errorResponse.message);
        done();
      });
  });

  it('should make successful request and return error without message', (done) => {
    // when
    spyOnProperty(XMLHttpRequest.prototype, 'status').and.returnValue(200);
    spyOnProperty(XMLHttpRequest.prototype, 'responseText').and.returnValue(JSON.stringify({ error: true }));
    // then
    uAjax.get(url)
      .then(() => done.fail('resolve callback called'))
      .catch((e) => {
        expect(e.message).toBe('');
        done();
      });
  });

  it('should make request and fail with HTTP error code', (done) => {
    // when
    const status = 401;
    spyOnProperty(XMLHttpRequest.prototype, 'status').and.returnValue(status);
    // then
    uAjax.get(url)
      .then(() => done.fail('resolve callback called'))
      .catch((e) => {
        expect(e.message).toBe(`HTTP error ${status}`);
        done();
      });
  });

  it('should make request and fail with JSON parse error', (done) => {
    // when
    spyOnProperty(XMLHttpRequest.prototype, 'status').and.returnValue(200);
    spyOnProperty(XMLHttpRequest.prototype, 'responseText').and.returnValue(invalidResponse);
    // then
    uAjax.get(url)
      .then(() => done.fail('resolve callback called'))
      .catch((e) => {
        expect(e.message).toContain('JSON');
        done();
      });
  });

});
