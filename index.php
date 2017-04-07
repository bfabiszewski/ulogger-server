<?php
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
 
require_once("auth.php");
require_once("helpers/position.php");
require_once("helpers/track.php");

if ($user->isValid) {
  $itemPass = '<a href="javascript:void(0)" onclick="changePass()">' . $lang["changepass"] . '</a>';
  $itemLogout = '<a href="logout.php">' . $lang["logout"] . '</a>';
  $userHeader = '
  <div id="user_menu">
  <a href="javascript:void(0);" onclick="userMenu()">' . $user->login . '</a>
  <div id="user_dropdown" class="dropdown">
  ' . $itemPass . '
  ' . $itemLogout . '
  </div>
  </div>';
} else {
  $userHeader = '<a href="index.php?force_login=1">' . $lang["login"] . '</a>';
}
$lastUserId = NULL;
$userForm = '';
if ($user->isAdmin || $config::$public_tracks) {
  // public access or admin user
  // prepare user select form
  $userForm = '
  <br /><u>' . $lang["user"] . '</u>
  <br />
  <form>
  <select name="user" onchange="selectUser(this)">
  <option value="0">' . $lang["suser"] . '</option>';
  // get last position user
  $lastPosition = new uPosition();
  $lastPosition->getLast();
  if ($lastPosition->isValid) {
    $lastUserId = $lastPosition->userId;
  }
  
  $usersArr = $user->getAll();
  if (!empty($usersArr)) {
    foreach ($usersArr as $aUser) {
      $userForm.= sprintf("<option %svalue=\"%s\">%s</option>\n", (($aUser->id == $lastUserId) ? "selected " : ""), $aUser->id, $aUser->login);
    }
  }
  $userForm.= '
  </select>
  </form>
  ';
}
// prepare track select form
$trackForm = '
<u>' . $lang["track"] . '</u><br />
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

$track = new uTrack();
$tracksArr = $track->getAll($displayId);
$trackId = NULL;
if (!empty($tracksArr)) {
  $trackId = $tracksArr[0]->id; // get id of the latest track
  foreach ($tracksArr as $aTrack) {
    $trackForm.= sprintf("<option value=\"%s\">%s</option>\n", $aTrack->id, $aTrack->name);
  }
}
$trackForm.= '
</select>
<input id="latest" type="checkbox" onchange="toggleLatest();"> ' . $lang["latest"] . '<br />
</form>
';
// map api select form
$apiForm = '
<u>' . $lang["api"] . '</u><br />
<form>
<select name="api" onchange="loadMapAPI(this.options[this.selectedIndex].value);">
<option value="gmaps"' . (($config::$mapapi == "gmaps") ? ' selected' : '') . '>Google Maps</option>
<option value="openlayers"' . (($config::$mapapi == "openlayers") ? ' selected' : '') . '>OpenLayers</option>
</select>
</form>
';
// language select form
$langForm = '
<u>' . $lang["language"] . '</u><br />
<form>
<select name="units" onchange="setLang(this.options[this.selectedIndex].value);">';
asort($langsArr);
foreach ($langsArr as $langCode => $langName) {
  $langForm .= '<option value="' . $langCode . '"' . (($config::$lang == $langCode) ? ' selected' : '') . '>' . $langName . '</option>';
}
$langForm .= '
</select>
</form>
';
// units select form
$unitsForm = '
<u>' . $lang["units"] . '</u><br />
<form>
<select name="units" onchange="setUnits(this.options[this.selectedIndex].value);">
<option value="metric"' . (($config::$units == "metric") ? ' selected' : '') . '>' . $lang["metric"] . '</option>
<option value="imperial"' . (($config::$units == "imperial") ? ' selected' : '') . '>' . $lang["imperial"] . '</option>
</select>
</form>
';
// admin menu
$adminMenu = '';
$adminScript = '';
if ($user->isAdmin) {
  $adminMenu = '
  <div id="admin_menu">
    <u>' . $lang["adminmenu"] . '</u><br />
      <a href="javascript:void(0);" onclick="addUser()">' . $lang["adduser"] . '</a><br />
  </div>
  ';
  $adminScript = '<script type="text/javascript" src="admin.js"></script>';
}
print '<!DOCTYPE html>
<html>
  <head>
    <title>' . $lang["title"] . '</title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" type="text/css" href="main.css" />
    <script>
      var interval = ' . $config::$interval . ';
      var userid = ' . (($displayId) ? $displayId : -1) . ';
      var trackid = ' . (($trackId) ? $trackId : -1) . ';
      var units = "' . $config::$units . '";
      var mapapi = "' . $config::$mapapi . '";
      var gkey = ' . (!empty($config::$gkey) ? '"' . $config::$gkey . '"' : 'null') . ';
      var layer_ocm = "' . $config::$layer_ocm . '";
      var layer_mq = "' . $config::$layer_mq . '";
      var layer_osmapa = "' . $config::$layer_osmapa . '";
      var layer_ump = "' . $config::$layer_ump . '";
      var init_latitude = "' . $config::$init_latitude . '";
      var init_longitude = "' . $config::$init_longitude . '";
      var lang = ' . json_encode($lang) . ';
    </script>
    <script type="text/javascript" src="main.js"></script>
';
if ($config::$mapapi == "gmaps") {
  print '   <script type="text/javascript" src="//maps.googleapis.com/maps/api/js' . (!empty($config::$gkey) ? '?key=' . $config::$gkey : '') . '"></script>
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
          <input type="checkbox" onchange="autoReload();"> ' . $lang["autoreload"] . ' (<a href="javascript:void(0);" onclick="setTime()"><span id="auto">' . $config::$interval . '</span></a> s)<br />
          <a href="javascript:void(0);" onclick="loadTrack(userid,trackid,0)">' . $lang["reload"] . '</a><br />
        </div>
        <div id="summary"></div>
        <div id="other">
          <a href="javascript:void(0);" onclick="toggleChart();">' . $lang["chart"] . '</a>
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
          <u>' . $lang["download"] . '</u><br />
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
        <div id="close"><a href="javascript:void(0);" onclick="toggleChart(0);">' . $lang["close"] . '</a></div>
      </div>
    </div>
  </body>
</html>';

$mysqli->close();
?>
