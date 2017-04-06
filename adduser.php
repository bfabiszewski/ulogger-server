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
 
  require_once("auth.php"); // sets $mysqli, $user
  
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

  $login = isset($_REQUEST['login']) ? trim($_REQUEST['login']) : NULL;
  $hash = isset($_REQUEST['pass']) ? password_hash($_REQUEST['pass'], PASSWORD_DEFAULT) : NULL;
  if ($user->isAdmin && !empty($login) && !empty($hash)) {
    $newUser = new uUser($login);
    if ($newUser->isValid) {
      exitWithStatus(true, $lang_userexists);
    }
    if ($newUser->add($login, $hash) === false) {
      exitWithStatus(true, $mysqli->error);
    }
  }
  exitWithStatus(false);

?>