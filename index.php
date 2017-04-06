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
require_once ("auth.php");
if ($user->isValid) {
  $userHeader = $user->login . ' (<a href="logout.php">' . $lang_logout . '</a>)';
} else {
  $userHeader = '<a href="index.php?force_login=1">' . $lang_login . '</a>';
}
$lastUserId = NULL;
$userForm = '';
if ($user->isAdmin || $config::$public_tracks) {
  // public access or admin user
  // prepare user select form
  $userForm = '
  <br /><u>' . $lang_user . '</u>
  <br />
  <form>
  <select name="user" onchange="selectUser(this)">
  <option value="0">' . $lang_suser . '</option>';
  // get last position user
  $query = "SELECT p.user_id FROM positions p ORDER BY p.time LIMIT 1";
  $result = $mysqli->query($query);
  if ($result->num_rows) {
    $last = $result->fetch_row();
    $lastUserId = $last[0];
  }
  $usersArr = $user->listAll();
  if (!empty($usersArr)) {
    foreach ($usersArr as $userId => $userLogin) {
      $userForm.= sprintf("<option %svalue=\"%s\">%s</option>\n", (($userId == $lastUserId) ? "selected " : ""), $userId, $userLogin);
    }
  }
  $userForm.= '
  </select>
  </form>
  ';
}
// prepare track select form
$trackForm = '
<u>' . $lang_track . '</u><br />
<form>
<select name="track" onchange="selectTrack(this)">';
$displayId = NULL;
if ($lastUserId) {
  // or user who did last move
  $displayId = $lastUserId;
} else if ($user->isValid) {
  // display track of authenticated user
  $displayId = $user->id;
} 
$query = "SELECT * FROM tracks WHERE user_id='$displayId' ORDER BY id DESC";
$result = $mysqli->query($query);
$trackId = NULL;
while ($row = $result->fetch_assoc()) {
  if (is_null($trackId)) { $trackId = $row["id"]; } // get first row
  $trackForm.= sprintf("<option value=\"%s\">%s</option>\n", $row["id"], $row["name"]);
}
$trackForm.= '
</select>
<input id="latest" type="checkbox" onchange="toggleLatest();"> ' . $lang_latest . '<br />
</form>
';
// map api select form
$apiForm = '
<u>' . $lang_api . '</u><br />
<form>
<select name="api" onchange="loadMapAPI(this.options[this.selectedIndex].value);">
<option value="gmaps"' . (($config::$mapapi == "gmaps") ? ' selected' : '') . '>Google Maps</option>
<option value="openlayers"' . (($config::$mapapi == "openlayers") ? ' selected' : '') . '>OpenLayers</option>
</select>
</form>
';
// language select form
$langForm = '
<u>' . $lang_language . '</u><br />
<form>
<select name="units" onchange="setLang(this.options[this.selectedIndex].value);">
<option value="en"' . (($config::$lang == "en") ? ' selected' : '') . '>English</option>
<option value="pl"' . (($config::$lang == "pl") ? ' selected' : '') . '>Polski</option>
<option value="de"' . (($config::$lang == "de") ? ' selected' : '') . '>Deutsch</option>
<option value="hu"' . (($config::$lang == "hu") ? ' selected' : '') . '>Magyar</option>
<option value="fr"' . (($config::$lang == "fr") ? ' selected' : '') . '>Français</option>
<option value="it"' . (($config::$lang == "it") ? ' selected' : '') . '>Italiano</option>
</select>
</form>
';
// units select form
$unitsForm = '
<u>' . $lang_units . '</u><br />
<form>
<select name="units" onchange="setUnits(this.options[this.selectedIndex].value);">
<option value="metric"' . (($config::$units == "metric") ? ' selected' : '') . '>' . $lang_metric . '</option>
<option value="imperial"' . (($config::$units == "imperial") ? ' selected' : '') . '>' . $lang_imperial . '</option>
</select>
</form>
';
// admin menu
$adminMenu = '';
$adminScript = '';
if ($user->isAdmin) {
  $adminMenu = '
  <div id="admin_menu">
    <u>' . $lang_adminmenu . '</u><br />
      <a href="javascript:void(0);" onclick="addUser()">' . $lang_adduser . '</a><br />
  </div>
  ';
  $adminScript = '<script type="text/javascript" src="admin.js"></script>';
}
print '<!DOCTYPE html>
<html>
  <head>
    <title>' . $lang_title . '</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" type="text/css" href="main.css" />
    <script>
      var interval = ' . $config::$interval . ';
      var userid = ' . (($displayId) ? $displayId : -1) . ';
      var trackid = ' . (($trackId) ? $trackId : -1) . ';
      var lang_user = "' . $lang_user . '";
      var lang_time = "' . $lang_time . '";
      var lang_speed = "' . $lang_speed . '";
      var lang_accuracy = "' . $lang_accuracy . '";
      var lang_altitude = "' . $lang_altitude . '";
      var lang_ttime = "' . $lang_ttime . '";
      var lang_aspeed = "' . $lang_aspeed . '";
      var lang_tdistance = "' . $lang_tdistance . '";
      var lang_point = "' . $lang_point . '";
      var lang_of = "' . $lang_of . '";
      var lang_summary = "' . $lang_summary . '";
      var lang_latest = "' . $lang_latest . '";
      var lang_track = "' . $lang_track . '";
      var lang_newinterval = "' . $lang_newinterval . '";
      var lang_username = "' . $lang_username . '";
      var lang_password = "' . $lang_password . '";
      var lang_passwordrepeat = "' . $lang_passwordrepeat . '";
      var lang_passwordenter = "' . $lang_passwordenter . '";
      var lang_usernameenter = "' . $lang_usernameenter . '";
      var lang_cancel = "' . $lang_cancel . '";
      var lang_submit = "' . $lang_submit . '";
      var units = "' . $config::$units . '";
      var mapapi = "' . $config::$mapapi . '";
      var gkey = ' . (isset($gkey) ? '"' . $gkey . '"' : 'null') . ';
      var layer_ocm = "' . $config::$layer_ocm . '";
      var layer_mq = "' . $config::$layer_mq . '";
      var layer_osmapa = "' . $config::$layer_osmapa . '";
      var layer_ump = "' . $config::$layer_ump . '";
      var init_latitude = "' . $config::$init_latitude . '";
      var init_longitude = "' . $config::$init_longitude . '";
    </script>
    <script type="text/javascript" src="main.js"></script>
