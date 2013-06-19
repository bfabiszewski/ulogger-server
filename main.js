/* phpTrackme
 *
 * Copyright(C) 2013 Bartek Fabiszewski (www.fabiszewski.net)
 *
 * This is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Library General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */
// google maps
function displayTrack(xml,update) {
  altitudes.length = 0;
  var totalMeters = 0;
  var totalSeconds = 0;
  // init polyline
  var poly = new google.maps.Polyline(polyOptions);
  poly.setMap(map);  
  var path = poly.getPath();  
  var latlngbounds = new google.maps.LatLngBounds( );  
  var positions = xml.getElementsByTagName('position');
  var posLen = positions.length;
  for (var i=0; i<posLen; i++) {
    var p = parsePosition(positions[i]);
    totalMeters += p.distance;
    totalSeconds += p.seconds;
    p['totalMeters'] = totalMeters;
    p['totalSeconds'] = totalSeconds;
    p['coordinates'] = new google.maps.LatLng(p.latitude,p.longitude);
    // set marker
    setMarker(p,i,posLen);
    // update polyline
    path.push(p.coordinates);    
    latlngbounds.extend(p.coordinates);
    // save altitudes for chart
    altitudes[i] = p.altitude;
  }  
  if (update) {
    map.fitBounds(latlngbounds);
    if (i==1) {
      // only one point, zoom out
      zListener = 
          google.maps.event.addListenerOnce(map, 'bounds_changed', function(event) {
              if (this.getZoom()){
                  this.setZoom(15);
              }
      });
      setTimeout(function(){google.maps.event.removeListener(zListener)}, 2000);  
    }
  }
  latestTime = p.dateoccured;
  polies.push(poly);
  
  updateSummary(p.dateoccured,totalMeters,totalSeconds);
  if (p.tid!=trackid) {
    trackid=p.tid;
    setTrack(trackid);
  }
  if (document.getElementById('bottom').style.display=='block') {
    // update altitudes chart
    chart.clearChart();
    displayChart();
  }
}

function clearMap(){
  if (polies){
    for (var i=0; i<polies.length; i++){
      polies[i].setMap(null);
    }
  }
  if (markers){
    for (var i=0; i<markers.length; i++){
      google.maps.event.removeListener(popups[i].listener);
      popups[i].setMap(null);
      markers[i].setMap(null);
    }
  }
  markers.length = 0;
  polies.length = 0;
  popups.lentgth = 0;
}

var popup;
function setMarker(p,i,posLen) {
  // marker
  var marker = new google.maps.Marker( {
    map: map,
    position: p.coordinates,
    title: p.dateoccured
  });
  if (latest==1) { marker.setIcon('http://maps.google.com/mapfiles/dd-end.png') }
  else if (i==0) { marker.setIcon('http://maps.google.com/mapfiles/marker_greenA.png') }
  else if (i==posLen-1) { marker.setIcon('http://maps.google.com/mapfiles/markerB.png') }
  else { marker.setIcon('http://labs.google.com/ridefinder/images/mm_20_gray.png') }
  // popup
  var content = '<div id="popup">'+
    '<div id="pheader">'+lang_user+': '+p.username.toUpperCase()+'<br />'+lang_track+': '+p.trackname.toUpperCase()+
    '</div>'+
    '<div id="pbody">'+
    '<div id="pleft"><b>'+lang_time+':</b> '+p.dateoccured+'<br />'+
    ((p.speed != null)?'<b>'+lang_speed+':</b> '+(p.speed.toKmH()*factor_kmh)+' '+unit_kmh+'<br />':'')+
    ((p.altitude != null)?'<b>'+lang_altitude+':</b> '+(p.altitude*factor_m).toFixed()+' '+unit_m+'<br />':'')+'</div>'+
    ((latest==0)?
    ('<div id="pright"><b>'+lang_ttime+':</b> '+p.totalSeconds.toHMS()+'<br />'+
    '<b>'+lang_aspeed+':</b> '+((p.totalSeconds>0)?((p.totalMeters/p.totalSeconds).toKmH()*factor_kmh).toFixed():0)+' '+unit_kmh+'<br />'+
    '<b>'+lang_tdistance+':</b> '+(p.totalMeters.toKm()*factor_km).toFixed(2)+' '+unit_km+'<br />'+'</div>'):'')+
    '<div id="pfooter">'+lang_point+' '+(i+1)+' '+lang_of+' '+(posLen)+'</div>'+
    '</div></div>';    
  popup = new google.maps.InfoWindow();
  popup.listener = google.maps.event.addListener(marker, 'click', (function(marker,content) {
    return function() {
      popup.setContent(content);
      popup.open(map, marker);
      if (document.getElementById('bottom').style.display=='block') {
        chart.setSelection([{row:i,column:null}]);
      }
    }
  })(marker,content));    
  markers.push(marker);    
  popups.push(popup);  
}



