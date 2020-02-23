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

import uConfig from '../src/config.js';
import uObserve from '../src/observe.js';

describe('Config tests', () => {

  let config;
  beforeEach(() => {
    config = new uConfig();
  });

  it('should create instance', () => {
    expect(config.interval).toBeDefined();
    expect(config.interval).toBeDefined();
    expect(config.units).toBeDefined();
    expect(config.lang).toBeDefined();
    expect(config.mapApi).toBeDefined();
    expect(config.googleKey).toBeDefined();
    expect(config.olLayers).toBeDefined();
    expect(config.initLatitude).toBeDefined();
    expect(config.initLongitude).toBeDefined();
    expect(config.strokeWeight).toBeDefined();
    expect(config.strokeColor).toBeDefined();
    expect(config.strokeOpacity).toBeDefined();
    expect(config.colorNormal).toBeDefined();
    expect(config.colorStart).toBeDefined();
    expect(config.colorStop).toBeDefined();
    expect(config.colorExtra).toBeDefined();
    expect(config.colorHilite).toBeDefined();
    expect(config.factorSpeed).toBeDefined();
    expect(config.unitSpeed).toBeDefined();
    expect(config.factorDistance).toBeDefined();
    expect(config.unitDistance).toBeDefined();
    expect(config.factorDistanceMajor).toBeDefined();
    expect(config.unitDistanceMajor).toBeDefined();
    expect(config.unitDay).toBeDefined();
  });

  it('should set units to imperial', () => {
    // when
    config.units = 'imperial';
    config.initUnits();
    // then
    expect(config.factorSpeed).toBe(2.237); // to mph
    expect(config.unitSpeed).toBe('unitmph');
    expect(config.factorDistance).toBe(3.28); // to feet
    expect(config.unitDistance).toBe('unitft');
    expect(config.factorDistanceMajor).toBe(0.621); // to miles
    expect(config.unitDistanceMajor).toBe('unitmi');
  });

  it('should set units to nautical', () => {
    // when
    config.units = 'nautical';
    config.initUnits();
    // then
    expect(config.factorSpeed).toBe(1.944); // to knots
    expect(config.unitSpeed).toBe('unitkt');
    expect(config.factorDistance).toBe(1); // meters
    expect(config.unitDistance).toBe('unitm');
    expect(config.factorDistanceMajor).toBe(0.54); // to nautical miles
    expect(config.unitDistanceMajor).toBe('unitnm');
  });

  it('should set units to metric', () => {
    // when
    config.units = 'metric';
    config.initUnits();
    // then
    expect(config.factorSpeed).toBe(3.6);
    expect(config.unitSpeed).toBe('unitkmh');
    expect(config.factorDistance).toBe(1);
    expect(config.unitDistance).toBe('unitm');
    expect(config.factorDistanceMajor).toBe(1);
    expect(config.unitDistanceMajor).toBe('unitkm');
  });

  it('should load property from data', () => {
    // given
    config.interval = 1;
    const data = {
      interval: 2
    };
    // when
    config.load(data);
    // then
    expect(config.interval).toBe(data.interval);
  });

  it('should skip unknown propery loaded from data', () => {
    // given
    const data = {
      unknownProperty: 2
    };
    // when
    config.load(data);
    // then
    expect(config.unknownProperty).not.toBeDefined();
  });

  it('should update units after loading data', () => {
    // given
    const data = {
      units: 'imperial'
    };
    // when
    config.load(data);
    // then
    expect(config.factorSpeed).toBe(2.237);
  });

  it('should generate regex for values in data', () => {
    // given
    const data = {
      passLenMin: 5,
      passStrength: 2
    };
    // when
    config.load(data);
    // then
    expect(config.getPassRegExp()).toEqual(jasmine.any(RegExp));
  });

  it('should reinitialize config and remove any observers', () => {
    // given
    const data = {
      interval: 10000
    };
    config.load(data);
    uObserve.observe(config, 'interval', () => {/* ignored */});
    // when
    config.reinitialize();
    // then
    expect(config.interval).not.toBe(data.interval);
    expect(uObserve.isObserved(config, 'interval')).toBe(false);
  });

  it('should notify observer on changed config value', (done) => {
    // given
    const newInterval = 10000;
    // when
    config.onChanged('interval', (interval) => {
      // then
      expect(interval).toBe(newInterval);
      done();
    });
    config.interval = newInterval;
  });

  it('should validate passwords length', () => {
    // given
    config.passLenMin = 4;
    config.passStrength = 0;
    // when
    let password = '123';
    // then
    expect(config.validPassStrength(password)).toBeFalse();
    // when
    password = '';
    // then
    expect(config.validPassStrength(password)).toBeFalse();
    // when
    password = '1234';
    // then
    expect(config.validPassStrength(password)).toBeTrue();
    // when
    password = '1235';
    // then
    expect(config.validPassStrength(password)).toBeTrue();
  });

  it('should validate passwords strength 0', () => {
    // given
    config.passLenMin = 0;
    config.passStrength = 0;
    // when
    let password = 'abcd';
    // then
    expect(config.validPassStrength(password)).toBeTrue();
    // when
    password = 'aBcD';
    // then
    expect(config.validPassStrength(password)).toBeTrue();
    // when
    password = '';
    // then
    expect(config.validPassStrength(password)).toBeTrue();
    // when
    password = '1234';
    // then
    expect(config.validPassStrength(password)).toBeTrue();
    // when
    password = 'Abcde1235$%';
    // then
    expect(config.validPassStrength(password)).toBeTrue();
  });

  it('should validate passwords strength 1 (one upper, one lower case letter)', () => {
    // given
    config.passLenMin = 0;
    config.passStrength = 1;
    // when
    let password = 'abcd';
    // then
    expect(config.validPassStrength(password)).toBeFalse();
    // when
    password = 'aBcD';
    // then
    expect(config.validPassStrength(password)).toBeTrue();
    // when
    password = '';
    // then
    expect(config.validPassStrength(password)).toBeFalse();
    // when
    password = '1234';
    // then
    expect(config.validPassStrength(password)).toBeFalse();
    // when
    password = 'Abcde1235$%';
    // then
    expect(config.validPassStrength(password)).toBeTrue();
  });

  it('should validate passwords strength 2 (one upper, one lower case letter, one digit)', () => {
    // given
    config.passLenMin = 0;
    config.passStrength = 2;
    // when
    let password = 'abcd';
    // then
    expect(config.validPassStrength(password)).toBeFalse();
    // when
    password = 'aBcD';
    // then
    expect(config.validPassStrength(password)).toBeFalse();
    // when
    password = '';
    // then
    expect(config.validPassStrength(password)).toBeFalse();
    // when
    password = '1234';
    // then
    expect(config.validPassStrength(password)).toBeFalse();
    // when
    password = 'ab1234';
    // then
    expect(config.validPassStrength(password)).toBeFalse();
    // when
    password = 'aB1234';
    // then
    expect(config.validPassStrength(password)).toBeTrue();
    // when
    password = 'Abcde1235$%';
    // then
    expect(config.validPassStrength(password)).toBeTrue();
  });

  it('should validate passwords strength 3 (one upper, one lower case letter, one digit, one non-alpha)', () => {
    // given
    config.passLenMin = 0;
    config.passStrength = 3;
    // when
    let password = 'abcd';
    // then
    expect(config.validPassStrength(password)).toBeFalse();
    // when
    password = 'aBcD';
    // then
    expect(config.validPassStrength(password)).toBeFalse();
    // when
    password = '';
    // then
    expect(config.validPassStrength(password)).toBeFalse();
    // when
    password = '1234';
    // then
    expect(config.validPassStrength(password)).toBeFalse();
    // when
    password = 'ab1234';
    // then
    expect(config.validPassStrength(password)).toBeFalse();
    // when
    password = 'aB1234';
    // then
    expect(config.validPassStrength(password)).toBeFalse();
    // when
    password = 'ab1234.';
    // then
    expect(config.validPassStrength(password)).toBeFalse();
    // when
    password = 'Abcde1235$%';
    // then
    expect(config.validPassStrength(password)).toBeTrue();
  });

});