';
if ($config::$mapapi == "gmaps") {
  print '   <script type="text/javascript" src="//maps.googleapis.com/maps/api/js' . (isset($gkey) ? '?key=' . $gkey : '') . '"></script>
    <script type="text/javascript" src="api_gmaps.js"></script>
';
} else {
  print '   <script type="text/javascript" src="//openlayers.org/api/OpenLayers.js"></script>
    <script type="text/javascript" src="api_openlayers.js"></script>
';
}
print '
   ' . $adminScript . '
   <script type="text/javascript" src="pass.js"></script>
   <script type="text/javascript" src="//www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
    </script>

  </head>
  <body onload="init();loadTrack(userid,trackid,1);">
    <div id="menu">
      <div id="menu-content">
          ' . $userHeader . '
        <div id="user">
          ' . $userForm . '
        </div>
        <div id="track">
          ' . $trackForm . '
          <input type="checkbox" onchange="autoReload();"> ' . $lang_autoreload . ' (<a href="javascript:void(0);" onclick="setTime()"><span id="auto">' . $config::$interval . '</span></a> s)<br />
          <a href="javascript:void(0);" onclick="loadTrack(userid,trackid,0)">' . $lang_reload . '</a><br />
        </div>
        <div id="summary"></div>
        <div id="other">
          <a href="javascript:void(0);" onclick="toggleChart();">' . $lang_chart . '</a>
        </div>
        <div id="api">
          ' . $apiForm . '
        </div>
        <div id="lang">
          ' . $langForm . '
        </div>
        <div id="units">
          ' . $unitsForm . '
        </div>
        <div id="export">
          <u>' . $lang_download . '</u><br />
          <a href="javascript:void(0);" onclick="load(\'kml\',userid,trackid)">kml</a><br />
          <a href="javascript:void(0);" onclick="load(\'gpx\',userid,trackid)">gpx</a><br />
        </div>
        ' . $adminMenu . '
      </div>
      <div id="menu-close" onclick="toggleMenu();">»</div>
      <div id="footer"><a target="_blank" href="https://github.com/bfabiszewski/ulogger-server"><span class="mi">μ</span>logger</a> ' . $config::$version . '</div>
    </div>
    <div id="main">
      <div id="map-canvas"></div>
      <div id="bottom">
        <div id="chart"></div>
        <div id="close"><a href="javascript:void(0);" onclick="toggleChart(0);">' . $lang_close . '</a></div>
      </div>
    </div>
  </body>
</html>';
$mysqli->close();
?>
