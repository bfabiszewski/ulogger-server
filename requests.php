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
// TrackMe API
// http://forum.xda-developers.com/showpost.php?p=3250539&postcount=2

require_once("config.php");
$user = (isset($_REQUEST['u']) ? $_REQUEST['u'] : "");
$pass = (isset($_REQUEST['p']) ? md5($salt.$_REQUEST['p']) : "");
$requireddb = (isset($_REQUEST['db']) ? $_REQUEST['db'] : 0);
$tripname = (isset($_REQUEST['tn']) ? $_REQUEST['tn'] : "");
$action = (isset($_REQUEST['a']) ? $_REQUEST['a'] : "");

// If the client uses Backitude then define the tripname as user-date
if ($requireddb == 'backitude') {
   $tripname = $user.'-'.date("Ymd");
}
// FIXME what is it for?
elseif ($requireddb<8) {
  //Result:5 Incompatible database.
  quit(5);
}

$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($mysqli->connect_errno) {
  //Result:4 Unable to connect database.
  quit(4);
}

if ((!$user) || (!$pass)){
  //Result:3 User or password not specified.
  quit(3);
}

$query = $mysqli->prepare("SELECT ID,username,password FROM users WHERE username=? LIMIT 1");
$query->bind_param('s', $user);
$query->execute();
$query->store_result();
$query->bind_result($userid, $rec_user, $rec_pass);
$query->fetch();
$num = $query->num_rows;
$query->free_result();
$query->close();
if ($num) {
  if (($user==$rec_user) && ($pass!=$rec_pass)) {
    //Result:1 User correct, invalid password.
    quit(1);
  }
}
else {
  if ($allow_registration) {
    // User unknown, let's create it
    $query = $mysqli->prepare("INSERT INTO users (username,password) VALUES (?,?)");
    $query->bind_param('ss', $user, $pass);
    $query->execute();
    $userid = $mysqli->insert_id;
    $query->close();
    if (!$userid) {
      //Result:2 User did not exist but after being created couldn't be found.
      // Or rather something went wrong while updating database
      quit(2);
    }
  }
  else {
    // User unknown, we don't allow autoregistration
    // Let's use this one:
    //Result:1 User correct, invalid password.
    quit(1);
  }
}

