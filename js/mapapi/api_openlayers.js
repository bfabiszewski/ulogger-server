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

import { config } from '../constants.js';
import uEvent from '../event.js';
import uUI from '../ui.js';
import uUtils from '../utils.js';

// openlayers 3+
/**
 * OpenLayers API module
 * @module olApi
 * @implements {uMap.api}
 */

/** @type {ol.Map} */
let map = null;
/** @type {uBinder} */
let binder = null;
/** @type {ol.layer.Vector} */
let layerTrack = null;
/** @type {ol.layer.Vector} */
let layerMarkers = null;
/** @type {ol.layer.Base} */
let selectedLayer = null;
/** @type {ol.style.Style|{}} */
let olStyles = {};
/** @type {string} */
const name = 'openlayers';
/** @type {?number} */
let pointOver = null;

/**
 * Initialize map
 * @param {uBinder} b
 * @param {HTMLElement} target
 */
function init(b, target) {

  binder = b;

  uUtils.addScript('//cdn.polyfill.io/v2/polyfill.min.js?features=requestAnimationFrame,Element.prototype.classList', 'mapapi_openlayers_polyfill');
  uUtils.addScript('js/lib/ol.js', 'mapapi_openlayers');
  uUtils.addCss('css/ol.css', 'ol_css');

  const controls = [
    new ol.control.Zoom(),
    new ol.control.Rotate(),
    new ol.control.ScaleLine(),
    new ol.control.ZoomToExtent({ label: getExtentImg() })
  ];

  const view = new ol.View({
    center: ol.proj.fromLonLat([ config.init_longitude, config.init_latitude ]),
    zoom: 8
  });

  map = new ol.Map({
    target: target,
    controls: controls,
    view: view
  });

  map.on('pointermove', (e) => {
    const feature = map.forEachFeatureAtPixel(e.pixel,
      (_feature, _layer) => {
        if (_layer.get('name') === 'Markers') {
          return _feature;
        }
        return null;
      });
    // emit mouse over marker event
    /** @type {?number} */
    const id = feature ? feature.getId() : null;
    if (id !== pointOver) {
      binder.dispatchEvent(uEvent.MARKER_OVER);
      pointOver = id;
      if (id) {
        binder.dispatchEvent(uEvent.MARKER_OVER, id);
      }
    }
    // change mouse cursor when over marker
    if (feature) {
      map.getTargetElement().style.cursor = 'pointer';
    } else {
      map.getTargetElement().style.cursor = '';
    }
  });

  initLayers();
  initStyles();
  initPopups();
}

/**
 * Initialize map layers
 */
function initLayers() {
  // default layer: OpenStreetMap
  const osm = new ol.layer.Tile({
    name: 'OpenStreetMap',
    visible: true,
    source: new ol.source.OSM()
  });
  map.addLayer(osm);
  selectedLayer = osm;

  // add extra tile layers
  for (const layerName in config.ol_layers) {
    if (config.ol_layers.hasOwnProperty(layerName)) {
      const layerUrl = config.ol_layers[layerName];
      const ol_layer = new ol.layer.Tile({
        name: layerName,
        visible: false,
        source: new ol.source.XYZ({
          url: layerUrl
        })
      });
      map.addLayer(ol_layer);
    }
  }

  // add track and markers layers
  const lineStyle = new ol.style.Style({
    stroke: new ol.style.Stroke({
      color: uUtils.hexToRGBA(config.strokeColor, config.strokeOpacity),
      width: config.strokeWeight
    })
  });
  layerTrack = new ol.layer.Vector({
    name: 'Track',
    type: 'data',
    source: new ol.source.Vector(),
    style: lineStyle
  });
  layerMarkers = new ol.layer.Vector({
    name: 'Markers',
    type: 'data',
    source: new ol.source.Vector()
  });
  map.addLayer(layerTrack);
  map.addLayer(layerMarkers);

  initLayerSwitcher();
}


