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

import uObserve from '../src/observe.js';

describe('Observe tests', () => {
  let object;
  let result = false;
  let resultValue;

  beforeEach(() => {
    object = { observed: 1, nonObserved: 1 };
    result = false;
  });

  describe('when object is observed', () => {

    it('should throw error if observer is missing', () => {
      expect(() => { uObserve.observe(object, 'observed'); }).toThrow(new Error('Invalid arguments'));
    });

    it('should notify observers when observed property is modified', () => {
      // given
      uObserve.observe(object, 'observed', (value) => {
        result = true;
        resultValue = value;
      });
      // when
      expect(result).toBe(false);
      object.observed = 2;
      // then
      expect(result).toBe(true);
      expect(resultValue).toBe(2);
    });

    it('should not notify observers when non-observed property is modified', () => {
      // given
      uObserve.observe(object, 'observed', () => {
        result = true;
      });
      // when
      expect(result).toBe(false);
      object.nonObserved = 2;
      // then
      expect(result).toBe(false);
    });

    it('should not notify observers when modified value is same', () => {
      // given
      uObserve.observe(object, 'observed', () => {
        result = true;
      });
      // when
      expect(result).toBe(false);
      object.observed = 1;
      // then
      expect(result).toBe(false);
    });

    it('should notify observers when any property is modified', () => {
      // given
      uObserve.observe(object, (value) => {
        result = true;
        resultValue = value;
      });
      // when
      expect(result).toBe(false);
      object.observed = 2;
      // then
      expect(result).toBe(true);
      expect(resultValue).toBe(2);

      // given
      result = false;
      resultValue = null;

      // when
      expect(result).toBe(false);
      object.nonObserved = 2;
      // then
      expect(result).toBe(true);
      expect(resultValue).toBe(2);
    });

    it('should notify observers when observed array property is modified', () => {
      // given
      const array = [ 1, 2 ];
      object = { array: array };
      uObserve.observe(object, 'array', (value) => {
        result = true;
        resultValue = value;
      });
      // when
      expect(result).toBe(false);
      array.push(3);
      // then
      expect(result).toBe(true);
      expect(resultValue).toEqual(array);
    });

    it('should notify observers when observed array object is modified', () => {
      // given
      const array = [ 1, 2 ];
      uObserve.observe(array, (value) => {
        result = true;
        resultValue = value;
      });
      // when
      expect(result).toBe(false);
      array.push(3);
      // then
      expect(result).toBe(true);
      expect(resultValue).toEqual(array);
    });

    it('should retain observers after array is reassigned', () => {
      // given
      const array = [ 1, 2 ];
      const newArray = [ 3, 4 ];
      object = { array: array };
      uObserve.observe(object, 'array', (value) => {
        result = true;
        resultValue = value;
      });
      // when
      object.array = newArray;
      result = false;

      expect(result).toBe(false);
      object.array.push(5);
      // then
      expect(result).toBe(true);
      expect(resultValue).toEqual(newArray);
    });
  });

  describe('when notify is called directly', () => {
    it('should call observers with given value', () => {
      // given
      const observers = new Set();
      let result2 = false;
      observers.add((value) => { result = value; });
      observers.add((value) => { result2 = value; });
      // when
      expect(result).toBe(false);
      expect(result2).toBe(false);
      uObserve.notify(observers, true);
      // then
      expect(result).toBe(true);
      expect(result2).toBe(true);
    });
  });
});
