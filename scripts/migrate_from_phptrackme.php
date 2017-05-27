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

/* This script imports data from old phpTrackme database scheme.
 *
 * However, as μlogger uses more secure password storage methods,
 * it is impossible to convert old password hashes to the new format.
 * Administrator will have to fill in user passwords manually.
 * Alternatively authentication code could be modify in order to
 * temporarily accept old hashes and convert it as users log in.
 * It should be pretty simple, but this is not a top priority
 * for this small project.
 */

// this script is disabled by default. Change below to true before running.
$enabled = false;

// path to root folder of phpTrackme
$phpTrackmePath = "../../phpTrackme";

// path to root folder of μlogger
$uloggerPath = "..";


/* -------------------------------------------- */
/* no user modifications should be needed below */

if ($enabled == false) {
  echo "Script is disabled\n";
  exit(1);
}
$path = realpath(dirname(__FILE__));
if (!empty($phpTrackmePath) && $phpTrackmePath[0] == ".") {
  $phpTrackmePath = $path . "/" . $phpTrackmePath;
}
$phpTrackmeConfig = $phpTrackmePath . "/config.php";
if (!is_readable($phpTrackmeConfig)) {
  echo "Can't find phpTrackme config file: $phpTrackmeConfig\n";
  exit(1);
}
include ($phpTrackmeConfig);
$pt_dbhost = $dbhost;
$pt_dbuser = $dbuser;
$pt_dbpass = $dbpass;
$pt_dbname = $dbname;
$pt_mysqli = new mysqli($pt_dbhost, $pt_dbuser, $pt_dbpass, $pt_dbname);
$pt_mysqli->set_charset("utf8");
if ($pt_mysqli->connect_errno) {
  echo "Can't connect to $pt_dbname database: (" . $pt_mysqli->errno . ") " . $pt_mysqli->error . "\n";
  exit(1);
}
if (!empty($uloggerPath) && $uloggerPath[0] == ".") {
  $uloggerPath = $path . "/" . $uloggerPath;
}
$uloggerConfig = $uloggerPath . "/config.php";
if (!is_readable($uloggerConfig)) {
  echo "Can't find μlogger config fiel: $uloggerConfige\n";
  exit(1);
}
include ($uloggerConfig);
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
$mysqli->set_charset("utf8");
if ($mysqli->connect_errno) {
  echo "Can't connect to $dbname database : (" . $mysqli->errno . ") " . $mysqli->error . "\n";
  exit(1);
}
$prefix = preg_replace('/[^a-z0-9_]/i', '', $dbprefix);
$tPositions = $prefix . "positions";
$tTracks = $prefix . "tracks";
$tUsers = $prefix . "users";

// import data
if (!$users_result = $pt_mysqli->query("SELECT * FROM users ORDER BY ID")) {
  echo "Query failed\n";
  exit(1);
}

if (!($user_insert = $mysqli->prepare("INSERT INTO `$tUsers` (login, password) VALUES (?, ?)"))) {
  echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "\n";
  exit(1);
}
$pt_user = null;
$pt_pass = null;
if (!$user_insert->bind_param("ss", $pt_user, $pt_pass)) {
  echo "Binding parameters failed: (" . $user_insert->errno . ") " . $user_insert->error . "\n";
  exit(1);
}

while ($user = $users_result->fetch_assoc()) {
  $pt_user = $user['username'];
  $pt_pass = $user['password'];
  $pt_id = $user['ID'];
  if (!$user_insert->execute()) {
    echo "Execute failed: (" . $user_insert->errno . ") " . $user_insert->error . "\n";
    exit(1);
  }
  $user_id = $user_insert->insert_id;
  process_user_tracks($user_id);
}
$users_result->close();
$user_insert->close();

$mysqli->close();
$pt_mysqli->close();
echo "Import finished successfully\n";
exit(0);

/* Helper functions */

/** Import tracks metadata for given user
 * @param $user_id User id
 */