function initStyles() {
  olStyles = {};
  const iconRed = new ol.style.Icon({
    anchor: [ 0.5, 1 ],
    src: 'images/marker-red.png'
  });
  const iconGreen = new ol.style.Icon({
    anchor: [ 0.5, 1 ],
    src: 'images/marker-green.png'
  });
  const iconWhite = new ol.style.Icon({
    anchor: [ 0.5, 1 ],
    opacity: 0.7,
    src: 'images/marker-white.png'
  });
  const iconGold = new ol.style.Icon({
    anchor: [ 0.5, 1 ],
    src: 'images/marker-gold.png'
  });
  olStyles['red'] = new ol.style.Style({
    image: iconRed
  });
  olStyles['green'] = new ol.style.Style({
    image: iconGreen
  });
  olStyles['white'] = new ol.style.Style({
    image: iconWhite
  });
  olStyles['gold'] = new ol.style.Style({
    image: iconGold
  });
}

function initPopups() {
  const popupContainer = document.createElement('div');
  popupContainer.id = 'popup';
  popupContainer.className = 'ol-popup';
  document.body.appendChild(popupContainer);
  const popupCloser = document.createElement('a');
  popupCloser.id = 'popup-closer';
  popupCloser.className = 'ol-popup-closer';
  popupCloser.href = '#';
  popupContainer.appendChild(popupCloser);
  const popupContent = document.createElement('div');
  popupContent.id = 'popup-content';
  popupContainer.appendChild(popupContent);

  const popup = new ol.Overlay({
    element: popupContainer,
    autoPan: true,
    autoPanAnimation: {
      duration: 250
    }
  });

  popupCloser.onclick = () => {
    // eslint-disable-next-line no-undefined
    popup.setPosition(undefined);
    popupCloser.blur();
    return false;
  };

  // add click handler to map to show popup
  map.on('click', (e) => {
    const coordinate = e.coordinate;
    const feature = map.forEachFeatureAtPixel(e.pixel,
      (_feature, _layer) => {
        if (_layer.get('name') === 'Markers') {
          return _feature;
        }
        return null;
      });
    if (feature) {
      // popup show
      popup.setPosition(coordinate);
      popupContent.innerHTML = uUI.getPopupHtml(feature.getId());
      map.addOverlay(popup);
      binder.dispatchEvent(uEvent.MARKER_SELECT, feature.getId());
    } else {
      // popup destroy
      // eslint-disable-next-line no-undefined
      popup.setPosition(undefined);
      binder.dispatchEvent(uEvent.MARKER_SELECT);
    }
  });
}

function initLayerSwitcher() {
  const switcher = document.createElement('div');
  switcher.id = 'switcher';
  switcher.className = 'ol-control';
  document.body.appendChild(switcher);
  const switcherContent = document.createElement('div');
  switcherContent.id = 'switcher-content';
  switcherContent.className = 'ol-layerswitcher';
  switcher.appendChild(switcherContent);

  map.getLayers().forEach((_layer) => {
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
    layerRadio.onclick = switchLayer;
    if (_layer.getVisible()) {
      layerRadio.checked = true;
    }
    layerLabel.insertBefore(layerRadio, layerLabel.childNodes[0]);
  });

  function switchLayer() {
    const targetName = this.value;
    map.getLayers().forEach((_layer) => {
      if (_layer.get('name') === targetName) {
        if (_layer.get('type') === 'data') {
          if (_layer.getVisible()) {
            _layer.setVisible(false);
          } else {
            _layer.setVisible(true);
          }
        } else {
          selectedLayer.setVisible(false);
          selectedLayer = _layer;
          _layer.setVisible(true);
        }
      }
    });
  }

  const switcherButton = document.createElement('button');
  const layerImg = document.createElement('img');
  layerImg.src = 'images/layers.svg';
  layerImg.style.width = '60%';
  switcherButton.appendChild(layerImg);

  const switcherHandle = () => {
    const el = document.getElementById('switcher');
    if (el.style.display === 'block') {
      el.style.display = 'none';
    } else {
      el.style.display = 'block';
    }
  };

  switcherButton.addEventListener('click', switcherHandle, false);
  switcherButton.addEventListener('touchstart', switcherHandle, false);

  const element = document.createElement('div');
  element.className = 'ol-switcher-button ol-unselectable ol-control';
  element.appendChild(switcherButton);

  const switcherControl = new ol.control.Control({
    element: element
  });
  map.addControl(switcherControl);
}

/**
 * Clean up API
 */
function cleanup() {
  layerTrack = null;
  layerMarkers = null;
  selectedLayer = null;
  olStyles = null;
  uUI.removeElementById('popup');
  uUI.removeElementById('switcher');
  if (map && map.getTargetElement()) {
    map.getTargetElement().innerHTML = '';
  }
  map = null;
}

