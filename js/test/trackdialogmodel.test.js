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
import TrackDialogModel from '../src/trackdialogmodel.js';
import TrackFactory from './helpers/trackfactory.js';
import uDialog from '../src/dialog.js';
import uObserve from '../src/observe.js';
import uState from '../src/state.js';


describe('TrackDialogModel tests', () => {

  let dm;
  let mockVM;

  beforeEach(() => {
    config.reinitialize();
    config.interval = 10;
    lang.init(config);
    spyOn(lang, '_').and.returnValue('{placeholder}');
    mockVM = { state: new uState(), onTrackDeleted: {} };
    dm = new TrackDialogModel(mockVM);
    dm.track = TrackFactory.getTrack();
    spyOn(dm.track, 'delete').and.returnValue(Promise.resolve());
    spyOn(dm.track, 'saveMeta').and.returnValue(Promise.resolve());
    spyOn(dm.track, 'setName');
    spyOn(window, 'alert');
  });

  afterEach(() => {
    document.body.innerHTML = '';
    uObserve.unobserveAll(lang);
  });

  it('should create instance with parent view model as parameter', () => {
    expect(dm).toBeDefined();
    expect(dm.trackVM).toBe(mockVM);
  });

  it('should show dialog for current track', () => {
    // when
    dm.init();
    // then
    expect(document.querySelector('#modal')).toBeInstanceOf(HTMLDivElement);
  });

  it('should show confirmation dialog on track delete button click', (done) => {
    // given
    spyOn(uDialog, 'isConfirmed').and.returnValue(false);
    dm.init();
    const button = dm.dialog.element.querySelector("[data-bind='onTrackDelete']");
    // when
    button.click();
    // then
    setTimeout(() => {
      expect(uDialog.isConfirmed).toHaveBeenCalledTimes(1);
      done();
    }, 100);
  });

  it('should delete track and hide dialog on confirmation dialog accepted', (done) => {
    // given
    spyOn(mockVM, 'onTrackDeleted');
    spyOn(uDialog, 'isConfirmed').and.returnValue(true);
    dm.init();
    const button = dm.dialog.element.querySelector("[data-bind='onTrackDelete']");
    // when
    button.click();
    // then
    setTimeout(() => {
      expect(dm.track.delete).toHaveBeenCalledTimes(1);
      expect(mockVM.onTrackDeleted).toHaveBeenCalledTimes(1);
      expect(document.querySelector('#modal')).toBe(null);
      done();
    }, 100);
  });

  it('should update track name and hide dialog on positive button clicked', (done) => {
    // given
    spyOn(dm, 'validate').and.returnValue(true);
    dm.init();
    const button = dm.dialog.element.querySelector("[data-bind='onTrackUpdate']");
    const trackEl = dm.dialog.element.querySelector("[data-bind='trackname']");
    const newName = 'new name';
    // when
    trackEl.value = newName;
    trackEl.dispatchEvent(new Event('change'));
    button.click();
    // then
    setTimeout(() => {
      expect(dm.track.setName).toHaveBeenCalledTimes(1);
      expect(dm.track.setName).toHaveBeenCalledWith(newName);
      expect(dm.track.saveMeta).toHaveBeenCalledTimes(1);
      expect(document.querySelector('#modal')).toBe(null);
      done();
    }, 100);
  });

  it('should do nothing on positive button clicked and false validation', (done) => {
    // given
    spyOn(dm, 'validate').and.returnValue(false);
    dm.init();
    const button = dm.dialog.element.querySelector("[data-bind='onTrackUpdate']");
    const trackEl = dm.dialog.element.querySelector("[data-bind='trackname']");
    // when
    trackEl.value = 'new name';
    trackEl.dispatchEvent(new Event('change'));
    button.click();
    // then
    setTimeout(() => {
      expect(dm.track.setName).not.toHaveBeenCalled();
      expect(dm.track.saveMeta).not.toHaveBeenCalled();
      expect(document.querySelector('#modal')).toBeInstanceOf(HTMLDivElement);
      done();
    }, 100);
  });

  it('should hide dialog on negative button clicked', (done) => {
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

  it('should quietly return false if track name is not changed', () => {
    // given
    dm.track.name = 'test';
    dm.model.trackname = dm.track.name;
    // when
    const result = dm.validate();
    // then
    expect(result).toBe(false);
    expect(window.alert).not.toHaveBeenCalled();
  });

  it('should return false and raise alert if track name is empty', () => {
    // given
    dm.track.name = 'test';
    dm.model.trackname = '';
    // when
    const result = dm.validate();
    // then
    expect(result).toBe(false);
    expect(window.alert).toHaveBeenCalledTimes(1);
  });

  it('should return true on valid track name', () => {
    // given
    dm.track.name = 'test';
    dm.model.trackname = 'new name';
    // when
    const result = dm.validate();
    // then
    expect(result).toBe(true);
    expect(window.alert).not.toHaveBeenCalled();
  });
});
