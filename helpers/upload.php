<?php
/**
 * μlogger
 *
 * Copyright(C) 2019 Bartek Fabiszewski (www.fabiszewski.net)
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

require_once(ROOT_DIR . "/helpers/db.php");
require_once(ROOT_DIR . "/helpers/utils.php");

/**
 * Uploaded files
 */
class uUpload {

  const META_TYPE = "type";
  const META_NAME = "name";
  const META_TMP_NAME = "tmp_name";
  const META_ERROR = "error";
  const META_SIZE = "size";
  public static $uploadDir = ROOT_DIR . "/uploads/";
  private static $filePattern = "[a-z0-9_.]{20,}";
  private static $mimeMap = [];

  /**
   * @return string[] Mime to extension mapping
   */
  private static function getMimeMap() {
    if (empty(self::$mimeMap)) {
      self::$mimeMap["image/jpeg"] = "jpg";
      self::$mimeMap["image/x-ms-bmp"] = "bmp";
      self::$mimeMap["image/gif"] = "gif";
      self::$mimeMap["image/png"] = "png";
    }
    return self::$mimeMap;
  }

  /**
   * Is mime accepted type
   * @param string $mime Mime type
   * @return bool True if known
   */
  private static function isKnownMime($mime) {
    return array_key_exists($mime, self::getMimeMap());
  }

  /**
   * Get file extension for given mime
   * @param $mime
   * @return string|null Extension or NULL if not found
   */
  private static function getExtension($mime) {
    if (self::isKnownMime($mime)) {
      return self::getMimeMap()[$mime];
    }
    return NULL;
  }

  /**
   * Save file to uploads, basic sanitizing
   * @param array $uploaded File meta array from $_FILES[]
   * @param int $trackId
   * @return string|NULL Unique file name, null on error
   */
  public static function add($uploaded, $trackId) {
    try {
      $fileMeta = self::sanitizeUpload($uploaded);
    } catch (Exception $e) {
      syslog(LOG_ERR, $e->getMessage());
      // save exception to txt file as image replacement?
      return NULL;
    }

    $extension = self::getExtension($fileMeta[self::META_TYPE]);

    do {
      $fileName = uniqid("{$trackId}_") . ".$extension";
    } while (file_exists(self::$uploadDir . $fileName));
    if (move_uploaded_file($fileMeta[self::META_TMP_NAME], self::$uploadDir . $fileName)) {
      return $fileName;
    }
    return NULL;
  }

  /**
   * Delete upload from database and filesystem
   * @param String $path File relative path
   * @return bool False if file exists but can't be unlinked
   */
  public static function delete($path) {
    $ret = true;
    if (preg_match(self::$filePattern, $path)) {
      $path = self::$uploadDir . $path;
      if (file_exists($path)) {
        $ret = unlink($path);
      }
    }
    return $ret;
  }

  /**
   * @param array $fileMeta File meta array from $_FILES[]
   * @param boolean $checkMime Check with known mime types
   * @return array File metadata array
   * @throws ErrorException Internal server exception
   * @throws Exception File upload exception
   */
  public static function sanitizeUpload($fileMeta, $checkMime = true) {
    if (!isset($fileMeta) ||
      !isset($fileMeta[self::META_NAME]) || !isset($fileMeta[self::META_TYPE]) ||
      !isset($fileMeta[self::META_SIZE]) || !isset($fileMeta[self::META_TMP_NAME])) {
      $message = "no uploaded file";
      $lastErr = error_get_last();
      if (!empty($lastErr)) {
        $message = $lastErr["message"];
      }
      throw new ErrorException($message);
    }

    $uploadErrors = [];
    $uploadErrors[UPLOAD_ERR_INI_SIZE] = "Uploaded file exceeds the upload_max_filesize directive in php.ini";
    $uploadErrors[UPLOAD_ERR_FORM_SIZE] = "Uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
    $uploadErrors[UPLOAD_ERR_PARTIAL] = "File was only partially uploaded";
    $uploadErrors[UPLOAD_ERR_NO_FILE] = "No file was uploaded";
    $uploadErrors[UPLOAD_ERR_NO_TMP_DIR] = "Missing a temporary folder";
    $uploadErrors[UPLOAD_ERR_CANT_WRITE] = "Failed to write file to disk";
    $uploadErrors[UPLOAD_ERR_EXTENSION] = "A PHP extension stopped file upload";

    $file = NULL;
    $fileError = isset($fileMeta[self::META_ERROR]) ? $fileMeta[self::META_ERROR] : UPLOAD_ERR_OK;
    if ($fileMeta[self::META_SIZE] > uUtils::getUploadMaxSize() && $fileError == UPLOAD_ERR_OK) {
      $fileError = UPLOAD_ERR_FORM_SIZE;
    }
    if ($fileError == UPLOAD_ERR_OK) {
      $file = $fileMeta[self::META_TMP_NAME];
    } else {
      $message = "Unknown error";
      if (isset($uploadErrors[$fileError])) {
        $message = $uploadErrors[$fileError];
      }
      $message .= " ($fileError)";
      throw new Exception($message);
    }

    if (!$file || !file_exists($file)) {
      throw new ErrorException("File not found");
    }
    if ($checkMime && !self::isKnownMime($fileMeta[self::META_TYPE])) {
      throw new Exception("Unsupported mime type");
    }
    return $fileMeta;
  }
}