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
    // eslint-disable-next-line no-undefined
    resultValue = undefined;
  });

  describe('when object is observed', () => {

    it('should throw error if observer is missing', () => {
      expect(() => { uObserve.observe(object, 'observed'); }).toThrow(new Error('Invalid argument for observe'));
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

    it('should notify multiple observers when observed property is modified', () => {
      // given
      let result2 = false;
      let resultValue2;
      uObserve.observe(object, 'observed', (value) => {
        result = true;
        resultValue = value;
      });
      uObserve.observe(object, 'observed', (value) => {
        result2 = true;
        resultValue2 = value;
      });
      // when
      expect(result).toBe(false);
      expect(result2).toBe(false);
      object.observed = 2;
      // then
      expect(result).toBe(true);
      expect(resultValue).toBe(2);
      expect(result2).toBe(true);
      // noinspection JSUnusedAssignment
      expect(resultValue2).toBe(2);
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

    it('should notify observers when observed array is modified', () => {
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
      let result2 = false;
      let resultValue2;
      const array = [ 1, 2 ];
      const newArray = [ 3, 4 ];
      object = { array: array };
      uObserve.observe(object, 'array', (value) => {
        result = true;
        resultValue = value;
      });
      uObserve.observe(object, 'array', (value) => {
        result2 = true;
        resultValue2 = value;
      });
      // when
      object.array = newArray;
      result = false;
      result2 = false;

      expect(result).toBe(false);
      expect(result2).toBe(false);
      object.array.push(5);
      // then
      expect(result).toBe(true);
      expect(result2).toBe(true);
      expect(resultValue).toEqual(newArray);
      // noinspection JSUnusedAssignment
      expect(resultValue2).toEqual(newArray);
    });

    it('should retain observers after array property is silently set', () => {
      // given
      let result2 = false;
      let resultValue2;
      const array = [ 1, 2 ];
      const newArray = [ 3, 4 ];
      object = { array: [] };
      uObserve.observe(object, 'array', (value) => {
        result = true;
        resultValue = value;
      });
      uObserve.observe(object, 'array', (value) => {
        result2 = true;
        resultValue2 = value;
      });
      // when
      uObserve.setSilently(object, 'array', array);
      object.array = newArray;
      result = false;
      result2 = false;

      expect(result).toBe(false);
      expect(result2).toBe(false);
      object.array.push(5);
      // then
      expect(result).toBe(true);
      expect(result2).toBe(true);
      expect(resultValue).toEqual(newArray);
      // noinspection JSUnusedAssignment
      expect(resultValue2).toEqual(newArray);
    });
  });

  describe('when object is unobserved', () => {

    it('should throw error if removed observer is missing', () => {
      expect(() => {
        uObserve.unobserve(object, 'unobserved');
      }).toThrow(new Error('Invalid argument for unobserve'));
    });

    it('should not notify observers when unobserved property is modified', () => {
      // given
      const observer = (value) => {
        result = true;
        resultValue = value;
      };
      uObserve.observe(object, 'observed', observer);
      // when
      uObserve.unobserve(object, 'observed', observer);

      expect(result).toBe(false);
      object.observed = 2;
      // then
      expect(result).toBe(false);
      // eslint-disable-next-line no-undefined
      expect(resultValue).toBe(undefined);
      expect(object.observed).toBe(2);
    });

    it('should not notify observers when any unobserved object property is modified', () => {
      // given
      const observer = (value) => {
        result = true;
        resultValue = value;
      };
      uObserve.observe(object, observer);
      // when
      uObserve.unobserve(object, observer);

      expect(result).toBe(false);
      object.observed = 2;
      // then
      expect(result).toBe(false);
      // eslint-disable-next-line no-undefined
      expect(resultValue).toBe(undefined);
      expect(object.observed).toBe(2);

      // given
      result = false;
      // eslint-disable-next-line no-undefined
      resultValue = undefined;

      // when
      expect(result).toBe(false);
      object.nonObserved = 2;
      // then
      expect(result).toBe(false);
      // eslint-disable-next-line no-undefined
      expect(resultValue).toBe(undefined);
      expect(object.nonObserved).toBe(2);
    });

    it('should not notify observers when unobserved array property is modified', () => {
      // given
      const observer = (value) => {
        result = true;
        resultValue = value;
      };
      const array = [ 1, 2 ];
      object = { array: array };
      uObserve.observe(object, 'array', observer);
      // when
      uObserve.unobserve(object, 'array', observer);

      expect(result).toBe(false);
      array.push(3);
      // then
      expect(result).toBe(false);
      // eslint-disable-next-line no-undefined
      expect(resultValue).toEqual(undefined);
      expect(array).toEqual([ 1, 2, 3 ]);
    });

    it('should not notify observers when unobserved array is modified', () => {
      // given
      const observer = (value) => {
        result = true;
        resultValue = value;
      };
      const array = [ 1, 2 ];
      uObserve.observe(array, observer);
      // when
      uObserve.unobserve(array, observer);

      expect(result).toBe(false);
      array.push(3);
      // then
      expect(result).toBe(false);
      // eslint-disable-next-line no-undefined
      expect(resultValue).toEqual(undefined);
      expect(array).toEqual([ 1, 2, 3 ]);
    });

    it('should remove one of two observers of object property', () => {
      // given
      let result2 = false;
      let resultValue2;
      const observer = (value) => {
        result = true;
        resultValue = value;
      };
      const observer2 = (value) => {
        result2 = true;
        resultValue2 = value;
      };
      uObserve.observe(object, 'observed', observer);
      uObserve.observe(object, 'observed', observer2);
      // when
      uObserve.unobserve(object, 'observed', observer2);

      expect(result).toBe(false);
      expect(result2).toBe(false);
      object.observed = 2;
      // then
      expect(result).toBe(true);
      expect(resultValue).toBe(2);
      expect(result2).toBe(false);
      // noinspection JSUnusedAssignment
      expect(resultValue2).toBe(undefined);// eslint-disable-line no-undefined
    });

    it('should remove one of two observers from array', () => {
      // given
      let result2 = false;
      let resultValue2;
      const observer = (value) => {
        result = true;
        resultValue = value;
      };
      const observer2 = (value) => {
        result2 = true;
        resultValue2 = value;
      };
      const array = [ 1, 2 ];
      uObserve.observe(array, observer);
      uObserve.observe(array, observer2);
      // when
      uObserve.unobserve(array, observer2);

      expect(result).toBe(false);
      expect(result2).toBe(false);
      array.push(3);
      // then
      expect(result).toBe(true);
      expect(result2).toBe(false);
      expect(resultValue).toEqual(array);
      // noinspection JSUnusedAssignment
      expect(resultValue2).toEqual(undefined);// eslint-disable-line no-undefined
      expect(array).toEqual([ 1, 2, 3 ]);
    });

    it('should remove all observers of object property', () => {
      // given
      let result2 = false;
      let resultValue2;
      const observer = (value) => {
        result = true;
        resultValue = value;
      };
      const observer2 = (value) => {
        result2 = true;
        resultValue2 = value;
      };
      uObserve.observe(object, 'observed', observer);
      uObserve.observe(object, 'observed', observer2);
      // when
      uObserve.unobserveAll(object, 'observed');

      expect(result).toBe(false);
      expect(result2).toBe(false);
      object.observed = 2;
      // then
      expect(result).toBe(false);
      expect(resultValue).toBe(undefined);// eslint-disable-line no-undefined
      expect(result2).toBe(false);
      // noinspection JSUnusedAssignment
      expect(resultValue2).toBe(undefined);// eslint-disable-line no-undefined
      expect(object.observed).toBe(2);
    });

    it('should remove all observers from array property', () => {
      // given
      let result2 = false;
      let resultValue2;
      const observer = (value) => {
        result = true;
        resultValue = value;
      };
      const observer2 = (value) => {
        result2 = true;
        resultValue2 = value;
      };
      const array = [ 1, 2 ];
      object.arr = array;
      uObserve.observe(object, 'arr', observer);
      uObserve.observe(object, 'arr', observer2);
      // when
      uObserve.unobserveAll(object, 'arr');

      expect(result).toBe(false);
      expect(result2).toBe(false);
      array.push(3);
      // then
      expect(result).toBe(false);
      expect(result2).toBe(false);
      expect(resultValue).toEqual(undefined);// eslint-disable-line no-undefined
      // noinspection JSUnusedAssignment
      expect(resultValue2).toEqual(undefined);// eslint-disable-line no-undefined
      expect(object.arr).toEqual([ 1, 2, 3 ]);
    });

    it('should remove all observers of all object properties', () => {
      // given
      let result2 = false;
      let resultValue2;
      const observer = (value) => {
        result = true;
        resultValue = value;
      };
      const observer2 = (value) => {
        result2 = true;
        resultValue2 = value;
      };
      object.observed2 = null;
      uObserve.observe(object, 'observed', observer);
      uObserve.observe(object, 'observed2', observer2);
      // when
      uObserve.unobserveAll(object);

      expect(result).toBe(false);
      expect(result2).toBe(false);
      object.observed = 2;
      object.observed2 = 2;
      // then
      expect(result).toBe(false);
      expect(resultValue).toBe(undefined);// eslint-disable-line no-undefined
      expect(result2).toBe(false);
      // noinspection JSUnusedAssignment
      expect(resultValue2).toBe(undefined);// eslint-disable-line no-undefined
      expect(object.observed).toBe(2);
      expect(object.observed2).toBe(2);
    });

    it('should throw error when observing non-existing property', () => {
      // given
      const nonExisting = '___non-existing___';

      expect(object.hasOwnProperty(nonExisting)).toBe(false);
      // then
      expect(() => uObserve.observe(object, nonExisting, (value) => {
        result = true;
        resultValue = value;
      })).toThrow();

      expect(object.hasOwnProperty(nonExisting)).toBe(false);
    });

    it('should throw error when observing non-object', () => {
      // given
      const nonExisting = '___non-existing___';
      // then
      expect(() => uObserve.observe(nonExisting, (value) => {
        result = true;
        resultValue = value;
      })).toThrow();
    });

    it('should throw error when observing null object', () => {
      // given
      const nullObject = null;
      // then
      expect(() => uObserve.observe(nullObject, (value) => {
        result = true;
        resultValue = value;
      })).toThrow();
    });

    it('should not notify observers when observed property is silently changed', () => {
      // given
      uObserve.observe(object, 'observed', (value) => {
        result = true;
        resultValue = value;
      });
      // when
      expect(result).toBe(false);
      uObserve.setSilently(object, 'observed', 2);
      // then
      expect(result).toBe(false);
      // eslint-disable-next-line no-undefined
      expect(resultValue).toBe(undefined);
    });

    it('should return true if property is observed', () => {
      // when
      uObserve.observe(object, 'observed', (value) => {
        result = true;
        resultValue = value;
      });
      // then
      expect(uObserve.isObserved(object, 'observed')).toBe(true);
    });

    it('should return false if property is not observed', () => {
      // when
      uObserve.observe(object, 'observed', (value) => {
        result = true;
        resultValue = value;
      });
      // then
      expect(uObserve.isObserved(object, 'nonObserved')).toBe(false);
    });

    it('should return true if array property is observed', () => {
      // when
      const array = [ 1, 2 ];
      object = { array: array };
      uObserve.observe(object, 'array', (value) => {
        result = true;
        resultValue = value;
      });
      // then
      expect(uObserve.isObserved(object, 'array')).toBe(true);
    });

    it('should return false if property is unobserved', () => {
      // when
      const observer = (value) => {
        result = true;
        resultValue = value;
      };
      uObserve.observe(object, 'observed', observer);
      uObserve.unobserve(object, 'observed', observer);
      // then
      expect(uObserve.isObserved(object, 'observed')).toBe(false);
    });

    it('should return true if property is observed by given observer', () => {
      // when
      const observer = (value) => {
        result = true;
        resultValue = value;
      };
      const observer2 = () => {/* ignored */};
      uObserve.observe(object, 'observed', observer);
      // then
      expect(uObserve.isObserved(object, 'observed', observer)).toBe(true);
      expect(uObserve.isObserved(object, 'observed', observer2)).toBe(false);
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
