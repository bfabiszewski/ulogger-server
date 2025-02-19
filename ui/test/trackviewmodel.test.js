/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import { auth, config, lang } from '../src/Initializer.js';
import Alert from '../src/Alert.js';
import Fixture from './helpers/fixture.js';
import Http from '../src/Http.js';
import Observer from '../src/Observer.js';
import PositionSet from '../src/PositionSet.js';
import State from '../src/State.js';
import Track from '../src/Track.js';
import TrackFactory from './helpers/trackfactory.js';
import TrackViewModel from '../src/models/TrackViewModel.js';
import User from '../src/User.js';
import ViewModel from '../src/ViewModel.js';

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
    state = new State();
    vm = new TrackViewModel(state);
    track1 = TrackFactory.getTrack(0, { id: 1, name: 'track1' });
    track2 = TrackFactory.getTrack(0, { id: 2, name: 'track2' });
    tracks = [
      track1,
      track2
    ];
    positions = [ TrackFactory.getPosition() ];
    user = new User(1, 'testUser');
  });

  afterEach(() => {
    Fixture.clear();
    Observer.unobserveAll(lang);
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
    spyOn(Track, 'fetchList').and.resolveTo(tracks);
    spyOn(track1, 'fetchPositions').and.callThrough();
    spyOn(Http, 'get').withArgs(`api/tracks/${track1.id}/positions`).and.resolveTo(positions);
    vm.init();
    // when
    state.currentUser = user;
    // then
    expect(Observer.isObserved(vm.model, 'trackList')).toBe(true);
    setTimeout(() => {
      expect(Track.fetchList).toHaveBeenCalledWith(state.currentUser);
      expect(track1.fetchPositions).toHaveBeenCalledWith();
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
    spyOn(Track, 'fetchList').and.resolveTo([]);
    spyOn(track1, 'fetchPositions').and.callThrough();
    vm.init();
    // when
    state.currentUser = user;
    // then
    setTimeout(() => {
      expect(Track.fetchList).toHaveBeenCalledWith(state.currentUser);
      expect(track1.fetchPositions).not.toHaveBeenCalled();
      expect(trackEl.options.length).toBe(0);
      expect(state.currentTrack).toBe(null);
      expect(vm.model.currentTrackId).toBe('');
      expect(summaryEl.innerText.length).toBe(0);
      done();
    }, 100);
  });

  it('should load track list, load user latest position and select corresponding track on current user change', (done) => {
    // given
    positions[0].trackId = track2.id;
    positions[0].trackName = track2.name;
    track2.positions = positions;
    spyOn(Track, 'fetchList').and.resolveTo(tracks);
    spyOn(Track, 'fetchLatest').and.resolveTo(track2);
    vm.model.showLatest = true;
    state.showLatest = true;
    vm.init();
    // when
    state.currentUser = user;
    // then
    setTimeout(() => {
      expect(Track.fetchList).toHaveBeenCalledWith(state.currentUser);
      expect(Track.fetchLatest).toHaveBeenCalledWith(user);
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
    spyOn(track2, 'fetchPositions').and.callThrough();
    spyOn(Http, 'get').withArgs(`api/tracks/${track2.id}/positions`).and.resolveTo(positions);
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
      expect(track2.fetchPositions).toHaveBeenCalledWith();
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
    positions[0].trackId = 100;
    positions[0].trackName = 'new track';
    const newTrack = new Track(positions[0].trackId, positions[0].trackName, user);
    newTrack.positions = positions;
    spyOn(Track, 'fetchLatest').and.resolveTo(newTrack);
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
      expect(Track.fetchLatest).toHaveBeenCalledWith(user);
      expect(state.currentTrack.id).toBe(positions[0].trackId);
      expect(state.currentTrack.name).toBe(positions[0].trackName);
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
    positions[0].trackId = track2.id;
    positions[0].trackName = track2.name;
    track2.positions = positions;
    spyOn(Track, 'fetchLatest').and.resolveTo(track2);
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
      expect(Track.fetchLatest).toHaveBeenCalledWith(user);
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
    positions[0].trackId = track1.id;
    positions[0].trackName = track1.name;
    spyOn(track1, 'fetchPositions').and.callThrough();
    spyOn(Http, 'get').withArgs(`api/tracks/${track1.id}/positions`).and.resolveTo(positions);
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
      expect(track1.fetchPositions).toHaveBeenCalledWith();
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
    const positionSet = new PositionSet();
    positionSet.positions = positions;
    spyOn(PositionSet, 'fetchLatest').and.resolveTo(positionSet);
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
      expect(PositionSet.fetchLatest).toHaveBeenCalledWith();
      expect(trackEl.options.length).toBe(0);
      // noinspection JSUnresolvedFunction
      expect(state.currentTrack).not.toBeInstanceOf(Track);
      expect(state.currentTrack).toBeInstanceOf(PositionSet);
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
    spyOn(PositionSet, 'fetchLatest');
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
      expect(PositionSet.fetchLatest).not.toHaveBeenCalled();
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
    spyOn(track2, 'fetchPositions').and.resolveTo(positions);
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
    spyOn(Track, 'import').and.callFake((form) => {
      expect(form.elements['gpx'].files[0]).toEqual(file);
      return Promise.resolve(imported);
    });
    spyOn(imported[0], 'fetchPositions').and.callFake(() => {
      imported[0].positions = positions;
      return Promise.resolve();
    });
    spyOn(Alert, 'toast');
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
      expect(Track.import).toHaveBeenCalledTimes(1);
      expect(Track.import).toHaveBeenCalledWith(jasmine.any(HTMLFormElement), user);
      expect(state.currentTrack).toBe(imported[0]);
      expect(vm.model.currentTrackId).toBe(imported[0].listValue);
      expect(state.currentTrack.length).toBe(positions.length);
      expect(Alert.toast).toHaveBeenCalledTimes(1);
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
    spyOn(Track, 'import').and.resolveTo(imported);
    spyOn(imported[0], 'fetchPositions').and.callFake(() => {
      imported[0].positions = positions;
      return Promise.resolve();
    });
    spyOn(Alert, 'error');
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
      expect(Track.import).not.toHaveBeenCalled();
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
    spyOn(Track, 'import').and.resolveTo(imported);
    spyOn(imported[0], 'fetchPositions').and.callFake(() => {
      imported[0].positions = positions;
      return Promise.resolve();
    });
    spyOn(Alert, 'error');
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
      expect(Track.import).not.toHaveBeenCalled();
      expect(state.currentTrack).toBe(track1);
      expect(vm.model.currentTrackId).toBe(track1.listValue);
      expect(Alert.error).toHaveBeenCalledTimes(1);
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

  it('should remove current last track from track list and set new current track id', () => {
    // given
    vm.model.trackList = [ track1, track2 ];
    vm.state.currentTrack = track2;
    vm.model.currentTrackId = track2.listValue;
    // when
    vm.onTrackDeleted();
    // then
    expect(vm.model.trackList.length).toBe(1);
    expect(vm.model.currentTrackId).toBe(track1.listValue);
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
      const track1PosLength = 2;
      track1 = TrackFactory.getTrack(track1PosLength, { id: 1, name: 'track1', user: user });
      spyOn(Http, 'get').and.resolveTo(positions);
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
        expect(Http.get).toHaveBeenCalledWith(`api/tracks/${track1.id}/positions?afterId=${track1PosLength}`);
        expect(state.currentTrack.length).toBe(posLength + positions.length);
        expect(trackEl.options.length).toBe(optLength);
        expect(trackEl.value).toBe(track1.listValue);
        done();
      }, 100);
    });

    it('should fetch user latest position if "show latest" is checked', (done) => {
      // given
      track1 = TrackFactory.getTrack(1, { id: 1, name: 'track1', user: user });
      positions[0].trackId = track1.id;
      positions[0].trackName = track1.name;
      spyOn(Http, 'get').and.resolveTo(positions[0]);
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
        expect(Http.get).toHaveBeenCalledWith(`api/users/${user.id}/position`);
        expect(state.currentTrack.id).toEqual(track1.id);
        expect(state.currentTrack.name).toEqual(track1.name);
        expect(state.currentTrack.length).toBe(1);
        expect(trackEl.options.length).toBe(optLength);
        expect(trackEl.value).toBe(positions[0].trackId.toString());
        done();
      }, 100);
    });

    it('should fetch user latest position if "show latest" is checked and add track if position is on a new track', (done) => {
      // given
      track1 = TrackFactory.getTrack(1, { id: 1, name: 'track1', user: user });
      positions[0].trackId = 100;
      positions[0].trackName = 'track100';
      spyOn(Http, 'get').and.resolveTo(positions[0]);
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
        expect(Http.get).toHaveBeenCalledWith(`api/users/${user.id}/position`);
        expect(state.currentTrack.id).toEqual(positions[0].trackId);
        expect(state.currentTrack.name).toEqual(positions[0].trackName);
        expect(state.currentTrack.length).toBe(1);
        expect(trackEl.options.length).toBe(optLength + 1);
        expect(trackEl.value).toBe(positions[0].trackId.toString());
        done();
      }, 100);
    });

    it('should fetch all users latest position if "all users" is selected', (done) => {
      // given
      const set = TrackFactory.getPositionSet(2, { id: 1, name: 'track1' });
      set.positions[0].trackId = track1.id;
      set.positions[0].trackName = track1.name;
      set.positions[1].trackId = track2.id;
      set.positions[1].trackName = track2.name;
      spyOn(Http, 'get').and.resolveTo(set.positions);
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
        expect(Http.get).toHaveBeenCalledWith('api/users/position');
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
      spyOn(Track, 'fetchList').and.resolveTo([]);
      vm.model.trackList = [];
      vm.model.currentTrackId = '';
      state.currentTrack = null;
      state.currentUser = user;
      vm.init();
      // when
      forceReloadEl.click();
      // then
      setTimeout(() => {
        expect(Track.fetchList).toHaveBeenCalledWith(user);
        expect(state.currentTrack).toBe(null);
        expect(trackEl.options.length).toBe(0);
        expect(trackEl.value).toBe('');
        done();
      }, 100);
    });

    it('should do nothing if no user is selected and no track is selected', (done) => {
      // given
      spyOn(Http, 'get');
      vm.model.trackList = [];
      vm.model.currentTrackId = '';
      state.currentTrack = null;
      state.currentUser = null;
      vm.init();
      // when
      forceReloadEl.click();
      // then
      setTimeout(() => {
        expect(Http.get).not.toHaveBeenCalled();
        expect(state.currentTrack).toBe(null);
        expect(trackEl.options.length).toBe(0);
        expect(trackEl.value).toBe('');
        done();
      }, 100);
    });

  });

});
