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
$langSetup["dbconnectfailed"] = "Datenbankverbindung fehlgeschlagen";
$langSetup["serversaid"] = "Serverantwort: %s"; // substitutes server error message
$langSetup["checkdbsettings"] = "Überprüfe die Datenbankeinstellungen in der Datei 'config.php'";
$langSetup["dbqueryfailed"] = "Datenbankabfrage fehlgeschlagen.";
$langSetup["dbtablessuccess"] = "Datenbank-Tabellen erfolgreich angelegt.";
$langSetup["setupuser"] = "Lege jetzt deinen Benutzer an.";
$langSetup["congratulations"] = "Herzlichen Glückwunsch!";
$langSetup["setupcomplete"] = "Die Einrichtung ist abgeschlossen. Rufe die <a href=\"../index.php\">Hauptseite</a> auf und logge Dich mit deinem Benutzer ein.";
$langSetup["disablewarn"] = "WICHTIG! Du musst die 'setup.php' deaktivieren oder vom Server löschen.";
$langSetup["disabledesc"] = "Leaving the script accessible from browser is a major security risk. Anybody will be able to run it, delete your database and set up new user account. Delete the file or disable it by setting %s value back to %s."; // substitutes variable name and value
$langSetup["setupfailed"] = "Das hat leider nicht funktioniert. Du findest möglicherweise weitere Informationen in den Logs deines Webservers.";
$langSetup["welcome"] = "Willkommen bei µlogger!";
$langSetup["disabledwarn"] = "Aus Sicherheitsgründen ist das Skript standardmäßig deaktiviert. Zum Aktivieren editiere die Datei 'scripts/setup.php' und setze die Variable %s am Anfang der Datei auf den Wert %s."; // substitutes variable name and value
$langSetup["lineshouldread"] = "Zeile %s sollte den Wert %s enthalten.";
$langSetup["dorestart"] = "Rufe das Skript erneut auf wenn das erledigt ist.";
$langSetup["createconfig"] = "Bitte erstellt die Datei 'config.php' im Stammverzeichnis. Du kannst die Datei 'config.default.php' umbenennen oder kopieren. Stelle sicher, dass in der Datei die korrekten Einstellungen hinterlegt sind.";
$langSetup["nodbsettings"] = "Die Verbindungsdaten zur Datenbank müssen in der Datei 'config.php' hinterlegt werden. (%s)"; // substitutes variable names
$langSetup["scriptdesc"] = "Das Skript richtet die Datenbanktabellen für µlogger (%s) in der Datenbank %s ein.\n\n
ACHTUNG: Wenn die Tabellen bereits vorhanden sind, werden sie gelöscht und neu angelegt. Der Inhalt geht dadurch verloren!"; // substitutes table names and db name
$langSetup["scriptdesc2"] = "When done the script will ask you to provide user name and password for your µlogger user.";
$langSetup["startbutton"] = "Klicken zum Starten";
$langSetup["restartbutton"] = "Neu starten";
$langSetup["optionwarn"] = "Die PHP-Konfiguration %s muss auf den Wert %s gesetzt sein."; // substitutes option name and value
$langSetup["extensionwarn"] = "Die PHP-Erweiterung %s ist nicht verfügbar."; // substitutes extension name


