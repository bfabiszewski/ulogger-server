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
require_once(ROOT_DIR . "/helpers/track.php");
require_once(ROOT_DIR . "/helpers/position.php");
require_once(ROOT_DIR . "/helpers/utils.php");

$uploadErrors[UPLOAD_ERR_INI_SIZE] = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
$uploadErrors[UPLOAD_ERR_FORM_SIZE] = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
$uploadErrors[UPLOAD_ERR_PARTIAL] = "The uploaded file was only partially uploaded";
$uploadErrors[UPLOAD_ERR_NO_FILE] = "No file was uploaded";
$uploadErrors[UPLOAD_ERR_NO_TMP_DIR] = "Missing a temporary folder";
$uploadErrors[UPLOAD_ERR_CANT_WRITE] = "Failed to write file to disk";
$uploadErrors[UPLOAD_ERR_EXTENSION] = "A PHP extension stopped the file upload";

/**
 * Exit with xml response
 * @param boolean $isError Error if true
 * @param string $errorMessage Optional error message
 */
function exitWithStatus($isError, $errorMessage = NULL, $trackId = NULL) {
  header("Content-type: text/xml");
  $xml = new XMLWriter();
  $xml->openURI("php://output");
  $xml->startDocument("1.0");
  $xml->setIndent(true);
  $xml->startElement('root');
    $xml->writeElement("error", (int) $isError);
  if ($isError) {
    $xml->writeElement("message", $errorMessage);
  } else {
    $xml->writeElement("trackid", $trackId);
  }
  $xml->endElement();
  $xml->endDocument();
  $xml->flush();
  exit;
}

if (!$user->isValid) {
  exitWithStatus(true, $lang["servererror"]);
}

if (!isset($_FILES["gpx"])) {
  $message = $lang["servererror"];
  $lastErr = error_get_last();
  if (!empty($lastErr)) {
    $message .= ": " . $lastErr["message"];
  }
  exitWithStatus(true, $message);
}

$gpxFile = NULL;
$gpxUpload = $_FILES["gpx"];
$uploadErr = $gpxUpload["error"];
if ($gpxUpload["size"] > uUtils::getUploadMaxSize() && $uploadErr == UPLOAD_ERR_OK) {
  $uploadErr = UPLOAD_ERR_FORM_SIZE;
}
if ($uploadErr == UPLOAD_ERR_OK) {
  $gpxFile = $gpxUpload["tmp_name"];
  $gpxName = basename($gpxUpload["name"]);
} else {
  $message = $lang("iuploadfailure");
  if (isset($errorMessage[$uploadErr])) {
    $message .= ": " . $errorMessage[$uploadErr];
  }
  $message .= " ($uploadErr)";
  exitWithStatus(true, $message);
}

$gpx = false;
libxml_use_internal_errors(true);
if ($gpxFile && file_exists($gpxFile)) {
  $gpx = simplexml_load_file($gpxFile);
}

if ($gpx === false) {
  $message = $lang["iparsefailure"];
  $parserMessages = [];
  foreach(libxml_get_errors() as $parseError) {
    $parserMessages[] = $parseError->message;
  }
  $parserMessage = implode(", ", $parserMessages);
  if (!empty($parserMessage)) {
    $message .= ": $parserMessage";
  }
  exitWithStatus(true, $message);
}
else if (empty($gpx->trk)) {
  exitWithStatus(true, $lang["idatafailure"]);
}

$trackName = empty($gpx->trk->name) ? $gpxName : $gpx->trk->name->__toString();
$metaName = empty($gpx->metadata->name) ? NULL : $gpx->metadata->name->__toString();
$track = new uTrack();
$trackId = $track->add($user->id, $trackName, $metaName);
if ($trackId === false) {
  exitWithStatus(true, $lang["servererror"]);
  break;
}

$position = new uPosition();
foreach($gpx->trk->trkseg as $segment) {
  foreach($segment->trkpt as $point) {
    $ret = $position->add($user->id, $trackId,
                  strtotime($point->time), $point["lat"], $point["lon"], $point->ele,
                  NULL, NULL, NULL, "gps", NULL, NULL);
    if ($ret === false) {
      exitWithStatus(true, $lang["servererror"]);
    }
  }
}

// return track id
exitWithStatus(false, NULL, $trackId);

?>