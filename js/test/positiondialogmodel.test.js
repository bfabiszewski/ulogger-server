/*
 * μlogger
 *
 * Copyright(C) 2020 Bartek Fabiszewski (www.fabiszewski.net)
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
import PositionDialogModel from '../src/positiondialogmodel.js';
import TrackFactory from './helpers/trackfactory.js';
import uDialog from '../src/dialog.js';
import uObserve from '../src/observe.js';
import uState from '../src/state.js';

describe('PositionDialogModel tests', () => {

  let dm;
  let positionIndex;
  let track;
  const comment = 'comment';

  beforeEach(() => {
    config.reinitialize();
    lang.init(config);
    spyOn(lang, '_').and.returnValue('{placeholder}');
    positionIndex = 0;
    const state = new uState();
    track = TrackFactory.getTrack();
    track.positions[positionIndex].comment = comment;
    state.currentTrack = track;
    dm = new PositionDialogModel(state, positionIndex);
    spyOn(track.positions[positionIndex], 'save').and.returnValue(Promise.resolve());
    spyOn(track.positions[positionIndex], 'delete').and.returnValue(Promise.resolve());
    spyOn(uObserve, 'forceUpdate');
    spyOn(window, 'alert');
  });

  afterEach(() => {
    document.body.innerHTML = '';
    uObserve.unobserveAll(lang);
  });

  it('should create instance', () => {
    expect(dm).toBeDefined();
    expect(dm.position).toBe(track.positions[positionIndex]);
  });

  it('should show dialog with position comment in textarea', () => {
    // when
    dm.init();
    // then
    expect(document.querySelector('#modal')).toBeInstanceOf(HTMLDivElement);
    expect(dm.dialog.element.querySelector("[data-bind='onPositionUpdate']")).toBeInstanceOf(HTMLButtonElement);
    expect(dm.dialog.element.querySelector("[data-bind='comment']").value).toEqual(comment);
  });

  it('should hide edit dialog on negative button clicked', (done) => {
    // given
    dm.init();
    const button = dm.dialog.element.querySelector("[data-bind='onCancel']");
    // when
    button.click();
    // then
    setTimeout(() => {
      expect(document.querySelector('#modal')).toBe(null);
      done();
    }, 100);
  });

  it('should update position comment and hide edit dialog on positive button clicked', (done) => {
    // given
    spyOn(dm, 'validate').and.returnValue(true);
    dm.init();
    const button = dm.dialog.element.querySelector("[data-bind='onPositionUpdate']");
    const commentEl = dm.dialog.element.querySelector("[data-bind='comment']");
    const newComment = 'newcomment';
    // when
    commentEl.value = newComment;
    commentEl.dispatchEvent(new Event('change'));
    button.click();
    // then
    setTimeout(() => {
      expect(track.positions[positionIndex].save).toHaveBeenCalledTimes(1);
      expect(track.positions[positionIndex].comment).toBe(newComment);
      expect(document.querySelector('#modal')).toBe(null);
      expect(uObserve.forceUpdate).toHaveBeenCalledWith(dm.state, 'currentTrack');
      done();
    }, 100);
  });

  it('should show confirmation dialog on position delete button click', (done) => {
    // given
    spyOn(uDialog, 'isConfirmed').and.returnValue(false);
    dm.init();
    const button = dm.dialog.element.querySelector("[data-bind='onPositionDelete']");
    // when
    button.click();
    // then
    setTimeout(() => {
      expect(uDialog.isConfirmed).toHaveBeenCalledTimes(1);
      done();
    }, 100);
  });

  it('should delete user and hide dialog on confirmation dialog accepted', (done) => {
    // given
    spyOn(uDialog, 'isConfirmed').and.returnValue(true);
    dm.init();
    const button = dm.dialog.element.querySelector("[data-bind='onPositionDelete']");
    // when
    button.click();
    // then
    setTimeout(() => {
      expect(dm.position.delete).toHaveBeenCalledTimes(1);
      expect(dm.state.currentTrack.length).toBe(1);
      expect(document.querySelector('#modal')).toBe(null);
      done();
    }, 100);
  });

  it('should positively validate form, check comment was modified', (done) => {
    // given
    dm.model.comment = track.positions[positionIndex].comment + '1234';
    // when
    const result = dm.validate();
    // then
    setTimeout(() => {
      expect(result).toBeTrue();
      done();
    }, 100);
  });

  it('should negatively validate form, check comment was not modified', (done) => {
    // given
    dm.model.comment = track.positions[positionIndex].comment;
    // when
    const result = dm.validate();
    // then
    setTimeout(() => {
      expect(result).toBeFalse();
      done();
    }, 100);
  });

});

