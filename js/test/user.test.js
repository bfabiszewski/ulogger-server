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

import uUser from '../src/user.js';

describe('User tests', () => {

  describe('simple tests', () => {

    it('should create uUser instance', () => {
      // given
      const id = 1;
      const login = 'test';
      // when
      const user = new uUser(id, login);
      // then
      expect(user.id).toBe(id);
      expect(user.login).toBe(login);
    });
  });

  describe('ajax tests', () => {
    const validResponse = [ { 'id': 1, 'login': 'test' }, { 'id': 2, 'login': 'test2' }, { 'id': 18, 'login': 'demo' } ];

    beforeEach(() => {
      spyOn(XMLHttpRequest.prototype, 'open').and.callThrough();
      spyOn(XMLHttpRequest.prototype, 'setRequestHeader').and.callThrough();
      spyOn(XMLHttpRequest.prototype, 'send');
      spyOnProperty(XMLHttpRequest.prototype, 'readyState').and.returnValue(XMLHttpRequest.DONE);
      spyOnProperty(XMLHttpRequest.prototype, 'status').and.returnValue(200);
    });

    it('should make successful request and return user array', (done) => {
      // when
      spyOnProperty(XMLHttpRequest.prototype, 'responseText').and.returnValue(JSON.stringify(validResponse));
      // then
      uUser.fetchList()
        .then((result) => {
          expect(result).toEqual(jasmine.arrayContaining([ new uUser(1, 'test') ]));
          expect(result.length).toBe(3);
          done();
        })
        .catch(() => done.fail('reject callback called'));
    });
  });
});
