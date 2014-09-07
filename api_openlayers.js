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
 
// openlayers
var map;
var layerTrack;
var layerMarkers;
var lineStyle = {strokeColor: '#FF0000', strokeOpacity: 1, strokeWidth: 2};	
var wgs84;
var mercator;
var loadedAPI = 'openlayers';
function init() {
	wgs84 = new OpenLayers.Projection('EPSG:4326');   // from WGS 1984
	mercator   = new OpenLayers.Projection('EPSG:900913'); // to Mercator
	var options = { controls: [
		new OpenLayers.Control.ArgParser(), // default
		new OpenLayers.Control.Attribution(), // default
		new OpenLayers.Control.LayerSwitcher(),
		new OpenLayers.Control.Navigation(), // default
		new OpenLayers.Control.PanZoomBar(),// do we need it?
		new OpenLayers.Control.ScaleLine()
		]	
	};		
	map = new OpenLayers.Map('map-canvas', options);
	// default layer: OpenStreetMap
	var mapnik = new OpenLayers.Layer.OSM();
	map.addLayer(mapnik);
	if (layer_ocm==1) {
	  // OpenCycleMap
    var ocm = new OpenLayers.Layer.OSM('OpenCycleMap',
      ['http://a.tile.opencyclemap.org/cycle/${z}/${x}/${y}.png',
      'http://b.tile.opencyclemap.org/cycle/${z}/${x}/${y}.png',
      'http://c.tile.opencyclemap.org/cycle/${z}/${x}/${y}.png']);
    map.addLayer(ocm);
  }
	if (layer_mq==1) {
	  // MapQuest-OSM
    var mq = new OpenLayers.Layer.OSM('MapQuest-OSM',
      ['http://otile1.mqcdn.com/tiles/1.0.0/map/${z}/${x}/${y}.jpg',
      'http://otile2.mqcdn.com/tiles/1.0.0/map/${z}/${x}/${y}.jpg',
      'http://otile3.mqcdn.com/tiles/1.0.0/map/${z}/${x}/${y}.jpg',
      'http://otile4.mqcdn.com/tiles/1.0.0/map/${z}/${x}/${y}.jpg']);
    map.addLayer(mq);
  }
	if (layer_osmapa==1) {
	  // osmapa.pl
    var osmapa = new OpenLayers.Layer.OSM('osmapa.pl', 
      ['http://a.osm.trail.pl/osmapa.pl/${z}/${x}/${y}.png',
      'http://b.osm.trail.pl/osmapa.pl/${z}/${x}/${y}.png',
      'http://c.osm.trail.pl/osmapa.pl/${z}/${x}/${y}.png']);
    map.addLayer(osmapa);
  }
	if (layer_ump==1) {
	  // UMP
    var ump = new OpenLayers.Layer.OSM('UMP', 
      ['http://1.tiles.ump.waw.pl/ump_tiles/${z}/${x}/${y}.png',
      'http://2.tiles.ump.waw.pl/ump_tiles/${z}/${x}/${y}.png',
      'http://3.tiles.ump.waw.pl/ump_tiles/${z}/${x}/${y}.png']);
    map.addLayer(ump);
  }  
        var position = new OpenLayers.LonLat(init_longitude,init_latitude).transform(wgs84, mercator);
	var zoom = 8; 
	map.setCenter(position, zoom);
  // init layers
  layerTrack = new OpenLayers.Layer.Vector('Track');
	layerMarkers = new OpenLayers.Layer.Markers('Markers');	
}
function displayTrack(xml,update) {
  altitudes.length = 0;
  var totalMeters = 0;
  var totalSeconds = 0;
  var points = new Array();
  var latlngbounds = new OpenLayers.Bounds();
  var positions = xml.getElementsByTagName('position');
  var posLen = positions.length;
  for (var i=0; i<posLen; i++) {
    var p = parsePosition(positions[i]);
    totalMeters += p.distance;
    totalSeconds += p.seconds;
    p['totalMeters'] = totalMeters;
    p['totalSeconds'] = totalSeconds;
    // set marker
    setMarker(p,i,posLen);
    // update polyline
    var point = new OpenLayers.Geometry.Point(p.longitude, p.latitude).transform(wgs84,mercator);
    latlngbounds.extend(point);
    points.push(point);
    // save altitudes for chart
    altitudes[i] = p.altitude;
  }
	var lineString = new OpenLayers.Geometry.LineString(points);
	var lineFeature = new OpenLayers.Feature.Vector(lineString, null, lineStyle); 
	layerTrack.addFeatures([lineFeature]); 
	map.addLayer(layerTrack);
	map.addLayer(layerMarkers);	  
  if (update) {
    map.zoomToExtent(latlngbounds);
    if (i==1) {
      // only one point, zoom out 
      map.zoomOut();
    }
  }
  latestTime = p.dateoccured;
  //polies.push(poly);
  
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

function clearMap() {
  if (layerTrack){
    layerTrack.removeAllFeatures();
  }
  if (layerMarkers){
    layerMarkers.clearMarkers();
  }
}

function setMarker(p,i,posLen) {
  // marker
  var lonLat = new OpenLayers.LonLat(p.longitude,p.latitude).transform(wgs84,mercator);
  var size = new OpenLayers.Size(21, 25);
  var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);	
  if (latest==1) { var icon = new OpenLayers.Icon('http://www.openstreetmap.org/openlayers/img/marker.png',size,offset); }
  else if (i==0) { var icon = new OpenLayers.Icon('http://www.openstreetmap.org/openlayers/img/marker-green.png',size,offset); }
  else if (i==posLen-1) { var icon = new OpenLayers.Icon('http://www.openstreetmap.org/openlayers/img/marker.png',size,offset); }
  else { 
  	size = new OpenLayers.Size(12, 20); 
  	offset = new OpenLayers.Pixel(-(size.w/2), -size.h);
  	var icon = new OpenLayers.Icon('http://labs.google.com/ridefinder/images/mm_20_gray.png',size,offset); 
  }
  var marker = new OpenLayers.Marker(lonLat,icon);
	layerMarkers.addMarker(marker);	
	

  // popup
  var content = '<div id="popup">'+
    '<div id="pheader">'+lang_user+': '+p.username.toUpperCase()+'<br />'+lang_track+': '+p.trackname.toUpperCase()+
    '</div>'+
    '<div id="pbody">'+
    ((p.comments != null)?'<div id="pcomments">'+p.comments+'</div>':'')+
    '<div id="pleft"><b>'+lang_time+':</b> '+p.dateoccured+'<br />'+
    ((p.speed != null)?'<b>'+lang_speed+':</b> '+(p.speed.toKmH()*factor_kmh)+' '+unit_kmh+'<br />':'')+
    ((p.altitude != null)?'<b>'+lang_altitude+':</b> '+(p.altitude*factor_m).toFixed()+' '+unit_m+'<br />':'')+'</div>'+
    ((latest==0)?
    ('<div id="pright"><b>'+lang_ttime+':</b> '+p.totalSeconds.toHMS()+'<br />'+
    '<b>'+lang_aspeed+':</b> '+((p.totalSeconds>0)?((p.totalMeters/p.totalSeconds).toKmH()*factor_kmh).toFixed():0)+' '+unit_kmh+'<br />'+
    '<b>'+lang_tdistance+':</b> '+(p.totalMeters.toKm()*factor_km).toFixed(2)+' '+unit_km+'<br />'+'</div>'):'')+
    '<div id="pfooter">'+lang_point+' '+(i+1)+' '+lang_of+' '+(posLen)+'</div>'+
    '</div></div>';    
	marker.events.register("mousedown", marker, (function() {
	return function() {
		// remove popups
		if (map.popups.length>0) {
			for (var j = map.popups.length-1; j>=0; j-- ) { 
				map.removePopup(map.popups[j])
			};		
		}
		// show popup
		var popup = new OpenLayers.Popup.FramedCloud("id "+(i+1),lonLat,null,content,icon,true);  
		map.addPopup(popup);
		if (document.getElementById('bottom').style.display=='block') {
			chart.setSelection([{row:i,column:null}]);
		}
	}
  })());    
}

function addChartEvent(chart) {
	google.visualization.events.addListener(chart, 'select', function() {
		var selection = chart.getSelection()[0];
		if (selection) {
			var id = selection.row;
			var marker = layerMarkers.markers[id];
			var url = marker.icon.url;
			marker.setUrl('http://www.openstreetmap.org/openlayers/img/marker-gold.png');
			altTimeout = setTimeout(function() { marker.setUrl(url); },2000);
		}
	}); 	
}
//20.597985430276808,52.15547181298076,21.363595171488573,52.33750879522563
function getBounds() {
	var b = map.getExtent().transform(mercator,wgs84).toString();
	var bounds = b.split(',',4);
	var lon_sw = bounds[0];
	var lat_sw = bounds[1];
	var lon_ne = bounds[2];
	var lat_ne = bounds[3];
	return [lon_sw,lat_sw,lon_ne,lat_ne];
}

function zoomToBounds(b) {
	var bounds = new OpenLayers.Bounds(b).transform(wgs84,mercator);
	map.zoomToExtent(bounds);
}