function process_user_tracks($user_id) {
  global $pt_mysqli, $mysqli;
  $sql = "SELECT ID, Name, Comments FROM trips WHERE FK_Users_ID = ? ORDER BY ID";
  if (!($tracks_select = $pt_mysqli->prepare($sql))) {
    echo "Prepare failed: (" . $pt_mysqli->errno . ") " . $pt_mysqli->error . "\n";
    exit(1);
  }
  if (!$tracks_select->bind_param('i', $user_id)) {
    echo "Binding parameters failed: (" . $tracks_select->errno . ") " . $tracks_select->error . "\n";
    exit(1);
  }
  if (!$tracks_select->bind_result($pt_id, $pt_name, $pt_comment)) {
    echo "Binding parameters failed: (" . $tracks_select->errno . ") " . $tracks_select->error . "\n";
    exit(1);
  }
  if (!$tracks_select->execute()) {
    echo "Execute failed: (" . $tracks_select->errno . ") " . $tracks_select->error . "\n";
    exit(1);
  }
  $tracks_select->store_result();
  if (!($track_insert = $mysqli->prepare("INSERT INTO `$tTracks` (user_id, name, comment) VALUES (?, ?, ?)"))) {
    echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "\n";
    exit(1);
  }
  $pt_name = null;
  $pt_comment = null;
  if (!$track_insert->bind_param("iss", $user_id, $pt_name, $pt_comment)) {
    echo "Binding parameters failed: (" . $track_insert->errno . ") " . $track_insert->error . "\n";
    exit(1);
  }
  while ($tracks_select->fetch()) {
    if (!$track_insert->execute()) {
      echo "Execute failed: (" . $track_insert->errno . ") " . $track_insert->error . "\n";
      exit(1);
    }
    $track_id = $track_insert->insert_id;
    process_track($user_id, $pt_id, $track_id);
  }
  $tracks_select->free_result();
  $tracks_select->close();
  $track_insert->close();
}

/** Import positions for given track
 * @param $user_id User id
 * @param $old_id Old database track id
 * @param $new_id New database track id
 */
function process_track($user_id, $old_id, $new_id) {
  global $pt_mysqli, $mysqli;
  $sql = "SELECT Latitude, Longitude, Altitude, Speed, Angle, UNIX_TIMESTAMP(DateOccurred), Comments FROM pt_positions WHERE FK_Users_ID = ? AND FK_Trips_ID = ? ORDER BY DateOccurred, ID";
  if (!($pos_select = $pt_mysqli->prepare($sql))) {
    echo "Prepare failed: (" . $pt_mysqli->errno . ") " . $pt_mysqli->error . "\n";
    exit(1);
  }
  if (!$pos_select->bind_param('ii', $user_id, $old_id)) {
    echo "Binding parameters failed: (" . $pos_select->errno . ") " . $pos_select->error . "\n";
    exit(1);
  }
  if (!$pos_select->bind_result($lat, $lon, $altitude, $speed, $bearing, $timestamp, $comment)) {
    echo "Binding parameters failed: (" . $pos_select->errno . ") " . $pos_select->error . "\n";
    exit(1);
  }
  if (!$pos_select->execute()) {
    echo "Execute failed: (" . $pos_select->errno . ") " . $pos_select->error . "\n";
    exit(1);
  }
  $pos_select->store_result();
  if (!($pos_insert = $mysqli->prepare("INSERT INTO `$tPositions` (FROM_UNIXTIME(time), user_id, track_id, latitude, longitude, altitude, speed, bearing, accuracy, provider, comment, image_id)
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"))) {
    echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "\n";
    exit(1);
  }
  $provider = $comment = $timestamp = $imageid = null;
  $lat = $lon = 0;
  $altitude = $speed = $bearing = $accuracy = null;

  if (!$pos_insert->bind_param('siiddddddssi',
            $timestamp, $user_id, $new_id, $lat, $lon, $altitude, $speed, $bearing, $accuracy, $provider, $comment, $imageid)) {
    echo "Binding parameters failed: (" . $pos_insert->errno . ") " . $pos_insert->error . "\n";
    exit(1);
  }
  while ($pos_select->fetch()) {
    $provider = null;
    if (!$pos_insert->execute()) {
      echo "Execute failed: (" . $pos_insert->errno . ") " . $pos_insert->error . "\n";
      exit(1);
    }
  }
  $pos_insert->close();
  $pos_select->free_result();
  $pos_select->close();
}
?>