// openstreetmaps
// TODO


// general stuff
if (units=='imperial') {
  factor_kmh = 0.62; //to mph
  unit_kmh = 'mph';
  factor_m = 3.28; // to feet
  unit_m = 'ft';
  factor_km = 0.62; // to miles
  unit_km = 'mi';
} 
else {
  factor_kmh = 1;
  unit_kmh = 'km/h';
  factor_m = 1;
  unit_m = 'm';
  factor_km = 1;
  unit_km = 'km';
}
var latest = 0;
var latestTime = 0;
var live = 0;
var chart;
var altitudes = new Array();
var altTimeout;
function displayChart() {
  if (chart) { google.visualization.events.removeAllListeners(chart); }
  var data = new google.visualization.DataTable();
  data.addColumn('number', 'id');
  data.addColumn('number', 'altitude');
  var altLen = altitudes.length;
  for (var i=0; i<altLen; i++) {
    data.addRow([(i+1),Math.round((altitudes[i]*factor_m))]);
  }

  var options = {
    title: lang_altitude+' ('+unit_m+')',
    hAxis: { textPosition: 'none' },
    legend: { position: 'none' }
  };

  chart = new google.visualization.LineChart(document.getElementById('chart'));
  chart.draw(data, options);
  
  google.visualization.events.addListener(chart, 'select', function() {
      if (popup) {popup.close(); clearTimeout(altTimeout);} 
      var selection = chart.getSelection()[0];
      if (selection) {
        var id = selection.row;
        var contentString = '<div style="width:40px; height:20px;padding:10px">'+Math.round(altitudes[id]*factor_m)+' '+unit_m+'</div>';
        popup = new google.maps.InfoWindow({
            content: contentString
        });
        popup.open(map,markers[id]);
        altTimeout = setTimeout(function() { if (popup) {popup.close();} },2000);
      }
  });  
}

function toggleChart(i) {
  var altLen = altitudes.length;
  if (altLen<=1) { return; }  
  var e = document.getElementById('bottom');
  if (arguments.length < 1) {
    if (e.style.display == 'block') { i = 0 }
    else { i = 1; }
  }  
  if (i==0) {
    chart.clearChart();
    e.style.display = 'none';
  }
  else {
    e.style.display = 'block';
    displayChart();
  }
}

function getXHR() {
  var xmlhttp = null;
  if (window.XMLHttpRequest) {
    xmlhttp=new XMLHttpRequest();
  }
  else {
    xmlhttp=new ActiveXObject('Microsoft.XMLHTTP');
  }
  return xmlhttp;
}

function loadTrack(userid,trackid,update) {
  if (latest==1) { trackid=0; }
  var xhr = getXHR();
  xhr.onreadystatechange = function() {
    if (xhr.readyState==4 && xhr.status==200) {
      var xml = xhr.responseXML;
      var positions = xml.getElementsByTagName('position');
      if (positions.length>0) {  
        clearMap();        
        displayTrack(xml,update);
      }
      xhr = null;
    }
  }
  xhr.open('GET','getpositions.php?trackid='+trackid+'&userid='+userid,true);
  xhr.send();
}

function parsePosition(p) {
    // read data
  var latitude = getNode(p,'latitude');
  var longitude = getNode(p,'longitude');
  var altitude = getNode(p,'altitude'); // may be null
  if (altitude != null) { altitude = parseInt(altitude); }
  var speed = getNode(p,'speed'); // may be null
  if (speed != null) { speed = parseInt(speed); }
  var angle = getNode(p,'angle'); // may be null
  if (angle != null) { angle = parseInt(angle); }
  var username = getNode(p,'username');
  var trackname = getNode(p,'trackname');
  var tid = getNode(p,'trackid');
  var dateoccured = getNode(p,'dateoccured');
  var distance = parseInt(getNode(p,'distance'));
  var seconds = parseInt(getNode(p,'seconds'));
  return {
    'latitude': latitude,
    'longitude': longitude,
    'altitude': altitude,
    'speed': speed,
    'angle': angle,
    'username': username,
    'trackname': trackname,
    'tid': tid,
    'dateoccured': dateoccured,
    'distance': distance,
    'seconds': seconds
  }; 
}

