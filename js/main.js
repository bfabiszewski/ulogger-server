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

// general stuff
var factor_kmh, unit_kmh, factor_m, unit_m, factor_km, unit_km;
if (units == 'imperial') {
  factor_kmh = 0.62; //to mph
  unit_kmh = 'mph';
  factor_m = 3.28; // to feet
  unit_m = 'ft';
  factor_km = 0.62; // to miles
  unit_km = 'mi';
} else {
  factor_kmh = 1;
  unit_kmh = 'km/h';
  factor_m = 1;
  unit_m = 'm';
  factor_km = 1;
  unit_km = 'km';
}
var latest = 0;
var live = 0;
var chart;
var altitudes = {};
var altTimeout;
var gm_error = false;
var loadTime = 0;
var auto;
var savedBounds = null;

function displayChart() {
  if (chart) { google.visualization.events.removeAllListeners(chart); }
  var data = new google.visualization.DataTable();
  data.addColumn('number', 'id');
  data.addColumn('number', lang['altitude']);

  for (var id in altitudes) {
    if (altitudes.hasOwnProperty(id)) {
      data.addRow([parseInt(id) + 1, Math.round((altitudes[id] * factor_m))]);
    }
  }

  var options = {
    title: lang['altitude'] + ' (' + unit_m + ')',
    hAxis: { textPosition: 'none' },
    legend: { position: 'none' }
  };

  chart = new google.visualization.LineChart(document.getElementById('chart'));
  chart.draw(data, options);

  addChartEvent(chart, data);
}

function toggleChart(i) {
  var altLen = altitudes.length;
  if (altLen <= 1) { return; }
  var e = document.getElementById('bottom');
  if (arguments.length < 1) {
    if (e.style.display == 'block') { i = 0 }
    else { i = 1; }
  }
  if (i == 0) {
    chart.clearChart();
    e.style.display = 'none';
  } else {
    e.style.display = 'block';
    displayChart();
  }
}

function toggleChartLink() {
  var link = document.getElementById('altitudes');
  if (Object.keys(altitudes).length > 1) {
    link.style.visibility = 'visible';
  } else {
    link.style.visibility = 'hidden';
  }
}

function toggleMenu(i) {
  var emenu = document.getElementById('menu');
  var emain = document.getElementById('main');
  var ebutton = document.getElementById('menu-close');
  if (arguments.length < 1) {
    if (ebutton.innerHTML == '»') { i = 0 }
    else { i = 1; }
  }
  if (i == 0) {
    emenu.style.width = '0';
    emain.style.marginRight = '0';
    ebutton.style.right = '0';
    ebutton.innerHTML = '«';
  }
  else {
    emenu.style.width = '165px';
    emain.style.marginRight = '165px';
    ebutton.style.right = '165px';
    ebutton.innerHTML = '»';
  }
}

function getXHR() {
  var xmlhttp = null;
  if (window.XMLHttpRequest) {
    xmlhttp = new XMLHttpRequest();
  }
  else {
    xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
  }
  return xmlhttp;
}

function loadTrack(userid, trackid, update) {
  var title = document.getElementById("track").getElementsByClassName("menutitle")[0];
  if (trackid < 0) { return; }
  if (latest == 1) { trackid = 0; }
  var xhr = getXHR();
  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4) {
      if (xhr.status == 200) {
        var xml = xhr.responseXML;
        var positions = xml.getElementsByTagName('position');
        if (positions.length > 0) {
          clearMap();
          displayTrack(xml, update);
          toggleChartLink();
        }
      }
      xhr = null;
      removeLoader(title);
    }
  }
  xhr.open('GET', 'utils/getpositions.php?trackid=' + trackid + '&userid=' + userid, true);
  xhr.send();
  setLoader(title);
}

