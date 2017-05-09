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

  define("headless", true);
  require_once(dirname(__DIR__) . "/auth.php"); // sets $user

  /**
   * Exit with xml response
   * @param boolean $isError Error if true
   * @param string $errorMessage Optional error message
   */
  function exitWithStatus($isError, $errorMessage = NULL) {
    header("Content-type: text/xml");
    $xml = new XMLWriter();
    $xml->openURI("php://output");
    $xml->startDocument("1.0");
    $xml->setIndent(true);
    $xml->startElement('root');
      $xml->writeElement("error", (int) $isError);
    if ($isError) {
      $xml->writeElement("message", $errorMessage);
    }
    $xml->endElement();
    $xml->endDocument();
    $xml->flush();
    exit;
  }

  $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : NULL;
  $login = isset($_REQUEST['login']) ? trim($_REQUEST['login']) : NULL;
  $pass = isset($_REQUEST['pass']) ? $_REQUEST['pass'] : NULL;
  if (!$user->isAdmin || empty($action) || empty($login) || $user->login == $login) {
    exitWithStatus(true, $lang["servererror"]);
  }

  $aUser = new uUser($login);

  switch ($action) {
    case 'add':
      if ($aUser->isValid) {
        exitWithStatus(true, $lang["userexists"]);
      }
      if (empty($pass) || $aUser->add($login, $pass) === false) {
        exitWithStatus(true, $lang["servererror"]);
      }
      break;

    case 'update':
      // update password
      if (empty($pass) || $aUser->setPass($pass) === false) {
        exitWithStatus(true, $lang["servererror"]);
      }
      break;

    case 'delete':
      if ($aUser->delete() === false) {
        exitWithStatus(true, $lang["servererror"]);
      }
      break;

    default:
      exitWithStatus(true, $lang["servererror"]);
      break;
  }

  exitWithStatus(false);

?>