/**
 * Display track
 * @param {uTrack} track Track
 * @param {boolean} update Should fit bounds if true
 */
function displayTrack(track, update) {
  if (!track) {
    return;
  }
  let i = 0;
  const lineString = new ol.geom.LineString([]);
  for (const position of track.positions) {
    // set marker
    setMarker(i++, track);
    if (track.continuous) {
      // update polyline
      lineString.appendCoordinate(ol.proj.fromLonLat([ position.longitude, position.latitude ]));
    }
  }
  if (lineString.getLength() > 0) {
    const lineFeature = new ol.Feature({
      geometry: lineString
    });
    layerTrack.getSource().addFeature(lineFeature);
  }

  let extent = layerMarkers.getSource().getExtent();

  map.getControls().forEach((el) => {
    if (el instanceof ol.control.ZoomToExtent) {
      map.removeControl(el);
    }
  });

  if (update) {
    map.getView().fit(extent);
    const zoom = map.getView().getZoom();
    if (zoom > 20) {
      map.getView().setZoom(20);
      extent = map.getView().calculateExtent(map.getSize());
    }
  }

  const zoomToExtentControl = new ol.control.ZoomToExtent({
    extent,
    label: getExtentImg()
  });
  map.addControl(zoomToExtentControl);
}

/**
 * Clear map
 */
function clearMap() {
  if (layerTrack) {
    layerTrack.getSource().clear();
  }
  if (layerMarkers) {
    layerMarkers.getSource().clear();
  }
}

/**
 * Set marker
 * @param {number} id
 * @param {uTrack} track
 */
function setMarker(id, track) {
  // marker
  const position = track.positions[id];
  const posLen = track.positions.length;
  const marker = new ol.Feature({
    geometry: new ol.geom.Point(ol.proj.fromLonLat([ position.longitude, position.latitude ]))
  });

  let iconStyle;
  if (config.showLatest) {
    iconStyle = olStyles['red'];
  } else if (id === 0) {
    iconStyle = olStyles['green'];
  } else if (id === posLen - 1) {
    iconStyle = olStyles['red'];
  } else {
    iconStyle = olStyles['white'];
  }
  marker.setStyle(iconStyle);
  marker.setId(id);
  layerMarkers.getSource().addFeature(marker);
}

/**
 * Animate marker
 * @param id Marker sequential id
 */
function animateMarker(id) {
  const marker = layerMarkers.getSource().getFeatureById(id);
  const initStyle = marker.getStyle();
  const iconStyle = olStyles['gold'];
  marker.setStyle(iconStyle);
  setTimeout(() => marker.setStyle(initStyle), 2000);
}

/**
 * Get map bounds
 * eg. (20.597985430276808, 52.15547181298076, 21.363595171488573, 52.33750879522563)
 * @returns {number[]} Bounds
 */
function getBounds() {
  const extent = map.getView().calculateExtent(map.getSize());
  const bounds = ol.proj.transformExtent(extent, 'EPSG:900913', 'EPSG:4326');
  const lon_sw = bounds[0];
  const lat_sw = bounds[1];
  const lon_ne = bounds[2];
  const lat_ne = bounds[3];
  return [ lon_sw, lat_sw, lon_ne, lat_ne ];
}

/**
 * Zoom to track extent
 */
function zoomToExtent() {
  map.getView().fit(layerMarkers.getSource().getExtent());
}

/**
 * Zoom to bounds
 * @param {number[]} bounds
 */
function zoomToBounds(bounds) {
  const extent = ol.proj.transformExtent(bounds, 'EPSG:4326', 'EPSG:900913');
  map.getView().fit(extent);
}

/**
 * Update size
 */
function updateSize() {
  map.updateSize();
}

/**
 * Get extent image
 * @returns {HTMLImageElement}
 */
function getExtentImg() {
  const extentImg = document.createElement('img');
  extentImg.src = 'images/extent.svg';
  extentImg.style.width = '60%';
  return extentImg;
}

export {
  name,
  init,
  cleanup,
  displayTrack,
  clearMap,
  animateMarker,
  getBounds,
  zoomToExtent,
  zoomToBounds,
  updateSize
}
