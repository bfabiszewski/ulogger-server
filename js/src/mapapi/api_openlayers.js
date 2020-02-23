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

import MapViewModel from '../mapviewmodel.js';
import { config } from '../initializer.js';
import uTrack from '../track.js';
import uUtils from '../utils.js';

/**
 * @typedef {Object} MarkerStyles
 * @property {Style} normal
 * @property {Style} start
 * @property {Style} stop
 * @property {Style} extra
 * @property {Style} startExtra
 * @property {Style} stopExtra
 * @property {Style} hilite
 */

/**
 * @typedef {import("../lib/ol.js")} OpenLayers
 * @property {import("ol/control")} control
 * @property {import("ol/Feature").default} Feature
 * @property {import("ol/geom")} geom
 * @property {import("ol/layer/Tile").default} layer.TileLayer
 * @property {import("ol/layer/Vector").default} layer.VectorLayer
 * @property {import("ol/Map").default} Map
 * @property {import("ol/Overlay").default} Overlay
 * @property {import("ol/proj")} proj
 * @property {import("ol/source/OSM").default} source.OSM
 * @property {import("ol/source/Vector").default} source.Vector
 * @property {import("ol/source/XYZ").default} source.XYZ
 * @property {import("ol/style/Icon").default} style.Icon
 * @property {import("ol/style/Stroke").default} style.Stroke
 * @property {import("ol/style/Style").default} style.Style
 * @property {import("ol/View").default} View
 */
/** @type {?OpenLayers} */
let ol;

/**
 * OpenLayers API
 * @class OpenLayersApi
 * @implements {MapViewModel.api}
 */
export default class OpenLayersApi {

  /**
   * @param {MapViewModel} vm
   * @param {?OpenLayers=} olModule
   */
  constructor(vm, olModule = null) {
    /** @type {Map} */
    this.map = null;
    /** @type {MapViewModel} */
    this.viewModel = vm;
    /** @type {VectorLayer} */
    this.layerTrack = null;
    /** @type {VectorLayer} */
    this.layerMarkers = null;
    /** @type {Layer} */
    this.selectedLayer = null;
    /** @type {?MarkerStyles} */
    this.markerStyles = null;
    /** @type {?Overlay} */
    this.popup = null;
    // for tests
    if (olModule) { ol = olModule; }
  }

  /**
   * Initialize map
   * @return {Promise<void, Error>}
   */
  init() {
    uUtils.addCss('css/ol.css', 'ol_css');
    const olReady = ol ? Promise.resolve() : import(/* webpackChunkName : "ol" */'../lib/ol.js').then((m) => { ol = m; });
    return olReady.then(() => {
      this.initMap();
      this.initLayers();
      this.initStyles();
      this.initPopups();
    });
  }

  initMap() {
    const controls = [
      new ol.control.Zoom(),
      new ol.control.Rotate(),
      new ol.control.ScaleLine()
    ];

    const view = new ol.View({
      center: ol.proj.fromLonLat([ config.initLongitude, config.initLatitude ]),
      zoom: 8
    });

    this.map = new ol.Map({
      target: this.viewModel.mapElement,
      controls: controls,
      view: view
    });

    this.map.on('pointermove', (e) => {
      const feature = this.map.forEachFeatureAtPixel(e.pixel,
        /**
         * @param {Feature} _feature
         * @param {Layer} _layer
         * @return {Feature}
         */
        (_feature, _layer) => {
          if (_layer.get('name') === 'Markers') {
            return _feature;
          }
          return null;
        });

      if (feature) {
        this.map.getTargetElement().style.cursor = 'pointer';
        const id = feature.getId();
        if (id !== this.viewModel.model.markerOver) {
          this.viewModel.model.markerOver = id;
        }
      } else {
        this.map.getTargetElement().style.cursor = '';
        this.viewModel.model.markerOver = null;
      }
    });
  }

