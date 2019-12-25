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

import uListItem from '../src/listitem.js';
import uSelect from '../src/select.js';

describe('Select tests', () => {

  const head = 'test header';
  const allText = 'all text';
  let element;
  let options;

  class TestItem extends uListItem {
    constructor(id, name) {
      super(id, name);
      this.id = id;
      // noinspection JSUnusedGlobalSymbols
      this.name = name;
    }
  }

  beforeEach(() => {
    options = [
      new TestItem(1, 'test1'),
      new TestItem(5, 'test5')
    ];
    element = document.createElement('select');
  });

  it('should construct class instance with default values', () => {
    // when
    const select = new uSelect(element);
    // then
    expect(select.element).toBe(element);
    expect(select.element.options.length).toBe(0);
    expect(select.element.value).toBe('');
    expect(select.hasAllOption).toBe(false);
    expect(select.allText).toBe('');
    expect(select.hasHead).toBe(false);
    expect(select.headText).toBe('');
  });

  it('should should throw error on wrong obligatory parameter type', () => {
    // when
    // then
    expect(() => new uSelect(null)).toThrowError(/Invalid argument/);
  });

  it('should construct class instance with header', () => {
    // when
    const select = new uSelect(element, head);
    // then
    expect(select.element).toBe(element);
    expect(select.element.options.length).toBe(1);
    expect(select.element.options[0].disabled).toBe(true);
    expect(select.element.options[0].defaultSelected).toBe(true);
    expect(select.element.options[0].selected).toBe(true);
    expect(select.element.options[0].text).toBe(head);
    expect(select.element.options[0].value).toBe(uSelect.headValue);
  });

  it('should construct class instance and set options', () => {
    // when
    const select = new uSelect(element);
    select.setOptions(options);
    // then
    expect(select.element).toBe(element);
    expect(select.element.options.length).toBe(options.length);
    expect(select.element.options[0].disabled).toBe(false);
    expect(select.element.options[0].defaultSelected).toBe(false);
    expect(select.element.options[0].selected).toBe(true);
    expect(select.element.options[0].text).toBe(options[0].listText);
    expect(select.element.options[0].value).toBe(options[0].listValue);
    expect(select.element.options[1].disabled).toBe(false);
    expect(select.element.options[1].defaultSelected).toBe(false);
    expect(select.element.options[1].selected).toBe(false);
    expect(select.element.options[1].text).toBe(options[1].listText);
    expect(select.element.options[1].value).toBe(options[1].listValue);
  });

  it('should construct class instance and set options and default value', () => {
    // when
    const select = new uSelect(element);
    select.setOptions(options, options[1].listValue);
    // then
    expect(select.element).toBe(element);
    expect(select.element.options.length).toBe(options.length);
    expect(select.element.options[0].disabled).toBe(false);
    expect(select.element.options[0].defaultSelected).toBe(false);
    expect(select.element.options[0].selected).toBe(false);
    expect(select.element.options[0].text).toBe(options[0].listText);
    expect(select.element.options[0].value).toBe(options[0].listValue);
    expect(select.element.options[1].disabled).toBe(false);
    expect(select.element.options[1].defaultSelected).toBe(false);
    expect(select.element.options[1].selected).toBe(true);
    expect(select.element.options[1].text).toBe(options[1].listText);
    expect(select.element.options[1].value).toBe(options[1].listValue);
  });

  it('should construct class instance with options and head', () => {
    // when
    const select = new uSelect(element, head);
    select.setOptions(options);
    // then
    expect(select.element).toBe(element);
    expect(select.element.options.length).toBe(options.length + 1);
    expect(select.element.options[0].disabled).toBe(true);
    expect(select.element.options[0].defaultSelected).toBe(true);
    expect(select.element.options[0].selected).toBe(true);
    expect(select.element.options[0].text).toBe(head);
    expect(select.element.options[0].value).toBe(uSelect.headValue);
    expect(select.element.options[1].disabled).toBe(false);
    expect(select.element.options[1].defaultSelected).toBe(false);
    expect(select.element.options[1].selected).toBe(false);
    expect(select.element.options[1].text).toBe(options[0].listText);
    expect(select.element.options[1].value).toBe(options[0].listValue);
    expect(select.element.options[2].disabled).toBe(false);
    expect(select.element.options[2].defaultSelected).toBe(false);
    expect(select.element.options[2].selected).toBe(false);
    expect(select.element.options[2].text).toBe(options[1].listText);
    expect(select.element.options[2].value).toBe(options[1].listValue);
  });

  it('should construct class instance with options, default value and head', () => {
    // when
    const select = new uSelect(element, head);
    select.setOptions(options, options[1].listValue);
    // then
    expect(select.element).toBe(element);
    expect(select.element.options.length).toBe(options.length + 1);
    expect(select.element.options[0].disabled).toBe(true);
    expect(select.element.options[0].defaultSelected).toBe(true);
    expect(select.element.options[0].selected).toBe(false);
    expect(select.element.options[0].text).toBe(head);
    expect(select.element.options[0].value).toBe(uSelect.headValue);
    expect(select.element.options[1].disabled).toBe(false);
    expect(select.element.options[1].defaultSelected).toBe(false);
    expect(select.element.options[1].selected).toBe(false);
    expect(select.element.options[1].text).toBe(options[0].listText);
    expect(select.element.options[1].value).toBe(options[0].listValue);
    expect(select.element.options[2].disabled).toBe(false);
    expect(select.element.options[2].defaultSelected).toBe(false);
    expect(select.element.options[2].selected).toBe(true);
    expect(select.element.options[2].text).toBe(options[1].listText);
    expect(select.element.options[2].value).toBe(options[1].listValue);
  });

  it('should bind DOM option text with model property', (done) => {
    // given
    const select = new uSelect(element);
    select.setOptions(options);
    const newValue = 'new';
    // when
    options[0].listText = newValue;
    // then
    setTimeout(() => {
      expect(select.element.options[0].text).toBe(newValue);
      done();
    }, 100);
  });

  it('should set selected option', () => {
    // given
    const select = new uSelect(element);
    select.setOptions(options, options[1].listValue);

    expect(select.element.options[1].selected).toBe(true);
    // when
    select.selected = options[0].listValue;
    // then
    expect(select.element.options[1].selected).toBe(false);
    expect(select.element.options[0].selected).toBe(true);
  });

  it('should set "all" option and render when setting options', () => {
    // given
    const select = new uSelect(element);
    select.showAllOption(allText);

    expect(select.element.options.length).toBe(1);
    // when
    select.setOptions(options, options[1].listValue);
    // then
    expect(select.element.options.length).toBe(3);
    expect(select.element.options[0].value).toBe(uSelect.allValue);
    expect(select.element.options[0].text).toBe(allText);
    expect(select.element.options[2].selected).toBe(true);
  });

  it('should add/remove "all" option', () => {
    // given
    const select = new uSelect(element);
    select.setOptions(options, options[1].listValue);

    expect(select.element.options.length).toBe(2);
    expect(select.element.options[1].selected).toBe(true);
    // when
    select.showAllOption(allText);
    // then
    expect(select.element.options.length).toBe(3);
    expect(select.element.options[0].value).toBe(uSelect.allValue);
    expect(select.element.options[0].text).toBe(allText);
    expect(select.element.options[2].selected).toBe(true);
    // when
    select.hideAllOption();
    // then
    expect(select.element.options.length).toBe(2);
    expect(select.element.options[0].value).toBe(options[0].listValue);
    expect(select.element.options[0].text).toBe(options[0].listText);
    expect(select.element.options[1].value).toBe(options[1].listValue);
    expect(select.element.options[1].text).toBe(options[1].listText);
    expect(select.element.options[1].selected).toBe(true);
  });

  it('should set "all" option and render when setting options with header', () => {
    // given
    const select = new uSelect(element, head);
    select.showAllOption(allText);

    expect(select.element.options.length).toBe(2);
    // when
    select.setOptions(options, options[1].listValue);
    // then
    expect(select.element.options.length).toBe(4);
    expect(select.element.options[0].value).toBe(uSelect.headValue);
    expect(select.element.options[0].text).toBe(head);
    expect(select.element.options[1].value).toBe(uSelect.allValue);
    expect(select.element.options[1].text).toBe(allText);
    expect(select.element.options[3].selected).toBe(true);
  });

  it('should add/remove "all" option with head set', () => {
    // given
    const select = new uSelect(element, head);
    select.setOptions(options, options[1].listValue);

    expect(select.element.options.length).toBe(3);
    expect(select.element.options[2].selected).toBe(true);
    // when
    select.showAllOption(allText);
    // then
    expect(select.element.options.length).toBe(4);
    expect(select.element.options[1].value).toBe(uSelect.allValue);
    expect(select.element.options[1].text).toBe(allText);
    expect(select.element.options[3].selected).toBe(true);
    // when
    select.hideAllOption();
    // then
    expect(select.element.options.length).toBe(3);
    expect(select.element.options[1].value).toBe(options[0].listValue);
    expect(select.element.options[1].text).toBe(options[0].listText);
    expect(select.element.options[2].value).toBe(options[1].listValue);
    expect(select.element.options[2].text).toBe(options[1].listText);
    expect(select.element.options[0].selected).toBe(true);
  });

  it('should remove option from select elements', () => {
    // given
    const select = new uSelect(element);
    select.setOptions(options, options[1].listValue);

    expect(select.element.options.length).toBe(options.length);
    // when
    select.remove(options[0].listValue);
    // then
    expect(select.element).toBe(element);
    expect(select.element.options.length).toBe(options.length - 1);
    expect(select.element.options[0].disabled).toBe(false);
    expect(select.element.options[0].defaultSelected).toBe(false);
    expect(select.element.options[0].selected).toBe(true);
    expect(select.element.options[0].text).toBe(options[1].listText);
    expect(select.element.options[0].value).toBe(options[1].listValue);
  });

});
