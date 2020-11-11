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
require_once(ROOT_DIR . "/helpers/config.php");
require_once(ROOT_DIR . "/helpers/utils.php");
require_once(ROOT_DIR . "/helpers/lang.php");

$auth = new uAuth();
$config = uConfig::getInstance();
$lang = (new uLang($config))->getStrings();

if (!$auth->isAuthenticated()) {
  $auth->sendUnauthorizedHeader();
  uUtils::exitWithError($lang["notauthorized"]);
}

$login = uUtils::postString('login');
$oldpass = uUtils::postPass('oldpass');
$pass = uUtils::postPass('pass');
// FIXME: strings need to be localized
if (empty($pass)) {
  uUtils::exitWithError($lang["passempty"]);
}
if (!$config->validPassStrength($pass)) {
  uUtils::exitWithError($lang["passstrengthwarn"]);
}
if (empty($login)) {
  uUtils::exitWithError($lang["loginempty"]);
}
if ($auth->user->login === $login) {
  // current user
  $passUser = $auth->user;
  if (!$passUser->validPassword($oldpass)) {
    uUtils::exitWithError($lang["oldpassinvalid"]);
  }
} else if ($auth->isAdmin()) {
  // different user, only admin
  $passUser = new uUser($login);
  if (!$passUser->isValid) {
    uUtils::exitWithError($lang["userunknown"]);
  }
} else {
  uUtils::exitWithError($lang["notauthorized"]);
}
if ($passUser->setPass($pass) === false) {
  uUtils::exitWithError($lang["servererror"]);
}
$auth->updateSession();
uUtils::exitWithSuccess();

?>