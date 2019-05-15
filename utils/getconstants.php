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

require_once(dirname(__DIR__) . "/helpers/auth.php");
require_once(ROOT_DIR . "/helpers/config.php");
require_once(ROOT_DIR . "/helpers/lang.php");

$auth = new uAuth();
$langStrings = (new uLang(uConfig::$lang))->getStrings();

header("Content-type: text/xml");
$xml = new XMLWriter();
$xml->openURI("php://output");
$xml->startDocument("1.0");
$xml->setIndent(true);
$xml->startElement('root');


$xml->startElement("auth");
  $xml->writeElement("isAdmin", $auth->isAdmin());
  $xml->writeElement("isAuthenticated", $auth->isAuthenticated());
  if ($auth->isAuthenticated()) {
    $xml->writeElement("userId", $auth->user->id);
    $xml->writeElement("userLogin", $auth->user->login);
  }
$xml->endElement();

$xml->startElement("config");
  $xml->writeElement("interval", uConfig::$interval);
  $xml->writeElement("units", uConfig::$units);
  $xml->writeElement("mapapi", uConfig::$mapapi);
  $xml->writeElement("gkey", uConfig::$gkey);
  $xml->startElement("ol_layers");
  foreach (uConfig::$ol_layers as $key => $val) {
    $xml->writeElement($key, $val);
  }
  $xml->endElement();
  $xml->writeElement("init_latitude", uConfig::$init_latitude);
  $xml->writeElement("init_longitude", uConfig::$init_longitude);
  $xml->writeElement("pass_regex", uConfig::passRegex());
  $xml->writeElement("strokeWeight", uConfig::$strokeWeight);
  $xml->writeElement("strokeColor", uConfig::$strokeColor);
  $xml->writeElement("strokeOpacity", uConfig::$strokeOpacity);
$xml->endElement();

$xml->startElement("lang");
  $xml->startElement("strings");
  foreach ($langStrings as $key => $val) {
    $xml->writeElement($key, $val);
  }
  $xml->endElement();
$xml->endElement();


$xml->endElement();
$xml->endDocument();
$xml->flush();

?>
