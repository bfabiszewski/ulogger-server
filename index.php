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

  require_once(__DIR__ . "/auth.php"); // sets $mysqli, $user
  require_once(ROOT_DIR . "/helpers/position.php");
  require_once(ROOT_DIR . "/helpers/track.php");

  $displayUserId = NULL;
  $usersArr = [];
  if ($user->isAdmin || $config::$public_tracks) {
    // public access or admin user
    // get last position user
    $lastPosition = new uPosition();
    $lastPosition->getLast();
    if ($lastPosition->isValid) {
      // display track of last position user
      $displayUserId = $lastPosition->userId;
    }
    // populate users array (for <select>)
    $usersArr = $user->getAll();
  } else if ($user->isValid) {
    // display track of authenticated user
    $displayUserId = $user->id;
  }

  $track = new uTrack();
  $tracksArr = $track->getAll($displayUserId);
  if (!empty($tracksArr)) {
    // get id of the latest track
    $displayTrackId = $tracksArr[0]->id;
  } else {
    $tracksArr = [];
    $displayTrackId = NULL;
  }

  $mysqli->close();
?>
<!DOCTYPE html>
<html>
  <head>
    <title><?= $lang["title"] ?></title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <script>
      var interval = '<?= $config::$interval ?>';
      var userid = '<?= ($displayUserId) ? $displayUserId : -1 ?>';
      var trackid = '<?= ($displayTrackId) ? $displayTrackId : -1 ?>';
      var units = '<?= $config::$units ?>';
      var mapapi = '<?= $config::$mapapi ?>';
      var gkey = '<?= !empty($config::$gkey) ? $config::$gkey : "null" ?>';
      var layer_ocm = '<?= $config::$layer_ocm ?>';
      var layer_mq = '<?= $config::$layer_mq ?>';
      var layer_osmapa = '<?= $config::$layer_osmapa ?>';
      var layer_ump = '<?= $config::$layer_ump ?>';
      var init_latitude = '<?= $config::$init_latitude ?>';
      var init_longitude = '<?= $config::$init_longitude ?>';
      var lang = <?= json_encode($lang) ?>;
    </script>
    <script type="text/javascript" src="js/main.js"></script>

    <?php if ($config::$mapapi == "gmaps"): ?>
      <script type="text/javascript" src="//maps.googleapis.com/maps/api/js<?= !empty($config::$gkey) ? "?key={$config::$gkey}" : "" ?>"></script>
      <script type="text/javascript" src="js/api_gmaps.js"></script>
    <?php else: ?>
      <script type="text/javascript" src="//openlayers.org/api/OpenLayers.js"></script>
      <script type="text/javascript" src="js/api_openlayers.js"></script>
    <?php endif; ?>
    <?php if ($user->isAdmin): ?>
      <script type="text/javascript" src="js/admin.js"></script>
    <?php endif; ?>
    <script type="text/javascript" src="js/pass.js"></script>
    <script type="text/javascript" src="//www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load('visualization', '1', { packages:['corechart'] });
    </script>
  </head>

  <body onload="init(); loadTrack(userid, trackid, 1);">
    <div id="menu">
      <div id="menu-content">

        <?php if ($user->isValid): ?>
          <div id="user_menu">
            <a href="javascript:void(0);" onclick="userMenu()"><img class="icon" alt="<?= $lang["user"] ?>" src="images/user.svg"> <?= $user->login ?></a>
            <div id="user_dropdown" class="dropdown">
              <a href="javascript:void(0)" onclick="changePass()"><img class="icon" alt="<?= $lang["changepass"] ?>" src="images/lock.svg"> <?= $lang["changepass"] ?></a>
              <a href="utils/logout.php"><img class="icon" alt="<?= $lang["logout"] ?>" src="images/poweroff.svg"> <?= $lang["logout"] ?></a>
            </div>
          </div>
        <?php else: ?>
          <a href="index.php?force_login=1"><img class="icon" alt="<?= $lang["login"] ?>" src="images/key.svg"> <?= $lang["login"] ?></a>
        <?php endif; ?>

        <div id="user">
          <?php if (!empty($usersArr)): ?>
            <br><u><?= $lang["user"] ?></u><br>
            <form>
              <select name="user" onchange="selectUser(this);">
                <option value="0"><?= $lang["suser"] ?></option>
                <?php foreach ($usersArr as $aUser): ?>
                  <option <?= ($aUser->id == $displayUserId) ? "selected " : "" ?>value="<?= $aUser->id ?>"><?= $aUser->login ?></option>
                <?php endforeach; ?>
              </select>
            </form>
          <?php endif; ?>
        </div>

        <div id="track">
          <u><?= $lang["track"] ?></u><br>
          <form>
            <select name="track" onchange="selectTrack(this)">
              <?php foreach ($tracksArr as $aTrack): ?>
                <option value="<?= $aTrack->id ?>"><?= $aTrack->name ?></option>
              <?php endforeach; ?>
            </select>
            <input id="latest" type="checkbox" onchange="toggleLatest();"> <?= $lang["latest"] ?><br>
            </form>
          <input type="checkbox" onchange="autoReload();"><?= $lang["autoreload"] ?> (<a href="javascript:void(0);" onclick="setTime();"><span id="auto"><?= $config::$interval ?></span></a> s)<br>
          <a href="javascript:void(0);" onclick="loadTrack(userid, trackid, 0);"><?= $lang["reload"] ?></a><br>
        </div>

        <div id="summary"></div>

        <div id="other">
          <a href="javascript:void(0);" onclick="toggleChart();"><?= $lang["chart"] ?></a>
        </div>

        <div id="api">
          <u><?= $lang["api"] ?></u><br>
          <form>
            <select name="api" onchange="loadMapAPI(this.options[this.selectedIndex].value);">
              <option value="gmaps"<?= ($config::$mapapi == "gmaps") ? " selected" : "" ?>>Google Maps</option>
              <option value="openlayers"<?= ($config::$mapapi == "openlayers") ? " selected" : "" ?>>OpenLayers</option>
            </select>
          </form>
        </div>

        <div id="lang">
          <u><?= $lang["language"] ?></u><br>
          <form>
            <select name="units" onchange="setLang(this.options[this.selectedIndex].value);">
              <?php asort($langsArr); ?>
              <?php foreach ($langsArr as $langCode => $langName): ?>
                <option value="<?= $langCode ?>"<?= ($config::$lang == $langCode) ? " selected" : "" ?>><?= $langName ?></option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>

        <div id="units">
          <u><?= $lang["units"] ?></u><br>
          <form>
            <select name="units" onchange="setUnits(this.options[this.selectedIndex].value);">
              <option value="metric"<?= ($config::$units == "metric") ? " selected" : "" ?>><?= $lang["metric"] ?></option>
              <option value="imperial"<?= ($config::$units == "imperial") ? " selected" : "" ?>><?= $lang["imperial"] ?></option>
            </select>
          </form>
        </div>

        <div id="export">
          <u><?= $lang["download"] ?></u><br>
          <a href="javascript:void(0);" onclick="load('kml', userid, trackid);">kml</a><br>
          <a href="javascript:void(0);" onclick="load('gpx', userid, trackid);">gpx</a><br>
        </div>

        <?php if ($user->isAdmin): ?>
          <div id="admin_menu">
            <u><?= $lang["adminmenu"] ?></u><br>
            <a href="javascript:void(0);" onclick="addUser()"><?= $lang["adduser"] ?></a><br>
          </div>
        <?php endif; ?>

      </div>
      <div id="menu-close" onclick="toggleMenu();">»</div>
      <div id="footer"><a target="_blank" href="https://github.com/bfabiszewski/ulogger-server"><span class="mi">μ</span>logger</a> <?= $config::$version ?></div>
    </div>

    <div id="main">
      <div id="map-canvas"></div>
      <div id="bottom">
        <div id="chart"></div>
        <div id="close"><a href="javascript:void(0);" onclick="toggleChart(0);"><?= $lang["close"] ?></a></div>
      </div>
    </div>

  </body>
</html>