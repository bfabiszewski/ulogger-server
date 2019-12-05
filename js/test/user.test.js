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

import uTrack from '../src/track.js';
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

    it('should call uTrack.fetchLatest method', () => {
      // given
      const id = 1;
      const login = 'test';
      const user = new uUser(id, login);
      spyOn(uTrack, 'fetchLatest');
      // when
      user.fetchLastPosition();
      // then
      expect(uTrack.fetchLatest).toHaveBeenCalledWith(user);
    });

    it('should get class string representation', () => {
      // given
      const id = 1;
      const login = 'test';
      // when
      const user = new uUser(id, login);
      // then
      expect(user.toString()).toBe(`[${id}, ${login}]`);
    });
  });

  describe('ajax tests', () => {
    const validResponse = [ { 'id': 1, 'login': 'test' }, { 'id': 2, 'login': 'test2' }, { 'id': 18, 'login': 'demo' } ];
    const invalidResponse = [ { 'login': 'test' }, { 'id': 2, 'login': 'test2' }, { 'id': 18, 'login': 'demo' } ];

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
          expect(XMLHttpRequest.prototype.open).toHaveBeenCalledWith('GET', 'utils/getusers.php', true);
          expect(result).toEqual(jasmine.arrayContaining([ new uUser(1, 'test') ]));
          expect(result.length).toBe(3);
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

    it('should throw error on invalid data in JSON', (done) => {
      // when
      spyOnProperty(XMLHttpRequest.prototype, 'responseText').and.returnValue(JSON.stringify(invalidResponse));
      // then
      uUser.fetchList()
        .then(() => {
          done.fail('resolve callback called');
        })
        .catch((e) => {
          expect(e).toEqual(jasmine.any(Error));
          done();
        });
    });

  });
});
