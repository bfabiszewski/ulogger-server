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

// default language for translations

// strings only used in setup
$langSetup["dbconnectfailed"] = "Akatsa datu-basera konektatzean.";
$langSetup["serversaid"] = "Zerbitzariko mezua: %s"; // substitutes server error message
$langSetup["checkdbsettings"] = "Begiratu datu-base ezarpenak 'config.php' fitxategian";
$langSetup["dbqueryfailed"] = "Akatsa datu-baserako kontsultan";
$langSetup["dbtablessuccess"] = "Datu-baseko taulak arrakastaz sortuta!";
$langSetup["setupuser"] = "Orain aukeratu zure µlogger erabiltzailea";
$langSetup["congratulations"] = "Zorionak!";
$langSetup["setupcomplete"] = "Konfigurazioa osatuta. Orain <a href=\"../index.php\">orri nagusira</a> joan zaitezke eta saioa hasi zure erabiltzaile kontu berriarekin.";
$langSetup["disablewarn"] = "OHAR GARRANTZITSUA! 'setup.php' FITXATEGIA DESGAITU BEHAR DUZU EDO ZERBITZARITIK EZABATU.";
$langSetup["disabledesc"] = "Script-a nabigatzailetik eskuragarri uztea segurtasun arazo larria da. Edonor izango da gai exekutatzeko, zure datu basea ezabatzeko eta erabiltzaile berriak sortzeko. Ezabatu fitxategia edo desgaitu %s aukera atzera %s baliora ezarriz."; // substitutes variable name and value
$langSetup["setupfailed"] = "Zoritzarrez, zerbait oker joan da. Informazio gehiago nabigatzailearen log mezuetan bilatzen saia zaitezke.";
$langSetup["welcome"] = "Ongi etorri µlogger-era!";
$langSetup["disabledwarn"] = "Segurtasun arrazoiengatik besterik ezean script-a desgaituta dago. Gaitzeko 'scripts/setup.php' fitxategia editatu behar duzu eta %s aldagaia %s baliora ezarri fitxategiaren hasieran."; // substitutes variable name and value
$langSetup["lineshouldread"] = "Lerroa: %s izan beharko litzateke: %s";
$langSetup["dorestart"] = "Bukatzen duzunean script hau berrabiarazi.";
$langSetup["createconfig"] = "Sortu 'config.php' fitxategia erro karpetan. 'config.default.php' fitxategia kopiatzen has zaitezke. Kontuan izan konfigurazio aldagaiak eta datu-basearen ezarpenak zure beharretara egokitu beharko dituzula.";
$langSetup["nodbsettings"] = "Zure datu-basearen kredentzialak ezarri behar dituzu 'config.php' fitxategian (%s)"; // substitutes variable names
$langSetup["scriptdesc"] = "Script honek µlogger-erako taulak sortuko ditu (%s). Zure %s datu-basean sortuko dira. Adi, dagoeneko taulak existitzen badira, horiek ezabatu eta birsortu egingo dira, eduki guztia ezabatu egingo delarik."; // substitutes table names and db name
$langSetup["scriptdesc2"] = "Bukatutakoan script-ak zure izena eta pasahitza eskatuko dizkizu µlogger-eko erabiltzailea sortzeko.";
$langSetup["startbutton"] = "Zapaldu hasteko";
$langSetup["restartbutton"] = "Berrekin";
$langSetup["optionwarn"] = "PHPko %s konfigurazio aukera %s izan behar da."; // substitutes option name and value
$langSetup["extensionwarn"] = "Beharrezko PHP %s hedapena ez dago eskuragarri."; // substitutes extension name
$langSetup["notwritable"] = "'%s' karpetan idazteko baimena behar du PHPk."; // substitutes folder path


