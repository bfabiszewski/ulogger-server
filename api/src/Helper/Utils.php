<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

namespace uLogger\Helper;

/**
 * Various util functions
 */
class Utils {

  private static ?string $rootDir = null;

  public static function getRootDir(): string {
    if (self::$rootDir !== null) {
      return self::$rootDir;
    }
    return dirname(__DIR__, 2);
  }

  public static function getSourceDir(): string {
    return self::getRootDir() . '/src';
  }

  public static function getUploadDir(): string {
    return self::getRootDir() . '/uploads';
  }

  /**
   * Calculate maximum allowed size of uploaded file
   * for current PHP settings
   *
   * @return int Number of bytes
   */
  public static function getSystemUploadLimit(): int {
    $uploadMaxFilesize = self::iniGetBytes('upload_max_filesize');
    $postMaxSize = self::iniGetBytes('post_max_size');
    // post_max_size = 0 means unlimited size
    if ($postMaxSize === 0) { $postMaxSize = $uploadMaxFilesize; }
    $memoryLimit = self::iniGetBytes('memory_limit');
    // memory_limit = -1 means no limit
    if ($memoryLimit < 0) { $memoryLimit = $postMaxSize; }
    return min($uploadMaxFilesize, $postMaxSize, $memoryLimit);
  }

  /**
   * @param $path string Path
   * @return bool True if is absolute
   */
  public static function isAbsolutePath(string $path): bool {
    return $path[0] === '/' || $path[0] === '\\' || preg_match('/^[a-zA-Z]:\\\\/', $path);
  }

  /**
   * Get number of bytes from ini parameter.
   * Optionally parses shorthand byte values (G, M, B)
   *
   * @param string $iniParam Ini parameter name
   * @return int Bytes
   * @noinspection PhpMissingBreakStatementInspection
   */
  private static function iniGetBytes(string $iniParam): int {
    $iniStr = ini_get($iniParam);
    $val = (float) $iniStr;
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

}

?>