  /**
   * Initialize map layers
   */
  initLayers() {
    // default layer: OpenStreetMap
    const osm = new ol.layer.TileLayer({
      name: 'OpenStreetMap',
      visible: true,
      source: new ol.source.OSM()
    });
    this.map.addLayer(osm);
    this.selectedLayer = osm;

    // add extra tile layers
    for (const layer of config.olLayers) {
      const olLayer = new ol.layer.TileLayer({
        name: layer.name,
        visible: false,
        source: new ol.source.XYZ({
          url: layer.url
        })
      });
      this.map.addLayer(olLayer);
      if (layer.priority) {
        this.selectedLayer.setVisible(false);
        this.selectedLayer = olLayer;
        this.selectedLayer.setVisible(true);
      }
    }

    // add track and markers layers
    const lineStyle = new ol.style.Style({
      stroke: new ol.style.Stroke({
        color: uUtils.hexToRGBA(config.strokeColor, config.strokeOpacity),
        width: config.strokeWeight
      })
    });
    this.layerTrack = new ol.layer.VectorLayer({
      name: 'Track',
      type: 'data',
      source: new ol.source.Vector(),
      style: lineStyle
    });
    this.layerMarkers = new ol.layer.VectorLayer({
      name: 'Markers',
      type: 'data',
      source: new ol.source.Vector()
    });
    this.map.addLayer(this.layerTrack);
    this.map.addLayer(this.layerMarkers);

    this.initLayerSwitcher();
  }

  initStyles() {
    const anchor = [ 0.5, 1 ];
    this.markerStyles = {
      start: new ol.style.Style({
        image: new ol.style.Icon({
          anchor: anchor,
          src: MapViewModel.getSvgSrc(config.colorStart, true)
        })
      }),
      stop: new ol.style.Style({
        image: new ol.style.Icon({
          anchor: anchor,
          src: MapViewModel.getSvgSrc(config.colorStop, true)
        })
      }),
      normal: new ol.style.Style({
        image: new ol.style.Icon({
          anchor: anchor,
          opacity: 0.7,
          src: MapViewModel.getSvgSrc(config.colorNormal, false)
        })
      }),
      extra: new ol.style.Style({
        image: new ol.style.Icon({
          anchor: anchor,
          src: MapViewModel.getSvgSrc(config.colorExtra, false, true)
        })
      }),
      startExtra: new ol.style.Style({
        image: new ol.style.Icon({
          anchor: anchor,
          src: MapViewModel.getSvgSrc(config.colorStart, true, true)
        })
      }),
      stopExtra: new ol.style.Style({
        image: new ol.style.Icon({
          anchor: anchor,
          src: MapViewModel.getSvgSrc(config.colorStop, true, true)
        })
      }),
      hilite: new ol.style.Style({
        image: new ol.style.Icon({
          anchor: anchor,
          src: MapViewModel.getSvgSrc(config.colorHilite, false)
        })
      })
    };
  }

  initPopups() {
    const popupContainer = document.createElement('div');
    popupContainer.id = 'popup-container';
    popupContainer.className = 'ol-popup';
    const popupContent = document.createElement('div');
    popupContent.id = 'popup-content';
    popupContainer.appendChild(popupContent);
    const popupCloser = document.createElement('a');
    popupCloser.className = 'ol-popup-closer';
    popupContainer.appendChild(popupCloser);

    this.popup = new ol.Overlay({
      element: popupContainer,
      autoPan: true,
      autoPanAnimation: {
        duration: 250
      }
    });
    this.map.addOverlay(this.popup);

    popupCloser.onclick = () => {
      this.popupClose();
      popupCloser.blur();
      return false;
    };

    // add click handler to map to show popup
    this.map.on('click', (e) => {
      const coordinate = e.coordinate;
      const feature = this.map.forEachFeatureAtPixel(e.pixel,
        /** @param {Feature} _feature
         *  @param {Layer} _layer
         *  @return {?Feature}
         */
        (_feature, _layer) => {
          if (_layer.get('name') === 'Markers') {
            return _feature;
          }
          return null;
        });
      if (feature) {
        this.popupOpen(feature.getId(), coordinate);
      } else {
        this.popupClose();
      }
    });
  }

  /**
   * Show popup at coordinate
   * @param {number} id
   * @param {Coordinate} coordinate
   */
  popupOpen(id, coordinate) {
    this.popup.getElement().firstElementChild.innerHTML = '';
    this.popup.getElement().firstElementChild.appendChild(this.viewModel.getPopupElement(id));
    this.popup.setPosition(coordinate);
    this.viewModel.model.markerSelect = id;
  }

  /**
   * Close popup
   */
  popupClose() {
    if (this.popup) {
      // eslint-disable-next-line no-undefined
      this.popup.setPosition(undefined);
      this.popup.getElement().firstElementChild.innerHTML = '';
    }
    this.viewModel.model.markerSelect = null;
  }

  /**
   * Switch layer to target
   * @param {string} targetName
   */
  switchLayer(targetName) {
    this.map.getLayers().forEach(/** @param {Layer} _layer */(_layer) => {
      if (_layer.get('name') === targetName) {
        if (_layer.get('type') === 'data') {
          if (_layer.getVisible()) {
            _layer.setVisible(false);
          } else {
            _layer.setVisible(true);
          }
        } else {
          this.selectedLayer.setVisible(false);
          this.selectedLayer = _layer;
          _layer.setVisible(true);
        }
      }
    });
  }

