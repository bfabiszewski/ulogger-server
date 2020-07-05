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
$langSetup["dbconnectfailed"] = "Nepodarilo sa pripojiť k databáze.";
$langSetup["serversaid"] = "Hlásenie servera: %s"; // substitutes server error message
$langSetup["checkdbsettings"] = "Prosím, skontrolujte nastavenia databázy v súbore 'config.php'.";
$langSetup["dbqueryfailed"] = "Dopyt pre databázu sa nepodaril.";
$langSetup["dbtablessuccess"] = "Tabuľky v databáze boli úspešne vytvorené.";
$langSetup["setupuser"] = "Teraz, prosím, nastavte používateľa µlogger.";
$langSetup["congratulations"] = "Gratulujem!";
$langSetup["setupcomplete"] = "Inštalácia sa dokončila. Môžete prejsť <a href=\"../index.php\">na hlavnú stránku</a> a prihlásiť sa pod účtom nového používateľa.";
$langSetup["disablewarn"] = "DÔLEŽITÉ! MUSÍTE ZAKÁZAŤ SKRIPT 'setup.php' ALEBO HO ODSTRÁNIŤ Z VÁŠHO SERVERA.";
$langSetup["disabledesc"] = "Ak necháte skript sprístupnený z webového prehliadača, veľmi riskujete. Ktokoľvek ho totiž bude môcť spustiť, vymazať vašu databázu a nastaviť účet nového používateľa. Súbor vymažte alebo zakážte nastavením premennej %s späť na hodnotu %s."; // substitutes variable name and value
$langSetup["setupfailed"] = "Niečo nevyšlo. Zistite viac zo záznamov webového servera.";
$langSetup["welcome"] = "Vitajte v µlogger!";
$langSetup["disabledwarn"] = "Z bezpečnostných dôvodov je tento skript zakázaný. Aby ste ho povolili, otvorte súbor 'scripts/setup.php' v textovom editore a na začiatku súboru nastavte premennú %s na hodnotu %s."; // substitutes variable name and value
$langSetup["lineshouldread"] = "Riadok: %s by mal byť: %s";
$langSetup["dorestart"] = "Prosím, po ukončení reštartujte tento skript.";
$langSetup["createconfig"] = "Prosím, vytvorte súbor 'config.php' v hlavnom priečinku. Skopírujte ho zo súboru 'config.default.php' a nastavte požadované hodnoty tak, aby zodpovedali vašim potrebám a nastaveniu databáze.";
$langSetup["nodbsettings"] = "Do súboru 'config.php' musíte zadať prihlasovacie údaje do databázy (%s)."; // substitutes variable names
$langSetup["scriptdesc"] = "Tento skript vytvorí tabuľky, ktoré potrebuje µlogger (%s). Budú zapísané do databázy nazvanej %s. Ak tabuľky už existujú, budú vymazané a znovu vytvorené, pričom ich obsah bude odstránený."; // substitutes table names and db name
$langSetup["scriptdesc2"] = "Po ukončení bude µlogger žiadať meno a heslo nového používateľa.";
$langSetup["startbutton"] = "Stlačte pre pokračovanie";
$langSetup["restartbutton"] = "Reštartovať";
$langSetup["optionwarn"] = "PHP nastavenie %s musíte zadať ako %s."; // substitutes option name and value
$langSetup["extensionwarn"] = "Nenašlo sa potrebné rozšírenie PHP %s."; // substitutes extension name
$langSetup["notwritable"] = "PHP musí mať právo zapisovať do priečinka '%s'."; // substitutes folder path