function load(type,userid,trackid) {
  var url = 'download.php?type='+type+'&userid='+userid+'&trackid='+trackid;
  window.location.assign(url);
}

function updateSummary(l,d,s) {
  var t = document.getElementById('summary');
  if (latest==0){
    t.innerHTML = '<u>'+lang_summary+'</u><br />'+
    lang_tdistance+': '+(d.toKm()*factor_km).toFixed(2)+' '+unit_km+'<br />'+
    lang_ttime+': '+s.toHMS();
  }
  else {
    t.innerHTML = '<u>'+lang_latest+':</u><br />'+l;
  }
}

function getNode(p,name) {
  return ((p.getElementsByTagName(name)[0].childNodes[0]) ? p.getElementsByTagName(name)[0].childNodes[0].nodeValue : null);
}


// seconds to (d) H:M:S
Number.prototype.toHMS = function(){
  var s = this;
  var d = Math.floor(s / 86400);
  var h = Math.floor((s % 86400) / 3600);
  var m = Math.floor(((s % 86400) % 3600) / 60);
  s = ((s % 86400) % 3600) % 60;

  return ((d>0)?(d + ' d '):'') + (('00'+h).slice(-2)) + ':' + (('00'+m).slice(-2)) + ':' + (('00'+s).slice(-2)) + '';
}
// meters to km
Number.prototype.toKm = function() {
  return Math.round(this/10)/100;
}
// m/s to km/h
Number.prototype.toKmH = function() {
  return Math.round(this*3600/10)/100;
}

// negate value
function toggleLatest() {
  if (latest==0) {
    latest = 1;
    loadTrack(userid,0,1);
  }
  else {
    latest = 0;
    loadTrack(userid,trackid,1);
  }
}

function setTrack(t) {
  document.getElementsByName('track')[0].value = t;
}

function selectTrack(f) {
  trackid=f.options[f.selectedIndex].value;
  document.getElementById('latest').checked = false;
  if (latest==1) { toggleLatest(); }
  loadTrack(userid,trackid,1);
}

function selectUser(f) {
  userid=f.options[f.selectedIndex].value;
  if (f.options[0].disabled==false) {
    f.options[0].disabled = true;
  }
  document.getElementById('latest').checked = false;
  if (latest==1) { toggleLatest(); }
  getTrips(userid);
}

function getTrips(userid) {
  var xhr = getXHR();
  xhr.onreadystatechange = function() {
    if (xhr.readyState==4 && xhr.status==200) {
      var xml = xhr.responseXML;
      var trips = xml.getElementsByTagName('trip');
      if (trips.length>0) {  
        fillOptions(xml);
      }
      xhr = null;
    }
  }
  xhr.open('GET','gettrips.php?userid='+userid,true);
  xhr.send();  
}

function fillOptions(xml) {
  var trackSelect =  document.getElementsByName('track')[0];
  clearOptions(trackSelect);
  var trips = xml.getElementsByTagName('trip');
  var trpLen = trips.length;
  for (var i=0; i<trpLen; i++) {
    var trackid = getNode(trips[i],'trackid');
    var trackname = getNode(trips[i],'trackname');
    var option = document.createElement("option");
    option.value = trackid;
    option.innerHTML = trackname;
    trackSelect.appendChild(option);
  }  
  var defaultTrack = getNode(trips[0],'trackid');
  loadTrack(userid,defaultTrack,1);
}

function clearOptions(el){
  if (el.options) {
    while (el.options.length) {
      el.remove(0);
    }
  }
}

var auto;
function autoReload() {
  if (live==0) {
    live = 1;
    auto = setInterval(function() { loadTrack(userid,trackid,0); },interval*1000);
  }
  else {
    live = 0;
    clearInterval(auto);
  }
}

function setTime() {
  var i = parseInt(prompt(lang_newinterval));
  if (!isNaN(i) && i!=interval) {
    interval = i;
    document.getElementById('auto').innerHTML = interval;
    // if live tracking on, reload with new interval
    if (live==1) { 
      live = 0;
      clearInterval(auto);
      autoReload();
    }
  }
}
