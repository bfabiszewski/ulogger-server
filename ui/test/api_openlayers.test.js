/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

import * as ol from '../src/lib/ol.js';
import Layer from '../src/Layer.js';
import LayerCollection from '../src/LayerCollection.js';
import OpenLayersApi from '../src/mapapi/api_openlayers.js';
import TrackFactory from './helpers/trackfactory.js';
import Utils from '../src/Utils.js';
import { config } from '../src/Initializer.js'

describe('Openlayers map API tests', () => {
  let container;
  let mockViewModel;
  let api;
  let mockMap;

  beforeEach(() => {
    config.reinitialize();
    document.body.innerHTML = '';
    container = document.createElement('div');
    container.setAttribute('style', 'display: block; width: 100px; height: 100px');
    document.body.appendChild(container);
    mockViewModel = { mapElement: container, model: {} };
    api = new OpenLayersApi(mockViewModel, ol);
    mockMap = new ol.Map({
      target: container,
      view: new ol.View({ zoom: 8, center: [ 0, 0 ] })
    });
  });

  it('should load and initialize api scripts', (done) => {
    // given
    // spyOn(Utils, 'addCss');
    spyOn(OpenLayersApi, 'loadCss');

    spyOn(api, 'initMap');
    spyOn(api, 'initLayers');
    spyOn(api, 'initStyles');
    spyOn(api, 'initPopups');
    // when
    api.init()
      .then(() => {
        // then
        expect(OpenLayersApi.loadCss).toHaveBeenCalledTimes(1);
        // expect(Utils.addCss).toHaveBeenCalledTimes(1);
        expect(api.initMap).toHaveBeenCalledTimes(1);
        expect(api.initLayers).toHaveBeenCalledTimes(1);
        expect(api.initStyles).toHaveBeenCalledTimes(1);
        expect(api.initPopups).toHaveBeenCalledTimes(1);
        done();
      })
      .catch((e) => done.fail(e));
  });

  it('should initialize map engine with config values', () => {
    // given
    spyOn(ol.Map.prototype, 'onInternal');
    // when
    api.initMap();
    // then
    expect(api.map).toEqual(jasmine.any(ol.Map));
    expect(api.map.getTarget()).toBe(container);
    expect(api.map.getControls().getLength()).toBe(3);
    expect(api.map.getView().getCenter()).toEqual(ol.proj.fromLonLat([ config.initLongitude, config.initLatitude ]));
    // noinspection JSUnresolvedVariable
    expect(ol.Map.prototype.onInternal).toHaveBeenCalledWith('pointermove', jasmine.any(Function));
  });

  it('should initialize map layers with config values', () => {
    // given
    spyOn(api, 'initLayerSwitcher');
    config.olLayers = new LayerCollection(
      new Layer(1, 'layer1', 'http://layer1', 0),
      new Layer(1, 'layer2', 'http://layer2', 0)
    );

    api.map = mockMap;
    // when
    api.initLayers();
    // then
    expect(api.map.getLayers().getLength()).toEqual(5);
    api.map.getLayers().forEach((_layer) => {
      const name = _layer.get('name');
      switch (name) {
        case 'OpenStreetMap':

          expect(_layer).toEqual(jasmine.any(ol.layer.TileLayer));
          expect(_layer.getVisible()).toBe(true);
          expect(_layer.getSource()).toEqual(jasmine.any(ol.source.OSM));
          expect(_layer.get('type')).not.toBe('data');
          expect(api.selectedLayer).toEqual(_layer);
          break;
        case 'layer1':
        case 'layer2':

          expect(_layer.getVisible()).toBe(false);
          expect(_layer).toEqual(jasmine.any(ol.layer.TileLayer));
          expect(_layer.getSource()).toEqual(jasmine.any(ol.source.XYZ));
          expect(_layer.get('type')).not.toBe('data');
          break;
        case 'Track':

          expect(_layer.getVisible()).toBe(true);
          expect(_layer).toEqual(jasmine.any(ol.layer.VectorLayer));
          expect(_layer.getSource()).toEqual(jasmine.any(ol.source.Vector));
          expect(_layer.get('type')).toBe('data');
          expect(api.layerTrack).toEqual(_layer);
          break;
        case 'Markers':

          expect(_layer.getVisible()).toBe(true);
          expect(_layer).toEqual(jasmine.any(ol.layer.VectorLayer));
          expect(_layer.getSource()).toEqual(jasmine.any(ol.source.Vector));
          expect(_layer.get('type')).toBe('data');
          expect(api.layerMarkers).toEqual(_layer);
          break;
        default:
          fail(`Unexpected layer: ${name}`);
      }
    });

    expect(api.initLayerSwitcher).toHaveBeenCalledTimes(1);
  });

  it('should initialize layer switcher element and control', () => {
    // given
    api.map = mockMap;
    api.map.addLayer(new ol.layer.TileLayer());
    const controlsCount = api.map.getControls().getLength();
    // when
    api.initLayerSwitcher();
    const switcher = document.getElementById('switcher');
    // then
    expect(switcher).toEqual(jasmine.any(HTMLDivElement));
    expect(switcher.firstChild.childNodes.length).toEqual(1);
    expect(api.map.getControls().getLength()).toBe(controlsCount + 1)
  });

  it('should clean up and initialize styles', () => {
    // given
    api.markerStyles = {
      'style1': 'to be cleaned up'
    };
    // when
    api.initStyles();
    // then
    expect(Object.keys(api.markerStyles).length).toBe(7);
  });

  it('should initialize popups', () => {
    // given
    api.map = mockMap;
    // when
    api.initPopups();
    const popup = document.getElementById('popup-container');
    // then
    expect(popup).toEqual(jasmine.any(HTMLDivElement));
    expect(api.popup).toEqual(jasmine.any(ol.Overlay));
    expect(api.popup.getElement()).toBe(popup);
  });

  it('should switch layers', () => {
    // given
    api.map = mockMap;
    const layer1 = new ol.layer.TileLayer({ name: 'layer1', visible: true });
    const layer2 = new ol.layer.TileLayer({ name: 'layer2', visible: false });
    api.selectedLayer = layer1;
    api.map.addLayer(layer1);
    api.map.addLayer(layer2);
    // when
    api.switchLayer('layer2');
    // then
    expect(api.selectedLayer).toEqual(layer2);
    expect(layer1.getVisible()).toBe(false);
    expect(layer2.getVisible()).toBe(true);
  });

  it('should toggle data layer visibility', () => {
    // given
    api.map = mockMap;
    const layer1 = new ol.layer.TileLayer({ name: 'layer1', visible: true, type: 'data' });
    const layer2 = new ol.layer.TileLayer({ name: 'layer2', visible: false, type: 'data' });
    api.selectedLayer = layer1;
    api.map.addLayer(layer1);
    api.map.addLayer(layer2);
    // when
    api.switchLayer('layer1');
    // then
    expect(api.selectedLayer).toEqual(layer1);
    expect(layer1.getVisible()).toBe(false);
    expect(layer2.getVisible()).toBe(false);
    // when
    api.switchLayer('layer2');
    // then
    expect(api.selectedLayer).toEqual(layer1);
    expect(layer1.getVisible()).toBe(false);
    expect(layer2.getVisible()).toBe(true);
  });

  it('should clean up class fields', () => {
    // given
    api.layerTrack = new ol.layer.VectorLayer();
    api.layerMarkers = new ol.layer.VectorLayer();
    api.selectedLayer = api.layerTrack;
    api.markerStyles = { style: 'style' };
    document.body.appendChild(document.createElement('div'));
    api.map = mockMap;
    // when
    api.cleanup();
    // then
    expect(api.layerTrack).toBe(null);
    expect(api.layerMarkers).toBe(null);
    expect(api.selectedLayer).toBe(null);
    expect(api.markerStyles).toBe(null);
    expect(container.innerHTML).toBe('');
    expect(api.map).toBe(null);
  });

  it('should remove features with given index', () => {
    // given
    const id = 0;
    const marker = new ol.Feature();
    const lineString = new ol.geom.LineString([]);
    lineString.appendCoordinate(ol.proj.fromLonLat([ 0, 0 ]));
    const lineFeature = new ol.Feature({ geometry: lineString });
    marker.setId(id);
    api.layerTrack = new ol.layer.VectorLayer({ source: new ol.source.Vector() });
    api.layerTrack.getSource().addFeature(lineFeature);
    api.layerMarkers = new ol.layer.VectorLayer({ source: new ol.source.Vector() });
    api.layerMarkers.getSource().addFeature(marker);

    expect(lineString.getCoordinates().length).toBe(1);
    expect(api.layerMarkers.getSource().getFeatures().length).toBe(1);
    // when
    api.removePoint(id);
    // then
    expect(lineString.getCoordinates().length).toBe(0);
    expect(api.layerMarkers.getSource().getFeatures().length).toBe(0);
  });

  it('should clear marker and track layers features', () => {
    // given
    api.layerTrack = new ol.layer.VectorLayer({ source: new ol.source.Vector() });
    api.layerTrack.getSource().addFeature(new ol.Feature());
    api.layerMarkers = new ol.layer.VectorLayer({ source: new ol.source.Vector() });
    api.layerMarkers.getSource().addFeature(new ol.Feature());

    expect(api.layerTrack.getSource().getFeatures().length).toBe(1);
    expect(api.layerMarkers.getSource().getFeatures().length).toBe(1);
    // when
    api.clearMap();
    // then
    expect(api.layerTrack.getSource().getFeatures().length).toBe(0);
    expect(api.layerMarkers.getSource().getFeatures().length).toBe(0);
  });

  it('should construct track markers with track layer', () => {
    // given
    api.map = mockMap;
    api.layerTrack = new ol.layer.VectorLayer({ source: new ol.source.Vector() });
    api.layerMarkers = new ol.layer.VectorLayer({ source: new ol.source.Vector() });
    const track = TrackFactory.getTrack();
    spyOn(api, 'setMarker');
    spyOn(api, 'fitToExtent');
    spyOn(api.map, 'un');
    // when
    api.displayTrack(track, false);
    let zoomControl;
    api.map.getControls().forEach((el) => {
      if (el instanceof ol.control.ZoomToExtent) {
        zoomControl = el;
      }
    });
    // then
    expect(api.layerTrack.getSource().getFeatures().length).toBe(1);
    expect(api.setMarker).toHaveBeenCalledTimes(track.length);
    expect(api.setMarker).toHaveBeenCalledWith(0, track);
    expect(api.setMarker).toHaveBeenCalledWith(1, track);
    expect(api.fitToExtent).not.toHaveBeenCalled();
    // noinspection JSUnusedAssignment
    expect(zoomControl.extent).toEqual(api.layerMarkers.getSource().getExtent());
    expect(api.layerTrack.getSource().getFeatures()[0].getGeometry().getCoordinates().length).toEqual(track.length);
  });

  it('should construct non-continuous track markers without track layer', () => {
    // given
    api.map = mockMap;
    api.map.addControl(new ol.control.ZoomToExtent());
    api.layerTrack = new ol.layer.VectorLayer({ source: new ol.source.Vector() });
    api.layerMarkers = new ol.layer.VectorLayer({ source: new ol.source.Vector() });
    const track = TrackFactory.getPositionSet();
    spyOn(api, 'setMarker');
    spyOn(api, 'fitToExtent');
    spyOn(api.map, 'un');
    // when
    api.displayTrack(track, false);
    let zoomControl;
    api.map.getControls().forEach((el) => {
      if (el instanceof ol.control.ZoomToExtent) {
        zoomControl = el;
      }
    });
    // then
    expect(api.layerTrack.getSource().getFeatures().length).toBe(0);
    expect(api.setMarker).toHaveBeenCalledTimes(track.length);
    expect(api.setMarker).toHaveBeenCalledWith(0, track);
    expect(api.setMarker).toHaveBeenCalledWith(1, track);
    expect(api.fitToExtent).not.toHaveBeenCalled();
    // noinspection JSUnusedAssignment
    expect(zoomControl.extent).toEqual(api.layerMarkers.getSource().getExtent());
  });

  it('should fit to extent if update without zoom', () => {
    // given
    api.map = mockMap;
    api.layerTrack = new ol.layer.VectorLayer({ source: new ol.source.Vector() });
    api.layerMarkers = new ol.layer.VectorLayer({ source: new ol.source.Vector() });
    const track = TrackFactory.getTrack();
    spyOn(api, 'setMarker');
    const markersExtent = [ 3, 2, 1, 0 ];
    spyOn(api, 'fitToExtent').and.callFake((_extent) => _extent);
    spyOn(ol.source.Vector.prototype, 'getExtent').and.returnValue(markersExtent);
    spyOn(api.map, 'un');
    // when
    api.displayTrack(track, true);
    let zoomControl;
    api.map.getControls().forEach((el) => {
      if (el instanceof ol.control.ZoomToExtent) {
        zoomControl = el;
      }
    });
    // then
    expect(api.layerTrack.getSource().getFeatures().length).toBe(1);
    expect(api.setMarker).toHaveBeenCalledTimes(track.length);
    expect(api.setMarker).toHaveBeenCalledWith(0, track);
    expect(api.setMarker).toHaveBeenCalledWith(1, track);
    expect(api.fitToExtent).toHaveBeenCalledWith(markersExtent);
    // noinspection JSUnusedAssignment
    expect(zoomControl.extent).toEqual(markersExtent);
  });

  it('should fit to extent', () => {
    // given
    api.map = mockMap;
    spyOn(ol.View.prototype, 'fit');
    spyOn(ol.View.prototype, 'getZoom').and.returnValue(OpenLayersApi.ZOOM_MAX - 1);
    const extent = [ 0, 1, 2, 3 ];
    // when
    api.fitToExtent(extent);
    // then
    expect(ol.View.prototype.fit).toHaveBeenCalledWith(extent, jasmine.any(Object));
  });

  it('should fit to extent and zoom to max value', () => {
    // given
    const extent = [ 0, 1, 2, 3 ];
    const zoomedExtent = [ 3, 2, 1, 0 ];
    api.map = mockMap;
    spyOn(ol.View.prototype, 'fit');
    spyOn(ol.View.prototype, 'getZoom').and.returnValue(OpenLayersApi.ZOOM_MAX);
    spyOn(ol.View.prototype, 'setZoom');
    spyOn(ol.View.prototype, 'calculateExtent').and.returnValue(zoomedExtent);
    // when
    const result = api.fitToExtent(extent);
    // then
    expect(ol.View.prototype.fit).toHaveBeenCalledWith(extent, jasmine.any(Object));
    expect(result).toEqual(zoomedExtent);
  });

  it('should create marker from track position and add it to markers layer', () => {
    // given
    const track = TrackFactory.getTrack(1);
    track.positions[0].timestamp = 1;
    const id = 0;
    api.map = mockMap;
    api.layerMarkers = new ol.layer.VectorLayer({ source: new ol.source.Vector() });
    spyOn(api, 'getMarkerStyle');
    // when
    api.setMarker(id, track);
    const marker = api.layerMarkers.getSource().getFeatures()[0];
    // then
    expect(marker.getId()).toBe(id);
    expect(marker.getGeometry().getFirstCoordinate()).toEqual(ol.proj.fromLonLat([ track.positions[0].longitude, track.positions[0].latitude ]));
  });

  it('should center map to given marker', () => {
    // given
    const track = TrackFactory.getTrack(1);
    const coordinates = [ 1, 3 ];
    track.positions[0].timestamp = 1;
    track.positions[0].longitude = coordinates[0];
    track.positions[0].latitude = coordinates[1];
    const id = 0;
    api.map = mockMap;
    api.layerMarkers = new ol.layer.VectorLayer({ source: new ol.source.Vector() });
    spyOn(ol.View.prototype, 'setCenter');
    spyOn(api, 'getMarkerStyle');

    api.setMarker(id, track);
    // when
    api.centerToPosition(id);
    // then
    expect(ol.View.prototype.setCenter).toHaveBeenCalledWith(ol.proj.fromLonLat(coordinates));
  });

  it('should confirm that position is visible', () => {
    // given
    const track = TrackFactory.getTrack(1);
    const coordinates = [ 1, 3 ];
    track.positions[0].timestamp = 1;
    track.positions[0].longitude = coordinates[0];
    track.positions[0].latitude = coordinates[1];
    const id = 0;
    api.map = mockMap;
    api.layerMarkers = new ol.layer.VectorLayer({ source: new ol.source.Vector() });
    spyOn(ol.extent, 'containsCoordinate').and.returnValue(true);
    spyOn(api, 'getMarkerStyle');

    api.setMarker(id, track);
    // when
    const result = api.isPositionVisible(id);
    // then
    expect(ol.extent.containsCoordinate).toHaveBeenCalledWith(jasmine.any(Array), ol.proj.fromLonLat(coordinates));
    expect(result).toBeTrue();
  });

  it('should confirm that position is not visible', () => {
    // given
    const track = TrackFactory.getTrack(1);
    const coordinates = [ 1, 3 ];
    track.positions[0].timestamp = 1;
    track.positions[0].longitude = coordinates[0];
    track.positions[0].latitude = coordinates[1];
    const id = 0;
    api.map = mockMap;
    api.layerMarkers = new ol.layer.VectorLayer({ source: new ol.source.Vector() });
    spyOn(ol.extent, 'containsCoordinate').and.returnValue(false);
    spyOn(api, 'getMarkerStyle');

    api.setMarker(id, track);
    // when
    const result = api.isPositionVisible(id);
    // then
    expect(ol.extent.containsCoordinate).toHaveBeenCalledWith(jasmine.any(Array), ol.proj.fromLonLat(coordinates));
    expect(result).toBeFalse();
  });

  it('should get different marker style for start, end and normal position', () => {
    // given
    const track = TrackFactory.getTrack(3);
    api.markerStyles = {
      normal: 'normal',
      stop: 'stop',
      start: 'start'
    };
    // when
    let style = api.getMarkerStyle(0, track);
    // then
    expect(style).toBe('start');
    // when
    style = api.getMarkerStyle(1, track);
    // then
    expect(style).toBe('normal');
    // when
    style = api.getMarkerStyle(2, track);
    // then
    expect(style).toBe('stop');
  });

  it('should create different marker for position with comment', () => {
    // given
    const track = TrackFactory.getTrack(3);
    track.positions[0].comment = 'comment';
    track.positions[1].comment = 'comment';
    track.positions[2].comment = 'comment';
    api.markerStyles = {
      extra: 'extra',
      stopExtra: 'stopExtra',
      startExtra: 'startExtra'
    };
    // when
    let style = api.getMarkerStyle(0, track);
    // then
    expect(style).toBe('startExtra');
    // when
    style = api.getMarkerStyle(1, track);
    // then
    expect(style).toBe('extra');
    // when
    style = api.getMarkerStyle(2, track);
    // then
    expect(style).toBe('stopExtra');
  });

  it('should create different marker for position with image', () => {
    // given
    const track = TrackFactory.getTrack(3);
    track.positions[0].hasImage = true;
    track.positions[1].hasImage = true;
    track.positions[2].hasImage = true;
    api.markerStyles = {
      extra: 'extra',
      stopExtra: 'stopExtra',
      startExtra: 'startExtra'
    };
    // when
    let style = api.getMarkerStyle(0, track);
    // then
    expect(style).toBe('startExtra');
    // when
    style = api.getMarkerStyle(1, track);
    // then
    expect(style).toBe('extra');
    // when
    style = api.getMarkerStyle(2, track);
    // then
    expect(style).toBe('stopExtra');
  });

  it('should animate marker with given index', (done) => {
    // given
    const styleOriginal = new ol.style.Style();
    const styleAnimated = new ol.style.Style();
    api.markerStyles = {
      hilite: styleAnimated
    };
    const id = 1;
    const marker = new ol.Feature();
    marker.setStyle(styleOriginal);
    marker.setId(id);
    api.layerMarkers = new ol.layer.VectorLayer({ source: new ol.source.Vector() });
    api.layerMarkers.getSource().addFeature(marker);
    // when
    api.animateMarker(1);
    // then
    expect(marker.getStyle()).toBe(styleAnimated);
    setTimeout(() => {
      expect(marker.getStyle()).toBe(styleOriginal);
      done();
    }, 2100);
  });

  it('should call View.fit with markers layer extent', () => {
    // given
    const extent = [ 0, 1, 2, 3 ];
    api.map = mockMap;
    api.layerMarkers = new ol.layer.VectorLayer({ source: new ol.source.Vector() });
    spyOn(ol.View.prototype, 'fit');
    spyOn(ol.source.Vector.prototype, 'getExtent').and.returnValue(extent);
    // when
    api.zoomToExtent();
    // then
    expect(ol.View.prototype.fit).toHaveBeenCalledWith(extent, { padding: OpenLayersApi.TRACK_PADDING, maxZoom: OpenLayersApi.ZOOM_MAX });
  });

  it('should get map bounds and convert to WGS84 (EPSG:4326)', () => {
    // given
    api.map = mockMap;
    const extent = [ 2292957.24947, 6828285.71702, 2378184.536, 6861382.95027 ];
    spyOn(ol.View.prototype, 'calculateExtent').and.returnValue(extent);
    // when
    const bounds = api.getBounds();
    // then
    expect(ol.View.prototype.calculateExtent).toHaveBeenCalledTimes(1);
    expect(bounds[0]).toBeCloseTo(20.597985430276808);
    expect(bounds[1]).toBeCloseTo(52.15547181298076);
    expect(bounds[2]).toBeCloseTo(21.363595171488573);
    expect(bounds[3]).toBeCloseTo(52.33750879522563);
  });

  it('should convert bounds to EPSG:3857 and fit view', () => {
    // given
    api.map = mockMap;
    const bounds = [ 20.597985430276808, 52.15547181298076, 21.363595171488573, 52.33750879522563 ];
    spyOn(ol.View.prototype, 'fit');
    // when
    api.zoomToBounds(bounds);
    // then
    const extent = ol.View.prototype.fit.calls.mostRecent().args[0];

    expect(extent[0]).toBeCloseTo(2292957.24947);
    expect(extent[1]).toBeCloseTo(6828285.71702);
    expect(extent[2]).toBeCloseTo(2378184.536);
    expect(extent[3]).toBeCloseTo(6861382.95027);
  });

  it('should update map size', () => {
    // given
    api.map = mockMap;
    spyOn(ol.Map.prototype, 'updateSize');
    // when
    api.updateSize();
    // then
    expect(ol.Map.prototype.updateSize).toHaveBeenCalledTimes(1);
  });

  it('should open popup at coordinate and close it', () => {
    // given
    const id = 1;
    const coordinate = [ 1, 2 ];
    const popupEl = document.createElement('div');
    mockViewModel.getPopupElement = () => popupEl;
    mockViewModel.model.markerSelect = null;
    spyOn(mockViewModel, 'getPopupElement').and.callThrough();
    api.map = mockMap;
    const popupContainer = document.createElement('div');
    const popupContent = document.createElement('div');
    popupContainer.appendChild(popupContent);
    api.popup = new ol.Overlay({ element: popupContainer });
    api.map.addOverlay(api.popup);
    // when
    api.popupOpen(id, coordinate);
    // then
    expect(api.popup.getPosition()).toEqual(coordinate);
    expect(mockViewModel.getPopupElement).toHaveBeenCalledWith(id);
    expect(api.popup.getElement().firstElementChild.firstChild).toBe(popupEl);
    expect(mockViewModel.model.markerSelect).toBe(id);
    // when
    api.popupClose();
    // then
    // eslint-disable-next-line no-undefined
    expect(api.popup.getPosition()).toBe(undefined);
    expect(api.popup.getElement().firstElementChild.innerHTML).toBe('');
    expect(mockViewModel.model.markerSelect).toBe(null);
  });

  it('should create gradient style', () => {
    // given
    const ctx = document.createElement('canvas').getContext('2d');
    const coordinates = [ [ 0, 0 ], [ 1, 1 ] ];
    const colors = [ 'white', 'red' ];
    api.map = mockMap;
    spyOn(api.map, 'getPixelFromCoordinate').and.callFake((coord) => coord);
    // when
    const style = api.getGradientStyle(ctx, coordinates, colors);
    // then
    expect(style).toBeInstanceOf(ol.style.Style);
    expect(style.getGeometry().getCoordinates()).toEqual(coordinates);
    expect(style.getStroke().getColor()).toBeInstanceOf(CanvasGradient);
  });

  it('should set default style for track', () => {
    // given
    api.layerTrack = new ol.layer.VectorLayer();
    config.strokeWeight = 1234;
    config.strokeColor = 'test color';
    config.strokeOpacity = 0.1234;
    const color = 'rgba(1, 1, 1, 1)';
    spyOn(Utils, 'hexToRGBA').and.returnValue(color);
    // when
    api.setTrackDefaultStyle();
    // then
    expect(api.layerTrack.getStyle()).toBeInstanceOf(ol.style.Style);
    expect(api.layerTrack.getStyle().getStroke().getWidth()).toBe(config.strokeWeight);
    expect(api.layerTrack.getStyle().getStroke().getColor()).toBe(color);
    expect(Utils.hexToRGBA).toHaveBeenCalledWith(config.strokeColor, config.strokeOpacity);
  });

  it('should set gradient style for track', () => {
    // given
    const track = TrackFactory.getTrack(3);
    track.positions[0].speed = 0;
    track.positions[1].speed = 1;
    track.positions[2].speed = 2;
    api.map = mockMap;
    api.layerTrack = new ol.layer.VectorLayer({
      source: new ol.source.Vector()
    });
    spyOn(Utils, 'getScaleColor').and.returnValue('test color');
    spyOn(api, 'getGradientStyle').and.returnValue(new ol.style.Style());
    const lineFeature = new ol.Feature({ geometry: new ol.geom.LineString([]) });
    for (let i = 0; i < track.length; i++) {
      lineFeature.getGeometry().appendCoordinate(ol.proj.fromLonLat([ 0, 0 ]));
    }
    api.layerTrack.getSource().addFeature(lineFeature);
    // when
    api.setTrackGradientStyle(track, 'speed', { minValue: 0, maxValue: 2, minColor: [ 255, 255, 255 ], maxColor: [ 0, 0, 0 ] });
    api.layerTrack.getStyle()(lineFeature);
    // then
    expect(api.layerTrack.getStyle()).toBeInstanceOf(Function);
    expect(Utils.getScaleColor).toHaveBeenCalledTimes(track.length);
    expect(api.getGradientStyle).toHaveBeenCalledTimes(track.length - 1);
  });

});
