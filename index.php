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

  require_once(__DIR__ . "/helpers/auth.php");
  require_once(ROOT_DIR . "/helpers/config.php");
  require_once(ROOT_DIR . "/helpers/position.php");
  require_once(ROOT_DIR . "/helpers/track.php");
  require_once(ROOT_DIR . "/helpers/utils.php");
  require_once(ROOT_DIR . "/helpers/lang.php");

  $login = uUtils::postString('user');
  $pass = uUtils::postPass('pass');
  $action = uUtils::postString('action');

  $lang = (new uLang(uConfig::$lang))->getStrings();
  $langsArr = uLang::getLanguages();
  asort($langsArr);

  $auth = new uAuth();
  if ($action == "auth") {
    $auth->checkLogin($login, $pass);
  }

  if (!$auth->isAuthenticated() && $action == "auth") {
    $auth->exitWithRedirect("login.php?auth_error=1");
  }
  if (!$auth->isAuthenticated() && uConfig::$require_authentication) {
    $auth->exitWithRedirect("login.php");
  }

  $displayUserId = NULL;
  $usersArr = [];
  if ($auth->isAdmin() || uConfig::$public_tracks) {
    // public access or admin user
    // get last position user
    $lastPosition = uPosition::getLast();
    if ($lastPosition->isValid) {
      // display track of last position user
      $displayUserId = $lastPosition->userId;
    }
    // populate users array (for <select>)
    $usersArr = uUser::getAll();
  } else if ($auth->isAuthenticated()) {
    // display track of authenticated user
    $displayUserId = $auth->user->id;
  }

  $tracksArr = uTrack::getAll($displayUserId);
  if (!empty($tracksArr)) {
    // get id of the latest track
    $displayTrackId = $tracksArr[0]->id;
  } else {
    $tracksArr = [];
    $displayTrackId = NULL;
  }

?>
<!DOCTYPE html>
<html lang="<?= uConfig::$lang ?>">
  <head>
    <title><?= $lang["title"] ?></title>
    <?php include("meta.php"); ?>
    <script type="module" src="js/ulogger.js"></script>
      <!--    <script src="dist/ulogger.js"></script>-->
  </head>

  <body>
    <div id="menu">
      <div id="menu-content">

        <?php if ($auth->isAuthenticated()): ?>
          <div>
            <a id="user-menu"><img class="icon" alt="<?= $lang["user"] ?>" src="images/user.svg"> <?= htmlspecialchars($auth->user->login) ?></a>
            <div id="user-dropdown">
              <a id="user-pass"><img class="icon" alt="<?= $lang["changepass"] ?>" src="images/lock.svg"> <?= $lang["changepass"] ?></a>
              <a href="utils/logout.php"><img class="icon" alt="<?= $lang["logout"] ?>" src="images/poweroff.svg"> <?= $lang["logout"] ?></a>
            </div>
          </div>
        <?php else: ?>
          <a href="login.php"><img class="icon" alt="<?= $lang["login"] ?>" src="images/key.svg"> <?= $lang["login"] ?></a>
        <?php endif; ?>

        <div class="section">
          <?php if (!empty($usersArr)): ?>
            <label for="user"><?= $lang["user"] ?></label>
            <select id="user" name="user">
              <option value="0" disabled><?= $lang["suser"] ?></option>
              <?php foreach ($usersArr as $aUser): ?>
                <option <?= ($aUser->id == $displayUserId) ? "selected " : "" ?>value="<?= $aUser->id ?>"><?= htmlspecialchars($aUser->login) ?></option>
              <?php endforeach; ?>
              </select>
          <?php endif; ?>
        </div>

        <div class="section">
          <label for="track"><?= $lang["track"] ?></label>
          <select id="track" name="track">
            <?php foreach ($tracksArr as $aTrack): ?>
              <option value="<?= $aTrack->id ?>"><?= htmlspecialchars($aTrack->name) ?></option>
            <?php endforeach; ?>
          </select>
          <input id="latest" type="checkbox"> <label for="latest"><?= $lang["latest"] ?></label><br>
          <input id="auto-reload" type="checkbox"> <label for="auto-reload"><?= $lang["autoreload"] ?></label> (<a id="set-interval"><span id="interval"><?= uConfig::$interval ?></span></a> s)<br>
          <a id="force-reload"> <?= $lang["reload"] ?></a><br>
        </div>

        <div id="summary" class="section"></div>

        <div id="other" class="section">
          <a id="altitudes"><?= $lang["chart"] ?></a>
        </div>

        <div>
          <label for="api"><?= $lang["api"] ?></label>
          <select id="api" name="api">
            <option value="gmaps"<?= (uConfig::$mapapi == "gmaps") ? " selected" : "" ?>>Google Maps</option>
            <option value="openlayers"<?= (uConfig::$mapapi == "openlayers") ? " selected" : "" ?>>OpenLayers</option>
          </select>
        </div>

        <div>
          <label for="lang"><?= $lang["language"] ?></label>
          <select id="lang" name="lang">
            <?php foreach ($langsArr as $langCode => $langName): ?>
              <option value="<?= $langCode ?>"<?= (uConfig::$lang == $langCode) ? " selected" : "" ?>><?= $langName ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="section">
          <label for="units"><?= $lang["units"] ?></label>
          <select id="units" name="units">
            <option value="metric"<?= (uConfig::$units == "metric") ? " selected" : "" ?>><?= $lang["metric"] ?></option>
            <option value="imperial"<?= (uConfig::$units == "imperial") ? " selected" : "" ?>><?= $lang["imperial"] ?></option>
            <option value="nautical"<?= (uConfig::$units == "nautical") ? " selected" : "" ?>><?= $lang["nautical"] ?></option>
          </select>
        </div>

        <div class="section">
          <div class="menu-title"><?= $lang["export"] ?></div>
          <a id="export-kml" class="menu-link">kml</a>
          <a id="export-gpx" class="menu-link">gpx</a>
        </div>

        <?php if ($auth->isAuthenticated()): ?>
          <div class="section">
            <div id="import" class="menu-title"><?= $lang["import"] ?></div>
            <form id="import-form" enctype="multipart/form-data" method="post">
              <input type="hidden" name="MAX_FILE_SIZE" value="<?= uUtils::getUploadMaxSize() ?>" />
              <input type="file" id="input-file" name="gpx" />
            </form>
            <a id="import-gpx" class="menu-link">gpx</a>
          </div>

          <div id="admin-menu">
            <div class="menu-title"><?= $lang["adminmenu"] ?></div>
            <?php if ($auth->isAdmin()): ?>
              <a id="adduser" class="menu-link"><?= $lang["adduser"] ?></a>
              <a id="edituser" class="menu-link"><?= $lang["edituser"] ?></a>
            <?php endif; ?>
            <a id="edittrack" class="menu-link"><?= $lang["edittrack"] ?></a>
          </div>
        <?php endif; ?>

      </div>
      <div id="menu-close">»</div>
      <div id="footer"><a target="_blank" href="https://github.com/bfabiszewski/ulogger-server"><span class="mi">μ</span>logger</a> <?= uConfig::$version ?></div>
    </div>

    <div id="main">
      <div id="map-canvas"></div>
      <div id="bottom">
        <div id="chart"></div>
        <div id="chart-close"><?= $lang["close"] ?></div>
      </div>
    </div>

  </body>
</html>