  initLayerSwitcher() {
    const switcher = document.createElement('div');
    switcher.id = 'switcher';
    switcher.className = 'ol-control';
    document.body.appendChild(switcher);
    const switcherContent = document.createElement('div');
    switcherContent.id = 'switcher-content';
    switcherContent.className = 'ol-layerswitcher';
    switcher.appendChild(switcherContent);
    const switcherCloser = document.createElement('a');
    switcherCloser.className = 'ol-popup-closer';
    switcher.appendChild(switcherCloser);

    this.map.getLayers().forEach(/** @param {Layer} _layer */(_layer) => {
      const layerLabel = document.createElement('label');
      layerLabel.innerHTML = _layer.get('name');
      switcherContent.appendChild(layerLabel);

      const layerRadio = document.createElement('input');
      if (_layer.get('type') === 'data') {
        layerRadio.type = 'checkbox';
        layerLabel.className = 'ol-datalayer';
      } else {
        layerRadio.type = 'radio';
      }
      layerRadio.name = 'layer';
      layerRadio.value = _layer.get('name');
      layerRadio.onclick = (e) => {
        /** @type {HTMLInputElement} */
        const el = e.target;
        this.switchLayer(el.value);
      };
      if (_layer.getVisible()) {
        layerRadio.checked = true;
      }
      layerLabel.insertBefore(layerRadio, layerLabel.childNodes[0]);
    });

    const switcherButton = document.createElement('button');
    const layerImg = document.createElement('img');
    layerImg.src = 'images/layers.svg';
    layerImg.style.width = '60%';
    switcherButton.appendChild(layerImg);

    const switcherHandle = () => {
      if (switcher.style.display === 'block') {
        switcher.style.display = 'none';
      } else {
        switcher.style.display = 'block';
      }
    };

    switcherCloser.addEventListener('click', switcherHandle, false);
    switcherButton.addEventListener('click', switcherHandle, false);
    switcherButton.addEventListener('touchstart', switcherHandle, false);

    const element = document.createElement('div');
    element.className = 'ol-switcher-button ol-unselectable ol-control';
    element.appendChild(switcherButton);

    const switcherControl = new ol.control.Control({ element });
    this.map.addControl(switcherControl);
  }

  /**
   * Clean up API
   */
  cleanup() {
    this.layerTrack = null;
    this.layerMarkers = null;
    this.selectedLayer = null;
    this.markerStyles = null;
    uUtils.removeElementById('switcher');
    if (this.map && this.map.getTargetElement()) {
      this.map.getTargetElement().innerHTML = '';
    }
    this.map = null;
  }

  /**
   * Display track
   * @param {uPositionSet} track Track
   * @param {boolean} update Should fit bounds if true
   */
  displayTrack(track, update) {
    if (!track || !track.hasPositions) {
      return;
    }
    let start = this.layerMarkers ? this.layerMarkers.getSource().getFeatures().length : 0;
    if (start > 0) {
      this.removePoint(--start);
    }
    for (let i = start; i < track.length; i++) {
      this.setMarker(i, track);
    }
    if (track instanceof uTrack) {
      let lineString;
      if (this.layerTrack && this.layerTrack.getSource().getFeatures().length) {
        lineString = this.layerTrack.getSource().getFeatures()[0].getGeometry();
      } else {
        lineString = new ol.geom.LineString([]);
        const lineFeature = new ol.Feature({ geometry: lineString });
        this.layerTrack.getSource().addFeature(lineFeature);
      }
      for (let i = start; i < track.length; i++) {
        const position = track.positions[i];
        lineString.appendCoordinate(ol.proj.fromLonLat([ position.longitude, position.latitude ]));
      }
    }

    let extent = this.layerMarkers.getSource().getExtent();

    if (update) {
      extent = this.fitToExtent(extent);
    }
    this.setZoomToExtent(extent);
  }

  /**
   * Set or replace ZoomToExtent control
   * @param {Array.<number>} extent
   */
  setZoomToExtent(extent) {
    this.map.getControls().forEach((el) => {
      if (el instanceof ol.control.ZoomToExtent) {
        this.map.removeControl(el);
      }
    });
    this.map.addControl(new ol.control.ZoomToExtent({
      extent: extent,
      label: OpenLayersApi.getExtentImg()
    }));
  }