function parsePosition(p, id) {
  // read data
  var latitude = parseFloat(getNode(p, 'latitude'));
  var longitude = parseFloat(getNode(p, 'longitude'));
  var altitude = getNode(p, 'altitude'); // may be null
  if (altitude != null) {
    altitude = parseInt(altitude);
    // save altitudes for chart
    altitudes[id] = altitude;
  }
  var speed = getNode(p, 'speed'); // may be null
  if (speed != null) { speed = parseInt(speed); }
  var bearing = getNode(p, 'bearing'); // may be null
  if (bearing != null) { bearing = parseInt(bearing); }
  var accuracy = getNode(p, 'accuracy'); // may be null
  if (accuracy != null) { accuracy = parseInt(accuracy); }
  var provider = getNode(p, 'provider'); // may be null
  var comments = getNode(p, 'comments'); // may be null
  var username = getNode(p, 'username');
  var trackname = getNode(p, 'trackname');
  var tid = getNode(p, 'trackid');
  var timestamp = getNode(p, 'timestamp');
  var distance = parseInt(getNode(p, 'distance'));
  var seconds = parseInt(getNode(p, 'seconds'));
  return {
    'latitude': latitude,
    'longitude': longitude,
    'altitude': altitude,
    'speed': speed,
    'bearing': bearing,
    'accuracy': accuracy,
    'provider': provider,
    'comments': comments,
    'username': username,
    'trackname': trackname,
    'tid': tid,
    'timestamp': timestamp,
    'distance': distance,
    'seconds': seconds
  };
}

function getPopupHtml(p, i, count) {
  var date = '–––';
  var time = '–––';
  if (p.timestamp > 0) {
    var d = new Date(p.timestamp * 1000);
    date = d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2);
    time = d.toTimeString();
    var offset;
    if ((offset = time.indexOf(' ')) >= 0) {
      time = time.substr(0, offset) + ' <span class="smaller">' + time.substr(offset + 1) + '</span>';
    }
  }
  var provider = '';
  if (p.provider == 'gps') {
    provider = ' (<img class="icon" alt="' + lang['gps'] + '" title="' + lang['gps'] + '"  src="images/gps_dark.svg">)';
  } else if (p.provider == 'network') {
    provider = ' (<img class="icon" alt="' + lang['network'] + '" title="' + lang['network'] + '"  src="images/network_dark.svg">)';
  }
  var stats = '';
  if (latest == 0) {
    stats =
      '<div id="pright">' +
      '<img class="icon" alt="' + lang['track'] + '" src="images/stats_blue.svg" style="padding-left: 3em;"><br>' +
      '<img class="icon" alt="' + lang['ttime'] + '" title="' + lang['ttime'] + '" src="images/time_blue.svg"> ' +
      p.totalSeconds.toHMS() + '<br>' +
      '<img class="icon" alt="' + lang['aspeed'] + '" title="' + lang['aspeed'] + '" src="images/speed_blue.svg"> ' +
      ((p.totalSeconds > 0) ? ((p.totalMeters / p.totalSeconds).toKmH() * factor_kmh).toFixed() : 0) + ' ' + unit_kmh + '<br>' +
      '<img class="icon" alt="' + lang['tdistance'] + '" title="' + lang['tdistance'] + '" src="images/distance_blue.svg"> ' +
      (p.totalMeters.toKm() * factor_km).toFixed(2) + ' ' + unit_km + '<br>' + '</div>';
  }
  var popup =
    '<div id="popup">' +
    '<div id="pheader">' +
    '<div><img alt="' + lang['user'] + '" title="' + lang['user'] + '" src="images/user_dark.svg"> ' + htmlEncode(p.username) + '</div>' +
    '<div><img alt="' + lang['track'] + '" title="' + lang['track'] + '" src="images/route_dark.svg"> ' + htmlEncode(p.trackname) + '</div>' +
    '</div>' +
    '<div id="pbody">' +
    ((p.comments != null) ? '<div id="pcomments">' + htmlEncode(p.comments) + '</div>' : '') +
    '<div id="pleft">' +
    '<img class="icon" alt="' + lang['time'] + '" title="' + lang['time'] + '" src="images/calendar_dark.svg"> ' + date + '<br>' +
    '<img class="icon" alt="' + lang['time'] + '" title="' + lang['time'] + '" src="images/clock_dark.svg"> ' + time + '<br>' +
    ((p.speed != null) ? '<img class="icon" alt="' + lang['speed'] + '" title="' + lang['speed'] + '" src="images/speed_dark.svg"> ' +
    (p.speed.toKmH() * factor_kmh) + ' ' + unit_kmh + '<br>' : '') +
    ((p.altitude != null) ? '<img class="icon" alt="' + lang['altitude'] + '" title="' + lang['altitude'] + '" src="images/altitude_dark.svg"> ' +
    (p.altitude * factor_m).toFixed() + ' ' + unit_m + '<br>' : '') +
    ((p.accuracy != null) ? '<img class="icon" alt="' + lang['accuracy'] + '" title="' + lang['accuracy'] + '" src="images/accuracy_dark.svg"> ' +
    (p.accuracy * factor_m).toFixed() + ' ' + unit_m + provider + '<br>' : '') +
    '</div>' +
    stats +
    '</div><div id="pfooter">' + sprintf(lang['pointof'], i + 1, count) + '</div>' +
    '</div>';
  return popup;
}

