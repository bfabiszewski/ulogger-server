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

import { auth, config, lang } from '../src/initializer.js';
import Fixture from './helpers/fixture.js';
import TrackFactory from './helpers/trackfactory.js';
import TrackViewModel from '../src/trackviewmodel.js';
import ViewModel from '../src/viewmodel.js';
import uObserve from '../src/observe.js';
import uPositionSet from '../src/positionset.js';
import uState from '../src/state.js';
import uTrack from '../src/track.js';
import uUser from '../src/user.js';
import uUtils from '../src/utils.js';

describe('TrackViewModel tests', () => {

  let vm;
  let state;
  /** @type {HTMLSelectElement} */
  let trackEl;
  /** @type {HTMLDivElement} */
  let summaryEl;
  /** @type {HTMLInputElement} */
  let latestEl;
  /** @type {HTMLAnchorElement} */
  let exportKmlEl;
  /** @type {HTMLAnchorElement} */
  let exportGpxEl;
  /** @type {HTMLAnchorElement} */
  let importGpxEl;
  /** @type {HTMLAnchorElement} */
  let forceReloadEl;
  /** @type {HTMLInputElement} */
  let autoReloadEl;
  /** @type {HTMLInputElement} */
  let inputFileEl;
  /** @type {HTMLAnchorElement} */
  let trackEditEl;
  let tracks;
  let track1;
  let track2;
  let positions;
  let user;
  const MAX_FILE_SIZE = 10;

  beforeEach((done) => {
    Fixture.load('main-authorized.html')
      .then(() => done())
      .catch((e) => done.fail(e));
  });

  beforeEach(() => {
    config.reinitialize();
    config.interval = 10;
    lang.init(config);
    spyOn(lang, '_').and.returnValue('{placeholder}');
    trackEl = document.querySelector('#track');
    summaryEl = document.querySelector('#summary');
    latestEl = document.querySelector('#latest');
    exportKmlEl = document.querySelector('#export-kml');
    exportGpxEl = document.querySelector('#export-gpx');
    importGpxEl = document.querySelector('#import-gpx');
    forceReloadEl = document.querySelector('#force-reload');
    inputFileEl = document.querySelector('#input-file');
    autoReloadEl = document.querySelector('#auto-reload');
    trackEditEl = document.querySelector('#edittrack');
    const maxEl = document.querySelector('input[name="MAX_FILE_SIZE"]');
    maxEl.value = MAX_FILE_SIZE;
    state = new uState();
    vm = new TrackViewModel(state);
    track1 = TrackFactory.getTrack(0, { id: 1, name: 'track1' });
    track2 = TrackFactory.getTrack(0, { id: 2, name: 'track2' });
    tracks = [
      track1,
      track2
    ];
    positions = [ TrackFactory.getPosition() ];
    user = new uUser(1, 'testUser');
  });

  afterEach(() => {
    Fixture.clear();
    uObserve.unobserveAll(lang);
    auth.user = null;
  });

  it('should create instance with state as parameter', () => {
    expect(vm).toBeInstanceOf(ViewModel);
    expect(vm.importEl).toBeInstanceOf(HTMLInputElement);
    expect(vm.select.element).toBeInstanceOf(HTMLSelectElement);
    expect(vm.state).toBe(state);
  });

  it('should load track list and fetch first track on current user change', (done) => {
    // given
    spyOn(uTrack, 'fetchList').and.returnValue(Promise.resolve(tracks));
    spyOn(uPositionSet, 'fetch').and.returnValue(Promise.resolve(positions));
    vm.init();
    // when
    state.currentUser = user;
    // then
    expect(uObserve.isObserved(vm.model, 'trackList')).toBe(true);
    setTimeout(() => {
      expect(uTrack.fetchList).toHaveBeenCalledWith(state.currentUser);
      expect(uPositionSet.fetch).toHaveBeenCalledWith({ userid: user.id, trackid: track1.id });
      expect(trackEl.options.length).toBe(tracks.length);
      expect(trackEl.options[0].selected).toBe(true);
      expect(trackEl.options[0].value).toBe(track1.listValue);
      expect(state.currentTrack).toBe(track1);
      expect(state.currentTrack.length).toBe(positions.length);
      expect(vm.model.currentTrackId).toBe(track1.listValue);
      expect(summaryEl.innerText.length).not.toBe(0);
      done();
    }, 100);
  });

  it('should clear current track on empty track list loaded on current user change', (done) => {
    // given
    spyOn(uTrack, 'fetchList').and.returnValue(Promise.resolve([]));
    spyOn(uPositionSet, 'fetch').and.returnValue(Promise.resolve(positions));
    vm.init();
    // when
    state.currentUser = user;
    // then
    setTimeout(() => {
      expect(uTrack.fetchList).toHaveBeenCalledWith(state.currentUser);
      expect(uPositionSet.fetch).not.toHaveBeenCalled();
      expect(trackEl.options.length).toBe(0);
      expect(state.currentTrack).toBe(null);
      expect(vm.model.currentTrackId).toBe('');
      expect(summaryEl.innerText.length).toBe(0);
      done();
    }, 100);
  });

  it('should load track list, load user latest position and select coresponding track on current user change', (done) => {
    // given
    positions[0].trackid = track2.id;
    positions[0].trackname = track2.name;
    spyOn(uPositionSet, 'fetch').and.returnValue(Promise.resolve(positions));
    spyOn(uTrack, 'fetchList').and.returnValue(Promise.resolve(tracks));
    vm.model.showLatest = true;
    state.showLatest = true;
    vm.init();
    // when
    state.currentUser = user;
    // then
    setTimeout(() => {
      expect(uTrack.fetchList).toHaveBeenCalledWith(state.currentUser);
      expect(uPositionSet.fetch).toHaveBeenCalledWith({ userid: user.id, last: true });
      expect(trackEl.options.length).toBe(tracks.length);
      expect(trackEl.options[1].selected).toBe(true);
      expect(trackEl.options[1].value).toBe(track2.listValue);
      expect(state.currentTrack.id).toEqual(track2.id);
      expect(state.currentTrack.name).toEqual(track2.name);
      expect(state.currentTrack.length).toBe(positions.length);
      expect(vm.model.currentTrackId).toBe(track2.listValue);
      expect(summaryEl.innerText.length).not.toBe(0);
      done();
    }, 100);
  });

  it('should clear track when no user is selected on user list', (done) => {
    // given
    const options = '<option selected value="1">track1</option><option value="2">track2</option>';
    trackEl.insertAdjacentHTML('afterbegin', options);
    vm.model.trackList = tracks;
    vm.model.currentTrackId = track1.listValue;
    state.currentTrack = track1;
    state.currentUser = user;
    vm.init();
    // when
    state.currentUser = null;
    // then
    setTimeout(() => {
      expect(trackEl.options.length).toBe(0);
      expect(state.currentTrack).toBe(null);
      expect(vm.model.currentTrackId).toBe('');
      expect(summaryEl.innerText.length).toBe(0);
      done();
    }, 100);
  });

  it('should load track when selected in form select options', (done) => {
    // given
    spyOn(uPositionSet, 'fetch').and.returnValue(Promise.resolve(positions));
    const options = '<option selected value="1">track1</option><option value="2">track2</option>';
    trackEl.insertAdjacentHTML('afterbegin', options);
    vm.model.trackList = tracks;
    vm.model.currentTrackId = track1.listValue;
    state.currentTrack = track1;
    state.currentUser = user;
    vm.init();
    // when
    trackEl.value = track2.listValue;
    trackEl.dispatchEvent(new Event('change'));
    // then
    setTimeout(() => {
      expect(uPositionSet.fetch).toHaveBeenCalledWith({ userid: user.id, trackid: track2.id });
      expect(trackEl.options.length).toBe(tracks.length);
      expect(trackEl.options[0].value).toBe(track1.listValue);
      expect(trackEl.options[1].value).toBe(track2.listValue);
      expect(trackEl.options[1].selected).toBe(true);
      expect(state.currentTrack).toBe(track2);
      expect(state.currentTrack.length).toBe(positions.length);
      expect(vm.model.currentTrackId).toBe(track2.listValue);
      expect(summaryEl.innerText.length).not.toBe(0);
      done();
    }, 100);
  });

  it('should load user latest position when "show latest" is checked and insert new track to track list', (done) => {
    // given
    positions[0].trackid = 100;
    positions[0].trackname = 'new track';
    spyOn(uPositionSet, 'fetch').and.returnValue(Promise.resolve(positions));
    const options = '<option selected value="1">track1</option><option value="2">track2</option>';
    trackEl.insertAdjacentHTML('afterbegin', options);
    const optLength = trackEl.options.length;
    vm.model.trackList = tracks;
    vm.model.currentTrackId = track1.listValue;
    state.currentTrack = track1;
    state.currentUser = user;
    vm.init();
    // when
    latestEl.checked = true;
    latestEl.dispatchEvent(new Event('change'));
    // then
    setTimeout(() => {
      expect(uPositionSet.fetch).toHaveBeenCalledWith({ userid: user.id, last: true });
      expect(state.currentTrack.id).toBe(positions[0].trackid);
      expect(state.currentTrack.name).toBe(positions[0].trackname);
      expect(state.currentTrack.length).toBe(positions.length);
      expect(trackEl.options.length).toBe(optLength + 1);
      expect(trackEl.options.length).toBe(tracks.length);
      expect(trackEl.value).toBe(state.currentTrack.listValue);
      expect(trackEl.options[0].value).toBe(state.currentTrack.listValue);
      expect(trackEl.options[0].selected).toBe(true);
      expect(state.showLatest).toBe(true);
      expect(vm.model.currentTrackId).toBe(state.currentTrack.listValue);
      expect(summaryEl.innerText.length).not.toBe(0);
      done();
    }, 100);
  });

  it('should load user latest position when "show latest" is checked and select respective track in list', (done) => {
    // given
    positions[0].trackid = track2.id;
    positions[0].trackname = track2.name;
    spyOn(uPositionSet, 'fetch').and.returnValue(Promise.resolve(positions));
    const options = '<option selected value="1">track1</option><option value="2">track2</option>';
    trackEl.insertAdjacentHTML('afterbegin', options);
    const optLength = trackEl.options.length;
    vm.model.trackList = tracks;
    vm.model.currentTrackId = track1.listValue;
    state.currentTrack = track1;
    state.currentUser = user;
    vm.init();
    // when
    latestEl.checked = true;
    latestEl.dispatchEvent(new Event('change'));
    // then
    setTimeout(() => {
      expect(uPositionSet.fetch).toHaveBeenCalledWith({ userid: user.id, last: true });
      expect(state.currentTrack.id).toBe(track2.id);
      expect(state.currentTrack.name).toBe(track2.name);
      expect(state.currentTrack.length).toBe(positions.length);
      expect(trackEl.options.length).toBe(optLength);
      expect(trackEl.options.length).toBe(tracks.length);
      expect(trackEl.value).toBe(state.currentTrack.listValue);
      expect(trackEl.options[1].value).toBe(state.currentTrack.listValue);
      expect(trackEl.options[1].selected).toBe(true);
      expect(state.showLatest).toBe(true);
      expect(vm.model.currentTrackId).toBe(state.currentTrack.listValue);
      expect(summaryEl.innerText.length).not.toBe(0);
      done();
    }, 100);
  });

  it('should load all current track positions when "show latest" is unchecked', (done) => {
    // given
    positions[0].trackid = track1.id;
    positions[0].trackname = track1.name;
    spyOn(uPositionSet, 'fetch').and.returnValue(Promise.resolve(positions));
    const options = '<option selected value="1">track1</option><option value="2">track2</option>';
    trackEl.insertAdjacentHTML('afterbegin', options);
    const optLength = trackEl.options.length;
    vm.model.trackList = tracks;
    vm.model.currentTrackId = track1.listValue;
    vm.model.showLatest = true;
    state.currentUser = user;
    state.showLatest = true;
    vm.init();
    state.currentTrack = track1;
    latestEl.checked = true;
    // when
    latestEl.checked = false;
    latestEl.dispatchEvent(new Event('change'));
    // then
    setTimeout(() => {
      expect(uPositionSet.fetch).toHaveBeenCalledWith({ userid: user.id, trackid: track1.id });
      expect(state.currentTrack.id).toBe(track1.id);
      expect(state.currentTrack.name).toBe(track1.name);
      expect(state.currentTrack.length).toBe(positions.length);
      expect(trackEl.options.length).toBe(optLength);
      expect(trackEl.options.length).toBe(tracks.length);
      expect(trackEl.value).toBe(state.currentTrack.listValue);
      expect(trackEl.options[0].value).toBe(state.currentTrack.listValue);
      expect(trackEl.options[0].selected).toBe(true);
      expect(state.showLatest).toBe(false);
      expect(vm.model.currentTrackId).toBe(state.currentTrack.listValue);
      expect(summaryEl.innerText.length).not.toBe(0);
      done();
    }, 100);
  });

  it('should clear track list and fetch all users positions on "all users" option selected', (done) => {
    // given
    spyOn(uPositionSet, 'fetch').and.returnValue(Promise.resolve(positions));
    const options = '<option selected value="1">track1</option><option value="2">track2</option>';
    trackEl.insertAdjacentHTML('afterbegin', options);
    vm.model.trackList = tracks;
    vm.model.currentTrackId = track1.listValue;
    state.currentTrack = track1;
    state.currentUser = user;
    state.showLatest = true;
    latestEl.checked = true;
    vm.init();
    // when
    state.showAllUsers = true;
    // then
    setTimeout(() => {
      expect(uPositionSet.fetch).toHaveBeenCalledWith({ last: true });
      expect(trackEl.options.length).toBe(0);
      // noinspection JSUnresolvedFunction
      expect(state.currentTrack).not.toBeInstanceOf(uTrack);
      expect(state.currentTrack).toBeInstanceOf(uPositionSet);
      expect(state.currentTrack.positions.length).toBe(positions.length);
      expect(state.currentTrack.positions[0].id).toBe(positions[0].id);
      expect(state.currentTrack.length).toBe(positions.length);
      expect(vm.model.currentTrackId).toBe('');
      expect(summaryEl.innerText.length).not.toBe(0);
      done();
    }, 100);
  });

  it('should clear current track if "show latest" is unchecked when "all users" is set', (done) => {
    // given
    spyOn(uPositionSet, 'fetch').and.returnValue(Promise.resolve(positions));
    vm.model.trackList = [];
    vm.model.currentTrackId = '';
    vm.model.showLatest = true;
    state.currentUser = null;
    state.showLatest = true;
    state.showAllUsers = true;
    state.currentTrack = TrackFactory.getPositionSet(1);
    latestEl.checked = true;
    vm.init();
    // when
    latestEl.checked = false;
    latestEl.dispatchEvent(new Event('change'));
    // then
    setTimeout(() => {
      expect(uPositionSet.fetch).not.toHaveBeenCalled();
      expect(state.currentTrack).toBe(null);
      expect(vm.model.currentTrackId).toBe('');
      expect(trackEl.options.length).toBe(0);
      expect(state.showLatest).toBe(false);
      expect(summaryEl.innerText.length).toBe(0);
      done();
    }, 100);
  });

  it('should uncheck "show latest" when selected track in form select options', (done) => {
    // given
    spyOn(uPositionSet, 'fetch').and.returnValue(Promise.resolve(positions));
    const options = '<option selected value="1">track1</option><option value="2">track2</option>';
    trackEl.insertAdjacentHTML('afterbegin', options);
    vm.model.trackList = tracks;
    vm.model.currentTrackId = track1.listValue;
    vm.model.showLatest = true;
    state.currentTrack = track1;
    state.currentUser = user;
    state.showLatest = true;
    latestEl.checked = true;
    vm.init();
    // when
    trackEl.value = track2.listValue;
    trackEl.dispatchEvent(new Event('change'));
    // then
    setTimeout(() => {
      expect(state.showLatest).toBe(false);
      expect(vm.model.showLatest).toBe(false);
      expect(latestEl.checked).toBe(false);
      done();
    }, 100);
  });

  it('should export track to KML on link click', (done) => {
    // given
    spyOn(track1, 'export');
    state.currentTrack = track1;
    vm.init();
    // when
    exportKmlEl.click();
    // then
    setTimeout(() => {
      expect(track1.export).toHaveBeenCalledWith('kml');
      done();
    }, 100);
  });

  it('should export track to GPX on link click', (done) => {
    // given
    spyOn(track1, 'export');
    state.currentTrack = track1;
    vm.init();
    // when
    exportGpxEl.click();
    // then
    setTimeout(() => {
      expect(track1.export).toHaveBeenCalledWith('gpx');
      done();
    }, 100);
  });

  it('should import tracks on link click', (done) => {
    // given
    const imported = [
      TrackFactory.getTrack(0, { id: 3, name: 'track3', user: user }),
      TrackFactory.getTrack(0, { id: 4, name: 'track4', user: user })
    ];
    const file = new File([ 'blob' ], '/path/filepath.gpx');
    spyOn(uTrack, 'import').and.callFake((form) => {
      expect(form.elements['gpx'].files[0]).toEqual(file);
      return Promise.resolve(imported);
    });
    spyOn(uPositionSet, 'fetch').and.returnValue(Promise.resolve(positions));
    spyOn(window, 'alert');
    const options = '<option selected value="1">track1</option><option value="2">track2</option>';
    trackEl.insertAdjacentHTML('afterbegin', options);
    const optLength = trackEl.options.length;
    vm.model.trackList = tracks;
    vm.model.currentTrackId = track1.listValue;
    auth.user = user;
    state.currentTrack = track1;
    state.currentUser = user;
    inputFileEl.onclick = () => {
      const dt = new DataTransfer();
      dt.items.add(file);
      inputFileEl.files = dt.files;
      inputFileEl.dispatchEvent(new Event('change'));
    };
    vm.init();
    // when
    importGpxEl.click();
    // then
    setTimeout(() => {
      expect(uTrack.import).toHaveBeenCalledTimes(1);
      expect(uTrack.import).toHaveBeenCalledWith(jasmine.any(HTMLFormElement), user);
      expect(state.currentTrack).toBe(imported[0]);
      expect(vm.model.currentTrackId).toBe(imported[0].listValue);
      expect(state.currentTrack.length).toBe(positions.length);
      expect(window.alert).toHaveBeenCalledTimes(1);
      expect(trackEl.options.length).toBe(optLength + imported.length);
      expect(vm.model.trackList.length).toBe(optLength + imported.length);
      expect(vm.model.inputFile).toBe('');
      expect(inputFileEl.files.length).toBe(0);
      done();
    }, 100);
  });

  it('should raise error on file size above MAX_FILE_SIZE limit on link click', (done) => {
    // given
    const imported = [
      TrackFactory.getTrack(0, { id: 3, name: 'track3', user: user }),
      TrackFactory.getTrack(0, { id: 4, name: 'track4', user: user })
    ];
    spyOn(uTrack, 'import').and.returnValue(Promise.resolve(imported));
    spyOn(uPositionSet, 'fetch').and.returnValue(Promise.resolve(positions));
    spyOn(uUtils, 'error');
    const options = '<option selected value="1">track1</option><option value="2">track2</option>';
    trackEl.insertAdjacentHTML('afterbegin', options);
    const optLength = trackEl.options.length;
    vm.model.trackList = tracks;
    vm.model.currentTrackId = track1.listValue;
    auth.user = user;
    state.currentTrack = track1;
    state.currentUser = user;
    inputFileEl.onclick = () => {
      const dt = new DataTransfer();
      dt.items.add(new File([ '12345678901' ], 'filepath.gpx'));
      inputFileEl.files = dt.files;
      inputFileEl.dispatchEvent(new Event('change'));
    };
    vm.init();
    // when
    importGpxEl.click();
    // then
    setTimeout(() => {
      expect(uTrack.import).not.toHaveBeenCalled();
      expect(state.currentTrack).toBe(track1);
      expect(vm.model.currentTrackId).toBe(track1.listValue);
      expect(lang._.calls.mostRecent().args[1]).toBe(MAX_FILE_SIZE.toString());
      expect(trackEl.options.length).toBe(optLength);
      expect(vm.model.trackList.length).toBe(optLength);
      done();
    }, 100);
  });

  it('should raise error on non-authorized user', (done) => {
    // given
    const imported = [
      TrackFactory.getTrack(0, { id: 3, name: 'track3', user: user }),
      TrackFactory.getTrack(0, { id: 4, name: 'track4', user: user })
    ];
    const file = new File([ 'blob' ], '/path/filepath.gpx');
    spyOn(uTrack, 'import').and.returnValue(Promise.resolve(imported));
    spyOn(uPositionSet, 'fetch').and.returnValue(Promise.resolve(positions));
    spyOn(uUtils, 'error');
    const options = '<option selected value="1">track1</option><option value="2">track2</option>';
    trackEl.insertAdjacentHTML('afterbegin', options);
    const optLength = trackEl.options.length;
    vm.model.trackList = tracks;
    vm.model.currentTrackId = track1.listValue;
    state.currentTrack = track1;
    state.currentUser = user;
    inputFileEl.onclick = () => {
      const dt = new DataTransfer();
      dt.items.add(file);
      inputFileEl.files = dt.files;
      inputFileEl.dispatchEvent(new Event('change'));
    };
    vm.init();
    // when
    importGpxEl.click();
    // then
    setTimeout(() => {
      expect(uTrack.import).not.toHaveBeenCalled();
      expect(state.currentTrack).toBe(track1);
      expect(vm.model.currentTrackId).toBe(track1.listValue);
      expect(uUtils.error).toHaveBeenCalledTimes(1);
      expect(lang._).toHaveBeenCalledWith('notauthorized');
      expect(trackEl.options.length).toBe(optLength);
      expect(vm.model.trackList.length).toBe(optLength);
      done();
    }, 100);
  });

  it('should restart running auto-reload on config interval change', (done) => {
    // given
    const newInterval = 99;
    spyOn(window, 'prompt').and.returnValue(newInterval);
    spyOn(vm, 'stopAutoReload');
    spyOn(vm, 'startAutoReload');
    vm.timerId = 1;
    vm.init();
    // when
    config.interval = newInterval;
    // then
    setTimeout(() => {
      expect(vm.stopAutoReload).toHaveBeenCalledTimes(1);
      expect(vm.startAutoReload).toHaveBeenCalledTimes(1);
      done();
    }, 100);
  });

  it('should start auto-reload on checkbox checked and stop on checkbox unchecked', (done) => {
    // given
    spyOn(vm, 'onReload').and.callFake(() => {
      // then
      expect(vm.model.autoReload).toBe(true);
      autoReloadEl.checked = false;
      autoReloadEl.dispatchEvent(new Event('change'));
    });
    autoReloadEl.checked = false;
    config.interval = 0.001;
    vm.timerId = 0;
    vm.init();
    // when
    autoReloadEl.checked = true;
    autoReloadEl.dispatchEvent(new Event('change'));
    // then
    setTimeout(() => {
      expect(vm.onReload).toHaveBeenCalledTimes(1);
      expect(vm.model.autoReload).toBe(false);
      expect(autoReloadEl.checked).toBe(false);
      done();
    }, 100);
  });

  it('should show user edit dialog on button click', (done) => {
    // given
    spyOn(vm, 'showDialog');
    // when
    vm.bindAll();
    trackEditEl.click();
    // then
    setTimeout(() => {
      expect(vm.showDialog).toHaveBeenCalledTimes(1);
      done();
    }, 100);
  });

  it('should remove current track from track list and set new current track id', () => {
    // given
    vm.model.trackList = [ track1, track2 ];
    vm.state.currentTrack = track1;
    vm.model.currentTrackId = track1.listValue;
    // when
    vm.onTrackDeleted();
    // then
    expect(vm.model.trackList.length).toBe(1);
    expect(vm.model.currentTrackId).toBe(track2.listValue);
    expect(vm.state.currentTrack).toBe(null);
  });

  it('should remove last remaining element from track list and set empty track id', () => {
    // given
    vm.model.trackList = [ track1 ];
    vm.state.currentTrack = track1;
    vm.model.currentTrackId = track1.listValue;
    // when
    vm.onTrackDeleted();
    // then
    expect(vm.model.trackList.length).toBe(0);
    expect(vm.model.currentTrackId).toBe('');
    expect(vm.state.currentTrack).toBe(null);
  });

  it('show hide element', () => {
    // given
    const element = document.createElement('div');
    // when
    TrackViewModel.setMenuVisible(element, false);
    // then
    expect(element.classList.contains('menu-hidden')).toBe(true);
  });

  it('show shown hidden element', () => {
    // given
    const element = document.createElement('div');
    element.classList.add('menu-hidden');
    // when
    TrackViewModel.setMenuVisible(element, true);
    // then
    expect(element.classList.contains('menu-hidden')).toBe(false);
  });

  describe('on reload clicked', () => {

    it('should reload selected track', (done) => {
      // given
      track1 = TrackFactory.getTrack(2, { id: 1, name: 'track1' });
      spyOn(uPositionSet, 'fetch').and.returnValue(Promise.resolve(positions));
      const options = '<option selected value="1">track1</option><option value="2">track2</option>';
      trackEl.insertAdjacentHTML('afterbegin', options);
      const optLength = trackEl.options.length;
      const posLength = track1.length;
      vm.model.trackList = [ track1, track2 ];
      vm.model.currentTrackId = track1.listValue;
      state.currentTrack = track1;
      state.currentUser = user;
      vm.init();
      // when
      forceReloadEl.click();
      // then
      setTimeout(() => {
        expect(uPositionSet.fetch).toHaveBeenCalledWith({ userid: user.id, trackid: track1.id, afterid: track1.maxId });
        expect(state.currentTrack.length).toBe(posLength + positions.length);
        expect(trackEl.options.length).toBe(optLength);
        expect(trackEl.value).toBe(track1.listValue);
        done();
      }, 100);
    });

    it('should fetch user latest position if "show latest" is checked', (done) => {
      // given
      track1 = TrackFactory.getTrack(1, { id: 1, name: 'track1' });
      positions[0].trackid = track1.id;
      positions[0].trackname = track1.name;
      spyOn(uPositionSet, 'fetch').and.returnValue(Promise.resolve(positions));
      const options = '<option selected value="1">track1</option><option value="2">track2</option>';
      trackEl.insertAdjacentHTML('afterbegin', options);
      const optLength = trackEl.options.length;
      vm.model.trackList = [ track1, track2 ];
      vm.model.currentTrackId = track1.listValue;
      vm.model.showLatest = true;
      state.currentTrack = track1;
      state.currentUser = user;
      state.showLatest = true;
      latestEl.checked = true;
      vm.init();
      // when
      forceReloadEl.click();
      // then
      setTimeout(() => {
        expect(uPositionSet.fetch).toHaveBeenCalledWith({ userid: user.id, last: true });
        expect(state.currentTrack.id).toEqual(track1.id);
        expect(state.currentTrack.name).toEqual(track1.name);
        expect(state.currentTrack.length).toBe(1);
        expect(trackEl.options.length).toBe(optLength);
        expect(trackEl.value).toBe(positions[0].trackid.toString());
        done();
      }, 100);
    });

    it('should fetch user latest position if "show latest" is checked and add track if position is on a new track', (done) => {
      // given
      track1 = TrackFactory.getTrack(1, { id: 1, name: 'track1' });
      positions[0].trackid = 100;
      positions[0].trackname = 'track100';
      spyOn(uPositionSet, 'fetch').and.returnValue(Promise.resolve(positions));
      const options = '<option selected value="1">track1</option><option value="2">track2</option>';
      trackEl.insertAdjacentHTML('afterbegin', options);
      const optLength = trackEl.options.length;
      vm.model.trackList = [ track1, track2 ];
      vm.model.currentTrackId = track1.listValue;
      vm.model.showLatest = true;
      state.currentTrack = track1;
      state.currentUser = user;
      state.showLatest = true;
      latestEl.checked = true;
      vm.init();
      // when
      forceReloadEl.click();
      // then
      setTimeout(() => {
        expect(uPositionSet.fetch).toHaveBeenCalledWith({ userid: user.id, last: true });
        expect(state.currentTrack.id).toEqual(positions[0].trackid);
        expect(state.currentTrack.name).toEqual(positions[0].trackname);
        expect(state.currentTrack.length).toBe(1);
        expect(trackEl.options.length).toBe(optLength + 1);
        expect(trackEl.value).toBe(positions[0].trackid.toString());
        done();
      }, 100);
    });

    it('should fetch all users latest position if "all users" is selected', (done) => {
      // given
      const set = TrackFactory.getPositionSet(2, { id: 1, name: 'track1' });
      set.positions[0].trackid = track1.id;
      set.positions[0].trackname = track1.name;
      set.positions[1].trackid = track2.id;
      set.positions[1].trackname = track2.name;
      spyOn(uPositionSet, 'fetch').and.returnValue(Promise.resolve(set.positions));
      vm.model.trackList = [];
      vm.model.currentTrackId = '';
      vm.model.showLatest = true;
      state.currentTrack = null;
      state.currentUser = null;
      state.showLatest = true;
      state.showAllUsers = true;
      latestEl.checked = true;
      vm.init();
      // when
      forceReloadEl.click();
      // then
      setTimeout(() => {
        expect(uPositionSet.fetch).toHaveBeenCalledWith({ last: true });
        expect(state.currentTrack.length).toEqual(set.length);
        expect(state.currentTrack.positions[0]).toEqual(set.positions[0]);
        expect(state.currentTrack.positions[1]).toEqual(set.positions[1]);
        expect(trackEl.options.length).toBe(0);
        expect(trackEl.value).toBe('');
        done();
      }, 100);
    });

    it('should fetch track list if user is selected and no track is selected', (done) => {
      // given
      spyOn(uTrack, 'fetchList').and.returnValue(Promise.resolve([]));
      vm.model.trackList = [];
      vm.model.currentTrackId = '';
      state.currentTrack = null;
      state.currentUser = user;
      vm.init();
      // when
      forceReloadEl.click();
      // then
      setTimeout(() => {
        expect(uTrack.fetchList).toHaveBeenCalledWith(user);
        expect(state.currentTrack).toBe(null);
        expect(trackEl.options.length).toBe(0);
        expect(trackEl.value).toBe('');
        done();
      }, 100);
    });

    it('should do nothing if no user is selected and no track is selected', (done) => {
      // given
      spyOn(uTrack, 'fetchList');
      spyOn(uPositionSet, 'fetch');
      vm.model.trackList = [];
      vm.model.currentTrackId = '';
      state.currentTrack = null;
      state.currentUser = null;
      vm.init();
      // when
      forceReloadEl.click();
      // then
      setTimeout(() => {
        expect(uTrack.fetchList).not.toHaveBeenCalled();
        expect(uPositionSet.fetch).not.toHaveBeenCalled();
        expect(state.currentTrack).toBe(null);
        expect(trackEl.options.length).toBe(0);
        expect(trackEl.value).toBe('');
        done();
      }, 100);
    });

  });

});
