<?php
/* phpTrackme
 *
 * Copyright(C) 2013 Bartek Fabiszewski (www.fabiszewski.net)
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

$userid = ((isset($_REQUEST["userid"]) && is_numeric($_REQUEST["userid"])) ? $_REQUEST["userid"] : 0);

if ($userid) {
  $query = $mysqli->prepare("SELECT ID,Name FROM trips WHERE FK_Users_ID=? ORDER BY ID DESC");
  $query->bind_param('i', $userid);
  $query->execute();
  $query->bind_result($trackid,$trackname);

  header("Content-type: text/xml");
  $xml = new XMLWriter();
  $xml->openURI("php://output");
  $xml->startDocument("1.0");
  $xml->setIndent(true);
  $xml->startElement('root');

  while ($query->fetch()) {
    $xml->startElement("trip");
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
