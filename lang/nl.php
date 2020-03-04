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
$langSetup["dbconnectfailed"] = "Database connection failed.";
$langSetup["serversaid"] = "Server said: %s"; // substitutes server error message
$langSetup["checkdbsettings"] = "Please check database settings in 'config.php' file.";
$langSetup["dbqueryfailed"] = "Database query failed.";
$langSetup["dbtablessuccess"] = "Database tables successfully created!";
$langSetup["setupuser"] = "Now please set up your µlogger user.";
$langSetup["congratulations"] = "Congratulations!";
$langSetup["setupcomplete"] = "Setup is now complete. You may go to the <a href=\"../index.php\">main page</a> now and log in with your new user account.";
$langSetup["disablewarn"] = "IMPORTANT! YOU MUST DISABLE 'setup.php' SCRIPT OR REMOVE IT FROM YOUR SERVER.";
$langSetup["disabledesc"] = "Leaving the script accessible from browser is a major security risk. Anybody will be able to run it, delete your database and set up new user account. Delete the file or disable it by setting %s value back to %s."; // substitutes variable name and value
$langSetup["setupfailed"] = "Unfortunately something has gone wrong. You may try to find more info in your webserver logs.";
$langSetup["welcome"] = "Welcome to µlogger!";
$langSetup["disabledwarn"] = "For security reasons this script is disabled by default. To enable it you must edit 'scripts/setup.php' file in text editor and set %s variable at the beginning of the file to %s."; // substitutes variable name and value
$langSetup["lineshouldread"] = "Line: %s should read: %s";
$langSetup["dorestart"] = "Please restart this script when you are done.";
$langSetup["createconfig"] = "Please create 'config.php' file in root folder. You may start by copying it from 'config.default.php'. Make sure that you adjust config values to match your needs and your database setup.";
$langSetup["nodbsettings"] = "You must provide your database credentials in 'config.php' file (%s)."; // substitutes variable names
$langSetup["scriptdesc"] = "This script will set up tables needed for µlogger (%s). They will be created in your database named %s. Warning, if the tables already exist they will be dropped and recreated, their content will be destroyed."; // substitutes table names and db name
$langSetup["scriptdesc2"] = "When done the script will ask you to provide user name and password for your µlogger user.";
$langSetup["startbutton"] = "Press to start";
$langSetup["restartbutton"] = "Restart";
$langSetup["optionwarn"] = "PHP configuration option %s must be set to %s."; // substitutes option name and value
$langSetup["extensionwarn"] = "Required PHP extension %s is not available."; // substitutes extension name


