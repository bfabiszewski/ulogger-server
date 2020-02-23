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

  require_once(__DIR__ . '/helpers/auth.php');
  require_once(ROOT_DIR . '/helpers/config.php');
  require_once(ROOT_DIR . '/helpers/position.php');
  require_once(ROOT_DIR . '/helpers/track.php');
  require_once(ROOT_DIR . '/helpers/utils.php');
  require_once(ROOT_DIR . '/helpers/lang.php');

  $login = uUtils::postString('user');
  $pass = uUtils::postPass('pass');
  $action = uUtils::postString('action');

  $config = uConfig::getInstance();
  $lang = (new uLang($config))->getStrings();
  $langsArr = uLang::getLanguages();

  $auth = new uAuth();
  if ($action === 'auth') {
    $auth->checkLogin($login, $pass);
  }

  if ($action === 'auth' && !$auth->isAuthenticated()) {
    $auth->exitWithRedirect('login.php?auth_error=1');
  }
  if ($config->requireAuthentication && !$auth->isAuthenticated()) {
    $auth->exitWithRedirect('login.php');
  }

?>
<!DOCTYPE html>
<html lang="<?= $config->lang ?>">
  <head>
    <title><?= $lang['title'] ?></title>
    <?php include('meta.php'); ?>
    <script src="js/dist/bundle.js"></script>
  </head>

  <body>
    <div id="container">
      <div id="menu">
        <div id="menu-content">

          <?php if ($auth->isAuthenticated()): ?>
            <div>
              <a data-bind="onShowUserMenu"><img class="icon" alt="<?= $lang['user'] ?>" src="images/user.svg"> <?= htmlspecialchars($auth->user->login) ?></a>
              <div id="user-menu" class="menu-hidden">
                <a id="user-pass" data-bind="onPasswordChange"><img class="icon" alt="<?= $lang['changepass'] ?>" src="images/lock.svg"> <?= $lang['changepass'] ?></a>
                <a href="utils/logout.php"><img class="icon" alt="<?= $lang['logout'] ?>" src="images/poweroff.svg"> <?= $lang['logout'] ?></a>
              </div>
            </div>
          <?php else: ?>
            <a href="login.php"><img class="icon" alt="<?= $lang['login'] ?>" src="images/key.svg"> <?= $lang['login'] ?></a>
          <?php endif; ?>

          <div class="section">
            <label for="user"><?= $lang['user'] ?></label>
            <select id="user" data-bind="currentUserId" name="user"></select>
          </div>

          <div class="section">
            <label for="track"><?= $lang['track'] ?></label>
            <select id="track" data-bind="currentTrackId" name="track"></select>
            <input id="latest" type="checkbox" data-bind="showLatest"> <label for="latest"><?= $lang['latest'] ?></label><br>
            <input id="auto-reload" type="checkbox" data-bind="autoReload"> <label for="auto-reload"><?= $lang['autoreload'] ?></label> (<a id="set-interval" data-bind="onSetInterval"><span id="interval" data-bind="interval"><?= $config->interval ?></span></a> s)<br>
            <a id="force-reload" data-bind="onReload"> <?= $lang['reload'] ?></a><br>
          </div>

          <div id="summary" class="section" data-bind="summary"></div>

          <div id="other" class="section">
            <a id="altitudes" data-bind="onChartToggle"><?= $lang['chart'] ?></a>
          </div>

          <div>
            <label for="api"><?= $lang['api'] ?></label>
            <select id="api" name="api" data-bind="mapApi">
              <option value="gmaps"<?= ($config->mapApi === 'gmaps') ? ' selected' : '' ?>>Google Maps</option>
              <option value="openlayers"<?= ($config->mapApi === 'openlayers') ? ' selected' : '' ?>>OpenLayers</option>
            </select>
          </div>

          <div>
            <label for="lang"><?= $lang['language'] ?></label>
            <select id="lang" name="lang" data-bind="lang">
              <?php foreach ($langsArr as $langCode => $langName): ?>
                <option value="<?= $langCode ?>"<?= ($config->lang === $langCode) ? ' selected' : '' ?>><?= $langName ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="section">
            <label for="units"><?= $lang['units'] ?></label>
            <select id="units" name="units" data-bind="units">
              <option value="metric"<?= ($config->units === 'metric') ? ' selected' : '' ?>><?= $lang['metric'] ?></option>
              <option value="imperial"<?= ($config->units === 'imperial') ? ' selected' : '' ?>><?= $lang['imperial'] ?></option>
              <option value="nautical"<?= ($config->units === 'nautical') ? ' selected' : '' ?>><?= $lang['nautical'] ?></option>
            </select>
          </div>

          <div class="section">
            <div class="menu-title"><?= $lang['export'] ?></div>
            <a id="export-kml" class="menu-link" data-bind="onExportKml">kml</a>
            <a id="export-gpx" class="menu-link" data-bind="onExportGpx">gpx</a>
          </div>

          <?php if ($auth->isAuthenticated()): ?>
            <div class="section">
              <div id="import" class="menu-title"><?= $lang['import'] ?></div>
              <form id="import-form" enctype="multipart/form-data" method="post">
                <input type="hidden" name="MAX_FILE_SIZE" value="<?= uUtils::getUploadMaxSize() ?>" />
                <input type="file" id="input-file" name="gpx" data-bind="inputFile"/>
              </form>
              <a id="import-gpx" class="menu-link" data-bind="onImportGpx">gpx</a>
            </div>

            <div id="admin-menu">
              <div class="menu-title"><?= $lang['adminmenu'] ?></div>
              <?php if ($auth->isAdmin()): ?>
                <a id="adduser" class="menu-link" data-bind="onConfigEdit"><?= $lang['config'] ?></a>
                <a id="adduser" class="menu-link" data-bind="onUserAdd"><?= $lang['adduser'] ?></a>
                <a id="edituser" class="menu-link" data-bind="onUserEdit"><?= $lang['edituser'] ?></a>
              <?php endif; ?>
              <a id="edittrack" class="menu-link" data-bind="onTrackEdit"><?= $lang['edittrack'] ?></a>
            </div>
          <?php endif; ?>

        </div>
        <div id="menu-button"><a data-bind="onMenuToggle"></a></div>
        <div id="footer"><a target="_blank" href="https://github.com/bfabiszewski/ulogger-server"><span class="mi">μ</span>logger</a> <?= $config->version ?></div>
      </div>

      <div id="main">
        <div id="map-canvas"></div>
        <div id="bottom">
          <div id="chart"></div>
          <a id="chart-close" data-bind="onChartToggle"><?= $lang['close'] ?></a>
        </div>
      </div>

    </div>
  </body>
</html>
