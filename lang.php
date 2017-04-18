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

  // available languages
  $langsArr = [
    "en" => "English",
    "pl" => "Polski",
    "de" => "Deutsch",
    "hu" => "Magyar",
    "fr" => "Français",
    "it" => "Italiano",
    "es" => "Español"
  ];

  // always load en base
  require_once(ROOT_DIR . "/lang/en.php");

  // override with translated strings if needed
  // missing strings will be displayed in English
  if (uConfig::$lang != "en" && array_key_exists(uConfig::$lang, $langsArr)) {
    require_once(ROOT_DIR . "/lang/" . uConfig::$lang . ".php");
  }

  // choose password messages based on config
  $lang['passrules'] = isset($lang["passrules"][uConfig::$pass_strength]) ? $lang["passrules"][uConfig::$pass_strength] : "";
  $lang['passlenmin'] = sprintf($lang["passlenmin"], uConfig::$pass_lenmin);

?>