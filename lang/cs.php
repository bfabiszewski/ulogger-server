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
$langSetup["dbconnectfailed"] = "Připojení k databázi se nezdařilo.";
$langSetup["serversaid"] = "Server reaguje: %s"; // substitutes server error message
$langSetup["checkdbsettings"] = "Zkontrolujte prosím nastavení databáze v souboru 'config.php'.";
$langSetup["dbqueryfailed"] = "Databázový dotaz selhal";
$langSetup["dbtablessuccess"] = "Databázové tabulky byly úspěšně vytvořeny!";
$langSetup["setupuser"] = "Nyní si nastavte uživatele µlogger .";
$langSetup["congratulations"] = "Gratulujeme";
$langSetup["setupcomplete"] = "Nastavení je nyní dokončeno. Nyní můžete přejít na <a href=\"../index.php\">hlavní stránku</a> a přihlásit se pomocí nového uživatelského účtu";
$langSetup["disablewarn"] = "DŮLEŽITÉ! MUSÍTE VYPNOUT 'setup.php' SCRIPT NEBO JEJ ODSTRANIT ZE SERVERU.";
$langSetup["disabledesc"] = "Hlavní bezpečnostní riziko představuje ponechání skriptu přístupného z prohlížeče. Ten jej bude moci spustit, smazat databázi a nastavit nový uživatelský účet. Odstraňte soubor nebo jej zakažte nastavením hodnoty %s zpět na %s."; // substitutes variable name and value
$langSetup["setupfailed"] = "Bohužel se něco pokazilo. Můžete se pokusit najít více informací ve svých logech webserveru.";
$langSetup["welcome"] = "Vítejte v µloggeru. ";
$langSetup["disabledwarn"] = "Z bezpečnostních důvodů je tento skript ve výchozím nastavení zakázán. Pro jeho aktivaci musíte upravit soubor  'scripts/setup.php'  v textovém editoru a nastavit %s proměnnou na začátku souboru na %s."; // substitutes variable name and value
$langSetup["lineshouldread"] = "Řádek: %s by měl číst: %s";
$langSetup["dorestart"] = "Po dokončení skriptu restartujte tento skript.";
$langSetup["createconfig"] = "Vytvořte soubor 'config.php' v kořenové složce. Můžete začít zkopírováním z 'config.default.php'. Ujistěte se, že jste nastavili konfigurační hodnoty tak, aby odpovídaly vašim potřebám a nastavení vaší databáze.";
$langSetup["nodbsettings"] = "Musíte zadat své údaje databáze v souboru 'config.php' (%s)."; // substitutes variable names
$langSetup["scriptdesc"] = "Tento skript nastaví tabulky potřebné pro µlogger (%s). Budou vytvořeny ve vaší databázi s názvem %s. Upozorňujeme, že pokud tabulky již existují, budou zrušeny a znovu vytvořeny, jejich obsah bude zničen."; // substitutes table names and db name
$langSetup["scriptdesc2"] = "Po dokončení skriptu budete vyzváni k zadání uživatelského jména a hesla pro uživatele µloggeru .";
$langSetup["startbutton"] = "Stiskněte pro spuštění";
$langSetup["restartbutton"] = "Restart";
$langSetup["optionwarn"] = "Možnost konfigurace PHP %s musí být nastavena na %s."; // substitutes option name and value
$langSetup["extensionwarn"] = "Požadované rozšíření PHP %s není k dispozici."; // substitutes extension name


