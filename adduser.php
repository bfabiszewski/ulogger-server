<?php
/* μlogger
 *
 * Copyright(C) 2017 Bartek Fabiszewski (www.fabiszewski.net)
 *
 * This is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Library General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public
 * License along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 */
 
  require_once("auth.php");
  
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

  /** 
   * Check if login is allowed
   * @param string $login Login
   */
  function checkUser($login) {
    global $mysqli;
    $sql = "SELECT id FROM users WHERE login = ?";
    $query = $mysqli->prepare($sql);
    $query->bind_param('s', $login);
    $query->execute();
    if ($query->errno) {
      exitWithStatus(true, $query->error);
    }
    $query->store_result();
    if ($query->num_rows) {
      exitWithStatus(true, $lang_userexists);
    }
    $query->free_result();
    $query->close();
  }

  /** 
   * Add new user to database
   * @param string $login Login
   * @param string $hash Password hash
   */
  function insertUser($login, $hash) {
    global $mysqli;
    $sql = "INSERT INTO users (login, password) VALUES (?, ?)";
    $query = $mysqli->prepare($sql);
    $query->bind_param('ss', $login, $hash);
    $query->execute();
    if ($query->errno) {
      exitWithStatus(true, $query->error);
      $isError = false;
    }
    $query->close();
  }

  $login = isset($_REQUEST['login']) ? trim($_REQUEST['login']) : NULL;
  $hash = isset($_REQUEST['pass']) ? password_hash($_REQUEST['pass'], PASSWORD_DEFAULT) : NULL;
  if ($admin && !empty($login) && !empty($hash)) {
    checkUser($login);
    insertUser($login, $hash);
  }
  exitWithStatus(false);
  
?>