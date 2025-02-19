/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import Utils from '../src/Utils.js';

describe('Utils tests', () => {

  const name = 'test_name';
  const value = 'test_value';
  const url = 'test_url';
  const id = 'test_id';

  beforeEach(() => {
    document.cookie = `ulogger_${name}=${value}; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/`;
    let head = document.querySelector('head');
    if (head === null) {
      head = document.createElement('head');
      document.appendChild(head);
    } else if (head.querySelector(`#${id}`) !== null) {
      head.removeChild(head.querySelector(`#${id}`));
    }
  });

  it('should set cookie', () => {
    // given
    const days = 1234;
    // when
    Utils.setCookie(name, value, days);
    const cookie = document.cookie;
    // then
    expect(cookie).toContain(`${name}=${value}`);
  });

  it('should create string with format and params', () => {
    // given
    const stringParam = 'test';
    const numberParam = 1234;
    // then
    expect(Utils.sprintf('-%s-', stringParam)).toBe(`-${stringParam}-`);
    expect(Utils.sprintf('-%d-', numberParam)).toBe(`-${numberParam}-`);
    expect(Utils.sprintf('-%d%s-', numberParam, stringParam)).toBe(`-${numberParam}${stringParam}-`);
    expect(Utils.sprintf('-%%d-')).toBe('-%d-');
    expect(() => Utils.sprintf('-%d-')).toThrowError(/Missing argument/);
    expect(() => Utils.sprintf('-%d-', stringParam)).toThrowError(/Wrong format/);
    expect(() => Utils.sprintf('-%d-', numberParam, stringParam)).toThrowError(/Unused argument/);
  });

  it('should add script to head tag', (done) => {
    // given
    const head = document.querySelector('head');
    // when
    Utils.addScript(url, id, null, () => done());
    // then
    expect(head.querySelector(`script#${id}`)).toBeInstanceOf(HTMLScriptElement);
    expect(head.querySelector(`script#${id}`).src).toContain(url);
    expect(head.querySelector(`script#${id}`).id).toBe(id);
  });

  it('should add stylesheet link to head tag', () => {
    // given
    const head = document.querySelector('head');
    // when
    Utils.addCss(url, id);
    // then
    expect(head.querySelector(`link#${id}`)).toBeInstanceOf(HTMLLinkElement);
    expect(head.querySelector(`link#${id}`).href).toContain(url);
    expect(head.querySelector(`link#${id}`).id).toBe(id);
    expect(head.querySelector(`link#${id}`).rel).toBe('stylesheet');
    expect(head.querySelector(`link#${id}`).type).toBe('text/css');
  });

  it('should load script', (done) => {
    // given
    spyOn(Utils, 'addScript').and.callFake((_url, _id, _onload) => _onload());
    // when
    Utils.loadScript(url, id, 100)
      // then
      .then(() => done())
      .catch((e) => done.fail(`reject callback called: ${e}`));
  });

  it('should fail loading script', (done) => {
    // given
    // eslint-disable-next-line max-params
    spyOn(Utils, 'addScript').and.callFake((_url, _id, _onload, _onerror) => _onerror(new Error(`error loading ${_id} script`)));
    // when
    Utils.loadScript(url, id, 100)
      // then
      .then(() => done.fail('resolve callback called'))
      .catch((e) => {
        expect(e.message).toContain('loading');
        done();
      });
  });

  it('should timeout loading script', (done) => {
    // given
    // eslint-disable-next-line max-params
    spyOn(Utils, 'addScript');
    // when
    Utils.loadScript(url, id, 1)
      // then
      .then(() => done.fail('resolve callback called'))
      .catch((e) => {
        expect(e.message).toContain('timeout');
        done();
      });
  });

  it('should timeout promise', (done) => {
    // when
    Utils.timeoutPromise(1)
      // then
      .then(() => done.fail('resolve callback called'))
      .catch((e) => {
        expect(e.message).toContain('timeout');
        done();
      });
  });

  it('should encode html', () => {
    expect(Utils.htmlEncode('\'foo\' & "bar" <foobar>'))
      .toBe('&#39;foo&#39; &amp; &quot;bar&quot; &lt;foobar&gt;');
  });

  it('should convert hex to rgba', () => {
    expect(Utils.hexToRGBA('#abcdef', 0.3))
      .toBe('rgba(171,205,239,0.3)');

    expect(Utils.hexToRGBA('#abc', 0))
      .toBe('rgba(170,187,204,0)');

    expect(Utils.hexToRGBA('#abc'))
      .toBe('rgba(170,187,204,1)');
  });

  it('should remove DOM element by id', () => {
    // given
    const element = document.createElement('script');
    element.id = id;
    document.head.appendChild(element);

    expect(document.getElementById(id)).toBeInstanceOf(HTMLScriptElement);
    // when
    Utils.removeElementById(id);
    // then
    expect(document.getElementById(id)).toBeNull();
  });

  it('should create node from html string', () => {
    // given
    const html = `<div id="${id}"><span>test</span></div>`;
    // when
    const node = Utils.nodeFromHtml(html);
    // then
    expect(node).toBeInstanceOf(HTMLDivElement);
    expect(node.id).toBe(id);
    expect(node.firstChild).toBeInstanceOf(HTMLSpanElement);
  });

  it('should create multiple nodes from html string', () => {
    // given
    const html = `<div id="${id}"><span>test</span></div><div id="${id}_2"><span>test2</span></div>`;
    // when
    const nodes = Utils.nodeFromHtml(html);
    // then
    expect(nodes).toBeInstanceOf(NodeList);
    expect(nodes.length).toBe(2);
    expect(nodes[0].id).toBe(id);
    expect(nodes[0].firstChild).toBeInstanceOf(HTMLSpanElement);
    expect(nodes[1].id).toBe(`${id}_2`);
    expect(nodes[1].firstChild).toBeInstanceOf(HTMLSpanElement);
  });

  it('should parse float values', () => {
    expect(Utils.getFloat('1.234')).toEqual(jasmine.any(Number));
    expect(Utils.getFloat('1.234')).toBe(1.234);
    expect(Utils.getFloat('-1.234')).toBe(-1.234);
    expect(Utils.getFloat('-0')).toBe(0);
    expect(Utils.getFloat('1')).toBe(1);
    expect(Utils.getFloat('1a')).toBe(1);
    expect(Utils.getFloat(1.234)).toBe(1.234);
    expect(Utils.getFloat(1)).toBe(1);
    expect(Utils.getFloat(null, true)).toBeNull();
    expect(() => Utils.getFloat(null)).toThrowError(/Invalid value/);
    // eslint-disable-next-line no-undefined
    expect(() => Utils.getFloat(undefined)).toThrowError(/Invalid value/);
    expect(() => Utils.getFloat('string')).toThrowError(/Invalid value/);
    expect(() => Utils.getFloat('string', true)).toThrowError(/Invalid value/);
    expect(() => Utils.getFloat('a1')).toThrowError(/Invalid value/);
  });

  it('should parse boolean values', () => {
    expect(Utils.getBoolean(1)).toBeTrue();
    expect(Utils.getBoolean(true)).toBeTrue();
    expect(Utils.getBoolean(0)).toBeFalse();
    expect(Utils.getBoolean(false)).toBeFalse();
    expect(Utils.getBoolean(null, true)).toBeNull();
    // eslint-disable-next-line no-undefined
    expect(() => Utils.getBoolean(undefined)).toThrowError(/Invalid value/);
    expect(() => Utils.getBoolean(null)).toThrowError(/Invalid value/);
    expect(() => Utils.getBoolean('0')).toThrowError(/Invalid value/);
    expect(() => Utils.getBoolean('1')).toThrowError(/Invalid value/);
  });

  it('should parse integer values', () => {
    expect(Utils.getInteger('1234')).toEqual(jasmine.any(Number));
    expect(Utils.getInteger('1234')).toBe(1234);
    expect(Utils.getInteger('-1234')).toBe(-1234);
    expect(Utils.getInteger('-0')).toBe(0);
    expect(Utils.getInteger('1')).toBe(1);
    expect(Utils.getInteger('1a')).toBe(1);
    expect(Utils.getInteger(1234)).toBe(1234);
    expect(Utils.getInteger(1.234)).toBe(1);
    expect(Utils.getInteger(-1.234)).toBe(-1);
    expect(Utils.getInteger(null, true)).toBeNull();
    expect(() => Utils.getInteger(null)).toThrowError(/Invalid value/);
    // eslint-disable-next-line no-undefined
    expect(() => Utils.getInteger(undefined)).toThrowError(/Invalid value/);
    expect(() => Utils.getInteger('string')).toThrowError(/Invalid value/);
    expect(() => Utils.getInteger('string', true)).toThrowError(/Invalid value/);
    expect(() => Utils.getInteger('a1')).toThrowError(/Invalid value/);
  });

  it('should parse string values', () => {
    expect(Utils.getString('1234')).toEqual(jasmine.any(String));
    expect(Utils.getString(1234)).toEqual(jasmine.any(String));
    expect(Utils.getString(1.234)).toEqual(jasmine.any(String));
    expect(Utils.getString('1234')).toBe('1234');
    expect(Utils.getString(1234)).toBe('1234');
    expect(Utils.getString(1.234)).toBe('1.234');
    expect(Utils.getString(-1.234)).toBe('-1.234');
    expect(Utils.getString(null, true)).toBeNull();
    expect(() => Utils.getString(null)).toThrowError(/Invalid value/);
    // eslint-disable-next-line no-undefined
    expect(() => Utils.getString(undefined)).toThrowError(/Invalid value/);
  });

  it('should format date', () => {
    // given
    const date = new Date(2020, 1, 2, 3, 4, 5);
    spyOn(date, 'toTimeString').and.returnValues(
      '03:04:05 GMT+0200 (CEST)',
      '03:04:05 GMT+0200 (Central European Standard Time)',
      '03:04:05 GMT-0700 (Pacific Daylight Time)'
    );
    // when
    let formatted = Utils.getTimeString(date);
    // then
    expect(formatted.date).toBe('2020-02-02');
    expect(formatted.time).toBe('03:04:05');
    expect(formatted.zone).toBe(' GMT+2 CEST');
    // when
    formatted = Utils.getTimeString(date);
    // then
    expect(formatted.zone).toBe(' GMT+2 CEST');
    // when
    formatted = Utils.getTimeString(date);
    // then
    expect(formatted.zone).toBe(' GMT-7 PDT');
  });

  it('should convert degrees to radians', () => {
    expect(Utils.deg2rad(1)).toBeCloseTo(0.0174533, 7);
  });

  it('should confirm two objects are equal', () => {
    // given
    const obj1 = {
      property1: true,
      property2: null,
      property3: 'string',
      property4: 4,
      property5: {
        sub1: 4,
        sub2: 'sub'
      }
    }
    const obj2 = JSON.parse(JSON.stringify(obj1));
    // when
    const result = Utils.isDeepEqual(obj1, obj2);
    // then
    expect(result).toBeTrue();
  });

  it('should confirm two objects are not equal', () => {
    // given
    const obj1 = {
      property1: true,
      property2: null,
      property3: 'string',
      property4: 4,
      property5: {
        sub1: 4,
        sub2: 'sub'
      }
    }
    const obj2 = JSON.parse(JSON.stringify(obj1));
    obj2.property1 = false;
    // when
    const result = Utils.isDeepEqual(obj1, obj2);
    // then
    expect(result).toBeFalse();
  });

  it('should confirm two objects are not equal on deeper level', () => {
    // given
    const obj1 = {
      property1: true,
      property2: null,
      property3: 'string',
      property4: 4,
      property5: {
        sub1: 4,
        sub2: 'sub'
      }
    }
    const obj2 = JSON.parse(JSON.stringify(obj1));
    obj2.property5.sub1 = 5;
    // when
    const result = Utils.isDeepEqual(obj1, obj2);
    // then
    expect(result).toBeFalse();
  });

  it('should confirm two objects are not equal when object property is null on obj2', () => {
    // given
    const obj1 = {
      property1: true,
      property2: null,
      property3: 'string',
      property4: 4,
      property5: {
        sub1: 4,
        sub2: 'sub'
      }
    }
    const obj2 = JSON.parse(JSON.stringify(obj1));
    obj2.property5 = null;
    // when
    const result = Utils.isDeepEqual(obj1, obj2);
    // then
    expect(result).toBeFalse();
  });

  it('should confirm two objects are not equal when object property is null on obj1', () => {
    // given
    const obj1 = {
      property1: true,
      property2: null,
      property3: 'string',
      property4: 4,
      property5: {
        sub1: 4,
        sub2: 'sub'
      }
    }
    const obj2 = JSON.parse(JSON.stringify(obj1));
    obj2.property2 = { sub1: 1 };
    // when
    const result = Utils.isDeepEqual(obj1, obj2);
    // then
    expect(result).toBeFalse();
  });

  it('should create color from scale and intensity', () => {
    // given
    const start = [ 0, 128, 255 ];
    const stop = [ 255, 128, 0 ];
    const intensity = 0.5;
    // when
    const color = Utils.getScaleColor(start, stop, intensity);
    // then
    expect(color).toBe('rgb(128, 128, 128)');
  });

});
