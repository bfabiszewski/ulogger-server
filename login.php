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

  require_once("helpers/auth.php");
  require_once(ROOT_DIR . "/lang.php");
  require_once(ROOT_DIR . "/helpers/config.php");

  $auth_error = isset($_REQUEST['auth_error']) ? (bool) $_REQUEST['auth_error'] : false;

?>
<!DOCTYPE html>
<html>
  <head>
    <title><?= $lang["title"] ?></title>
    <?php include("meta.php"); ?>
    <script type="text/javascript">
      function focus() {
        document.forms[0].elements[0].focus();
      }
    </script>
  </head>
  <body onload="focus()">
    <div id="login">
      <div id="title"><?=  $lang["title"] ?></div>
      <div id="subtitle"><?=  $lang["private"] ?></div>
      <form action="<?= BASE_URL ?>" method="post">
      <?= $lang["username"] ?>:<br>
      <input type="text" name="user"><br>
      <?=  $lang["password"] ?>:<br>
      <input type="password" name="pass"><br>
      <br>
      <input type="submit" value="<?= $lang["login"] ?>">
      <input type="hidden" name="action" value="auth">
      <?php if (!uConfig::$require_authentication): ?>
        <div id="cancel"><a href="<?= BASE_URL ?>"><?= $lang["cancel"] ?></a></div>
      <?php endif; ?>
      </form>
      <div id="error"><?= (($auth_error) ? $lang["authfail"] : "") ?></div>
    </div>
  </body>
</html>