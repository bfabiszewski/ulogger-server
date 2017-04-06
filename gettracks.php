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
 
require_once("auth.php");

$userid = ((isset($_REQUEST["userid"]) && is_numeric($_REQUEST["userid"])) ? $_REQUEST["userid"] : 0);

if ($userid) {
  $query = $mysqli->prepare("SELECT id, name FROM tracks WHERE user_id=? ORDER BY id DESC");
  $query->bind_param('i', $userid);
  $query->execute();
  $query->bind_result($trackid, $trackname);

  header("Content-type: text/xml");
  $xml = new XMLWriter();
  $xml->openURI("php://output");
  $xml->startDocument("1.0");
  $xml->setIndent(true);
  $xml->startElement('root');

  while ($query->fetch()) {
    $xml->startElement("track");
      $xml->writeElement("trackid", $trackid);
      $xml->writeElement("trackname", $trackname);
    $xml->endElement();
  }

  $xml->endElement();
  $xml->endDocument();
  $xml->flush();

  $query->free_result();
}

$mysqli->close();
?>
