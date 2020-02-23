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

import { config, lang } from '../src/initializer.js';
import uDialog from '../src/dialog.js';
import uObserve from '../src/observe.js';

describe('Dialog tests', () => {

  let content;
  let dialog;

  beforeEach(() => {
    config.reinitialize();
    lang.init(config);
    spyOn(lang, '_').and.returnValue('{placeholder}');
    content = 'Test content';
    dialog = new uDialog(content);
  });

  afterEach(() => {
    document.body.innerHTML = '';
    uObserve.unobserveAll(lang);
  });

  it('should create dialog with string content', () => {
    // when
    const body = dialog.element.querySelector('#modal-body');
    body.firstChild.remove();
    // then
    expect(body.innerHTML).toBe(content);
    expect(dialog.visible).toBe(false);
  });

  it('should create dialog with node content', () => {
    // given
    content = document.createElement('div');
    dialog = new uDialog(content);
    // when
    const body = dialog.element.querySelector('#modal-body');
    body.firstChild.remove();
    // then
    expect(body.firstChild).toBe(content);
  });

  it('should create dialog with node array content', () => {
    // given
    content = [
      document.createElement('div'),
      document.createElement('div')
    ];
    dialog = new uDialog(content);
    // when
    const body = dialog.element.querySelector('#modal-body');
    body.firstChild.remove();
    // then
    expect(body.children[0]).toBe(content[0]);
    expect(body.children[1]).toBe(content[1]);
  });

  it('should create dialog with node list content', () => {
    // given
    const div1 = document.createElement('div');
    const div2 = document.createElement('div');
    const el = document.createElement('div');
    el.append(div1, div2);
    content = el.childNodes;
    dialog = new uDialog(content);
    // when
    const body = dialog.element.querySelector('#modal-body');
    body.firstChild.remove();
    // then
    expect(body.childNodes).toEqual(content);
  });

  it('should show dialog', () => {
    // when
    dialog.show();
    // then
    expect(document.querySelector('#modal')).toBe(dialog.element);
    expect(dialog.visible).toBe(true);
  });

  it('should destroy dialog', () => {
    // given
    dialog.show();
    // when
    dialog.destroy();
    // then
    expect(document.querySelector('#modal')).toBe(null);
    expect(dialog.visible).toBe(false);
  });

  it('should show confirm dialog', () => {
    // given
    const message = 'confirm message';
    spyOn(window, 'confirm');
    // when
    uDialog.isConfirmed(message);
    // then
    expect(window.confirm).toHaveBeenCalledWith(message);
  });

});