function exportFile(type, userid, trackid) {
  var url = 'utils/export.php?type=' + type + '&userid=' + userid + '&trackid=' + trackid;
  window.location.assign(url);
}

function importFile(input) {
  var form = input.parentElement;
  var title = form.parentElement.getElementsByClassName("menutitle")[0];
  var sizeMax = form.elements['MAX_FILE_SIZE'].value;
  if (input.files && input.files.length == 1 && input.files[0].size > sizeMax) {
    alert(sprintf(lang['isizefailure'], sizeMax));
    return;
  }
  var xhr = getXHR();
  xhr.onreadystatechange = function() {
    if (xhr.readyState == 4) {
      var error = true;
      var message = "";
      if (xhr.status == 200) {
        var xml = xhr.responseXML;
        if (xml) {
          var root = xml.getElementsByTagName('root');
          if (root.length && getNode(root[0], 'error') == 0) {
            trackId = getNode(root[0], 'trackid');
            trackCnt = getNode(root[0], 'trackcnt');
            getTracks(userid, trackId);
            if (trackCnt > 1) {
              alert(sprintf(lang['imultiple'], trackCnt));
            }
            error = false;
          } else if (root.length) {
            errorMsg = getNode(root[0], 'message');
            if (errorMsg) { message = errorMsg; }
          }
        }
      }
      if (error) {
        alert(lang['actionfailure'] + '\n' + message);
      }
      removeLoader(title);
      xhr = null;
    }
  }
  xhr.open("POST", "utils/import.php", true);
  xhr.send(new FormData(form));
  input.value = "";
  setLoader(title);
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

function updateSummary(timestamp, d, s) {
  var t = document.getElementById('summary');
  if (latest == 0) {
    t.innerHTML = '<div class="menutitle u">' + lang['summary'] + '</div>' +
      '<div><img class="icon" alt="' + lang['tdistance'] + '" title="' + lang['tdistance'] + '" src="images/distance.svg"> ' + (d.toKm() * factor_km).toFixed(2) + ' ' + unit_km + '</div>' +
      '<div><img class="icon" alt="' + lang['ttime'] + '" title="' + lang['ttime'] + '" src="images/time.svg"> ' + s.toHMS() + '</div>';
  } else {
    var today = new Date();
    var d = new Date(timestamp * 1000);
    var dateString = '';
    if (d.toDateString() != today.toDateString()) {
      dateString += d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2);
      dateString += '<br>';
    }
    var timeString = d.toTimeString();
    var offset;
    if ((offset = timeString.indexOf(' ')) >= 0) {
      timeString = timeString.substr(0, offset) + ' <span style="font-weight:normal">' + timeString.substr(offset + 1) + '</span>';
    }
    t.innerHTML = '<div class="menutitle u">' + lang['latest'] + ':</div>' + dateString + timeString;
  }
}

function getNode(p, name) {
  return ((p.getElementsByTagName(name)[0].childNodes[0]) ? p.getElementsByTagName(name)[0].childNodes[0].nodeValue : null);
}