// application strings
$lang["title"] = "• μlogger •";
$lang["private"] = "Pro přístup na tuto stránku potřebujete login a heslo.";
$lang["authfail"] = "Chybné uživatelské jméno nebo heslo";
$lang["user"] = "Uživatel";
$lang["track"] = "Trasa";
$lang["latest"] = "poslední pozice";
$lang["autoreload"] = "automatické obnovení";
$lang["reload"] = "Znovu načíst";
$lang["export"] = "Export trasy";
$lang["chart"] = "Graf výšek";
$lang["close"] = "zavřít";
$lang["time"] = "Čas";
$lang["speed"] = "Rychlost";
$lang["accuracy"] = "Přesnost";
$lang["position"] = "Position";
$lang["altitude"] = "Výška";
$lang["bearing"] = "Bearing";
$lang["ttime"] = "Celkový čas";
$lang["aspeed"] = "Průměrná rychlost";
$lang["tdistance"] = "Celková vzdálenost";
$lang["pointof"] = "Bod %d z %d"; // e.g. Point 3 of 10
$lang["summary"] = "Shrnutí výletu";
$lang["suser"] = "vybrat uživatele";
$lang["logout"] = "Odhlásit";
$lang["login"] = "Přihlášení";
$lang["username"] = "Uživatelské jméno";
$lang["password"] = "Heslo";
$lang["language"] = "Jazyk";
$lang["newinterval"] = "Zadejte novou hodnotu intervalu (sekundy)";
$lang["api"] = "Map API";
$lang["units"] = "Jednotky";
$lang["metric"] = "Metrický";
$lang["imperial"] = "Imperiální / US";
$lang["nautical"] = "Námořní";
$lang["admin"] = "Administrator";
$lang["adminmenu"] = "Administrace";
$lang["passwordrepeat"] = "Opakovat heslo";
$lang["passwordenter"] = "Zadejte heslo";
$lang["usernameenter"] = "Zadat uživatelské jméno";
$lang["adduser"] = "Přidat uživatele";
$lang["userexists"] = "Uživatel již existuje";
$lang["cancel"] ="Zrušit";
$lang["submit"] = "Odeslat";
$lang["oldpassword"] = "Staré heslo";
$lang["newpassword"] = "Nové heslo";
$lang["newpasswordrepeat"] = "opakujte nové heslo";
$lang["changepass"] = "Změnit heslo";
$lang["gps"] = "GPS";
$lang["network"] = "Síť";
$lang["deluser"] = "Odstranit uživatele";
$lang["edituser"] = "Upravit uživatele";
$lang["servererror"] = "Chyba serveru";
$lang["allrequired"] = "Všechna pole jsou povinná";
$lang["passnotmatch"] = "Hesla neodpovídají";
$lang["actionsuccess"] = "Akce úspěšně dokončena";
$lang["actionfailure"] = "Něco se pokazilo";
$lang["notauthorized"] = "Uživatel není oprávněn";
$lang["userdelwarn"] = "Upozornění:\n\nTrvale smažete uživatele %s spolu se všemi jeho trasami a pozicemi.\n\nJste si jist?"; // substitutes user login
$lang["editinguser"] = "Upravujete uživatele %s"; // substitutes user login
$lang["selfeditwarn"] = "Pomocí tohoto nástroje nemůžete upravovat vlastního uživatele";
$lang["apifailure"] = "Litujeme, nelze načíst %s API"; // substitutes api name (gmaps or openlayers)
$lang["trackdelwarn"] = "Upozornění: \n\nTrvale smažete trasu %s a všechny její pozice. \n\nJste si jist?"; // substitutes track name
$lang["editingtrack"] = "Upravujete trasu %s"; // substitutes track name
$lang["deltrack"] = "Odstranit trasu";
$lang["trackname"] = "Název trasy";
$lang["edittrack"] = "Upravit trasu";
$lang["positiondelwarn"] = "Warning!\n\nYou are going to permanently delete position %d of track %s.\n\nAre you sure?"; // substitutes position index and track name
$lang["editingposition"] = "You are editing position #%d of track %s"; // substitutes position index and track name
$lang["delposition"] = "Remove position";
$lang["comment"] = "Comment";
$lang["editposition"] = "Edit position";
$lang["passlenmin"] = "Heslo musí být alespoň %d znaků"; // substitutes password minimum length
$lang["passrules_1"] = "Mělo by obsahovat alespoň jedno malé písmeno, jedno velké písmeno";
$lang["passrules_2"] = "Mělo by obsahovat alespoň jedno malé písmeno, jedno velké písmeno a jednu číslici";
$lang["passrules_3"] = "Mělo by obsahovat alespoň jedno malé písmeno, jedno velké písmeno, jednu číslici a jeden nealfanumerický znak";
$lang["owntrackswarn"] = "Můžete upravovat pouze své vlastní trasy";
$lang["gmauthfailure"] = "Na této stránce může být problém s klíčem API služby Mapy Google";
$lang["gmapilink"] = "Další informace o klíčích API můžete najít <a target=\"_blank\" href=\"https://developers.google.com/maps/documentation/javascript/get-api-key\"> na tomto webu Google </a>";
$lang["import"] = "Importovat trasu";
$lang["iuploadfailure"] = "Nahrávání se nezdařilo";
$lang["iparsefailure"] = "Parsování selhalo";
$lang["idatafailure"] = "Žádné údaje o trase v importovaném souboru";
$lang["isizefailure"] = "Velikost nahraného souboru by neměla překročit %d bajtů"; // substitutes number of bytes
$lang["imultiple"] = "Všimněte si, že bylo importováno více tras (%d)"; // substitutes number of imported tracks
$lang["allusers"] = "Všichni uživatelé";
$lang["unitday"] = "d"; // abbreviation for days, like 4 d 11:11:11
$lang["unitkmh"] = "km/h"; // kilometer per hour
$lang["unitm"] = "m"; // meter
$lang["unitkm"] = "km"; // kilometer
$lang["unitmph"] = "mph"; // mile per hour
$lang["unitft"] = "ft"; // feet
$lang["unitmi"] = "mi"; // mile
$lang["unitkt"] = "kt"; // knot
$lang["unitnm"] = "nm"; // nautical mile
$lang["config"] = "Settings";
$lang["editingconfig"] = "Default application settings";
$lang["latitude"] = "Initial latitude";
$lang["longitude"] = "Initial longitude";
$lang["interval"] = "Interval";
$lang["googlekey"] = "Google Maps API key";
$lang["passlength"] = "Minimum password length";
$lang["passstrength"] = "Minimum password strength";
$lang["requireauth"] = "Require authorization";
$lang["publictracks"] = "Public tracks";
$lang["strokeweight"] = "Stroke weight";
$lang["strokeopacity"] = "Stroke opacity";
$lang["strokecolor"] = "Stroke color";
$lang["colornormal"] = "Marker color";
$lang["colorstart"] = "Start marker color";
$lang["colorstop"] = "Stop marker color";
$lang["colorextra"] = "Extra marker color";
$lang["colorhilite"] = "Hilite marker color";
$lang["ollayers"] = "OpenLayers layer";
$lang["layername"] = "Layer name";
$lang["layerurl"] = "Layer URL";
$lang["add"] = "Add";
$lang["edit"] = "Edit";
$lang["delete"] = "Delete";
$lang["settings"] = "Settings";
?>
