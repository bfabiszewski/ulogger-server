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
  require_once(ROOT_DIR . "/helpers/lang.php");
  require_once(ROOT_DIR . "/helpers/config.php");

  $auth_error = uUtils::getBool('auth_error', false);

  $config = uConfig::getInstance();
  $lang = (new uLang($config))->getStrings();

?>
<!DOCTYPE html>
<html lang="<?= $config->lang ?>">
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
        <label for="login-user"><?= $lang["username"] ?></label><br>
        <input id="login-user" type="text" name="user" required><br>
        <label for="login-pass"><?=  $lang["password"] ?></label><br>
        <input id="login-pass" type="password" name="pass" required><br>
        <br>
        <input type="submit" value="<?= $lang["login"] ?>">
        <input type="hidden" name="action" value="auth">
        <?php if (!$config->requireAuthentication): ?>
          <div id="cancel"><a href="<?= BASE_URL ?>"><?= $lang["cancel"] ?></a></div>
        <?php endif; ?>
      </form>
      <?php if ($auth_error): ?>
        <div id="error"><?= $lang["authfail"] ?></div>
      <?php endif; ?>
    </div>
  </body>
</html>