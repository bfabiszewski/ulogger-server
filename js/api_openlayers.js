/* μlogger
 *
 * Copyright(C) 2017 Bartek Fabiszewski (www.fabiszewski.net)
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


  // openlayers 3+
  /** @namespace */
  var uLogger = uLogger || {};
  /** @namespace */
  uLogger.mapAPI = uLogger.mapAPI || {};
  /** @namespace */
  uLogger.mapAPI.ol = (function(ns) {

    /** @type {ol.Map} */
    var map;
    /** @type {ol.layer.Vector} */
    var layerTrack;
    /** @type {ol.layer.Vector} */
    var layerMarkers;
    /** @type {ol.layer.Base} */
    var selectedLayer;
    /** @type {ol.style.Style|{}} */
    var olStyles;
    var name = 'openlayers';

    /**
     * Initialize map
     */
    function init() {
      var urls = [];
      urls.push('//cdn.polyfill.io/v2/polyfill.min.js?features=requestAnimationFrame,Element.prototype.classList');
      urls.push('js/ol.js');
      for (var i = 0; i < urls.length; i++) {
        ns.addScript(urls[i], 'mapapi_openlayers' + '_' + i);
      }

      ns.addCss('css/ol.css', 'ol_css');

      var controls = [
        new ol.control.Zoom(),
        new ol.control.Rotate(),
        new ol.control.ScaleLine(),
        new ol.control.ZoomToExtent({label: getExtentImg()})
      ];

      var view = new ol.View({
        center: ol.proj.fromLonLat([ns.config.init_longitude, ns.config.init_latitude]),
        zoom: 8
      });

      map = new ol.Map({
        target: 'map-canvas',
        controls: controls,
        view: view
      });

      // default layer: OpenStreetMap
      var osm = new ol.layer.Tile({
        name: 'OpenStreetMap',
        visible: true,
        source: new ol.source.OSM()
      });
      map.addLayer(osm);
      selectedLayer = osm;

      // add extra layers
      for (var layerName in ns.config.ol_layers) {
        if (ns.config.ol_layers.hasOwnProperty(layerName)) {
          var layerUrl = ns.config.ol_layers[layerName];
          var ol_layer = new ol.layer.Tile({
            name: layerName,
            visible: false,
            source: new ol.source.XYZ({
              url: layerUrl
            })
          });
          map.addLayer(ol_layer);
        }
      }

      // init layers
      var lineStyle = new ol.style.Style({
        stroke: new ol.style.Stroke({
          color: ns.hexToRGBA(ns.config.strokeColor, ns.config.strokeOpacity),
          width: ns.config.strokeWeight
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

      // styles
      olStyles = {};
      var iconRed = new ol.style.Icon({
        anchor: [0.5, 1],
        src: 'images/marker-red.png'
      });
      var iconGreen = new ol.style.Icon({
        anchor: [0.5, 1],
        src: 'images/marker-green.png'
      });
      var iconWhite = new ol.style.Icon({
        anchor: [0.5, 1],
        opacity: 0.7,
        src: 'images/marker-white.png'
      });
      var iconGold = new ol.style.Icon({
        anchor: [0.5, 1],
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

      // popups
      var popupContainer = document.createElement('div');
      popupContainer.id = 'popup';
      popupContainer.className = 'ol-popup';
      document.body.appendChild(popupContainer);
      var popupCloser = document.createElement('a');
      popupCloser.id = 'popup-closer';
      popupCloser.className = 'ol-popup-closer';
      popupCloser.href = '#';
      popupContainer.appendChild(popupCloser);
      var popupContent = document.createElement('div');
      popupContent.id = 'popup-content';
      popupContainer.appendChild(popupContent);

      var popup = new ol.Overlay({
        element: popupContainer,
        autoPan: true,
        autoPanAnimation: {
          duration: 250
        }
      });

      popupCloser.onclick = function () {
        // eslint-disable-next-line no-undefined
        popup.setPosition(undefined);
        popupCloser.blur();
        return false;
      };

      // add click handler to map to show popup
      map.on('click', function (e) {
        var coordinate = e.coordinate;
        var feature = map.forEachFeatureAtPixel(e.pixel,
          function (_feature, _layer) {
            if (_layer.get('name') === 'Markers') {
              return _feature;
            }
            return null;
          });
        if (feature) {
          var pos = feature.get('p');
          var id = feature.getId();
          var posLen = feature.get('posLen');
          // popup show
          popup.setPosition(coordinate);
          popupContent.innerHTML = ns.getPopupHtml(pos, id, posLen);
          map.addOverlay(popup);
          ns.chartShowPosition(id);
        } else {
          // popup destroy
          // eslint-disable-next-line no-undefined
          popup.setPosition(undefined);
        }
      });

      // change mouse cursor when over marker
      map.on('pointermove', function (e) {
        var hit = map.forEachFeatureAtPixel(e.pixel, function (feature, layer) {
          return layer.get('name') === 'Markers';
        });
        if (hit) {
          this.getTargetElement().style.cursor = 'pointer';
        } else {
          this.getTargetElement().style.cursor = '';
        }
      });

      // layer switcher
      var switcher = document.createElement('div');
      switcher.id = 'switcher';
      switcher.className = 'ol-control';
      document.body.appendChild(switcher);
      var switcherContent = document.createElement('div');
      switcherContent.id = 'switcher-content';
      switcherContent.className = 'ol-layerswitcher';
      switcher.appendChild(switcherContent);

      map.getLayers().forEach(function (layer) {
        var layerLabel = document.createElement('label');
        layerLabel.innerHTML = layer.get('name');
        switcherContent.appendChild(layerLabel);

        var layerRadio = document.createElement('input');
        if (layer.get('type') === 'data') {
          layerRadio.type = 'checkbox';
          layerLabel.className = 'ol-datalayer';
        } else {
          layerRadio.type = 'radio';
        }
        layerRadio.name = 'layer';
        layerRadio.value = layer.get('name');
        layerRadio.onclick = switchLayer;
        if (layer.getVisible()) {
          layerRadio.checked = true;
        }
        layerLabel.insertBefore(layerRadio, layerLabel.childNodes[0]);
      });

      function switchLayer() {
        var targetName = this.value;
        map.getLayers().forEach(function (layer) {
          if (layer.get('name') === targetName) {
            if (layer.get('type') === 'data') {
              if (layer.getVisible()) {
                layer.setVisible(false);
              } else {
                layer.setVisible(true);
              }
            } else {
              selectedLayer.setVisible(false);
              selectedLayer = layer;
              layer.setVisible(true);
            }
          }
        });
      }

      var switcherButton = document.createElement('button');
      var layerImg = document.createElement('img');
      layerImg.src = 'images/layers.svg';
      layerImg.style.width = '60%';
      switcherButton.appendChild(layerImg);

      // eslint-disable-next-line func-style
      var switcherHandle = function () {
        var el = document.getElementById('switcher');
        if (el.style.display === 'block') {
          el.style.display = 'none';
        } else {
          el.style.display = 'block';
        }
      };

      switcherButton.addEventListener('click', switcherHandle, false);
      switcherButton.addEventListener('touchstart', switcherHandle, false);

      var element = document.createElement('div');
      element.className = 'ol-switcher-button ol-unselectable ol-control';
      element.appendChild(switcherButton);

      var switcherControl = new ol.control.Control({
        element: element
      });
      map.addControl(switcherControl);
    }

    /**
     * Clean up API
     */
    function cleanup() {
      map = null;
      layerTrack = null;
      layerMarkers = null;
      selectedLayer = null;
      olStyles = null;
      ns.removeElementById('popup');
      ns.removeElementById('switcher');
      ns.clearMapCanvas();
    }

    /**
     * Display track
     * @param {HTMLCollection} positions XML element
     * @param {boolean} update Should fit bounds if true
     */
    function displayTrack(positions, update) {
      var totalMeters = 0;
      var totalSeconds = 0;
      var points = [];
      var posLen = positions.length;
      for (var i = 0; i < posLen; i++) {
        var p = ns.parsePosition(positions[i], i);
        totalMeters += p.distance;
        totalSeconds += p.seconds;
        p.totalMeters = totalMeters;
        p.totalSeconds = totalSeconds;
        // set marker
        setMarker(p, i, posLen);
        // update polyline
        var point = ol.proj.fromLonLat([p.longitude, p.latitude]);
        points.push(point);
      }
      var lineString = new ol.geom.LineString(points);

      var lineFeature = new ol.Feature({
        geometry: lineString
      });

      layerTrack.getSource().addFeature(lineFeature);

      var extent = layerTrack.getSource().getExtent();

      map.getControls().forEach(function (el) {
        if (el instanceof ol.control.ZoomToExtent) {
          map.removeControl(el);
        }
      });

      if (update) {
        map.getView().fit(extent);
        var zoom = map.getView().getZoom();
        if (zoom > 20) {
          map.getView().setZoom(20);
          extent = map.getView().calculateExtent(map.getSize());
        }
      }

      var zoomToExtentControl = new ol.control.ZoomToExtent({
        extent: extent,
        label: getExtentImg()
      });
      map.addControl(zoomToExtentControl);

      ns.updateSummary(p.timestamp, totalMeters, totalSeconds);
      if (p.tid !== ns.config.trackid) {
        ns.config.trackid = p.tid;
        ns.setTrack(ns.config.trackid);
      }
      ns.updateChart();
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
     * @param {uLogger.Position} pos
     * @param {number} id
     * @param {number} posLen
     */
    function setMarker(pos, id, posLen) {
      // marker
      var marker = new ol.Feature({
        geometry: new ol.geom.Point(ol.proj.fromLonLat([pos.longitude, pos.latitude]))
      });

      var iconStyle;
      if (ns.isLatest()) {
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
      marker.set('p', pos);
      marker.set('posLen', posLen);
      layerMarkers.getSource().addFeature(marker);
    }

    /**
     * Add listener on chart to show position on map
     * @param {google.visualization.LineChart} chart
     * @param {google.visualization.DataTable} data
     */
    function addChartEvent(chart, data) {
      google.visualization.events.addListener(chart, 'select', function () {
        var selection = chart.getSelection()[0];
        if (selection) {
          var id = data.getValue(selection.row, 0) - 1;
          var marker = layerMarkers.getSource().getFeatureById(id);
          var initStyle = marker.getStyle();
          var iconStyle = olStyles['gold'];
          marker.setStyle(iconStyle);
          setTimeout(function () {
            marker.setStyle(initStyle);
          }, 2000);
        }
      });
    }

    /**
     * Get map bounds
     * eg. (20.597985430276808, 52.15547181298076, 21.363595171488573, 52.33750879522563)
     * @returns {number[]} Bounds
     */
    function getBounds() {
      var extent = map.getView().calculateExtent(map.getSize());
      var bounds = ol.proj.transformExtent(extent, 'EPSG:900913', 'EPSG:4326');
      var lon_sw = bounds[0];
      var lat_sw = bounds[1];
      var lon_ne = bounds[2];
      var lat_ne = bounds[3];
      return [lon_sw, lat_sw, lon_ne, lat_ne];
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
      var extent = ol.proj.transformExtent(bounds, 'EPSG:4326', 'EPSG:900913');
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
      var extentImg = document.createElement('img');
      extentImg.src = 'images/extent.svg';
      extentImg.style.width = '60%';
      return extentImg;
    }

    return {
      name: name,
      init: init,
      cleanup: cleanup,
      displayTrack: displayTrack,
      clearMap: clearMap,
      setMarker: setMarker,
      addChartEvent: addChartEvent,
      getBounds: getBounds,
      zoomToExtent: zoomToExtent,
      zoomToBounds: zoomToBounds,
      updateSize: updateSize
    }

  })(uLogger);