  /**
   * Fit to extent, zoom out if needed
   * @param {Array.<number>} extent
   * @return {Array.<number>}
   */
  fitToExtent(extent) {
    this.map.getView().fit(extent, { padding: [ 40, 10, 10, 10 ] });
    const zoom = this.map.getView().getZoom();
    if (zoom > OpenLayersApi.ZOOM_MAX) {
      this.map.getView().setZoom(OpenLayersApi.ZOOM_MAX);
      extent = this.map.getView().calculateExtent(this.map.getSize());
    }
    return extent;
  }

  /**
   * Clear map
   */
  clearMap() {
    this.popupClose();
    if (this.layerTrack) {
      this.layerTrack.getSource().clear();
    }
    if (this.layerMarkers) {
      this.layerMarkers.getSource().clear();
    }
  }

  /**
   * Get marker style
   * @param {number} id
   * @param {uPositionSet} track
   * @return {Style}
   */
  getMarkerStyle(id, track) {
    const position = track.positions[id];
    let iconStyle = this.markerStyles.normal;
    if (position.hasComment() || position.hasImage()) {
      if (track.isLastPosition(id)) {
        iconStyle = this.markerStyles.stopExtra;
      } else if (track.isFirstPosition(id)) {
        iconStyle = this.markerStyles.startExtra;
      } else {
        iconStyle = this.markerStyles.extra;
      }
    } else if (track.isLastPosition(id)) {
      iconStyle = this.markerStyles.stop;
    } else if (track.isFirstPosition(id)) {
      iconStyle = this.markerStyles.start;
    }
    return iconStyle;
  }

  /**
   * Set marker
   * @param {number} id
   * @param {uPositionSet} track
   */
  setMarker(id, track) {
    // marker
    const position = track.positions[id];
    const marker = new ol.Feature({
      geometry: new ol.geom.Point(ol.proj.fromLonLat([ position.longitude, position.latitude ]))
    });

    const iconStyle = this.getMarkerStyle(id, track);
    marker.setStyle(iconStyle);
    marker.setId(id);
    this.layerMarkers.getSource().addFeature(marker);
  }

  /**
   * @param {number} id
   */
  removePoint(id) {
    const marker = this.layerMarkers.getSource().getFeatureById(id);
    if (marker) {
      this.layerMarkers.getSource().removeFeature(marker);
      if (this.layerTrack) {
        const lineString = this.layerTrack.getSource().getFeatures()[0].getGeometry();
        const coordinates = lineString.getCoordinates();
        coordinates.splice(id, 1);
        lineString.setCoordinates(coordinates);
      }
      if (this.viewModel.model.markerSelect === id) {
        this.popupClose();
      }
    }
  }

  /**
   * Animate marker
   * @param id Marker sequential id
   */
  animateMarker(id) {
    const marker = this.layerMarkers.getSource().getFeatureById(id);
    const initStyle = marker.getStyle();
    marker.setStyle(this.markerStyles.hilite);
    setTimeout(() => marker.setStyle(initStyle), 2000);
  }

  /**
   * Get map bounds
   * eg. (20.597985430276808, 52.15547181298076, 21.363595171488573, 52.33750879522563)
   * @returns {number[]} Bounds [ lon_sw, lat_sw, lon_ne, lat_ne ]
   */
  getBounds() {
    const extent = this.map.getView().calculateExtent(this.map.getSize());
    const sw = ol.proj.toLonLat([ extent[0], extent[1] ]);
    const ne = ol.proj.toLonLat([ extent[2], extent[3] ]);
    return [ sw[0], sw[1], ne[0], ne[1] ];
  }

  /**
   * Zoom to track extent
   */
  zoomToExtent() {
    this.map.getView().fit(this.layerMarkers.getSource().getExtent());
  }

  /**
   * Zoom to bounds
   * @param {number[]} bounds [ lon_sw, lat_sw, lon_ne, lat_ne ]
   */
  zoomToBounds(bounds) {
    const sw = ol.proj.fromLonLat([ bounds[0], bounds[1] ]);
    const ne = ol.proj.fromLonLat([ bounds[2], bounds[3] ]);
    this.map.getView().fit([ sw[0], sw[1], ne[0], ne[1] ]);
  }

  /**
   * Update size
   */
  updateSize() {
    this.map.updateSize();
  }

  /**
   * Get extent image
   * @returns {HTMLImageElement}
   */
  static getExtentImg() {
    const extentImg = document.createElement('img');
    extentImg.src = 'images/extent.svg';
    extentImg.style.width = '60%';
    return extentImg;
  }
}
OpenLayersApi.ZOOM_MAX = 20;
