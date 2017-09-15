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

  require_once(dirname(__DIR__) . "/helpers/auth.php");
  require_once(ROOT_DIR . "/lang.php");
  require_once(ROOT_DIR . "/helpers/utils.php");

  $auth = new uAuth();

  $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : NULL;
  $login = isset($_REQUEST['login']) ? trim($_REQUEST['login']) : NULL;
  $pass = isset($_REQUEST['pass']) ? $_REQUEST['pass'] : NULL;
  if (!$auth->isAuthenticated() || !$auth->isAdmin() || $auth->user->login == $login || empty($action) || empty($login)) {
    uUtils::exitWithError($lang["servererror"]);
  }

  $aUser = new uUser($login);

  switch ($action) {
    case 'add':
      if ($aUser->isValid) {
        uUtils::exitWithError($lang["userexists"]);
      }
      if (empty($pass) || uUser::add($login, $pass) === false) {
        uUtils::exitWithError($lang["servererror"]);
      }
      break;

    case 'update':
      // update password
      if (empty($pass) || $aUser->setPass($pass) === false) {
        uUtils::exitWithError($lang["servererror"]);
      }
      break;

    case 'delete':
      if ($aUser->delete() === false) {
        uUtils::exitWithError($lang["servererror"]);
      }
      break;

    default:
      uUtils::exitWithError($lang["servererror"]);
      break;
  }

  uUtils::exitWithSuccess();

?>