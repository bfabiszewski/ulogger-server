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

  require_once(dirname(__DIR__) . "/auth.php"); // sets $mysqli, $user

  /**
   * Exit with error message
   *
   * @param string $errorMessage Message
   */
  function exitWithError($errorMessage) {
    return exitWithStatus(true, $errorMessage);
  }

  /**
   * Exit with xml response
   * @param boolean $isError Error if true
   * @param string $errorMessage Optional error message
   */
  function exitWithStatus($isError = false, $errorMessage = NULL) {
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
    $mysqli->close();
    exit;
  }

  $login = isset($_REQUEST['login']) ? trim($_REQUEST['login']) : NULL;
  $oldpass = isset($_REQUEST['oldpass']) ? $_REQUEST['oldpass'] : NULL;
  $pass = isset($_REQUEST['pass']) ? $_REQUEST['pass'] : NULL;
  if (empty($pass)) {
    exitWithError("Empty password");
  }
  if ($user->isAdmin && !empty($login)) {
    // different user, only admin
    $passUser = new uUser($login);
    if (!$passUser->valid) {
      exitWithError("User unknown");
    }
  } else {
    // current user
    $passUser = $user;
    if (!$passUser->validPassword($oldpass)) {
      exitWithError("Wrong old password");
    }
  }
  if ($passUser->setPass($pass) === false) {
    exitWithError("Server error");
  }

  exitWithStatus();

?>