// application strings
$lang["title"] = "• μlogger •";
$lang["private"] = "Voor deze pagina moet je inloggen.";
$lang["authfail"] = "Verkeerde gebruikersnaam en/of wachtwoord";
$lang["user"] = "Gebruiker";
$lang["track"] = "Track";
$lang["latest"] = "laatste positie";
$lang["autoreload"] = "Auto Vernieuwen";
$lang["reload"] = "Vernieuw nu";
$lang["export"] = "Export track";
$lang["chart"] = "Hoogte Kaart";
$lang["close"] = "sluiten";
$lang["time"] = "Tijd";
$lang["speed"] = "Snelheid";
$lang["accuracy"] = "Nauwkeurigheid";
$lang["position"] = "Position";
$lang["altitude"] = "Hoogte";
$lang["bearing"] = "Bearing";
$lang["ttime"] = "Totale tijd";
$lang["aspeed"] = "Gemiddelde snelheid";
$lang["tdistance"] = "Totale afstand";
$lang["pointof"] = "Punt %d van %d"; // e.g. Point 3 of 10
$lang["summary"] = "Trip samenvatting";
$lang["suser"] = "selecteer gebruiker";
$lang["logout"] = "Log out";
$lang["login"] = "Log in";
$lang["username"] = "Gebruiker";
$lang["password"] = "Wachtwoord";
$lang["language"] = "Taal";
$lang["newinterval"] = "Geef een nieuwe interval waarde (seconds)";
$lang["api"] = "Map API";
$lang["units"] = "Units";
$lang["metric"] = "Metrisch";
$lang["imperial"] = "Imperial/US";
$lang["nautical"] = "Nautical";
$lang["admin"] = "Administrator";
$lang["adminmenu"] = "Administratie";
$lang["passwordrepeat"] = "Herhaal password";
$lang["passwordenter"] = "Voer password in";
$lang["usernameenter"] = "Voer gebruikersnaam in";
$lang["adduser"] = "Voeg gebruiker toe";
$lang["userexists"] = "Gebruiker bestaat al";
$lang["cancel"] ="Afbreken";
$lang["submit"] = "Doorgaan";
$lang["oldpassword"] = "Oud password";
$lang["newpassword"] = "Nieuw password";
$lang["newpasswordrepeat"] = "Herhaal nieuw password";
$lang["changepass"] = "Verander password";
$lang["gps"] = "GPS";
$lang["network"] = "Netwerk";
$lang["deluser"] = "Verwijder Gebruiker";
$lang["edituser"] = "Verander Gebruiker";
$lang["servererror"] = "Server fout";
$lang["allrequired"] = "Alle velden zijn benodigd";
$lang["passnotmatch"] = "Passwords zijn niet gelijk";
$lang["actionsuccess"] = "Actie succesvol afgerond";
$lang["actionfailure"] = "Iets ging verkeerd";
$lang["notauthorized"] = "User not authorized";
$lang["userdelwarn"] = "Pas op!\n\nJe gaat definitief gebruiker %s weghalen, inclusief alle routes en tracks.\n\nWeet je het zeker?"; // substitutes user login
$lang["editinguser"] = "Je bent gebruiker %s aan het veranderen"; // substitutes user login
$lang["selfeditwarn"] = "Je kunt niet je eigen gebruiker hier veranderen";
$lang["apifailure"] = "Sorry, kan API %s niet laden"; // substitutes api name (gmaps or openlayers)
$lang["trackdelwarn"] = "Pas Op !\n\nJe gaat definitief track %s met alle posities weghalen.\n\nWeet je het zeker?"; // substitutes track name
$lang["editingtrack"] = "Je bent track %s aan het veranderen"; // substitutes track name
$lang["deltrack"] = "Verwijder track";
$lang["trackname"] = "Track naam";
$lang["edittrack"] = "Verander track";
$lang["positiondelwarn"] = "Warning!\n\nYou are going to permanently delete position %d of track %s.\n\nAre you sure?"; // substitutes position index and track name
$lang["editingposition"] = "You are editing position #%d of track %s"; // substitutes position index and track name
$lang["delposition"] = "Remove position";
$lang["comment"] = "Comment";
$lang["editposition"] = "Edit position";
$lang["passlenmin"] = "Password moet minsten %d tekens hebben"; // substitutes password minimum length
$lang["passrules_1"] = "Het moet minstens één kleine- en één grote-letter hebben";
$lang["passrules_2"] = "Het moet minstens één kleine-letter, één grote-letter, en één cijfer hebben";
$lang["passrules_3"] = "Het moet minstens één kleine-letter, één grote-letter, één cijfer, en één ander teken hebben";
$lang["owntrackswarn"] = "Je kunt alleen je eigen tracks veranderen";
$lang["gmauthfailure"] = "Er is een probleem met de Google Maps API key op deze pagina";
$lang["gmapilink"] = "Je vindt meer info over API keys op <a target=\"_blank\" href=\"https://developers.google.com/maps/documentation/javascript/get-api-key\">this Google webpage</a>";
$lang["import"] = "Importeer track";
$lang["iuploadfailure"] = "Uploadie mislukt";
$lang["iparsefailure"] = "Verwerking mislukt";
$lang["idatafailure"] = "Geen track data in geïmporteerd bestand";
$lang["isizefailure"] = "Het upload bestand kan niet groter zijn dan %d bytes"; // substitutes number of bytes
$lang["imultiple"] = "Info, meerdere tracks geïmporteerd (%d)"; // substitutes number of imported tracks
$lang["allusers"] = "All users";
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
