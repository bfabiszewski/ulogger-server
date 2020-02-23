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

import * as gmStub from './helpers/googlemaps.stub.js';
import { config, lang } from '../src/initializer.js'
import GoogleMapsApi from '../src/mapapi/api_gmaps.js';
import TrackFactory from './helpers/trackfactory.js';
import uObserve from '../src/observe.js';
import uUtils from '../src/utils.js';

describe('Google Maps map API tests', () => {
  let container;
  const loadTimeout = 100;
  let mockViewModel;
  let api;

  beforeEach(() => {
    gmStub.setupGmapsStub();
    GoogleMapsApi.authError = false;
    GoogleMapsApi.gmInitialized = false;
    config.reinitialize();
    lang.init(config);
    container = document.createElement('div');
    mockViewModel = { mapElement: container, model: {} };
    api = new GoogleMapsApi(mockViewModel);
    spyOn(google.maps, 'InfoWindow').and.callThrough();
    spyOn(google.maps, 'LatLngBounds').and.callThrough();
    spyOn(google.maps, 'Map').and.callThrough();
    spyOn(google.maps, 'Marker').and.callThrough();
    spyOn(google.maps, 'Polyline').and.callThrough();
    spyOnProperty(GoogleMapsApi, 'loadTimeoutMs', 'get').and.returnValue(loadTimeout);
    spyOn(window, 'alert');
    spyOn(lang, '_').and.returnValue('{placeholder}');
    gmStub.applyPrototypes();
  });

  afterEach(() => {
    gmStub.clear();
    uObserve.unobserveAll(lang);
  });

  it('should timeout initialization of map engine', (done) => {
    // given
    spyOn(uUtils, 'loadScript').and.returnValue(Promise.resolve());
    // when
    api.init()
      .then(() => done.fail('resolve callback called'))
      .catch((e) => {
        expect(e.message).toContain('timeout');
        done();
      });
  });

  it('should fail loading script', (done) => {
    // given
    spyOn(uUtils, 'loadScript').and.returnValue(Promise.reject(Error('script loading error')));
    // when
    api.init()
      .then(() => done.fail('resolve callback called'))
      .catch((e) => {
        expect(e.message).toContain('loading');
        done();
      });
  });

  it('should load and initialize api scripts', (done) => {
    // given
    spyOn(uUtils, 'loadScript').and.returnValue(Promise.resolve());
    spyOn(api, 'initMap');
    config.googleKey = 'key1234567890';
    // when
    api.init()
      .then(() => {
        // then
        expect(uUtils.loadScript).toHaveBeenCalledWith(`https://maps.googleapis.com/maps/api/js?key=${config.googleKey}&callback=gm_loaded`, 'mapapi_gmaps', loadTimeout);
        expect(api.initMap).toHaveBeenCalledTimes(1);
        done();
      })
      .catch((e) => done.fail(e));
    window.gm_loaded();
  });

  it('should initialize map engine with config values', () => {
    // given
    spyOn(google.maps.InfoWindow.prototype, 'addListener');
    // when
    api.initMap();
    // then
    expect(google.maps.Map).toHaveBeenCalledTimes(1);
    expect(google.maps.Map.calls.mostRecent().args[0]).toEqual(container);
    expect(google.maps.Map.calls.mostRecent().args[1].center.latitude).toEqual(config.initLatitude);
    expect(google.maps.Map.calls.mostRecent().args[1].center.longitude).toEqual(config.initLongitude);
    expect(google.maps.InfoWindow).toHaveBeenCalledTimes(1);
    expect(google.maps.InfoWindow.prototype.addListener).toHaveBeenCalledTimes(1);
    expect(google.maps.InfoWindow.prototype.addListener).toHaveBeenCalledWith('closeclick', jasmine.any(Function));
  });

  it('should initialize map engine without Google API key', (done) => {
    // given
    spyOn(uUtils, 'loadScript').and.returnValue(Promise.resolve());
    // when
    api.init()
      .then(() => {
        expect(google.maps.Map).toHaveBeenCalledTimes(1);
        done();
      })
      .catch((e) => done.fail(e));
    window.gm_loaded();
    // then
    expect(uUtils.loadScript).toHaveBeenCalledWith('https://maps.googleapis.com/maps/api/js?callback=gm_loaded', 'mapapi_gmaps', loadTimeout);
  });

  it('should fail with authorization error', (done) => {
    // given
    spyOn(uUtils, 'loadScript').and.returnValue(Promise.resolve());
    lang._.and.returnValue('authfailure');
    // when
    api.init()
      .then(() => done.fail('resolve callback called'))
      .catch((e) => {
        // then
        expect(e.message).toContain('authfailure');
        done();
      });
    window.gm_authFailure();
  });

  it('should show alert if authorization error occurs after initialization', (done) => {
    // given
    spyOn(uUtils, 'loadScript').and.returnValue(Promise.resolve());
    lang._.and.returnValue('authfailure');
    // when
    api.init()
      .then(() => {
        expect(google.maps.Map).toHaveBeenCalledTimes(1);
        done();
      })
      .catch((e) => done.fail(e));
    window.gm_loaded();
    window.gm_authFailure();

    expect(window.alert).toHaveBeenCalledTimes(1);
    expect(window.alert.calls.mostRecent().args[0]).toContain('authfailure');
  });

  it('should clean up class fields', () => {
    // given
    api.polies.length = 1;
    api.markers.length = 1;
    api.popup = new google.maps.InfoWindow();
    container.innerHTML = 'content';
    api.map = new google.maps.Map(container);
    spyOn(google.maps.Map.prototype, 'getDiv').and.returnValue(container);
    // when
    api.cleanup();
    // then
    expect(api.polies.length).toBe(0);
    expect(api.markers.length).toBe(0);
    expect(api.popup).toBe(null);
    expect(container.innerHTML).toBe('');
    expect(api.map).toBe(null);
  });

  it('should remove features by id', () => {
    // given
    const poly = new google.maps.Polyline();
    const marker = new google.maps.Marker();
    api.polies.push(poly);
    api.markers.push(marker);
    const path = {};
    path.removeAt = () => {/* ignore */};
    poly.getPath = () => path;
    marker.setMap = () => {/* ignore */};
    spyOn(marker, 'setMap');
    spyOn(path, 'removeAt');
    const id = 0;
    // when
    api.removePoint(id);
    // then
    expect(marker.setMap).toHaveBeenCalledWith(null);
    expect(api.markers.length).toBe(0);
    expect(path.removeAt).toHaveBeenCalledWith(id);
  });

  it('should clear map features', () => {
    // given
    const poly = new google.maps.Polyline();
    const marker = new google.maps.Marker();
    const popup = new google.maps.InfoWindow();
    api.polies.push(poly);
    api.markers.push(marker);
    api.popup = popup;
    spyOn(api, 'popupClose');
    poly.setMap = () => {/* ignore */};
    spyOn(poly, 'setMap');
    marker.setMap = () => {/* ignore */};
    spyOn(marker, 'setMap');
    popup.setContent = () => {/* ignore */};
    spyOn(popup, 'setContent');
    spyOn(google.maps.InfoWindow.prototype, 'getMap').and.returnValue(true);
    // when
    api.clearMap();
    // then
    expect(poly.setMap).toHaveBeenCalledWith(null);
    expect(api.polies.length).toBe(0);
    expect(marker.setMap).toHaveBeenCalledWith(null);
    expect(api.markers.length).toBe(0);
    expect(popup.setContent).toHaveBeenCalledWith('');
    expect(api.popupClose).toHaveBeenCalledTimes(1);
  });

  it('should construct track polyline and markers', () => {
    // given
    const track = TrackFactory.getTrack();
    spyOn(api, 'setMarker');
    spyOn(google.maps, 'LatLng').and.callThrough();
    spyOn(google.maps.LatLngBounds.prototype, 'extend').and.callThrough();
    const expectedPolyOptions = {
      strokeColor: config.strokeColor,
      strokeOpacity: config.strokeOpacity,
      strokeWeight: config.strokeWeight
    };
    // when
    api.displayTrack(track, false);
    // then
    expect(api.polies.length).toBe(1);
    expect(api.polies[0].path.length).toBe(track.length);
    expect(api.setMarker).toHaveBeenCalledTimes(track.length);
    expect(api.setMarker).toHaveBeenCalledWith(0, track);
    expect(api.setMarker).toHaveBeenCalledWith(1, track);
    expect(google.maps.Polyline).toHaveBeenCalledTimes(1);
    expect(google.maps.Polyline).toHaveBeenCalledWith(expectedPolyOptions);
    expect(google.maps.LatLng.calls.mostRecent().args[0]).toEqual(track.positions[track.length - 1].latitude);
    expect(google.maps.LatLng.calls.mostRecent().args[1]).toEqual(track.positions[track.length - 1].longitude);
    expect(google.maps.LatLngBounds.prototype.extend).toHaveBeenCalledTimes(track.length);
    expect(google.maps.LatLngBounds.prototype.extend.calls.mostRecent().args[0].latitude).toEqual(track.positions[track.length - 1].latitude);
    expect(google.maps.LatLngBounds.prototype.extend.calls.mostRecent().args[0].longitude).toEqual(track.positions[track.length - 1].longitude);
  });

  it('should construct non-continuous track markers without polyline', () => {
    // given
    const track = TrackFactory.getPositionSet();
    spyOn(api, 'setMarker');
    // when
    api.displayTrack(track, false);
    // then
    expect(api.polies.length).toBe(1);
    expect(api.polies[0].path.length).toBe(0);
    expect(api.setMarker).toHaveBeenCalledTimes(track.length);
  });

  it('should fit bounds if update without zoom (should not add listener for "bounds_changed")', () => {
    // given
    const track = TrackFactory.getTrack();
    spyOn(google.maps.event, 'addListenerOnce');
    spyOn(google.maps.Map.prototype, 'fitBounds');
    spyOn(api, 'setMarker');
    spyOn(window, 'setTimeout');
    api.map = new google.maps.Map(container);
    // when
    api.displayTrack(track, true);
    // then
    expect(api.polies.length).toBe(1);
    expect(api.polies[0].path.length).toBe(track.length);
    expect(api.setMarker).toHaveBeenCalledTimes(track.length);
    expect(google.maps.Map.prototype.fitBounds).toHaveBeenCalledTimes(1);
    expect(google.maps.event.addListenerOnce).not.toHaveBeenCalled();
    expect(setTimeout).not.toHaveBeenCalled();
  });

  it('should fit bounds and zoom (add listener for "bounds_changed") if update with single position', () => {
    // given
    const track = TrackFactory.getTrack(1);
    spyOn(google.maps.event, 'addListenerOnce');
    spyOn(google.maps.Map.prototype, 'fitBounds');
    spyOn(api, 'setMarker');
    spyOn(window, 'setTimeout');
    api.map = new google.maps.Map(container);
    // when
    api.displayTrack(track, true);
    // then
    expect(api.polies.length).toBe(1);
    expect(api.polies[0].path.length).toBe(track.length);
    expect(api.setMarker).toHaveBeenCalledTimes(track.length);
    expect(google.maps.Map.prototype.fitBounds).toHaveBeenCalledTimes(1);
    expect(google.maps.event.addListenerOnce.calls.mostRecent().args[1]).toBe('bounds_changed');
    expect(setTimeout).toHaveBeenCalledTimes(1);
  });

  it('should create marker from track position and add it to markers array', () => {
    // given
    const track = TrackFactory.getTrack(1);
    track.positions[0].timestamp = 1;
    spyOn(google.maps.Marker.prototype, 'addListener');
    spyOn(google.maps.Marker.prototype, 'setIcon');
    spyOn(GoogleMapsApi, 'getMarkerIcon');
    api.map = new google.maps.Map(container);

    expect(api.markers.length).toBe(0);
    // when
    api.setMarker(0, track);
    // then
    expect(google.maps.Marker).toHaveBeenCalledTimes(1);
    expect(google.maps.Marker.calls.mostRecent().args[0].position.latitude).toBe(track.positions[0].latitude);
    expect(google.maps.Marker.calls.mostRecent().args[0].position.longitude).toBe(track.positions[0].longitude);
    expect(google.maps.Marker.calls.mostRecent().args[0].title).toContain('1970');
    expect(google.maps.Marker.calls.mostRecent().args[0].map).toEqual(api.map);
    expect(google.maps.Marker.prototype.setIcon).toHaveBeenCalledTimes(1);
    expect(google.maps.Marker.prototype.addListener).toHaveBeenCalledTimes(3);
    expect(google.maps.Marker.prototype.addListener).toHaveBeenCalledWith('click', jasmine.any(Function));
    expect(google.maps.Marker.prototype.addListener).toHaveBeenCalledWith('mouseover', jasmine.any(Function));
    expect(google.maps.Marker.prototype.addListener).toHaveBeenCalledWith('mouseout', jasmine.any(Function));
    expect(api.markers.length).toBe(1);
  });

  it('should create marker different marker icon for start, end and normal position', () => {
    // given
    const track = TrackFactory.getTrack(3);
    spyOn(google.maps.Marker.prototype, 'setIcon');
    spyOn(GoogleMapsApi, 'getMarkerIcon');
    api.map = new google.maps.Map(container);
    // when
    api.setMarker(0, track);
    // then
    expect(GoogleMapsApi.getMarkerIcon).toHaveBeenCalledTimes(1);
    expect(GoogleMapsApi.getMarkerIcon).toHaveBeenCalledWith(config.colorStart, true, jasmine.any(Boolean));
    // when
    api.setMarker(1, track);
    // then
    expect(GoogleMapsApi.getMarkerIcon).toHaveBeenCalledTimes(2);
    expect(GoogleMapsApi.getMarkerIcon).toHaveBeenCalledWith(config.colorNormal, false, jasmine.any(Boolean));
    // when
    api.setMarker(2, track);
    // then
    expect(GoogleMapsApi.getMarkerIcon).toHaveBeenCalledTimes(3);
    expect(GoogleMapsApi.getMarkerIcon).toHaveBeenCalledWith(config.colorStop, true, jasmine.any(Boolean));
  });

  it('should create different marker for position with comment or image', () => {
    // given
    const track = TrackFactory.getTrack(4);
    const positionWithComment = 0;
    const positionWithImage = 1;
    const positionWithImageAndComment = 2;
    const positionWithoutCommentAndImage = 3;
    track.positions[positionWithComment].comment = 'comment 0';
    track.positions[positionWithImage].image = 'image 1';
    track.positions[positionWithImageAndComment].comment = 'comment 2';
    track.positions[positionWithImageAndComment].image = 'image 2';
    spyOn(google.maps.Marker.prototype, 'setIcon');
    spyOn(GoogleMapsApi, 'getMarkerIcon');
    api.map = new google.maps.Map(container);
    // when
    api.setMarker(positionWithComment, track);
    // then
    expect(GoogleMapsApi.getMarkerIcon).toHaveBeenCalledTimes(1);
    expect(GoogleMapsApi.getMarkerIcon).toHaveBeenCalledWith(jasmine.any(String), jasmine.any(Boolean), true);
    // when
    api.setMarker(positionWithImage, track);
    // then
    expect(GoogleMapsApi.getMarkerIcon).toHaveBeenCalledTimes(2);
    expect(GoogleMapsApi.getMarkerIcon).toHaveBeenCalledWith(jasmine.any(String), jasmine.any(Boolean), true);
    // when
    api.setMarker(positionWithImageAndComment, track);
    // then
    expect(GoogleMapsApi.getMarkerIcon).toHaveBeenCalledTimes(3);
    expect(GoogleMapsApi.getMarkerIcon).toHaveBeenCalledWith(jasmine.any(String), jasmine.any(Boolean), true);
    // when
    api.setMarker(positionWithoutCommentAndImage, track);
    // then
    expect(GoogleMapsApi.getMarkerIcon).toHaveBeenCalledTimes(4);
    expect(GoogleMapsApi.getMarkerIcon).toHaveBeenCalledWith(jasmine.any(String), jasmine.any(Boolean), false);
  });

  it('should open popup for given marker with content generated by id', () => {
    // given
    const popup = new google.maps.InfoWindow();
    const marker = new google.maps.Marker();
    const id = 1;
    spyOn(popup, 'setContent').and.callThrough();
    spyOn(popup, 'open');
    const popupEl = document.createElement('div');
    mockViewModel.getPopupElement = () => popupEl;
    spyOn(mockViewModel, 'getPopupElement').and.callThrough();
    api.map = new google.maps.Map(container);
    // when
    api.popup = popup;
    api.popupOpen(id, marker);
    // then
    expect(mockViewModel.getPopupElement).toHaveBeenCalledWith(id);
    expect(popup.setContent).toHaveBeenCalledWith(popupEl);
    expect(popup.open).toHaveBeenCalledWith(api.map, marker);
    expect(api.viewModel.model.markerSelect).toBe(id);
  });

  it('should close popup', () => {
    // given
    const popup = new google.maps.InfoWindow();
    spyOn(popup, 'close');
    api.map = new google.maps.Map(container);
    api.viewModel.model.markerSelect = 1;
    // when
    api.popup = popup;
    api.popupClose();
    // then
    expect(popup.close).toHaveBeenCalledTimes(1);
    expect(api.viewModel.model.markerSelect).toBe(null);
  });

  it('should animate marker with given index', (done) => {
    // given
    const marker = new google.maps.Marker();
    const iconOriginal = new google.maps.Icon();
    const iconAnimated = new google.maps.Icon();
    api.markers.push(marker);
    api.popup = new google.maps.InfoWindow();
    spyOn(google.maps.Marker.prototype, 'getIcon').and.returnValue(iconOriginal);
    spyOn(google.maps.Marker.prototype, 'setIcon').and.callThrough();
    spyOn(google.maps.Marker.prototype, 'setAnimation');
    spyOn(GoogleMapsApi, 'getMarkerIcon').and.returnValue(iconAnimated);
    spyOn(google.maps.InfoWindow.prototype, 'getMap').and.returnValue(true);
    spyOn(api, 'popupClose');
    // when
    api.animateMarker(0);
    // then
    expect(api.popupClose).toHaveBeenCalledTimes(1);
    expect(google.maps.Marker.prototype.setIcon).toHaveBeenCalledWith(iconAnimated);
    expect(GoogleMapsApi.getMarkerIcon).toHaveBeenCalledWith(config.colorHilite, jasmine.any(Boolean), jasmine.any(Boolean));
    expect(google.maps.Marker.prototype.setAnimation).toHaveBeenCalledWith(google.maps.Animation.BOUNCE);
    setTimeout(() => {
      expect(google.maps.Marker.prototype.setIcon).toHaveBeenCalledWith(iconOriginal);
      expect(google.maps.Marker.prototype.setAnimation).toHaveBeenCalledWith(null);
      done();
    }, 2100);
  });

  it('should return map bounds array', () => {
    // given
    const ne = new google.maps.LatLng(1, 2);
    const sw = new google.maps.LatLng(3, 4);
    const bounds = new google.maps.LatLngBounds(sw, ne);
    api.map = new google.maps.Map(container);
    spyOn(google.maps.Map.prototype, 'getBounds').and.returnValue(bounds);
    // when
    const result = api.getBounds();
    // then
    expect(result).toEqual([ sw.longitude, sw.latitude, ne.longitude, ne.latitude ]);
  });

  it('should zoom to markers extent', () => {
    // given
    api.markers.push(new google.maps.Marker());
    api.markers.push(new google.maps.Marker());
    api.map = new google.maps.Map(container);
    spyOn(google.maps.LatLngBounds.prototype, 'extend');
    spyOn(google.maps.Map.prototype, 'fitBounds');
    // when
    api.zoomToExtent();
    // then
    expect(google.maps.LatLngBounds.prototype.extend).toHaveBeenCalledTimes(api.markers.length);
    expect(google.maps.Map.prototype.fitBounds).toHaveBeenCalledTimes(1);
  });

  it('should zoom to given bounds array', () => {
    // given
    const ne = new google.maps.LatLng(1, 2);
    const sw = new google.maps.LatLng(3, 4);
    const fitBounds = new google.maps.LatLngBounds(sw, ne);
    const bounds = [ sw.longitude, sw.latitude, ne.longitude, ne.latitude ];
    api.map = new google.maps.Map(container);
    spyOn(google.maps.Map.prototype, 'fitBounds');
    // when
    api.zoomToBounds(bounds);
    // then
    expect(google.maps.Map.prototype.fitBounds).toHaveBeenCalledWith(fitBounds);
  });

  it('should return timeout in ms', () => {
    jasmine.getEnv().allowRespy(true);
    spyOnProperty(GoogleMapsApi, 'loadTimeoutMs', 'get').and.callThrough();

    expect(GoogleMapsApi.loadTimeoutMs).toEqual(jasmine.any(Number));
  });

});
