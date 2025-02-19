/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import Session from '../src/Session.js';
import User from '../src/User.js';

describe('AuthReadOnly tests', () => {

  let auth;
  let user;

  beforeEach(() => {
    auth = new Session();
    user = new User(1, 'testUser');
  });

  it('should create instance', () => {
    expect(auth.isAdmin).toBe(false);
    expect(auth.isAuthenticated).toBe(false);
    expect(auth.user).toBe(null);
  });

  it('should set authenticated user', () => {
    // when
    auth.user = user;
    // then
    expect(auth.user).toBe(user);
    expect(auth.isAuthenticated).toBe(true);
    expect(auth.isAdmin).toBe(false);
  });

  it('should unset authenticated user', () => {
    // given
    auth.user = user;
    auth.isAdmin = true;
    // when
    auth.user = null;
    // then
    expect(auth.user).toBe(null);
    expect(auth.isAuthenticated).toBe(false);
    expect(auth.isAdmin).toBe(false);
  });

  it('should set user as admin', () => {
    // given
    auth.user = user;
    // when
    auth.isAdmin = true;
    // then
    expect(auth.user).toBe(user);
    expect(auth.isAuthenticated).toBe(true);
    expect(auth.isAdmin).toBe(true);
  });

  it('should throw error when setting admin when no user is defined', () => {
    // given
    auth.user = null;
    // when
    // then
    expect(() => { auth.isAdmin = true; }).toThrowError('No authenticated user');
    expect(auth.user).toBe(null);
    expect(auth.isAuthenticated).toBe(false);
    expect(auth.isAdmin).toBe(false);
  });

  it('should initialize with loaded data', () => {
    // given
    auth.user = null;
    const data = {
      isAuthenticated: true,
      user: new User(5, 'dataUser', false)
    };
    // when
    auth.load(data);
    // then
    expect(auth.user).toEqual(new User(data.user.id, data.user.login, data.user.isAdmin));
    expect(auth.isAuthenticated).toBe(true);
    expect(auth.isAdmin).toBe(false);
  });

  it('should initialize with loaded data containing admin user', () => {
    // given
    auth.user = null;
    const data = {
      isAuthenticated: true,
      user: new User(5, 'dataUser', true)
    };
    // when
    auth.load(data);
    // then
    expect(auth.user).toEqual(new User(data.user.id, data.user.login, data.user.isAdmin));
    expect(auth.isAuthenticated).toBe(true);
    expect(auth.isAdmin).toBe(true);
  });

  it('should skip loaded data if isAuthenticated is not set', () => {
    // given
    auth.user = null;
    const data = {
      userId: 5,
      userLogin: 'dataUser'
    };
    // when
    auth.load(data);
    // then
    expect(auth.user).toBe(null);
    expect(auth.isAuthenticated).toBe(false);
    expect(auth.isAdmin).toBe(false);
  });

  it('should skip loading if data is not set', () => {
    // given
    auth.user = null;
    // when
    auth.load(null);
    // then
    expect(auth.user).toBe(null);
    expect(auth.isAuthenticated).toBe(false);
    expect(auth.isAdmin).toBe(false);
  });

});