switch($action) {
  // action: noop
  case "noop":
    // test
    quit(0);
    break;

  // action: deletetrip
  case "deletetrip":
    if ($tripname) {
      $sql = "DELETE FROM positions LEFT JOIN trips ON positions.FK_Trips_ID=trips.ID "
            ."WHERE positions.FK_Users_ID=? AND trips.Name=?";
      $query = $mysqli->prepare($sql);
      if ($query) {
        $query->bind_param('is', $userid, $tripname);
        $query->execute();
        $query->close();
      }
      $sql = "DELETE FROM trips WHERE FK_Users_ID=? AND Name=?";
      $query = $mysqli->prepare($sql);
      $query->bind_param('is', $userid, $tripname);
      $query->execute();
      $rows = $mysqli->affected_rows;
      $query->close();
      if ($rows) {
        quit(0);
      }
      else {
        //Result:7 Trip not found
        quit(7);
      }
    }
    else {
      //Result:6 Trip not specified.
      quit(6);
    }
    break;

  // action: addtrip
  case "addtrip":
    if ($tripname) {
      $sql = "INSERT INTO trips (FK_Users_ID,Name) VALUES (?,?)";
      $query = $mysqli->prepare($sql);
      $query->bind_param('is', $userid, $tripname);
      $query->execute();
      $query->close();
    }
    else {
      //Result:6 Trip not specified.
      quit(6);
    }
    break;

  // action: gettriplist
  case "gettriplist":
    $sql = "SELECT a1.Name,(SELECT MIN(a2.DateOccurred) FROM positions a2 "
        ."WHERE a2.FK_Trips_ID=a1.ID) AS startdate "
        ."FROM trips a1 WHERE a1.FK_Users_ID=? ORDER BY Name";
    $query = $mysqli->prepare($sql);
    $query->bind_param('i', $userid);
    $query->execute();
    $query->store_result();
    $query->bind_result($tripname,$startdate);
    $num = $query->num_rows;
    $triplist = array();
    if ($num) {
      while ($query->fetch()) {
        $triplist[] = $tripname."|".$startdate;
      }
    }
    $query->free_result();
    $query->close();
    $param = implode("\n",$triplist);
    quit(0,$param);
    break;

  // action: upload
  case "upload":
    $lat = isset($_REQUEST["lat"]) ? $_REQUEST["lat"] : NULL;
    $long = isset($_REQUEST["long"]) ? $_REQUEST["long"] : NULL;
    // If the client uses Backitude then convert the date into handled format
    if ($requireddb == 'backitude') {
        $dateoccurred = isset($_REQUEST["do"]) ? date("Y-m-d H:i:s",intval($_REQUEST["do"])) : NULL;
    }
    else {
        $dateoccurred = isset($_REQUEST["do"]) ? $_REQUEST["do"] : NULL;
    }
    $altitude = isset($_REQUEST["alt"]) ? $_REQUEST["alt"] : NULL;
    $angle = isset($_REQUEST["ang"]) ? $_REQUEST["ang"] : NULL;
    $speed = isset($_REQUEST["sp"]) ? $_REQUEST["sp"] : NULL;
    $iconname = isset($_REQUEST["iconname"]) ? $_REQUEST["iconname"] : NULL;
    $comments = isset($_REQUEST["comments"]) ? $_REQUEST["comments"] : NULL;
    $imageurl = isset($_REQUEST["imageurl"]) ? $_REQUEST["imageurl"] : NULL;
    $cellid = isset($_REQUEST["cid"]) ? $_REQUEST["cid"] : NULL;
    $signalstrength = isset($_REQUEST["ss"]) ? $_REQUEST["ss"] : NULL;
    $signalstrengthmax = isset($_REQUEST["ssmax"]) ? $_REQUEST["ssmax"] : NULL;
    $signalstrengthmin = isset($_REQUEST["ssmin"]) ? $_REQUEST["ssmin"] : NULL;
    $batterystatus = isset($_REQUEST["bs"]) ? $_REQUEST["bs"] : NULL;
    $uploadss = isset($_REQUEST["upss"]) ? $_REQUEST["upss"] : NULL; // FIXME is it needed?
    $iconid = NULL;
    if ($iconname) {
      $sql = "SELECT ID FROM icons WHERE Name=? LIMIT 1";
      $query = $mysqli->prepare($sql);
      $query->bind_param('s', $iconname);
      $query->execute();
      $query->store_result();
      $query->bind_result($id);
      $query->fetch();
      $num = $query->num_rows;
      $query->free_result();
      $query->close();
      if ($num) {
        $iconid = $id;
      }
    }
    $tripid = NULL; // FIXME: not sure what trips with null id are
    if ($tripname) {
      // get tripid
      $query = $mysqli->prepare("SELECT ID FROM trips WHERE FK_Users_ID=? AND Name=? LIMIT 1");
      $query->bind_param('is', $userid, $tripname);
      $query->execute();
      $query->store_result();
      $query->bind_result($tripid);
      $query->fetch();
      $num = $query->num_rows;
      $query->free_result();
      $query->close();
      if (!$num) {
        // create trip
        $query = $mysqli->prepare("INSERT INTO trips (FK_Users_ID,Name) VALUES (?,?)");
        $query->bind_param('is', $userid, $tripname);
        $query->execute();
        $tripid = $mysqli->insert_id;
        $query->close();
        if (!$tripid) {
          //Result:6 Trip didn't exist and system was unable to create it.
          quit(6);
        }
      }
    }
    $sql = "INSERT INTO positions "
          ."(FK_Users_ID,FK_Trips_ID,Latitude,Longitude,DateOccurred,FK_Icons_ID,"
          ."Speed,Altitude,Comments,ImageURL,Angle,SignalStrength,SignalStrengthMax,"
          ."SignalStrengthMin,BatteryStatus) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $query = $mysqli->prepare($sql);
    $query->bind_param('iiddsiddssdiiii',
            $userid,$tripid,$lat,$long,$dateoccurred,$iconid,
            $speed,$altitude,$comments,$imageurl,$angle,$signalstrength,$signalstrengthmax,
            $signalstrengthmin,$batterystatus);
    $query->execute();
    $query->close();
    if ($mysqli->errno) {
      //Result:7|SQLERROR   Insert statement failed.
      quit(7,$mysqli->error);
    }
    //FIXME Are cellids used in Android client?
    $upcellext = isset($_REQUEST["upcellext"]) ? $_REQUEST["upcellext"] : NULL;
    if ($upcellext==1 && $cellid) {
      $sql = "INSERT INTO cellids (CellID,Latitude,Longitude,SignalStrength,SignalStrengthMax,SignalStrengthMin) "
            ."VALUES (?,?,?,?,?,?)";
      $query = $mysqli->prepare($sql);
      $query->bind_param('sddiii',$cellid,$lat,$long,$signalstrength,$signalstrengthmax,$signalstrengthmin);
      $query->execute();
      $query->close();
      if ($mysqli->errno) {
        //Result:7|SQLERROR   Insert statement failed.
        quit(7,$mysqli->error);
      }
    }
    quit(0);
    break;

  // action: geticonlist
  // action: renametrip
  // action: findclosestbuddy
  // action: delete
  // action: sendemail
  // action: updateimageurl
  // action: findclosestpositionbytime
  // action: findclosestpositionbyposition
  // action: gettripinfo
  // action: gettriphighlights
}

function quit($errno,$param=""){
  print "Result:".$errno.(($param)?"|$param":"");
  exit();
}

$mysqli->close();
?>