// application strings
$lang["title"] = "• μlogger •";
$lang["private"] = "Na prístup k tejto stránke sa potrebujete prihlásiť menom a heslom.";
$lang["authfail"] = "Nesprávne meno alebo heslo";
$lang["user"] = "Používateľ";
$lang["track"] = "Cesta";
$lang["latest"] = "posledná poloha";
$lang["autoreload"] = "automaticky obnoviť";
$lang["reload"] = "Obnoviť teraz";
$lang["export"] = "Exportovať cestu";
$lang["chart"] = "Graf nadmorskej výšky";
$lang["close"] = "zatvoriť";
$lang["time"] = "Čas";
$lang["speed"] = "Rýchlosť";
$lang["accuracy"] = "Presnosť";
$lang["position"] = "Poloha";
$lang["altitude"] = "Výška";
$lang["bearing"] = "Azimut";
$lang["ttime"] = "Celkový čas";
$lang["aspeed"] = "Priemerná rýchlosť";
$lang["tdistance"] = "Celková vzdialenosť";
$lang["pointof"] = "Miesto %d z %d"; // e.g. Point 3 of 10
$lang["summary"] = "Zhrnutie trasy";
$lang["suser"] = "vyberte používateľa";
$lang["logout"] = "Odhlásiť";
$lang["login"] = "Prihlásiť";
$lang["username"] = "Meno";
$lang["password"] = "Heslo";
$lang["language"] = "Jazyk";
$lang["newinterval"] = "Zadajte novú hodnotu intervalu (v sekundách)";
$lang["api"] = "API máp";
$lang["units"] = "Jednotky";
$lang["metric"] = "Metrické";
$lang["imperial"] = "Imperiálne";
$lang["nautical"] = "Námorné";
$lang["admin"] = "Administrátor";
$lang["adminmenu"] = "Administrácia";
$lang["passwordrepeat"] = "Heslo ešte raz";
$lang["passwordenter"] = "Zadajte heslo";
$lang["usernameenter"] = "Zadajte meno";
$lang["adduser"] = "Pridať používateľa";
$lang["userexists"] = "Používateľ už existuje";
$lang["cancel"] ="Zrušiť";
$lang["submit"] = "Poslať";
$lang["oldpassword"] = "Staré heslo";
$lang["newpassword"] = "Nové heslo";
$lang["newpasswordrepeat"] = "Zopakovať nové heslo";
$lang["changepass"] = "Zmeniť heslo";
$lang["gps"] = "GPS";
$lang["network"] = "Sieť";
$lang["deluser"] = "Odstrániť používateľa";
$lang["edituser"] = "Upraviť používateľa";
$lang["servererror"] = "Chyba servera";
$lang["allrequired"] = "Všetky polia sú povinné";
$lang["passnotmatch"] = "Heslá sa nezhodujú";
$lang["actionsuccess"] = "Akcia bola úspešne vykonaná";
$lang["actionfailure"] = "Niečo nevyšlo";
$lang["notauthorized"] = "Používateľ nebol autorizovaný";
$lang["userdelwarn"] = "Upozornenie!\n\nChystáte sa nenávratne odstrániť používateľa %s spolu s jeho cestami a miestami.\n\nSte si istý?"; // substitutes user login
$lang["editinguser"] = "Upravujete používateľa %s"; // substitutes user login
$lang["selfeditwarn"] = "Týmto nástrojom nemôžete upravovať samého seba";
$lang["apifailure"] = "Prepáčte, nepodarilo sa načítať API %s"; // substitutes api name (gmaps or openlayers)
$lang["trackdelwarn"] = "Upozornenie!\n\nChystáte sa nenávratne odstrániť cestu %s a všetky jej miesta.\n\nSte si istý?"; // substitutes track name
$lang["editingtrack"] = "Upravujete cestu %s"; // substitutes track name
$lang["deltrack"] = "Odstrániť cestu";
$lang["trackname"] = "Názov cesty";
$lang["edittrack"] = "Upraviť cestu";
$lang["positiondelwarn"] = "Upozornenie!\n\nChystáte sa nenávratne odstrániť polohu %d cesty %s.\n\nSte si istý?"; // substitutes position index and track name
$lang["editingposition"] = "Upravujete polohu #%d cesty %s"; // substitutes position index and track name
$lang["delposition"] = "Odstrániť polohu";
$lang["delimage"] = "Odstrániť obrázok";
$lang["comment"] = "Komentár";
$lang["image"] = "Obrázok";
$lang["editposition"] = "Upraviť polohu";
$lang["passlenmin"] = "Heslo musí mať minimálne počet znakov: %d"; // substitutes password minimum length
$lang["passrules_1"] = "Malo by obsahovať najmenej jedno malé písmeno a jedno veľké písmeno";
$lang["passrules_2"] = "Malo by obsahovať najmenej jedno malé písmeno, jedno veľké písmeno a jednu číslovku";
$lang["passrules_3"] = "Malo by obsahovať najmenej jedno malé písmeno, jedno veľké písmeno, jednu číslovku a jeden špeciálny znak";
$lang["owntrackswarn"] = "Môžete upravovať iba vaše cesty";
$lang["gmauthfailure"] = "Na tejto stránke je asi problém s Google Maps API";
$lang["gmapilink"] = "Viac informácií o API kľúčoch získate <a target=\"_blank\" href=\"https://developers.google.com/maps/documentation/javascript/get-api-key\">na tejto stránke od Google</a>";
$lang["import"] = "Importovať cestu";
$lang["iuploadfailure"] = "Nahrávanie sa nepodarilo";
$lang["iparsefailure"] = "Rozbor sa nepodaril";
$lang["idatafailure"] = "V importovanom súbore sa nenašli údaje o ceste";
$lang["isizefailure"] = "Veľkosť nahrávaného súboru nesmie prekročiť počet bytov: %d"; // substitutes number of bytes
$lang["imultiple"] = "Bolo importovaných viacero ciest (%d)"; // substitutes number of imported tracks
$lang["allusers"] = "Všetci používatelia";
$lang["unitday"] = "d"; // abbreviation for days, like 4 d 11:11:11
$lang["unitkmh"] = "km/h"; // kilometer per hour
$lang["unitm"] = "m"; // meter
$lang["unitamsl"] = "n. m."; // above mean see level
$lang["unitkm"] = "km"; // kilometer
$lang["unitmph"] = "m/h"; // mile per hour
$lang["unitft"] = "stopa"; // feet
$lang["unitmi"] = "mi"; // mile
$lang["unitkt"] = "kt"; // knot
$lang["unitnm"] = "nm"; // nautical mile
$lang["config"] = "Nastavenia";
$lang["editingconfig"] = "Predvolené nastavenia aplikácie";
$lang["latitude"] = "Počiatočná výška";
$lang["longitude"] = "Počiatočná dĺžka";
$lang["interval"] = "Interval(y)";
$lang["googlekey"] = "API kľúč Google Maps";
$lang["passlength"] = "Minimálna dĺžka hesla";
$lang["passstrength"] = "Minimálna sila hesla";
$lang["requireauth"] = "Vyžadovať prihlasovanie";
$lang["publictracks"] = "Cesty sú verejné";
$lang["strokeweight"] = "Šírka čiary";
$lang["strokeopacity"] = "Priehľadnosť čiary";
$lang["strokecolor"] = "Farba čiary";
$lang["colornormal"] = "Farba značky";
$lang["colorstart"] = "Farba počiatočnej značky";
$lang["colorstop"] = "Farba ukončujúcej značky";
$lang["colorextra"] = "Farba zvláštnej značky";
$lang["colorhilite"] = "Farba zvýrazňujúcej značky";
$lang["uploadmaxsize"] = "Maximálna veľkosť nahrávaného súboru (MB)";
$lang["ollayers"] = "Vrstva OpenLayers";
$lang["layername"] = "Názov vrstvy";
$lang["layerurl"] = "URL vrstvy";
$lang["add"] = "Pridať";
$lang["edit"] = "Upraviť";
$lang["delete"] = "Zmazať";
$lang["settings"] = "Nastavenia";
$lang["trackcolor"] = "Farba cesty";
?>
