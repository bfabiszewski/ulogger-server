/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import Http from '../src/Http.js';
import Track from '../src/Track.js';
import User from '../src/User.js';

describe('User tests', () => {

  describe('simple tests', () => {

    it('should create User instance', () => {
      // given
      const id = 1;
      const login = 'test';
      // when
      const user = new User(id, login);
      // then
      expect(user.id).toBe(id);
      expect(user.login).toBe(login);
    });

    it('should call Track.fetchLatest method', () => {
      // given
      const id = 1;
      const login = 'test';
      const user = new User(id, login);
      spyOn(Track, 'fetchLatest');
      // when
      user.fetchLastPosition();
      // then
      expect(Track.fetchLatest).toHaveBeenCalledWith(user);
    });

    it('should get class string representation', () => {
      // given
      const id = 1;
      const login = 'test';
      // when
      const user = new User(id, login);
      // then
      expect(user.toString()).toBe(`[${id}, ${login}]`);
    });

    it('should be equal to other user with same id', () => {
      // given
      const user = new User(1, 'testUser');
      const otherUser = new User(1, 'other');
      // when
      const result = user.isEqualTo(otherUser);
      // then
      expect(result).toBe(true);
    });

    it('should not be equal to other track with other id', () => {
      // given
      const user = new User(1, 'testUser');
      const otherUser = new User(2, 'other');
      // when
      const result = user.isEqualTo(otherUser);
      // then
      expect(result).toBe(false);
    });

    it('should not be equal to null user', () => {
      // given
      const user = new User(1, 'testUser');
      const otherUser = null;
      // when
      const result = user.isEqualTo(otherUser);
      // then
      expect(result).toBe(false);
    });
  });

  describe('request tests', () => {
    const validResponse = [ { 'id': 1, 'login': 'test', 'isAdmin': false }, { 'id': 2, 'login': 'test2', 'isAdmin': false }, { 'id': 18, 'login': 'demo', 'isAdmin': false } ];
    const invalidResponse = [ { 'login': 'test' }, { 'id': 2, 'login': 'test2' }, { 'id': 18, 'login': 'demo' } ];

    it('should make successful request and return user array', (done) => {
      // when
      spyOn(Http, 'get').and.resolveTo(validResponse);
      // then
      User.fetchList()
        .then((result) => {
          expect(Http.get).toHaveBeenCalledWith('api/users');
          expect(result).toEqual(jasmine.arrayContaining([ new User(1, 'test') ]));
          expect(result.length).toBe(3);
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

    it('should throw error on invalid data in JSON', (done) => {
      // when
      spyOn(Http, 'get').and.resolveTo(invalidResponse);
      // then
      User.fetchList()
        .then(() => {
          done.fail('resolve callback called');
        })
        .catch((e) => {
          expect(e).toEqual(jasmine.any(Error));
          done();
        });
    });

    it('should delete user', (done) => {
      // when
      const user = new User(1, 'testUser');
      spyOn(Http, 'delete').and.resolveTo();
      // then
      user.delete()
        .then(() => {
          expect(Http.delete).toHaveBeenCalledWith(`api/users/${user.id}`);
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

    it('should add user', (done) => {
      // when
      const id = 1;
      const login = 'testUser';
      const password = 'password';
      const isAdmin = true;
      const newUser = new User(id, login, isAdmin);
      spyOn(Http, 'post').and.resolveTo({ id, login, isAdmin });
      // then
      User.add(login, password, isAdmin)
        .then((user) => {
          expect(Http.post).toHaveBeenCalledWith('api/users', { login, password, isAdmin });
          expect(user).toEqual(newUser);
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

    it('should change user password', (done) => {
      // when
      const user = new User(1, 'testUser');
      const password = 'password';
      const oldPassword = 'oldPassword';
      spyOn(Http, 'put').and.resolveTo();
      // then
      user.setPassword(password, oldPassword)
        .then(() => {
          expect(Http.put).toHaveBeenCalledWith(`api/users/${user.id}/password`, { password, oldPassword });
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

    it('should modify user and set isAdmin', (done) => {
      // when
      const user = new User(1, 'testUser', false);
      spyOn(Http, 'put').and.resolveTo();
      // then
      user.modify(true)
        .then(() => {
          expect(Http.put).toHaveBeenCalledWith(`api/users/${user.id}`, { id: user.id, login: user.login, isAdmin: true });
          expect(user.isAdmin).toBeTrue();
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

    it('should modify user and set password', (done) => {
      // when
      const user = new User(1, 'testUser', false);
      const password = 'newPassword';
      spyOn(Http, 'put').and.resolveTo();
      // then
      user.modify(false, password)
        .then(() => {
          expect(Http.put).toHaveBeenCalledWith(`api/users/${user.id}`, { id: user.id, login: user.login, isAdmin: false, password: password });
          expect(user.isAdmin).toBeFalse();
          done();
        })
        .catch((e) => done.fail(`reject callback called (${e})`));
    });

  });
});