// application strings
$lang["title"] = "• μlogger •";
$lang["private"] = "Erabiltzaile eta pasahitza beharrezkoak dira orrialde honetarako.";
$lang["authfail"] = "Erabiltzaile edo pasahitz okerra";
$lang["user"] = "Erabiltzailea";
$lang["track"] = "Ibilbidea";
$lang["latest"] = "Azken posizioa";
$lang["autoreload"] = "Automatikoki birkargatu";
$lang["reload"] = "Orain birkargatu";
$lang["export"] = "Esportatu ibilbidea";
$lang["chart"] = "Perfila";
$lang["close"] = "Itxi";
$lang["time"] = "Denbora";
$lang["speed"] = "Abiadura";
$lang["accuracy"] = "Doitasuna";
$lang["position"] = "Posizioa";
$lang["altitude"] = "Altuera";
$lang["bearing"] = "Norabidea";
$lang["ttime"] = "Denbora totala";
$lang["aspeed"] = "Batez besteko abiadura";
$lang["tdistance"] = "Distantzia totala";
$lang["pointof"] = "%d/%d puntua"; // e.g. Point 3 of 10
$lang["summary"] = "Ibilbidearen laburpena";
$lang["suser"] = "Aukeratu erabiltzailea";
$lang["logout"] = "Saioa amaitu";
$lang["login"] = "Saioa hasi";
$lang["username"] = "Erabiltzaile izena";
$lang["password"] = "Pasahitza";
$lang["language"] = "Hizkuntza";
$lang["newinterval"] = "Denbora tarte berria sartu (segundoak)";
$lang["api"] = "Mapa APIa";
$lang["units"] = "Unitateak";
$lang["metric"] = "Metrikoa";
$lang["imperial"] = "Inperiala/AEB";
$lang["nautical"] = "Itsasokoa";
$lang["admin"] = "Administraria";
$lang["adminmenu"] = "Kudeaketa";
$lang["passwordrepeat"] = "Errepikatu pasahitza";
$lang["passwordenter"] = "Sartu pasahitza";
$lang["usernameenter"] = "Sartu erabiltzaile izena";
$lang["adduser"] = "Erabiltzailea gehitu";
$lang["userexists"] = "Erabiltzailea existitzen da";
$lang["cancel"] ="Utzi";
$lang["submit"] = "Bidali";
$lang["oldpassword"] = "Pasahitz zaharra";
$lang["newpassword"] = "Pasahitz berria";
$lang["newpasswordrepeat"] = "Errepikatu pasahitz berria";
$lang["changepass"] = "Aldatu pasahitza";
$lang["gps"] = "GPSa";
$lang["network"] = "Datu-konexioa";
$lang["deluser"] = "Erabiltzailea ezabatu";
$lang["edituser"] = "Erabiltzailea editatu";
$lang["servererror"] = "Zerbitzari errorea";
$lang["allrequired"] = "Eremu guztiak derrigorrezkoak dira";
$lang["passnotmatch"] = "Pasahitzek ez dute bat egiten";
$lang["actionsuccess"] = "Ekintza arrakastaz burutu da";
$lang["actionfailure"] = "Zerbait gaizki joan da";
$lang["notauthorized"] = "Erabiltzeak ez du baimenik";
$lang["userdelwarn"] = "Adi!\n\n%serabiltzailea behin-betiko ezabatzera zoaz, bere ibilbide guztiekin eta posizio guztiekin batera.\n\nZiur zaude?"; // substitutes user login
$lang["editinguser"] = "%s erabiltzailea editatzen ari zara"; // substitutes user login
$lang["selfeditwarn"] = "Erreminta honekin ezin duzu zure erabiltzailea editatu";
$lang["apifailure"] = "Barkatu, ezin da %s APIa kargatu"; // substitutes api name (gmaps or openlayers)
$lang["trackdelwarn"] = "Adi!\n\nBehin betiko %s ibilbidea ezabatzera zoaz eta bere posizio guztiak.\n\nZiur zaude?"; // substitutes track name
$lang["editingtrack"] = "%s ibilbidea editatzen ari zara"; // substitutes track name
$lang["deltrack"] = "Ezabatu ibilbidea";
$lang["trackname"] = "Ibilbide izena";
$lang["edittrack"] = "Editatu ibilbidea";
$lang["positiondelwarn"] = "Adi!\n\n%sibilbideko %d posizioa ezabatzera zoaz.\n\nZiur zaude?"; // substitutes position index and track name
$lang["editingposition"] = "%s ibilbideko %d. posizioa editatzen ari zara"; // substitutes position index and track name
$lang["delposition"] = "Ezabatu posizioa";
$lang["delimage"] = "Ezabatu irudia";
$lang["comment"] = "Iruzkina";
$lang["image"] = "Irudia";
$lang["editposition"] = "Editatu posizioa";
$lang["passlenmin"] = "Pasahitzak gutxienez %d karaktere izan behar ditu"; // substitutes password minimum length
$lang["passrules_1"] = "Gutxienez minuskula eta maiuskula bana izan behar du";
$lang["passrules_2"] = "Gutxienez minuskula, maiuskula eta digitu bana izan behar du";
$lang["passrules_3"] = "Gutxienez minuskula, maiuskula, digitu eta karaktere ez alfanumeriko bana izan behar du";
$lang["owntrackswarn"] = "Zure ibilbideak bakarrik edita ditzakezu";
$lang["gmauthfailure"] = "Orri honetan Google Maps API gakoarekin arazoak egon litezke";
$lang["gmapilink"] = "<a target=\"_blank\" href=\"https://developers.google.com/maps/documentation/javascript/get-api-key\">Google-eko web orrian</a> API gakoaren inguruko laguntza gehiago lor dezakezu";
$lang["import"] = "Ibilbidea inportatu";
$lang["iuploadfailure"] = "Hutsegitea igoeran";
$lang["iparsefailure"] = "Hutsegitea parserrean";
$lang["idatafailure"] = "Ez dago ibilbideko daturik inportatutako fitxategian";
$lang["isizefailure"] = "Igotako fitxategiak ez du %d baino gehiago izan behar"; // substitutes number of bytes
$lang["imultiple"] = "Kontuan izan hainbat ibilbide inportatu direla (%d)"; // substitutes number of imported tracks
$lang["allusers"] = "Erabiltzaile guztiak";
$lang["unitday"] = "egun"; // abbreviation for days, like 4 d 11:11:11
$lang["unitkmh"] = "km/h"; // kilometer per hour
$lang["unitm"] = "m"; // meter
$lang["unitamsl"] = " "; // above mean see level
$lang["unitkm"] = "km"; // kilometer
$lang["unitmph"] = "mph"; // mile per hour
$lang["unitft"] = "ft"; // feet
$lang["unitmi"] = "mi"; // mile
$lang["unitkt"] = "kt"; // knot
$lang["unitnm"] = "nm"; // nautical mile
$lang["config"] = "Ezarpenak";
$lang["editingconfig"] = "Aplikazio ezarpen lehenetsiak";
$lang["latitude"] = "Hasierako latitudea";
$lang["longitude"] = "Hasierako longitudea";
$lang["interval"] = "Tartea (s)";
$lang["googlekey"] = "Google Maps API gakoa";
$lang["passlength"] = "Pasahitzaren luzera minimoa";
$lang["passstrength"] = "Pasahitzaren sendotasun minimoa";
$lang["requireauth"] = "Baimena derrigortu";
$lang["publictracks"] = "Ibilbide publikoak";
$lang["strokeweight"] = "Trazuaren zabalera";
$lang["strokeopacity"] = "Trazuaren opakotasuna";
$lang["strokecolor"] = "Trazuaren kolorea";
$lang["colornormal"] = "Markagailuaren kolorea";
$lang["colorstart"] = "Hasierako markagailuaren kolorea";
$lang["colorstop"] = "Amaierako markagailuaren kolorea";
$lang["colorextra"] = "Gainerako markagailuen kolorea";
$lang["colorhilite"] = "Altuera markagailuaren kolorea";
$lang["uploadmaxsize"] = "Igotzeko fitxategien gehienezko tamaina (MB)";
$lang["ollayers"] = "OpenLayers geruza";
$lang["layername"] = "Geruzaren izena";
$lang["layerurl"] = "Geruzaren URLa";
$lang["add"] = "Gehitu";
$lang["edit"] = "Editatu";
$lang["delete"] = "Ezabatu";
$lang["settings"] = "Ezarpenak";
$lang["trackcolor"] = "Ibilbide kolorea";
?>