// application strings
$lang["title"] = "• μlogger •";
$lang["private"] = "Sie müssen Benutzername und Passwort eingeben, um auf diese Seite zuzugreifen.";
$lang["authfail"] = "Falscher Benutzername oder Passwort";
$lang["user"] = "Benutzer";
$lang["track"] = "Route";
$lang["latest"] = "letzte Position";
$lang["autoreload"] = "Autoneuladen";
$lang["reload"] = "Neuladen";
$lang["export"] = "Route exportieren";
$lang["chart"] = "Höhentabelle";
$lang["close"] = "Schließen";
$lang["time"] = "Zeit";
$lang["speed"] = "Geschwindigkeit";
$lang["accuracy"] = "Genauigkeit";
$lang["position"] = "Position";
$lang["altitude"] = "Höhe";
$lang["bearing"] = "Bearing";
$lang["ttime"] = "Gesamte Zeit";
$lang["aspeed"] = "Durchschnittstempo";
$lang["tdistance"] = "Distanz";
$lang["pointof"] = "Punkt %d von %d"; // e.g. Point 3 of 10
$lang["summary"] = "Infos zur Route";
$lang["suser"] = "Wähle Benutzer";
$lang["logout"] = "Abmelden";
$lang["login"] = "Anmelden";
$lang["username"] = "Benutzername";
$lang["password"] = "Passwort";
$lang["language"] = "Sprache";
$lang["newinterval"] = "Neuen Intervall-Wert eingeben (in Sekunden)";
$lang["api"] = "Karten API";
$lang["units"] = "Maßsystem";
$lang["metric"] = "Metrisch";
$lang["imperial"] = "US-Amerikanisch";
$lang["nautical"] = "Nautisch";
$lang["admin"] = "Administrator";
$lang["adminmenu"] = "Verwaltung";
$lang["passwordrepeat"] = "Passwort wiederholen";
$lang["passwordenter"] = "Passwort eingeben";
$lang["usernameenter"] = "Benutzername eingeben";
$lang["adduser"] = "Benutzer hinzufügen";
$lang["userexists"] = "Benutzer existiert bereits!";
$lang["cancel"] ="Abbrechen";
$lang["submit"] = "Speichern";
$lang["oldpassword"] = "Altes Kennwort";
$lang["newpassword"] = "Neues Passwort";
$lang["newpasswordrepeat"] = "Neues Passwort wiederholen";
$lang["changepass"] = "Passwort ändern";
$lang["gps"] = "GPS";
$lang["network"] = "Netzwerk";
$lang["deluser"] = "Benutzer löschen";
$lang["edituser"] = "Benutzer bearbeiten";
$lang["servererror"] = "Serverfehler";
$lang["allrequired"] = "Alle Felder müssen ausgefüllt werden";
$lang["passnotmatch"] = "Passwörter stimmen nicht überein!";
$lang["actionsuccess"] = "Aktion erfolgreich abgeschlossen";
$lang["actionfailure"] = "Da ist etwas schief gelaufen";
$lang["notauthorized"] = "User not authorized";
$lang["userdelwarn"] = "Achtung!\n\nDer Benutzer %s und all seine Routen werden endgültig gelöscht!\n\nBist Du sicher?"; // substitutes user login
$lang["editinguser"] = "Du bearbeitest Benutzer %s"; // substitutes user login
$lang["selfeditwarn"] = "Du kannst deinen eigenen Benutzer nicht bearbeiten.";
$lang["apifailure"] = "Die API %s kann nicht geladen werden"; // substitutes api name (gmaps or openlayers)
$lang["trackdelwarn"] = "Achtung!\n\nDie Route %s wird endgültig gelöscht!\n\nBist Du sicher?"; // substitutes track name
$lang["editingtrack"] = "Du bearbeitest Route %s"; // substitutes track name
$lang["deltrack"] = "Route löschen";
$lang["trackname"] = "Name der Route";
$lang["edittrack"] = "Route bearbeiten";
$lang["positiondelwarn"] = "Warning!\n\nYou are going to permanently delete position %d of track %s.\n\nAre you sure?"; // substitutes position index and track name
$lang["editingposition"] = "You are editing position #%d of track %s"; // substitutes position index and track name
$lang["delposition"] = "Remove position";
$lang["comment"] = "Comment";
$lang["editposition"] = "Edit position";
$lang["passlenmin"] = "Das Passwort muss mindestens %d Zeichen lang sein."; // substitutes password minimum length
$lang["passrules_1"] = "Es muss mindestens einen Groß- und einen Kleinbuchstaben enthalten.";
$lang["passrules_2"] = "Es muss mindestens einen Großbuchstaben, einen Kleinbuchstaben sowie eine Zahl enthalten.";
$lang["passrules_3"] = "Es muss mindestens einen Großbuchstaben, einen Kleinbuchstaben, eine Zahl sowie ein Sonderzeichen enthalten.";
$lang["owntrackswarn"] = "Du kannst nur deine eigenen Routen bearbeiten!";
$lang["gmauthfailure"] = "Der API-Schlüssel für Google Maps ist ungültig.";
$lang["gmapilink"] = "Weitere Informationen zum API-Schlüssel findest Du <a target=\"_blank\" href=\"https://developers.google.com/maps/documentation/javascript/get-api-key\">bei Google</a>.";
$lang["import"] = "Route importieren";
$lang["iuploadfailure"] = "Hochladen fehlgeschlagen";
$lang["iparsefailure"] = "Import der Route fehlgeschlagen";
$lang["idatafailure"] = "Die Datei enthält keine Routendaten";
$lang["isizefailure"] = "Die Größe der hochgeladenen Datei sollte %d Bytes nicht überschreiten."; // substitutes number of bytes
$lang["imultiple"] = "%d Routen importiert"; // substitutes number of imported tracks
$lang["allusers"] = "Alle Benutzer";
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
