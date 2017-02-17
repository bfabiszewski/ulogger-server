<?php
/* μlogger
 *
 * Copyright(C) 2017 Bartek Fabiszewski (www.fabiszewski.net)
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
require_once("auth.php");

if ($auth && !$admin && !$public_tracks) {
  // only authorized user tracks
  // get username
  $query = "SELECT login FROM users WHERE id='$auth' LIMIT 1";
  $result = $mysqli->query($query);
  $row = $result->fetch_assoc();
  $user = $row["login"];

  // users
  $user_form = '<u>'.$lang_user.'</u><br />'.$user.' (<a href="logout.php">'.$lang_logout.'</a>)';
}
else {
  // public access or admin user
  // prepare user select form
  if ($admin) {
     $user = $admin_user;
  }
  $user_form = '
  <u>'.$lang_user.'</u> ';
  if ($auth) {
    $user_form .= '&nbsp;'.$user.' (<a href="logout.php">'.$lang_logout.'</a>)';
  }
  $user_form .= '
  <br />
  <form>
  <select name="user" onchange="selectUser(this)">
  <option value="0">'.$lang_suser.'</option>';
  // get last position user
  $query = "SELECT p.user_id FROM positions p ORDER BY p.time LIMIT 1";
  $result = $mysqli->query($query);
  if ($result->num_rows) {
    $last = $result->fetch_row();
    $last_id = $last[0];
  } else {
    $last_id = "";
  }
  $query = "SELECT id, login FROM users ORDER BY login";
  $result = $mysqli->query($query);
  while ($row = $result->fetch_assoc()) {
    $user_form .= sprintf("<option %svalue=\"%s\">%s</option>\n", ($row["id"] == $last_id)?"selected ":"",$row["id"], $row["login"]);
  }
  $user_form .= '
  </select>
  </form>
  ';
}

// prepare track select form
$track_form = '
<u>'.$lang_track.'</u><br />
<form>
<select name="track" onchange="selectTrack(this)">';
$userid = "";
if ($auth && !$admin && !$public_tracks) {
  // display track of authenticated user
  $userid = $auth;
} elseif ($last_id) {
  // or user who did last move
  $userid = $last_id;
}
$query = "SELECT * FROM tracks WHERE user_id='$userid' ORDER BY id DESC";
$result = $mysqli->query($query);

$trackid = "";
while ($row = $result->fetch_assoc()) {
  if ($trackid == "") { $trackid = $row["id"]; } // get first row
  $track_form .= sprintf("<option value=\"%s\">%s</option>\n", $row["id"], $row["name"]);
}
$track_form .= '
</select>
<input id="latest" type="checkbox" onchange="toggleLatest();"> '.$lang_latest.'<br />
</form>
';
// map api select form
$api_form = '
<u>'.$lang_api.'</u><br />
<form>
<select name="api" onchange="loadMapAPI(this.options[this.selectedIndex].value);">
<option value="gmaps"'.(($mapapi=="gmaps")?' selected':'').'>Google Maps</option>
<option value="openlayers"'.(($mapapi=="openlayers")?' selected':'').'>OpenLayers</option>
</select>
</form>
';

// language select form
$lang_form = '
<u>'.$lang_language.'</u><br />
<form>
<select name="units" onchange="setLang(this.options[this.selectedIndex].value);">
<option value="en"'.(($lang=="en")?' selected':'').'>English</option>
<option value="pl"'.(($lang=="pl")?' selected':'').'>Polski</option>
<option value="de"'.(($lang=="de")?' selected':'').'>Deutsch</option>
<option value="hu"'.(($lang=="hu")?' selected':'').'>Magyar</option>
</select>
</form>
';
// units select form
$units_form = '
<u>'.$lang_units.'</u><br />
<form>
<select name="units" onchange="setUnits(this.options[this.selectedIndex].value);">
<option value="metric"'.(($units=="metric")?' selected':'').'>'.$lang_metric.'</option>
<option value="imperial"'.(($units=="imperial")?' selected':'').'>'.$lang_imperial.'</option>
</select>
</form>
';
// admin menu
$admin_menu = '';
$admin_script = '';
if ($admin) {
  $admin_menu = '
  <div id="admin_menu">
    <u>'.$lang_adminmenu.'</u><br />
      <a href="javascript:void(0);" onclick="addUser()">'.$lang_adduser.'</a><br />
  </div>
  ';
  $admin_script = '<script type="text/javascript" src="admin.js"></script>';
}

print
'<!DOCTYPE html>
<html>
  <head>
    <title>'.$lang_title.'</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" type="text/css" href="main.css" />
    <script>
      var interval = '.$interval.';
      var userid = '.(($userid)?$userid:-1).';
      var trackid = '.(($trackid)?$trackid:-1).';
      var lang_user = "'.$lang_user.'";
      var lang_time = "'.$lang_time.'";
      var lang_speed = "'.$lang_speed.'";
      var lang_accuracy = "'.$lang_accuracy.'";
      var lang_altitude = "'.$lang_altitude.'";
      var lang_ttime = "'.$lang_ttime.'";
      var lang_aspeed = "'.$lang_aspeed.'";
      var lang_tdistance = "'.$lang_tdistance.'";
      var lang_point = "'.$lang_point.'";
      var lang_of = "'.$lang_of.'";
      var lang_summary = "'.$lang_summary.'";
      var lang_latest = "'.$lang_latest.'";
      var lang_track = "'.$lang_track.'";
      var lang_newinterval = "'.$lang_newinterval.'";
      var units = "'.$units.'";
      var mapapi = "'.$mapapi.'";
      var gkey = '.(isset($gkey)?'"'.$gkey.'"':'null').';
      var layer_ocm = "'.$layer_ocm.'";
      var layer_mq = "'.$layer_mq.'";
      var layer_osmapa = "'.$layer_osmapa.'";
      var layer_ump = "'.$layer_ump.'";
      var init_latitude = "'.$init_latitude.'";
      var init_longitude = "'.$init_longitude.'";
    </script>
    <script type="text/javascript" src="main.js"></script>
';
if ($mapapi == "gmaps") {
  print
'   <script type="text/javascript" src="//maps.googleapis.com/maps/api/js'.(isset($gkey)?'?key='.$gkey:'').'"></script>
    <script type="text/javascript" src="api_gmaps.js"></script>
';
}
else {
  print
'   <script type="text/javascript" src="//openlayers.org/api/OpenLayers.js"></script>
    <script type="text/javascript" src="api_openlayers.js"></script>
';
}
print '
   '.$admin_script.'
   <script type="text/javascript" src="//www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
    </script>

  </head>
  <body onload="init();loadTrack(userid,trackid,1);">
    <div id="menu">
      <div id="menu-content">
        <div id="user">
          '.$user_form.'
        </div>
        <div id="track">
          '.$track_form.'
          <input type="checkbox" onchange="autoReload();"> '.$lang_autoreload.' (<a href="javascript:void(0);" onclick="setTime()"><span id="auto">'.$interval.'</span></a> s)<br />
          <a href="javascript:void(0);" onclick="loadTrack(userid,trackid,0)">'.$lang_reload.'</a><br />
        </div>
        <div id="summary"></div>
        <div id="other">
          <a href="javascript:void(0);" onclick="toggleChart();">'.$lang_chart.'</a>
        </div>
        <div id="api">
          '.$api_form.'
        </div>
        <div id="lang">
          '.$lang_form.'
        </div>
        <div id="units">
          '.$units_form.'
        </div>
        <div id="export">
          <u>'.$lang_download.'</u><br />
          <a href="javascript:void(0);" onclick="load(\'kml\',userid,trackid)">kml</a><br />
          <a href="javascript:void(0);" onclick="load(\'gpx\',userid,trackid)">gpx</a><br />
        </div>
        '.$admin_menu.'
      </div>
      <div id="menu-close" onclick="toggleMenu();">»</div>
      <div id="footer"><a target="_blank" href="https://github.com/bfabiszewski/ulogger-server"><span class="mi">μ</span>logger</a> '.$version.'</div>
    </div>
    <div id="main">
      <div id="map-canvas"></div>
      <div id="bottom">
        <div id="chart"></div>
        <div id="close"><a href="javascript:void(0);" onclick="toggleChart(0);">'.$lang_close.'</a></div>
      </div>
    </div>
  </body>
</html>';
$mysqli->close();
?>
