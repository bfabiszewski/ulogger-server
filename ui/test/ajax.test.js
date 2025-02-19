/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import Http from '../src/Http.js';

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
    spyOn(window, 'fetch').and.resolveTo();
  });

  it('should make POST request', () => {
    // when
    Http.post(url).catch(() => { /* ignore */ });
    // then
    const init = window.fetch.calls.mostRecent().args[1];
    const headers = init.headers;

    expect(window.fetch).toHaveBeenCalledWith(url, jasmine.any(Object));
    expect(init.method).toEqual('POST');
    expect(init.body).toEqual('{}');
    expect(headers.get('Content-type')).toEqual('application/json');
  });

  it('should make GET request', () => {
    // when
    Http.get(url).catch(() => { /* ignore */ });
    // then
    const init = window.fetch.calls.mostRecent().args[1];
    const headers = init.headers;

    expect(window.fetch).toHaveBeenCalledWith(url, jasmine.any(Object));
    expect(init.method).toEqual('GET');
    expect(init.body).toBeUndefined();
    expect(headers.get('Content-type')).toBeNull();
  });

  it('should make GET request with parameters', () => {
    // when
    Http.get(url, { p1: 1, p2: 'test' }).catch(() => { /* ignore */ });
    // then
    const init = window.fetch.calls.mostRecent().args[1];
    const headers = init.headers;

    expect(window.fetch).toHaveBeenCalledWith(`${url}?p1=1&p2=test`, jasmine.any(Object));
    expect(init.method).toEqual('GET');
    expect(init.body).toBeUndefined();
    expect(headers.get('Content-type')).toBeNull();
  });

  it('should make POST request with parameters', () => {
    // when
    Http.post(url, { p1: 1, p2: 'test' }).catch(() => { /* ignore */ });
    // then
    const init = window.fetch.calls.mostRecent().args[1];
    const headers = init.headers;

    expect(window.fetch).toHaveBeenCalledWith(url, jasmine.any(Object));
    expect(init.method).toEqual('POST');
    expect(init.body).toEqual('{"p1":1,"p2":"test"}');
    expect(headers.get('Content-type')).toEqual('application/json');
  });

  it('should make POST request with form data', () => {
    // when
    Http.post(url, form).catch(() => { /* ignore */ });
    // then
    const init = window.fetch.calls.mostRecent().args[1];
    const headers = init.headers;

    expect(window.fetch).toHaveBeenCalledWith(url, jasmine.any(Object));
    expect(init.method).toEqual('POST');
    expect(init.body).toEqual(new FormData(form));
    expect(headers.get('Content-type')).toBeNull();
  });

  it('should make GET request with form data', () => {
    // when
    Http.get(url, form).catch(() => { /* ignore */ });
    // then
    const init = window.fetch.calls.mostRecent().args[1];
    const headers = init.headers;

    expect(window.fetch).toHaveBeenCalledWith(`${url}?p1=test`, jasmine.any(Object));
    expect(init.method).toEqual('GET');
    expect(init.body).toBeUndefined();
    expect(headers.get('Content-type')).toBeNull();
  });

  it('should make successful request and return value', (done) => {
    // when
    const headers = new Headers();
    headers.set('Content-type', 'application/json');
    const response = new Response(JSON.stringify(validResponse), { status: 200, statusText: 'OK', headers: headers });
    window.fetch.and.resolveTo(response);
    // then
    Http.get(url)
      .then((result) => {
        expect(result).toEqual(validResponse);
        done();
      })
      .catch((e) => done.fail(`reject callback called (${e})`));
  });

  it('should make request and return error with message', (done) => {
    // when
    const headers = new Headers();
    headers.set('Content-type', 'application/json');
    const response = new Response(JSON.stringify(errorResponse), { status: 422, statusText: 'Unprocessable entity', headers: headers });
    window.fetch.and.resolveTo(response);
    // then
    Http.get(url)
      .then(() => done.fail('resolve callback called'))
      .catch((e) => {
        expect(e.message).toBe(errorResponse.message);
        done();
      });
  });

  it('should make request and return json error with generic error message', (done) => {
    // when
    const headers = new Headers();
    headers.set('Content-type', 'application/json');
    const response = new Response(JSON.stringify({ error: true }), { status: 422, statusText: 'Unprocessable entity', headers: headers });
    window.fetch.and.resolveTo(response);
    // then
    Http.get(url)
      .then(() => done.fail('resolve callback called'))
      .catch((e) => {
        expect(e.message).toBe('Unprocessable entity');
        done();
      });
  });

  it('should make request and fail with generic error message', (done) => {
    // when
    const response = new Response(JSON.stringify({ error: true }), { status: 401, statusText: 'Unauthorized' });
    window.fetch.and.resolveTo(response);
    // then
    Http.get(url)
      .then(() => done.fail('resolve callback called'))
      .catch((e) => {
        expect(e.message).toBe('Unauthorized');
        done();
      });
  });

  it('should make request and fail with JSON parse error', (done) => {
    // when
    const headers = new Headers();
    headers.set('Content-type', 'application/json');
    const response = new Response(invalidResponse, { status: 200, statusText: 'Ok', headers: headers });
    window.fetch.and.resolveTo(response);
    // then
    Http.get(url)
      .then(() => done.fail('resolve callback called'))
      .catch((e) => {
        expect(e.message).toContain('JSON');
        done();
      });
  });

});
