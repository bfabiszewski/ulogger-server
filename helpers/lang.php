<?php
/* μlogger
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

  require_once(ROOT_DIR . "/helpers/config.php");

  /**
  * Localization
  */
  class uLang {

    /**
     * Available languages
     *
     * @var array
     */
    private static $languages = [
      "cs" => "Čeština",
      "de" => "Deutsch",
      "en" => "English",
      "es" => "Español",
      "fr" => "Français",
      "it" => "Italiano",
      "hu" => "Magyar",
      "nl" => "Nederlands",
      "pl" => "Polski",
      "ru" => "Русский",
      "zh" => "中文"
    ];

    /**
     * Application strings
     * Array of key => translation pairs
     *
     * @var array
     */
    private $strings;
    /**
     * Setup script strings
     * Array of key => translation pairs
     *
     * @var array
     */
    private $setupStrings;

    /**
     * Constructor
     *
     * @param string $language Language code (IANA)
     */
    public function __construct($language = "en") {
      $lang = [];
      $langSetup = [];
      // always load en base
      require(ROOT_DIR . "/lang/en.php");

      // override with translated strings if needed
      // missing strings will be displayed in English
      if ($language !== "en" && array_key_exists($language, self::$languages)) {
        require(ROOT_DIR . "/lang/$language.php");
      }

      // choose password messages based on config
      $passRules = "passrules_" . uConfig::$passStrength;
      $lang['passrules'] = isset($lang[$passRules]) ? $lang[$passRules] : "";
      $lang['passlenmin'] = sprintf($lang["passlenmin"], uConfig::$passLenMin);
      $this->strings = $lang;
      $this->setupStrings = $langSetup;
    }

    /**
     * Get supported languages array
     * Language code => Native language name
     *
     * @return array
     */
    public static function getLanguages() {
      return self::$languages;
    }

    /**
     * Get translated strings array
     * Key => translation string
     *
     * @return array
     */
    public function getStrings() {
      return $this->strings;
    }

    /**
     * Get translated strings array for setup script
     * Key => translation string
     *
     * @return array
     */
    public function getSetupStrings() {
      return $this->setupStrings;
    }

  }

 ?>
