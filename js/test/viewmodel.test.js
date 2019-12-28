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

import ViewModel from '../src/viewmodel.js';
import uObserve from '../src/observe.js';
import uUtils from '../src/utils.js';

describe('ViewModel tests', () => {

  let model;
  let vm;
  const propertyString = 'propertyString';
  const propertyStringVal = '1';
  const propertyBool = 'propertyBool';
  const propertyBoolVal = false;
  const propertyFunction = 'propertyFunction';

  beforeEach(() => {
    model = {};
    model[propertyString] = propertyStringVal;
    model[propertyBool] = propertyBoolVal;
    // eslint-disable-next-line no-empty-function
    model[propertyFunction] = () => {};
    vm = new ViewModel(model);
  });

  it('should create instance with model as parameter', () => {
    // when
    const viewModel = new ViewModel(model);
    // then
    expect(viewModel.model).toBe(model);
  });

  it('should call bind method with each model property as parameter', () => {
    // given
    spyOn(ViewModel.prototype, 'bind');
    // when
    vm.bindAll();
    // then
    expect(ViewModel.prototype.bind).toHaveBeenCalledTimes(3);
    expect(ViewModel.prototype.bind).toHaveBeenCalledWith(propertyString);
    expect(ViewModel.prototype.bind).toHaveBeenCalledWith(propertyBool);
  });

  it('should set root element', () => {
    // given
    spyOn(ViewModel.prototype, 'bind');
    const rootEl = document.querySelector('body');
    // when
    vm.bindAll(rootEl);
    // then
    expect(vm.root).toEqual(rootEl);
  });

  it('should set up binding between model property and DOM input element', () => {
    // given
    /** @type {HTMLInputElement} */
    const inputElement = uUtils.nodeFromHtml(`<input type="text" value="${propertyStringVal}" data-bind="${propertyString}">`);
    document.body.appendChild(inputElement);
    // when
    vm.bind(propertyString);
    // then
    expect(uObserve.isObserved(vm.model, propertyString)).toBe(true);
    expect(uObserve.isObserved(vm.model, propertyBool)).toBe(false);
    expect(vm.model[propertyString]).toBe(propertyStringVal);
    expect(inputElement.value).toBe(propertyStringVal);
    // when
    inputElement.value = propertyStringVal + 1;
    inputElement.dispatchEvent(new Event('change'));
    // then
    expect(vm.model[propertyString]).toBe(propertyStringVal + 1);
    // when
    vm.model[propertyString] = propertyStringVal;
    // then
    expect(inputElement.value).toBe(propertyStringVal);
  });

  it('should set up binding between model property and DOM select element', () => {
    // given
    const html = `<select data-bind="${propertyString}">
      <option value=""></option>
      <option selected value="${propertyStringVal}"></option>
      </select>`;
    /** @type {HTMLInputElement} */
    const selectElement = uUtils.nodeFromHtml(html);
    document.body.appendChild(selectElement);
    // when
    vm.bind(propertyString);
    // then
    expect(uObserve.isObserved(vm.model, propertyString)).toBe(true);
    expect(uObserve.isObserved(vm.model, propertyBool)).toBe(false);
    expect(vm.model[propertyString]).toBe(propertyStringVal);
    expect(selectElement.value).toBe(propertyStringVal);
    // when
    selectElement.value = '';
    selectElement.dispatchEvent(new Event('change'));
    // then
    expect(vm.model[propertyString]).toBe('');
    // when
    vm.model[propertyString] = propertyStringVal;
    // then
    expect(selectElement.value).toBe(propertyStringVal);
  });

  it('should set up binding between model property and DOM checkbox element', () => {
    // given
    /** @type {HTMLInputElement} */
    const checkboxElement = uUtils.nodeFromHtml(`<input type="checkbox" data-bind="${propertyBool}">`);
    document.body.appendChild(checkboxElement);
    checkboxElement.checked = false;
    // when
    vm.bind(propertyBool);
    // then
    expect(uObserve.isObserved(vm.model, propertyBool)).toBe(true);
    expect(uObserve.isObserved(vm.model, propertyString)).toBe(false);
    expect(vm.model[propertyBool]).toBe(propertyBoolVal);
    expect(checkboxElement.checked).toBe(propertyBoolVal);
    // when
    const newValue = !propertyBoolVal;
    checkboxElement.checked = newValue;
    checkboxElement.dispatchEvent(new Event('change'));
    // then
    expect(vm.model[propertyBool]).toBe(newValue);
    // when
    vm.model[propertyBool] = !newValue;
    // then
    expect(checkboxElement.checked).toBe(!newValue);
  });

  it('should bind DOM anchor element click event to model property', () => {
    // given
    /** @type {HTMLAnchorElement} */
    const anchorElement = uUtils.nodeFromHtml(`<a data-bind="${propertyFunction}">`);
    document.body.appendChild(anchorElement);
    spyOn(model, propertyFunction);
    // when
    vm.bind(propertyFunction);
    // then
    expect(uObserve.isObserved(vm.model, propertyFunction)).toBe(false);
    expect(vm.model[propertyFunction]).toBeInstanceOf(Function);
    // when
    anchorElement.dispatchEvent(new Event('click'));
    // then
    expect(model[propertyFunction]).toHaveBeenCalledTimes(1);
    expect(model[propertyFunction]).toHaveBeenCalledWith(jasmine.any(Event));
    expect(model[propertyFunction].calls.mostRecent().args[0].target).toBe(anchorElement);
  });

  it('should bind DOM div element to model property', () => {
    // given
    /** @type {HTMLDivElement} */
    const divElement = uUtils.nodeFromHtml(`<div data-bind="${propertyString}"></div>`);
    document.body.appendChild(divElement);
    const newContent = '<span>new value</span>';
    // when
    vm.bind(propertyString);
    // then
    expect(uObserve.isObserved(vm.model, propertyString)).toBe(true);
    // when
    model[propertyString] = newContent;
    // then
    expect(divElement.innerHTML).toBe(newContent);
  });

  it('should start observing model property', () => {
    // given
    // eslint-disable-next-line no-empty-function
    const callback = () => {};
    spyOn(uObserve, 'observe');
    // when
    vm.onChanged(propertyString, callback);
    // then
    expect(uObserve.observe).toHaveBeenCalledTimes(1);
    expect(uObserve.observe).toHaveBeenCalledWith(vm.model, propertyString, callback);
  });

  it('should stop observing model property', () => {
    // given
    // eslint-disable-next-line no-empty-function
    const callback = () => {};
    spyOn(uObserve, 'unobserve');
    // when
    vm.unsubscribe(propertyString, callback);
    // then
    expect(uObserve.unobserve).toHaveBeenCalledTimes(1);
    expect(uObserve.unobserve).toHaveBeenCalledWith(vm.model, propertyString, callback);
  });

  it('should get bound element by property name', () => {
    // given
    const property = 'property';
    spyOn(vm.root, 'querySelector');
    // when
    vm.getBoundElement(property);
    // then
    expect(vm.root.querySelector).toHaveBeenCalledWith(`[data-bind='${property}']`);
  });

});
