/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import { config, lang } from '../src/Initializer.js';
import Fixture from './helpers/fixture.js';
import MapViewModel from '../src/models/MapViewModel.js';
import Observer from '../src/Observer.js';
import Permalink from '../src/Permalink.js';
import State from '../src/State.js';
import Track from '../src/Track.js';
import TrackViewModel from '../src/models/TrackViewModel.js';
import User from '../src/User.js';
import UserViewModel from '../src/models/UserViewModel.js';

describe('Permalink tests', () => {

  let permalink;
  let state;
  const trackId = 123;
  const trackName = 'test track';
  const userId = 456;
  const mapApi = 'testApi';
  const lat = -267220.5357759836;
  const lng = 4514512.219090612;
  const zoom = 7.7081991502812075;
  const rotation = 20.21;
  const mapParams = {
    center: [ lat, lng ],
    zoom: zoom,
    rotation: rotation
  };
  let spy;
  let tm, um, mm;
  let mockApi;

  beforeEach((done) => {
    Fixture.load('main.html')
      .then(() => done())
      .catch((e) => done.fail(e));
  });

  beforeEach(() => {
    config.reinitialize();
    lang.init(config);
    spyOn(lang, '_').and.callFake((arg) => arg);
    mockApi = jasmine.createSpyObj('mockApi', {
      'init': Promise.resolve(),
      'getBounds': { /* ignored */ },
      'cleanup': { /* ignored */ },
      'zoomToBounds': { /* ignored */ },
      'zoomToExtent': { /* ignored */ },
      'displayTrack': Promise.resolve(),
      'clearMap': { /* ignored */ },
      'updateSize': { /* ignored */ },
      'updateState': { /* ignored */ }
    });
    state = new State();
    tm = new TrackViewModel(state);
    um = new UserViewModel(state);
    mm = new MapViewModel(state);
    mm.api = mockApi;
    spyOn(tm, 'onTrackSelect');
    spyOn(tm, 'loadTrackList');
    permalink = new Permalink(state);
    spy = spyOn(Track, 'getMeta').and.callFake((_trackId) => Promise.resolve({
      id: _trackId,
      name: trackName,
      userId: userId,
      comment: null
    }));
  });

  afterEach(() => {
    Fixture.clear();
    Observer.unobserveAll(lang);
  });

  it('should create instance', () => {
    expect(permalink).toBeInstanceOf(Permalink);
    expect(permalink.state).toBe(state);
    expect(permalink.skipPush).toBeFalse();
  });

  let testHashes = [
    { hash: `#${trackId}`,
      state: { title: trackName, userId: userId, trackId: trackId, mapApi: 'openlayers', mapParams: null } },
    { hash: `#${trackId}/`,
      state: { title: trackName, userId: userId, trackId: trackId, mapApi: 'openlayers', mapParams: null } },
    { hash: `#${trackId}/o`,
      state: { title: trackName, userId: userId, trackId: trackId, mapApi: 'openlayers', mapParams: null } },
    { hash: `#${trackId}/x`,
      state: { title: trackName, userId: userId, trackId: trackId, mapApi: 'openlayers', mapParams: null } },
    { hash: `#${trackId}/g`,
      state: { title: trackName, userId: userId, trackId: trackId, mapApi: 'gmaps', mapParams: null } },
    { hash: `#${trackId}/o/`,
      state: { title: trackName, userId: userId, trackId: trackId, mapApi: 'openlayers', mapParams: null } },
    { hash: `#${trackId}/o/${lat}/${lng}`,
      state: { title: trackName, userId: userId, trackId: trackId, mapApi: 'openlayers', mapParams: null } },
    { hash: `#${trackId}/o/${lat}/${lng}/${zoom}/${rotation}`,
      state: { title: trackName, userId: userId, trackId: trackId, mapApi: 'openlayers', mapParams: mapParams } }
  ];
  testHashes.forEach((test) => {
    it(`should parse link hash "${test.hash}"`, (done) => {
      Permalink.parse(test.hash).then((result) => {
        expect(result).toEqual(test.state);
        done();
      }).catch((e) => done.fail(e));
    });
  });
  testHashes = [ '#', '', '#hash' ];
  testHashes.forEach((test) => {
    it(`should parse link and return null for corrupt hash "${test}"`, (done) => {
      Permalink.parse(test).then((result) => {
        expect(result).toBeNull();
        done();
      }).catch((e) => done.fail(e));
    });
  });

  it('should parse link and return null for unknown track id', (done) => {
    spy.and.rejectWith(new Error('error'));
    Permalink.parse('#21').then((result) => {
      expect(result).toBeNull();
      done();
    }).catch((e) => done.fail(e));
  });

  it('should create hash from app state', () => {
    // given
    const permalinkState = {
      userId: userId,
      trackId: trackId,
      mapApi: mapApi,
      mapParams: null
    };
    // when
    const hash = Permalink.getHash(permalinkState);
    // then
    expect(hash).toBe(`#${trackId}/${mapApi.charAt(0)}`);
  });

  it('should create hash from app state and map parameters', () => {
    // given
    const permalinkState = { userId, trackId, mapApi, mapParams };
    // when
    const hash = Permalink.getHash(permalinkState);
    // then
    expect(hash).toBe(`#${trackId}/${mapApi.charAt(0)}/${mapParams.center[0]}/${mapParams.center[1]}/${mapParams.zoom}/${mapParams.rotation}`);
  });

  it('should return permalink state from application state', () => {
    // given
    state.currentUser = new User(userId, 'test');
    state.currentTrack = new Track(trackId, trackName, state.currentUser);
    state.mapParams = mapParams;
    config.mapApi = mapApi;
    const title = trackName;
    // when
    const permalinkState = permalink.getState();
    // then
    expect(permalinkState).toEqual({ title, userId, trackId, mapApi, mapParams });
  });

  it('should restore state and switch user', (done) => {
    // given
    const newUserId = userId + 1;
    const newUser = new User(newUserId, 'new');
    const newTrackId = trackId + 1;
    spyOn(User, 'fetchList').and.resolveTo([ newUser ]);
    state.currentUser = new User(userId, 'test');
    state.currentTrack = new Track(trackId, trackName, state.currentUser);
    tm.init();
    um.init().start();
    mm.init();
    permalink.init();
    const historyState = {
      title: 'title',
      userId: newUserId,
      trackId: newTrackId,
      mapApi: mapApi,
      mapParams: mapParams
    };
    const event = new PopStateEvent('popstate', { state: historyState });
    setTimeout(() => {
      // when
      dispatchEvent(event);
      // then
      setTimeout(() => {
        expect(tm.loadTrackList).toHaveBeenCalledTimes(1);
        expect(tm.onTrackSelect).toHaveBeenCalledTimes(1);
        expect(mockApi.updateState).not.toHaveBeenCalled();
        expect(state.currentUser).toBe(newUser);
        expect(tm.model.currentTrackId).toBe(newTrackId.toString());
        done();
      }, 100);
    }, 100);
  });

  it('should restore state and load track for same user', (done) => {
    // given
    const user = new User(userId, 'test');
    const newTrackId = trackId + 1;
    const track = new Track(trackId, trackName, user);
    state.currentUser = user;
    state.currentTrack = track;
    spyOn(User, 'fetchList').and.resolveTo([ user ]);
    tm.init();
    um.init().start();
    mm.init();
    permalink.init();
    const historyState = {
      title: 'title',
      userId: userId,
      trackId: newTrackId,
      mapApi: mapApi,
      mapParams: mapParams
    };
    const event = new PopStateEvent('popstate', { state: historyState });
    setTimeout(() => {
      // when
      dispatchEvent(event);
      // then
      setTimeout(() => {
        expect(tm.loadTrackList).not.toHaveBeenCalled();
        expect(tm.onTrackSelect).toHaveBeenCalledTimes(1);
        expect(mockApi.updateState).not.toHaveBeenCalled();
        expect(state.currentUser).toBe(user);
        expect(tm.model.currentTrackId).toBe(newTrackId.toString());
        done();
      }, 100);
    }, 100);
  });

  it('should restore state without user and track update, with map api change', (done) => {
    // given
    config.mapApi = 'oldApi';
    const user = new User(userId, 'test');
    const track = new Track(trackId, trackName, user);
    state.currentUser = user;
    state.currentTrack = track;
    spyOn(User, 'fetchList').and.resolveTo([ user ]);
    tm.model.currentTrackId = trackId.toString();
    tm.init();
    um.init().start();
    mm.init();
    permalink.init();
    const historyState = {
      title: 'title',
      userId: userId,
      trackId: trackId,
      mapApi: mapApi,
      mapParams: mapParams
    };
    const event = new PopStateEvent('popstate', { state: historyState });
    setTimeout(() => {
      // when
      dispatchEvent(event);
      // then
      setTimeout(() => {
        expect(tm.loadTrackList).not.toHaveBeenCalled();
        expect(tm.onTrackSelect).not.toHaveBeenCalled();
        expect(mockApi.updateState).not.toHaveBeenCalled();
        expect(config.mapApi).toBe(mapApi);
        expect(state.currentUser).toBe(user);
        expect(tm.model.currentTrackId).toBe(trackId.toString());
        done();
      }, 100);
    }, 100);
  });

  it('should restore state without user, track and map api update', (done) => {
    // given
    config.mapApi = mapApi;
    const user = new User(userId, 'test');
    const track = new Track(trackId, trackName, user);
    state.currentUser = user;
    state.currentTrack = track;
    spyOn(User, 'fetchList').and.resolveTo([ user ]);
    tm.model.currentTrackId = trackId.toString();
    tm.init();
    um.init().start();
    mm.init();
    permalink.init();
    const historyState = {
      title: 'title',
      userId: userId,
      trackId: trackId,
      mapApi: mapApi,
      mapParams: mapParams
    };
    const event = new PopStateEvent('popstate', { state: historyState });
    setTimeout(() => {
      // when
      dispatchEvent(event);
      // then
      setTimeout(() => {
        expect(tm.loadTrackList).not.toHaveBeenCalled();
        expect(tm.onTrackSelect).not.toHaveBeenCalled();
        expect(mockApi.updateState).toHaveBeenCalledTimes(1);
        expect(config.mapApi).toBe(mapApi);
        expect(state.currentUser).toBe(user);
        expect(tm.model.currentTrackId).toBe(trackId.toString());
        done();
      }, 100);
    }, 100);
  });

});