// seconds to (d) H:M:S
Number.prototype.toHMS = function() {
  var s = this;
  var d = Math.floor(s / 86400);
  var h = Math.floor((s % 86400) / 3600);
  var m = Math.floor(((s % 86400) % 3600) / 60);
  s = ((s % 86400) % 3600) % 60;

  return ((d > 0) ? (d + ' d ') : '') + (('00' + h).slice(-2)) + ':' + (('00' + m).slice(-2)) + ':' + (('00' + s).slice(-2)) + '';
};

// meters to km
Number.prototype.toKm = function() {
  return Math.round(this / 10) / 100;
};

// m/s to km/h
Number.prototype.toKmH = function() {
  return Math.round(this * 3600 / 10) / 100;
};

// negate value
function toggleLatest() {
  if (latest == 0) {
    latest = 1;
    loadTrack(userid, 0, 1);
  }
  else {
    latest = 0;
    loadTrack(userid, trackid, 1);
  }
}

function setTrack(t) {
  document.getElementsByName('track')[0].value = t;
}

function selectTrack(f) {
  if (f.selectedIndex >= 0) {
    trackid = f.options[f.selectedIndex].value;
  } else {
    trackid = 0;
  }
  document.getElementById('latest').checked = false;
  if (latest == 1) { toggleLatest(); }
  loadTrack(userid, trackid, 1);
}

function selectUser(f) {
  userid = f.options[f.selectedIndex].value;
  if (f.options[0].disabled == false) {
    f.options[0].disabled = true;
  }
  document.getElementById('latest').checked = false;
  if (latest == 1) { toggleLatest(); }
  getTracks(userid);
}

function getTracks(userid, trackid) {
  var title = document.getElementById("track").getElementsByClassName("menutitle")[0];
  var xhr = getXHR();
  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4) {
      if (xhr.status == 200) {
        var xml = xhr.responseXML;
        var trackSelect = document.getElementsByName('track')[0];
        clearOptions(trackSelect);
        var tracks = xml.getElementsByTagName('track');
        if (tracks.length > 0) {
          fillOptions(xml, userid, trackid);
        } else {
          clearMap();
        }
      }
      removeLoader(title);
      xhr = null;
    }
  }
  xhr.open('GET', 'utils/gettracks.php?userid=' + userid, true);
  xhr.send();
  setLoader(title);
}

function fillOptions(xml, uid, tid) {
  var trackSelect = document.getElementsByName('track')[0];
  var tracks = xml.getElementsByTagName('track');
  var trackLen = tracks.length;
  for (var i = 0; i < trackLen; i++) {
    var trackid = getNode(tracks[i], 'trackid');
    var trackname = getNode(tracks[i], 'trackname');
    var option = document.createElement("option");
    option.value = trackid;
    option.innerHTML = htmlEncode(trackname);
    trackSelect.appendChild(option);
  }
  var defaultTrack = tid || getNode(tracks[0], 'trackid');
  var defaultUser = uid || userid;
  loadTrack(defaultUser, defaultTrack, 1);
}

function clearOptions(el) {
  if (el.options) {
    while (el.options.length) {
      el.remove(0);
    }
  }
}

function autoReload() {
  if (live == 0) {
    live = 1;
    auto = setInterval(function () { loadTrack(userid, trackid, 0); }, interval * 1000);
  }
  else {
    live = 0;
    clearInterval(auto);
  }
}

function setTime() {
  var i = parseInt(prompt(lang['newinterval']));
  if (!isNaN(i) && i != interval) {
    interval = i;
    document.getElementById('auto').innerHTML = interval;
    // if live tracking on, reload with new interval
    if (live == 1) {
      live = 0;
      clearInterval(auto);
      autoReload();
    }
    // save current state as default
    setCookie('interval', interval, 30);
  }
}

