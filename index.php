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

  require_once(__DIR__ . "/auth.php"); // sets $user
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

?>
<!DOCTYPE html>
<html>
  <head>
    <title><?= $lang["title"] ?></title>
    <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <link rel="apple-touch-icon" sizes="180x180" href="icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" href="icons/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="icons/favicon-16x16.png" sizes="16x16">
    <link rel="manifest" href="manifest.json">
    <link rel="mask-icon" href="icons/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="shortcut icon" href="icons/favicon.ico">
    <meta name="msapplication-config" content="browserconfig.xml">
    <meta name="theme-color" content="#ffffff">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i&amp;subset=cyrillic" rel="stylesheet">
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
      var admin = <?= json_encode($user->isAdmin) ?>;
      var auth = '<?= ($user->isValid) ? $user->login : "null" ?>';
      var pass_regex = <?= $config->passRegex() ?>;
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
    <?php if ($user->isValid): ?>
      <script type="text/javascript" src="js/track.js"></script>
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
            <a href="javascript:void(0);" onclick="userMenu()"><img class="icon" alt="<?= $lang["user"] ?>" src="images/user.svg"> <?= htmlspecialchars($user->login) ?></a>
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
            <br><?= $lang["user"] ?><br>
            <form>
              <select name="user" onchange="selectUser(this);">
                <option value="0"><?= $lang["suser"] ?></option>
                <?php foreach ($usersArr as $aUser): ?>
                  <option <?= ($aUser->id == $displayUserId) ? "selected " : "" ?>value="<?= $aUser->id ?>"><?= htmlspecialchars($aUser->login) ?></option>
                <?php endforeach; ?>
              </select>
            </form>
          <?php endif; ?>
        </div>

        <div id="track">
          <?= $lang["track"] ?><br>
          <form>
            <select name="track" onchange="selectTrack(this)">
              <?php foreach ($tracksArr as $aTrack): ?>
                <option value="<?= $aTrack->id ?>"><?= htmlspecialchars($aTrack->name) ?></option>
              <?php endforeach; ?>
            </select>
            <input id="latest" type="checkbox" onchange="toggleLatest();"> <?= $lang["latest"] ?><br>
            <input type="checkbox" onchange="autoReload();"> <?= $lang["autoreload"] ?> (<a href="javascript:void(0);" onclick="setTime();"><span id="auto"><?= $config::$interval ?></span></a> s)<br>
          </form>
          <a href="javascript:void(0);" onclick="loadTrack(userid, trackid, 0);"> <?= $lang["reload"] ?></a><br>
        </div>

        <div id="summary"></div>

        <div id="other">
          <a href="javascript:void(0);" onclick="toggleChart();"><?= $lang["chart"] ?></a>
        </div>

        <div id="api">
          <?= $lang["api"] ?><br>
          <form>
            <select name="api" onchange="loadMapAPI(this.options[this.selectedIndex].value);">
              <option value="gmaps"<?= ($config::$mapapi == "gmaps") ? " selected" : "" ?>>Google Maps</option>
              <option value="openlayers"<?= ($config::$mapapi == "openlayers") ? " selected" : "" ?>>OpenLayers</option>
            </select>
          </form>
        </div>

        <div id="lang">
          <?= $lang["language"] ?><br>
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
          <?= $lang["units"] ?><br>
          <form>
            <select name="units" onchange="setUnits(this.options[this.selectedIndex].value);">
              <option value="metric"<?= ($config::$units == "metric") ? " selected" : "" ?>><?= $lang["metric"] ?></option>
              <option value="imperial"<?= ($config::$units == "imperial") ? " selected" : "" ?>><?= $lang["imperial"] ?></option>
            </select>
          </form>
        </div>

        <div id="export">
          <u><?= $lang["download"] ?></u><br>
          <a class="menulink" href="javascript:void(0);" onclick="load('kml', userid, trackid);">kml</a>
          <a class="menulink" href="javascript:void(0);" onclick="load('gpx', userid, trackid);">gpx</a>
        </div>

        <?php if ($user->isValid): ?>
          <div id="admin_menu">
            <u><?= $lang["adminmenu"] ?></u><br>
            <?php if ($user->isAdmin): ?>
              <a class="menulink" href="javascript:void(0);" onclick="addUser()"><?= $lang["adduser"] ?></a>
              <a class="menulink" href="javascript:void(0);" onclick="editUser()"><?= $lang["edituser"] ?></a>
            <?php endif; ?>
            <a class="menulink" href="javascript:void(0);" onclick="editTrack()"><?= $lang["edittrack"] ?></a>
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