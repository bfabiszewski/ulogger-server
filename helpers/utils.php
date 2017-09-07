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

 /**
  * Various util functions
  */
  class uUtils {

    /**
     * Calculate maximum allowed size of uploaded file
     * for current PHP settings
     *
     * @return int Number of bytes
     */
    public static function getUploadMaxSize() {
      $upload_max_filesize = self::iniGetBytes('upload_max_filesize');
      $post_max_size = self::iniGetBytes('post_max_size');
      // post_max_size = 0 means unlimited size
      if ($post_max_size == 0) { $post_max_size = $upload_max_filesize; }
      $memory_limit = self::iniGetBytes('memory_limit');
      // memory_limit = -1 means no limit
      if ($memory_limit < 0) { $memory_limit = $post_max_size; }
      return min($upload_max_filesize, $post_max_size, $memory_limit);
    }

    /**
     * Get number of bytes from ini parameter.
     * Optionally parses shorthand byte values (G, M, B)
     *
     * @param string $iniParam Ini parameter name
     * @return int Bytes
     */
    private static function iniGetBytes($iniParam) {
      $iniStr = ini_get($iniParam);
      $val = floatval($iniStr);
      $suffix = substr(trim($iniStr), -1);
      if (ctype_alpha($suffix)) {
        switch (strtolower($suffix)) {
          case 'g':
            $val *= 1024;
          case 'm':
            $val *= 1024;
          case 'k':
            $val *= 1024;
        }
      }
      return (int) $val;
    }

    /**
     * Exit with error message
     *
     * @param string $errorMessage Message
     * @param array|null $extra Optional array of extra parameters
     */
    public static function exitWithError($errorMessage, $extra = NULL) {
      $extra['message'] = $errorMessage;
      self::exitWithStatus(true, $extra);
    }

    /**
     * Exit with successful status code
     *
     * @param array|null $extra Optional array of extra parameters
     */
    public static function exitWithSuccess($extra = NULL) {
      self::exitWithStatus(false, $extra);
    }

    /**
     * Exit with xml response
     * @param boolean $isError Error if true
     * @param array|null $extra Optional array of extra parameters
     */
    private static function exitWithStatus($isError, $extra = NULL) {
      header("Content-type: text/xml");
      $xml = new XMLWriter();
      $xml->openURI("php://output");
      $xml->startDocument("1.0");
      $xml->setIndent(true);
      $xml->startElement("root");
      $xml->writeElement("error", (int) $isError);
      if (!empty($extra)) {
        foreach ($extra as $key => $value) {
          $xml->writeElement($key, $value);
        }
      }

      $xml->endElement();
      $xml->endDocument();
      $xml->flush();
      exit;
    }

    public static function getBaseUrl() {
      $proto = (!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] == "" || $_SERVER["HTTPS"] == "off") ? "http://" : "https://";
      $host = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "";
      if (realpath($_SERVER["SCRIPT_FILENAME"])) {
        $scriptPath = substr(dirname(realpath($_SERVER["SCRIPT_FILENAME"])), strlen(ROOT_DIR));
      } else {
        // for phpunit
        $scriptPath = substr(dirname($_SERVER["SCRIPT_FILENAME"]), strlen(ROOT_DIR));
      }
      $self = dirname($_SERVER["PHP_SELF"]);
      $path = str_replace("\\", "/", substr($self, 0, strlen($self) - strlen($scriptPath)));

      return $proto . str_replace("//", "/", $host . $path . "/");
    }

  }

?>