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

window.onload = function () {
  uLogger.initUI();
  uLogger.loadMapAPI();
};

/** @namespace */
var uLogger = window.uLogger || {};
(function (ns) {
  "use strict";

  /**
   * @namespace
   * @alias uLogger.mapAPI
   */
  var mapAPI = ns.mapAPI || {};
  /**
   * @namespace
   * @alias uLogger.ui
   */
  var ui = ns.ui || {};

  /** @type {uLogger.config} */
  ns.config = ns.config || {};
  /** @type {uLogger.admin} */
  ns.admin = ns.admin || {};
  /** @type {uLogger.lang} */
  ns.lang = ns.lang || {};

  /** @type {number} userId */
  ns.trackId = ns.trackId || -1;
  /** @type {number} trackId */
  ns.userId = ns.userId || -1;
  var factor_kmh, unit_kmh, factor_m, unit_m, factor_km, unit_km;
  var latest = 0;
  var live = 0;
  var chart = null;
  var altitudes = {};
  var loadTime = 0;
  var auto;
  var savedBounds = null;
  /** @type {?uLogger.mapAPI.api} */
  var map = null;
  initUnits();

  /**
   * Initialize UI elements
   */
  function initUI() {
    /** @type {HTMLElement} */
    ui.menu = document.getElementById('menu');
    /** @type {?HTMLElement} */
    ui.menuHead = document.getElementById('menu_head');
    /** @type {?HTMLElement} */
    ui.userDropdown = document.getElementById('user_dropdown');
    /** @type {?HTMLElement} */
    ui.menuPass = document.getElementById('menu_pass');
    // noinspection JSValidateTypes
    /** @type {?HTMLSelectElement} */
    ui.userSelect = function () {
      var list = document.getElementsByName('user');
      if (list.length) { return list[0]; }
      return null;
    }();
    // noinspection JSValidateTypes
    /** @type {HTMLSelectElement} */
    ui.trackSelect = document.getElementsByName('track')[0];
    // noinspection JSValidateTypes
    /** @type {HTMLSelectElement} */
    ui.api = document.getElementsByName('api')[0];
    // noinspection JSValidateTypes
    /** @type {HTMLSelectElement} */
    ui.lang = document.getElementsByName('lang')[0];
    // noinspection JSValidateTypes
    /** @type {HTMLSelectElement} */
    ui.units = document.getElementsByName('units')[0];
    /** @type {HTMLElement} */
    ui.chart = document.getElementById('chart');
    /** @type {HTMLElement} */
    ui.chartClose = document.getElementById('chart_close');
    /** @type {HTMLElement} */
    ui.bottom = document.getElementById('bottom');
    /** @type {HTMLElement} */
    ui.altitudes = document.getElementById('altitudes');
    /** @type {HTMLElement} */
    ui.main = document.getElementById('main');
    /** @type {HTMLElement} */
    ui.menuClose = document.getElementById('menu-close');
    /** @type {HTMLElement} */
    ui.track = document.getElementById('track');
    /** @type {HTMLElement} */
    ui.trackTitle = ui.track ? ui.track.getElementsByClassName('menutitle')[0] : null;
    /** @type {HTMLElement} */
    ui.import = document.getElementById('import');
    /** @type {HTMLElement} */
    ui.importTitle = ui.import ? ui.import.getElementsByClassName('menutitle')[0] : null;
    /** @type {HTMLElement} */
    ui.summary = document.getElementById('summary');
    /** @type {HTMLElement} */
    ui.latest = document.getElementById('latest');
    /** @type {HTMLElement} */
    ui.autoReload = document.getElementById('auto_reload');
    /** @type {HTMLElement} */
    ui.forceReload = document.getElementById('force_reload');
    /** @type {HTMLElement} */
    ui.auto = document.getElementById('auto');
    /** @type {HTMLElement} */
    ui.setTime = document.getElementById('set_time');
    /** @type {HTMLElement} */
    ui.exportKml = document.getElementById('export_kml');
    /** @type {HTMLElement} */
    ui.exportGpx = document.getElementById('export_gpx');
    /** @type {?HTMLElement} */
    ui.inputFile = document.getElementById('inputFile');
    /** @type {HTMLElement} */
    ui.importGpx = document.getElementById('import_gpx');
    /** @type {?HTMLElement} */
    ui.addUser = document.getElementById('adduser');
    /** @type {?HTMLElement} */
    ui.editUser = document.getElementById('edituser');
    /** @type {?HTMLElement} */
    ui.editTrack = document.getElementById('edittrack');
    /** @type {HTMLElement} */
    ui.map = document.getElementById('map-canvas');
    /** @type {HTMLElement} */
    ui.head = document.getElementsByTagName('head')[0];

    if (ui.menuHead) {
      ui.menuHead.onclick = userMenu;
    }
    if (ui.menuPass) {
      ui.menuPass.onclick = ns.changePass;
    }
    if (ui.userSelect) {
      ui.userSelect.onchange = selectUser;
    }
    ui.trackSelect.onchange = selectTrack;
    ui.latest.onchange = toggleLatest;
    ui.autoReload.onchange = autoReload;
    ui.setTime.onclick = setTime;
    ui.forceReload.onclick = reload;
    ui.altitudes.onclick = toggleChart;
    ui.api.onchange = function () {
      loadMapAPI(ui.api.options[ui.api.selectedIndex].value);
    };
    ui.lang.onchange = function () {
      setLang(ui.lang.options[ui.lang.selectedIndex].value);
    };
    ui.units.onchange = function () {
      setUnits(ui.units.options[ui.units.selectedIndex].value);
    };
    ui.exportKml.onclick = function () {
      exportFile('kml');
    };
    ui.exportGpx.onclick = function () {
      exportFile('gpx');
    };
    if (ui.inputFile) {
      ui.inputFile.onchange = importFile;
      ui.importGpx.onclick = function () {
        ui.inputFile.click();
      };
    }
    if (ui.addUser) {
      ui.addUser.onclick = ns.admin.addUser;
    }
    if (ui.editUser) {
      ui.editUser.onclick = ns.admin.editUser;
    }
    if (ui.editTrack) {
      ui.editTrack.onclick = ns.editTrack;
    }
    ui.menuClose.onclick = toggleMenu;
    ui.chartClose.onclick = hideChart;
  }

  /**
   * Initialize units based on settings
   */
  function initUnits() {
    if (ns.config.units === 'imperial') {
      factor_kmh = 0.62; // to mph
      unit_kmh = 'mph';
      factor_m = 3.28; // to feet
      unit_m = 'ft';
      factor_km = 0.62; // to miles
      unit_km = 'mi';
    } else if (ns.config.units === 'nautical') {
      factor_kmh = 0.54; // to knots
      unit_kmh = 'kt';
      factor_m = 1; // meters
      unit_m = 'm';
      factor_km = 0.54; // to nautical miles
      unit_km = 'nm';
    } else {
      factor_kmh = 1;
      unit_kmh = 'km/h';
      factor_m = 1;
      unit_m = 'm';
      factor_km = 1;
      unit_km = 'km';
    }
  }

  /**
   * Display altitudes chart
   */
  function displayChart() {
    ui.bottom.style.display = 'block';
    if (chart) {
      google.visualization.events.removeAllListeners(chart);
    }
    var data = new google.visualization.DataTable();
    data.addColumn('number', 'id');
    data.addColumn('number', ns.lang.strings['altitude']);

    for (var id in altitudes) {
      if (altitudes.hasOwnProperty(id)) {
        data.addRow([parseInt(id) + 1, Math.round((altitudes[id] * factor_m))]);
      }
    }

    var options = {
      title: ns.lang.strings['altitude'] + ' (' + unit_m + ')',
      hAxis: {textPosition: 'none'},
      legend: {position: 'none'}
    };

    chart = new google.visualization.LineChart(ui.chart);
    chart.draw(data, options);

    map.addChartEvent(chart, data);
  }

  /**
   * Update altitudes chart if visible
   */
  function updateChart() {
    if (isChartVisible()) {
      chart.clearChart();
      displayChart();
    }
  }

  /**
   * Is chart visible
   * @returns {boolean}
   */
  function isChartVisible() {
    return ui.bottom.style.display === 'block';
  }

  /**
   * Hide chart
   */
  function hideChart() {
    chart.clearChart();
    ui.bottom.style.display = 'none';
  }

  /**
   * Toggle chart visibility
   */
  function toggleChart() {
    if (isChartVisible()) {
      hideChart();
    } else if (Object.keys(altitudes).length > 1) {
      displayChart();
    }
  }

  /**
   * Show position on chart
   * @param {number} id Position id
   */
  function chartShowPosition(id) {
    if (isChartVisible()) {
      var index = 0;
      for (var key in altitudes) {
        if (altitudes.hasOwnProperty(key) && parseInt(key) === id) {
          chart.setSelection([{row: index, column: null}]);
          break;
        }
        index++;
      }
    }
  }

  /**
   * Toggle chart link visibility
   */
  function toggleChartLink() {
    var link = ui.altitudes;
    if (Object.keys(altitudes).length > 1) {
      link.style.visibility = 'visible';
    } else {
      link.style.visibility = 'hidden';
    }
  }

  /**
   * Toggle side menu
   */
  function toggleMenu() {
    if (ui.menuClose.innerHTML === '»') {
      ui.menu.style.width = '0';
      ui.main.style.marginRight = '0';
      ui.menuClose.style.right = '0';
      ui.menuClose.innerHTML = '«';
    } else {
      ui.menu.style.width = '165px';
      ui.main.style.marginRight = '165px';
      ui.menuClose.style.right = '165px';
      ui.menuClose.innerHTML = '»';
    }
    map.updateSize();
  }

  /**
   * Load track from database
   * @param {number} userid User id
   * @param {number} trackid Track id
   * @param update
   */
  function loadTrack(userid, trackid, update) {
    if (trackid < 0) {
      return;
    }
    if (latest === 1) {
      trackid = 0;
    }

    ns.get('utils/getpositions.php',
      {
        trackid: trackid,
        userid: userid,
        last: latest
      },
      {
        loader: ui.trackTitle,
        success: function (xml) {
          var positions = xml.getElementsByTagName('position');
          if (positions.length > 0) {
            map.clearMap();
            altitudes = {};
            map.displayTrack(positions, update);
            toggleChartLink();
          }
        },
        fail: function () {
          alert(ns.lang.strings['actionfailure']);
        }
      });
  }

  /**
   * Load all users positions from database
   */
  function loadLastPositionAllUsers() {
    ns.get('utils/getpositions.php',
      {
        last: latest
      },
      {
        loader: ui.trackTitle,
        success: function (xml) {
          map.clearMap();
          var positions = xml.getElementsByTagName('position');
          var posLen = positions.length;
          var timestampMax = 0;
          for (var i = 0; i < posLen; i++) {
            var p = parsePosition(positions[i], i);
            // set marker
            map.setMarker(p, i, posLen);
            if (p.timestamp > timestampMax) {
              timestampMax = p.timestamp;
            }
          }
          map.zoomToExtent();
          updateSummary(timestampMax);
        },
        fail: function () {
          alert(ns.lang.strings['actionfailure']);
        }
      });
  }

  /**
   * Position
   * @typedef {Object} uLogger.Position
   * @property {number} latitude
   * @property {number} longitude
   * @property {?number} altitude
   * @property {?number} speed
   * @property {?number} bearing
   * @property {?number} accuracy
   * @property {?string} provider
   * @property {?string} comment
   * @property {string} username
   * @property {string} trackname
   * @property {string} tid
   * @property {number} timestamp
   * @property {number} distance
   * @property {number} seconds
   * @property {number} totalMeters
   * @property {number} totalSeconds
   * */

  /**
   * Parse XML element to Position object
   * @param {Element} xmlPos XML position element
   * @param {number} id Position ID
   * @returns {uLogger.Position} Position
   */
  function parsePosition(xmlPos, id) {
    // read data
    var position = {};
    position.latitude = getNodeAsFloat(xmlPos, 'latitude');
    position.longitude = getNodeAsFloat(xmlPos, 'longitude');
    position.altitude = getNodeAsInt(xmlPos, 'altitude'); // may be null
    if (position.altitude != null) {
      // save altitudes for chart
      altitudes[id] = position.altitude;
    }
    position.speed = getNodeAsInt(xmlPos, 'speed'); // may be null
    position.bearing = getNodeAsInt(xmlPos, 'bearing'); // may be null
    position.accuracy = getNodeAsInt(xmlPos, 'accuracy'); // may be null
    position.provider = getNode(xmlPos, 'provider'); // may be null
    position.comments = getNode(xmlPos, 'comments'); // may be null
    position.username = getNode(xmlPos, 'username');
    position.trackname = getNode(xmlPos, 'trackname');
    position.tid = getNode(xmlPos, 'trackid');
    position.timestamp = getNodeAsInt(xmlPos, 'timestamp');
    position.distance = getNodeAsInt(xmlPos, 'distance');
    position.seconds = getNodeAsInt(xmlPos, 'seconds');
    return position;
  }

  /**
   * Get popup html
   * @param {uLogger.Position} pos Position
   * @param {number} id Position ID
   * @param {number} count Positions count
   * @returns {string}
   */
  function getPopupHtml(pos, id, count) {
    var date = '–––';
    var time = '–––';
    if (pos.timestamp > 0) {
      var d = new Date(pos.timestamp * 1000);
      date = d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2);
      time = d.toTimeString();
      var offset;
      if ((offset = time.indexOf(' ')) >= 0) {
        time = time.substr(0, offset) + ' <span class="smaller">' + time.substr(offset + 1) + '</span>';
      }
    }
    var provider = '';
    if (pos.provider === 'gps') {
      provider = ' (<img class="icon" alt="' + ns.lang.strings['gps'] + '" title="' + ns.lang.strings['gps'] + '"  src="images/gps_dark.svg">)';
    } else if (pos.provider === 'network') {
      provider = ' (<img class="icon" alt="' + ns.lang.strings['network'] + '" title="' + ns.lang.strings['network'] + '"  src="images/network_dark.svg">)';
    }
    var stats = '';
    if (latest === 0) {
      stats =
        '<div id="pright">' +
        '<img class="icon" alt="' + ns.lang.strings['track'] + '" src="images/stats_blue.svg" style="padding-left: 3em;"><br>' +
        '<img class="icon" alt="' + ns.lang.strings['ttime'] + '" title="' + ns.lang.strings['ttime'] + '" src="images/time_blue.svg"> ' +
        pos.totalSeconds.toHMS() + '<br>' +
        '<img class="icon" alt="' + ns.lang.strings['aspeed'] + '" title="' + ns.lang.strings['aspeed'] + '" src="images/speed_blue.svg"> ' +
        ((pos.totalSeconds > 0) ? ((pos.totalMeters / pos.totalSeconds).toKmH() * factor_kmh).toFixed() : 0) + ' ' + unit_kmh + '<br>' +
        '<img class="icon" alt="' + ns.lang.strings['tdistance'] + '" title="' + ns.lang.strings['tdistance'] + '" src="images/distance_blue.svg"> ' +
        (pos.totalMeters.toKm() * factor_km).toFixed(2) + ' ' + unit_km + '<br>' + '</div>';
    }
    return '<div id="popup">' +
      '<div id="pheader">' +
      '<div><img alt="' + ns.lang.strings['user'] + '" title="' + ns.lang.strings['user'] + '" src="images/user_dark.svg"> ' + htmlEncode(pos.username) + '</div>' +
      '<div><img alt="' + ns.lang.strings['track'] + '" title="' + ns.lang.strings['track'] + '" src="images/route_dark.svg"> ' + htmlEncode(pos.trackname) + '</div>' +
      '</div>' +
      '<div id="pbody">' +
      ((pos.comment != null) ? '<div id="pcomments">' + htmlEncode(pos.comment) + '</div>' : '') +
      '<div id="pleft">' +
      '<img class="icon" alt="' + ns.lang.strings['time'] + '" title="' + ns.lang.strings['time'] + '" src="images/calendar_dark.svg"> ' + date + '<br>' +
      '<img class="icon" alt="' + ns.lang.strings['time'] + '" title="' + ns.lang.strings['time'] + '" src="images/clock_dark.svg"> ' + time + '<br>' +
      ((pos.speed != null) ? '<img class="icon" alt="' + ns.lang.strings['speed'] + '" title="' + ns.lang.strings['speed'] + '" src="images/speed_dark.svg"> ' +
        (pos.speed.toKmH() * factor_kmh) + ' ' + unit_kmh + '<br>' : '') +
      ((pos.altitude != null) ? '<img class="icon" alt="' + ns.lang.strings['altitude'] + '" title="' + ns.lang.strings['altitude'] + '" src="images/altitude_dark.svg"> ' +
        (pos.altitude * factor_m).toFixed() + ' ' + unit_m + '<br>' : '') +
      ((pos.accuracy != null) ? '<img class="icon" alt="' + ns.lang.strings['accuracy'] + '" title="' + ns.lang.strings['accuracy'] + '" src="images/accuracy_dark.svg"> ' +
        (pos.accuracy * factor_m).toFixed() + ' ' + unit_m + provider + '<br>' : '') +
      '</div>' +
      stats +
      '</div><div id="pfooter">' + sprintf(ns.lang.strings['pointof'], id + 1, count) + '</div>' +
      '</div>';
  }

  /**
   * Export to file
   * @param {string} type File type
   */
  function exportFile(type) {
    var url = 'utils/export.php?type=' + type + '&userid=' + ns.userId + '&trackid=' + ns.trackId;
    window.location.assign(url);
  }

  /**
   * Import GPX file
   */
  function importFile() {
    var form = this.parentElement;
    var sizeMax = form.elements['MAX_FILE_SIZE'].value;
    if (this.files && this.files.length === 1 && this.files[0].size > sizeMax) {
      alert(sprintf(ns.lang.strings['isizefailure'], sizeMax));
      return;
    }

    ns.post('utils/import.php',
      form,
      {
        loader: ui.importTitle,
        success: function (xml) {
          var root = xml.getElementsByTagName('root');
          var trackId = getNodeAsInt(root[0], 'trackid');
          var trackCnt = getNodeAsInt(root[0], 'trackcnt');
          getTracks(ns.userId, trackId);
          if (trackCnt > 1) {
            alert(sprintf(ns.lang.strings['imultiple'], trackCnt));
          }
        },
        fail: function (message) {
          alert(ns.lang.strings['actionfailure'] + '\n' + message);
        }
      });
  }

  /**
   * Update track summary
   * @param {number} timestamp
   * @param {number=} d Total distance (m)
   * @param {number=} s Total time (s)
   */
  function updateSummary(timestamp, d, s) {
    if (latest === 0) {
      ui.summary.innerHTML = '<div class="menutitle u">' + ns.lang.strings['summary'] + '</div>' +
        '<div><img class="icon" alt="' + ns.lang.strings['tdistance'] + '" title="' + ns.lang.strings['tdistance'] + '" src="images/distance.svg"> ' + (d.toKm() * factor_km).toFixed(2) + ' ' + unit_km + '</div>' +
        '<div><img class="icon" alt="' + ns.lang.strings['ttime'] + '" title="' + ns.lang.strings['ttime'] + '" src="images/time.svg"> ' + s.toHMS() + '</div>';
    } else {
      var today = new Date();
      var date = new Date(timestamp * 1000);
      var dateString = '';
      if (date.toDateString() !== today.toDateString()) {
        dateString += date.getFullYear() + '-' + ('0' + (date.getMonth() + 1)).slice(-2) + '-' + ('0' + date.getDate()).slice(-2);
        dateString += '<br>';
      }
      var timeString = date.toTimeString();
      var offset;
      if ((offset = timeString.indexOf(' ')) >= 0) {
        timeString = timeString.substr(0, offset) + ' <span style="font-weight:normal">' + timeString.substr(offset + 1) + '</span>';
      }
      ui.summary.innerHTML = '<div class="menutitle u">' + ns.lang.strings['latest'] + ':</div>' + dateString + timeString;
    }
  }

  /**
   * Get value of first XML child node with given name
   * @param {Document|Element} root Root element
   * @param {string} name Node name
   * @returns {string|null} Node value or null if not found
   */
  function getNode(root, name) {
    var el = root.getElementsByTagName(name);
    if (el.length) {
      var children = el[0].childNodes;
      if (children.length) {
        return children[0].nodeValue;
      }
    }
    return null;
  }

  /**
   * Get value of first XML child node with given name
   * @param {Document|Element} root Root element
   * @param {string} name Node name
   * @returns {number|null} Node value or null if not found
   */
  function getNodeAsFloat(root, name) {
    var str = getNode(root, name);
    if (str != null) {
      return parseFloat(str);
    }
    return null;
  }

  /**
   * Get value of first XML child node with given name
   * @param {Document|Element} root Root element
   * @param {string} name Node name
   * @returns {number|null} Node value or null if not found
   */
  function getNodeAsInt(root, name) {
    var str = getNode(root, name);
    if (str != null) {
      return parseInt(str);
    }
    return null;
  }

// seconds to (d) H:M:S
  Number.prototype.toHMS = function () {
    var s = this;
    var d = Math.floor(s / 86400);
    var h = Math.floor((s % 86400) / 3600);
    var m = Math.floor(((s % 86400) % 3600) / 60);
    s = ((s % 86400) % 3600) % 60;

    return ((d > 0) ? (d + ' d ') : '') + (('00' + h).slice(-2)) + ':' + (('00' + m).slice(-2)) + ':' + (('00' + s).slice(-2)) + '';
  };

// meters to km
  Number.prototype.toKm = function () {
    return Math.round(this / 10) / 100;
  };

// m/s to km/h
  Number.prototype.toKmH = function () {
    return Math.round(this * 3600 / 10) / 100;
  };

  /**
   * On latest checbox toggle
   */
  function toggleLatest() {
    if (latest === 0) {
      if (!hasAllUsers() && ui.userSelect && ui.userSelect.length > 2) {
        ui.userSelect.options.add(new Option('- ' + ns.lang.strings['allusers'] + ' -', 'all'), ui.userSelect.options[1]);
      }
      latest = 1;
      loadTrack(ns.userId, 0, 1);
    } else {
      if (hasAllUsers()) {
        ui.userSelect.selectedIndex = 0;
        ui.userSelect.remove(1);
      }
      latest = 0;
      loadTrack(ns.userId, ns.trackId, 1);
    }
  }

  /**
   * Set track
   * @param {number} trackId
   */
  function setTrack(trackId) {
    ui.trackSelect.value = trackId.toString();
  }

  /**
   * On track select
   */
  function selectTrack() {
    var el = ui.trackSelect;
    if (el.selectedIndex >= 0) {
      ns.trackId = parseInt(el.options[el.selectedIndex].value);
    } else {
      ns.trackId = 0;
    }
    ui.latest.checked = false;
    if (latest === 1) {
      toggleLatest();
    }
    loadTrack(ns.userId, ns.trackId, 1);
  }

  /**
   * On user select
   */
  function selectUser() {
    ns.userId = parseInt(ui.userSelect.options[ui.userSelect.selectedIndex].value);
    if (isSelectedAllUsers()) {
      clearOptions(ui.trackSelect);
      loadLastPositionAllUsers();
    } else {
      getTracks(ns.userId);
    }
  }

  /**
   * Get track list from database
   * @param {number} userid Default user ID
   * @param {number=} trackid Default track ID
   */
  function getTracks(userid, trackid) {
    ns.get('utils/gettracks.php',
      {
        userid: userid
      },
      {
        loader: ui.trackTitle,
        success: function (xml) {
          clearOptions(ui.trackSelect);
          var tracks = xml.getElementsByTagName('track');
          if (tracks.length > 0) {
            fillOptions(xml, userid, trackid);
          } else {
            map.clearMap();
          }
        },
        fail: function () {
          alert(ns.lang.strings['actionfailure']);
        }
      });
  }

  /**
   * Fill track select options
   * @param {Document} xml XML document
   * @param {number=} uid Default user ID
   * @param {number=} tid Default track ID
   */
  function fillOptions(xml, uid, tid) {
    var tracks = xml.getElementsByTagName('track');
    var trackLen = tracks.length;
    for (var i = 0; i < trackLen; i++) {
      var trackid = getNode(tracks[i], 'trackid');
      var trackname = getNode(tracks[i], 'trackname');
      var option = document.createElement('option');
      option.value = trackid;
      option.innerHTML = htmlEncode(trackname);
      ui.trackSelect.appendChild(option);
    }
    var defaultTrack = tid || getNodeAsInt(tracks[0], 'trackid');
    var defaultUser = uid || ns.userId;
    loadTrack(defaultUser, defaultTrack, 1);
  }

  /**
   * Clear select options
   * @param {HTMLSelectElement} el
   */
  function clearOptions(el) {
    if (el.options) {
      while (el.options.length) {
        el.remove(0);
      }
    }
  }

  /**
   * Reload track
   */
  function reload() {
    if (isSelectedAllUsers()) {
      loadLastPositionAllUsers();
    } else {
      loadTrack(ns.userId, ns.trackId, 0);
    }
  }

  /**
   * Toggle auto-reload
   */
  function autoReload() {
    if (live === 0) {
      live = 1;
      if (isSelectedAllUsers()) {
        auto = setInterval(function () {
          loadLastPositionAllUsers();
        }, uLogger.config.interval * 1000);
      } else {
        auto = setInterval(function () {
          loadTrack(ns.userId, ns.trackId, 0);
        }, uLogger.config.interval * 1000);
      }
    } else {
      live = 0;
      clearInterval(auto);
    }
  }

  /**
   * Is selected option for all users position
   * @returns {boolean}
   */
  function isSelectedAllUsers() {
    var usersSelect = document.getElementsByName('user')[0];
    return usersSelect[usersSelect.selectedIndex].value === 'all';
  }

  /**
   * Is 'all users' option present in user select
   * @returns {boolean}
   */
  function hasAllUsers() {
    return ui.userSelect && ui.userSelect.length > 2 && ui.userSelect.options[1].value === 'all';
  }

  /**
   * Set new interval from user dialog
   */
  function setTime() {
    var i = parseInt(prompt(ns.lang.strings['newinterval']));
    if (!isNaN(i) && i !== ns.config.interval) {
      ns.config.interval = i;
      ui.auto.innerHTML = ns.config.interval.toString();
      // if live tracking on, reload with new interval
      if (live === 1) {
        live = 0;
        clearInterval(auto);
        autoReload();
      }
      // save current state as default
      setCookie('interval', ns.config.interval, 30);
    }
  }

  /**
   * Dynamic change of map api
   * @param {string=} api API name
   */
  function loadMapAPI(api) {
    if (api) {
      ns.config.mapapi = api;
      try {
        savedBounds = map.getBounds();
      } catch (e) {
        savedBounds = null;
      }
      map.cleanup();
    }
    if (ns.config.mapapi === 'gmaps') {
      map = mapAPI.gmaps;
    } else {
      map = mapAPI.ol;
    }
    waitAndInit();
  }

  /**
   * Try to initialize map engine
   */
  function waitAndInit() {
    // wait till main api loads
    if (loadTime > 10000) {
      loadTime = 0;
      alert(sprintf(ns.lang.strings['apifailure'], ns.config.mapapi));
      return;
    }
    try {
      map.init();
    } catch (e) {
      setTimeout(function () {
        loadTime += 50;
        waitAndInit();
      }, 50);
      return;
    }
    loadTime = 0;
    var update = 1;
    if (savedBounds) {
      map.zoomToBounds(savedBounds);
      update = 0;
    }
    if (latest && isSelectedAllUsers()) {
      loadLastPositionAllUsers();
    } else {
      loadTrack(ns.userId, ns.trackId, update);
    }
    // save current api as default
    setCookie('api', ns.config.mapapi, 30);
  }

  /**
   * Add script tag
   * @param {string} url attribute
   * @param {string} id attribute
   */
  function addScript(url, id) {
    if (id && document.getElementById(id)) {
      return;
    }
    var tag = document.createElement('script');
    tag.type = 'text/javascript';
    tag.src = url;
    if (id) {
      tag.id = id;
    }
    ui.head.appendChild(tag);
  }

  /**
   * Add link tag with type css
   * @param {string} url attribute
   * @param {string} id attribute
   */
  function addCss(url, id) {
    if (id && document.getElementById(id)) {
      return;
    }
    var tag = document.createElement('link');
    tag.type = 'text/css';
    tag.rel = 'stylesheet';
    tag.href = url;
    if (id) {
      tag.id = id;
    }
    ui.head.appendChild(tag);
  }

  /**
   * Remove HTML element
   * @param {string} id Element ID
   */
  function removeElementById(id) {
    var tag = document.getElementById(id);
    if (tag && tag.parentNode) {
      tag.parentNode.removeChild(tag);
    }
  }

  /**
   * Clear map canvas
   */
  function clearMapCanvas() {
    ui.map.innerHTML = '';
  }

  /**
   * Set cookie
   * @param {string} name
   * @param {(string|number)} value
   * @param {number=} days
   */
  function setCookie(name, value, days) {
    var expires = '';
    if (days) {
      var date = new Date();
      date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
      expires = '; expires=' + date.toUTCString();
    }
    document.cookie = 'ulogger_' + name + '=' + value + expires + '; path=/';
  }

  /**
   * Set language
   * @param {string} lang Language code
   */
  function setLang(lang) {
    setCookie('lang', lang, 30);
    location.reload();
  }

  /**
   * Set units
   * @param {string} unit New units
   */
  function setUnits(unit) {
    ns.config.units = unit;
    setCookie('units', unit, 30);
    location.reload();
  }

  /**
   * Show modal dialog
   * @param {string} contentHTML
   */
  ui.showModal = function (contentHTML) {
    var div = document.createElement('div');
    div.setAttribute('id', 'modal');
    div.innerHTML = '<div id="modal-header"><button type="button" onclick="uLogger.ui.removeModal()"><img alt="' + ns.lang.strings['close'] + '" src="images/close.svg"></button></div><div id="modal-body"></div>';
    document.body.appendChild(div);
    var modalBody = document.getElementById('modal-body');
    modalBody.innerHTML = contentHTML;
  };

  /**
   * Remove modal dialog
   */
  ui.removeModal = function () {
    document.body.removeChild(document.getElementById('modal'));
  };

  /**
   * Toggle user menu visibility
   */
  function userMenu() {
    if (ui.userDropdown.classList.contains('show')) {
      ui.userDropdown.classList.remove('show');
    } else {
      ui.userDropdown.classList.add('show');
      window.addEventListener('click', removeOnClick, true);
    }
  }

  /**
   * Click listener callback to hide user menu
   * @param {MouseEvent} e
   */
  function removeOnClick(e) {
    // noinspection JSUnresolvedVariable
    var parent = e.target.parentElement;
    ui.userDropdown.classList.remove('show');
    window.removeEventListener('click', removeOnClick, true);
    if (!parent.classList.contains('dropdown')) {
      e.stopPropagation();
    }
  }

  /**
   * sprintf, naive approach, only %s, %d supported
   * @param {string} fmt String
   * @param {...(string|number)=} params Optional parameters
   * @returns {string}
   */
  function sprintf(fmt, params) { // eslint-disable-line no-unused-vars
    var args = Array.prototype.slice.call(arguments);
    var format = args.shift();
    var i = 0;
    return format.replace(/%%|%s|%d/g, function (match) {
      if (match === '%%') {
        return '%';
      }
      return (typeof args[i] != 'undefined') ? args[i++] : match;
    });
  }

  /**
   * Encode string for HTML
   * @param {string} s
   * @returns {string}
   */
  function htmlEncode(s) {
    return s.replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  /**
   * Convert hex string and opacity to an rgba string
   * @param {string} hex
   * @param {number} opacity
   * @returns {string}
   */
  function hexToRGBA(hex, opacity) {
    return 'rgba(' + (hex = hex.replace('#', ''))
      .match(new RegExp('(.{' + hex.length / 3 + '})', 'g'))
      .map(function (l) {
        return parseInt(hex.length % 2 ? l + l : l, 16)
      })
      .concat(opacity || 1).join(',') + ')';
  }

  /**
   * Ajax request failure callback
   * @callback failCallback
   * @param {string=} message Error message
   */

  /**
   * Ajax request success callback
   * @callback successCallback
   * @param {Document=} xml XML response
   */

  /**
   * Perform POST HTTP request
   * @alias ajax
   */
  function post(url, data, options) {
    var params = options || {};
    params.method = 'POST';
    return ajax(url, data, params);
  }

  /**
   * Perform GET HTTP request
   * @alias ajax
   */
  function get(url, data, options) {
    var params = options || {};
    params.method = 'GET';
    return ajax(url, data, params);
  }

  /**
   * Perform ajax HTTP request
   * @param {string} url Request URL
   * @param {Object|HTMLFormElement} [data] Optional request parameters: key/value pairs or form element
   * @param {Object} [options] Optional options
   * @param {successCallback} [options.success] Optional on success callback
   * @param {failCallback} [options.fail] Optional on fail callback
   * @param {string} [options.method='GET'] Optional query method, default 'GET'
   * @param {HTMLElement} [options.loader] Optional element to animate during loading
   */
  function ajax(url, data, options) {
    data = data || {};
    options = options || {};
    var method = options.method || 'GET';
    var loader = options.loader;
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4) {
        var message = '';
        var error = true;
        if (xhr.status === 200) {
          var xml = xhr.responseXML;
          if (xml) {
            var root = xml.getElementsByTagName('root');
            if (root.length && ns.getNode(root[0], 'error') !== '1') {
              if (options.success && typeof options.success === 'function') {
                xml = xml || {};
                options.success(xml);
              }
              error = false;
            } else if (root.length) {
              var errorMsg = ns.getNode(root[0], 'message');
              if (errorMsg) {
                message = errorMsg;
              }
            }
          }
        }
        if (error) {
          if (options.fail && typeof options.fail === 'function') {
            options.fail(message);
          }
        }
      }
      if (loader) {
        removeLoader(loader);
      }
    };
    var body = null;
    if (data instanceof HTMLFormElement) {
      body = new FormData(data);
      method = 'POST';
    } else {
      var params = [];
      for (var key in data) {
        if (data.hasOwnProperty(key)) {
          params.push(key + '=' + encodeURIComponent(data[key]));
        }
      }
      body = params.join('&');
      body = body.replace(/%20/g, '+');
    }
    if (method === 'GET' && params.length) {
      url += "?" + body;
      body = null;
    }
    xhr.open(method, url, true);
    if (method === 'POST' && !(data instanceof HTMLFormElement)) {
      xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    }
    xhr.send(body);
    if (loader) {
      setLoader(loader);
    }

    function setLoader(el) {
      var s = el.textContent || el.innerText;
      var newHTML = '';
      for (var i = 0, len = s.length; i < len; i++) {
        newHTML += '<span class="loader">' + s.charAt(i) + '</span>';
      }
      el.innerHTML = newHTML;
    }

    function removeLoader(el) {
      el.innerHTML = el.textContent || el.innerText;
    }
  }

  ns.ui = ui;
  ns.mapAPI = mapAPI;
  ns.initUI = initUI;
  ns.loadMapAPI = loadMapAPI;
  ns.userMenu = userMenu;
  ns.selectUser = selectUser;
  ns.selectTrack = selectTrack;
  ns.toggleChart = toggleChart;
  ns.toggleLatest = toggleLatest;
  ns.toggleMenu = toggleMenu;
  ns.autoReload = autoReload;
  ns.setTime = setTime;
  ns.reload = reload;
  ns.exportFile = exportFile;
  ns.importFile = importFile;
  ns.htmlEncode = htmlEncode;
  ns.getNode = getNode;
  ns.addCss = addCss;
  ns.hexToRGBA = hexToRGBA;
  ns.getPopupHtml = getPopupHtml;
  ns.removeElementById = removeElementById;
  ns.clearMapCanvas = clearMapCanvas;
  ns.parsePosition = parsePosition;
  ns.updateSummary = updateSummary;
  ns.setTrack = setTrack;
  ns.updateChart = updateChart;
  ns.isChartVisible = isChartVisible;
  ns.chartShowPosition = chartShowPosition;
  ns.addScript = addScript;
  ns.sprintf = sprintf;
  ns.post = post;
  ns.get = get;
  Object.defineProperty(ns, 'map', {get: function() { return map; }});
  ns.isLatest = function () {
    return latest === 1;
  };

})(uLogger);
