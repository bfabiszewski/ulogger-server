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

import uAuth from '../src/auth.js';
import uUser from '../src/user.js';

describe('Auth tests', () => {

  let auth;
  let user;

  beforeEach(() => {
    auth = new uAuth();
    user = new uUser(1, 'testUser');
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
      isAdmin: false,
      isAuthenticated: true,
      userId: 5,
      userLogin: 'dataUser'
    };
    // when
    auth.load(data);
    // then
    expect(auth.user).toEqual(new uUser(data.userId, data.userLogin));
    expect(auth.isAuthenticated).toBe(true);
    expect(auth.isAdmin).toBe(false);
  });

  it('should initialize with loaded data containing admin user', () => {
    // given
    auth.user = null;
    const data = {
      isAdmin: true,
      isAuthenticated: true,
      userId: 5,
      userLogin: 'dataUser'
    };
    // when
    auth.load(data);
    // then
    expect(auth.user).toEqual(new uUser(data.userId, data.userLogin));
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