// dynamic change of map api
function loadMapAPI(api) {
  if (api) {
    mapapi = api;
    try {
      savedBounds = getBounds();
    } catch (e) {
      savedBounds = null;
    }
    cleanup();
  }
  removeElementById('mapapi');
  var urls = [];
  if (mapapi == 'gmaps') {
    addScript('js/api_gmaps.js', 'mapapi');
    urls.push('//maps.googleapis.com/maps/api/js?' + ((gkey !== null) ? ('key=' + gkey + '&') : '') + 'callback=init');
  } else if (mapapi == 'openlayers') {
    addScript('js/api_openlayers.js', 'mapapi');
    urls.push('//openlayers.org/api/OpenLayers.js');
  } else {
    addScript('js/api_openlayers3.js', 'mapapi');
    urls.push('//cdn.polyfill.io/v2/polyfill.min.js?features=requestAnimationFrame,Element.prototype.classList')
    urls.push('//openlayers.org/en/v4.3.2/build/ol.js');
  }
  waitAndLoad(mapapi, urls);
}

function waitAndLoad(api, urls) {
  // wait till first script loaded
  if (loadTime > 5000) { loadTime = 0; alert(sprintf(lang['apifailure'], api)); return; }
  if (typeof loadedAPI === 'undefined' || loadedAPI !== api) {
    setTimeout(function () { loadTime += 50; waitAndLoad(api, urls); }, 50);
    return;
  }
  for (var i = 0; i < urls.length; i++) {
    addScript(urls[i], 'mapapi_' + api + '_' + i);
  }
  loadTime = 0;
  waitAndInit(api);
}

function waitAndInit(api) {
  // wait till main api loads
  if (loadTime > 10000) { loadTime = 0; alert(sprintf(lang['apifailure'], api)); return; }
  try {
    init();
  } catch (e) {
    setTimeout(function () { loadTime += 50; waitAndInit(api); }, 50);
    return;
  }
  loadTime = 0;
  var update = 1;
  if (savedBounds) {
    zoomToBounds(savedBounds);
    update = 0;
  }
  loadTrack(userid, trackid, update);
  // save current api as default
  setCookie('api', api, 30);
}

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
  document.getElementsByTagName('head')[0].appendChild(tag);
}

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
  document.getElementsByTagName('head')[0].appendChild(tag);
}

function removeElementById(id) {
  var tag = document.getElementById(id);
  if (tag && tag.parentNode) {
    tag.parentNode.removeChild(tag);
  }
}

function setCookie(name, value, days) {
  if (days) {
    var date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    var expires = '; expires=' + date.toGMTString();
  } else {
    var expires = '';
  }
  document.cookie = 'ulogger_' + name + '=' + value + expires + '; path=/';
}

function setLang(lang) {
  setCookie('lang', lang, 30);
  location.reload();
}

function setUnits(unit) {
  units = unit;
  setCookie('units', unit, 30);
  location.reload();
}

function showModal(contentHTML) {
  var div = document.createElement('div');
  div.setAttribute('id', 'modal');
  div.innerHTML = '<div id="modal-header"><button type="button" onclick="removeModal()"><img alt="' + lang['close'] + '" src="images/close.svg"></button></div><div id="modal-body"></div>';
  document.body.appendChild(div);
  var modalBody = document.getElementById('modal-body');
  modalBody.innerHTML = contentHTML;
}

function removeModal() {
  document.body.removeChild(document.getElementById('modal'));
}

function userMenu() {
  var dropdown = document.getElementById('user_dropdown');
  if (dropdown.classList.contains('show')) {
    dropdown.classList.remove('show');
  } else {
    dropdown.classList.add('show');
    window.addEventListener('click', removeOnClick, true);
  }
}

function removeOnClick(event) {
  var parent = event.target.parentElement;
  var dropdown = document.getElementById('user_dropdown');
  dropdown.classList.remove('show');
  window.removeEventListener('click', removeOnClick, true);
  if (!parent.classList.contains('dropdown')) {
    event.stopPropagation();
  }
}

// naive approach, only %s, %d supported
function sprintf() {
  var args = Array.prototype.slice.call(arguments);
  var format = args.shift();
  var i = 0;
  return format.replace(/%%|%s|%d/g, function(match) {
    if (match == '%%') { return '%'; }
    return (typeof args[i] != 'undefined') ? args[i++] : match;
  });
};

function htmlEncode(s) {
  return s.replace(/&/g, '&amp;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#39;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;');
}

if (!String.prototype.trim) {
  String.prototype.trim = function () {
    return this.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
  };
}