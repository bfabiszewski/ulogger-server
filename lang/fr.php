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
$lang["private"] = "Il faut un nom d'utilisateur et un mot de passe pour accéder à cette page.";
$lang["authfail"] = "Nom d'utilisateur ou mot de passe erroné.";
$lang["user"] = "Utilisateur";
$lang["track"] = "Tracé";
$lang["latest"] = "dernière position";
$lang["autoreload"] = "actualisation automatique";
$lang["reload"] = "Actualiser maintenant";
$lang["export"] = "Télécharger les données";
$lang["chart"] = "Courbes d'altitudes";
$lang["close"] = "fermer";
$lang["time"] = "Temps";
$lang["speed"] = "Vitesse";
$lang["accuracy"] = "Accélération";
$lang["position"] = "Position";
$lang["altitude"] = "Altitude";
$lang["bearing"] = "Bearing";
$lang["ttime"] = "Temps total";
$lang["aspeed"] = "Vitesse moyenne";
$lang["tdistance"] = "Dist. totale";
$lang["pointof"] = "Point %d de %d"; // e.g. Point 3 of 10
$lang["summary"] = "Résumé du trajet";
$lang["suser"] = "Sélectionner un utilisateur";
$lang["logout"] = "Déconnexion";
$lang["login"] = "Connexion";
$lang["username"] = "Nom d'utilisateur";
$lang["password"] = "Mot de passe";
$lang["language"] = "Langue";
$lang["newinterval"] = "Entrez un intervalle (en seconde)";
$lang["api"] = "API des cartes";
$lang["units"] = "Unités";
$lang["metric"] = "Système métrique";
$lang["imperial"] = "Système anglophone";
$lang["nautical"] = "Système nautique";
$lang["admin"] = "Administrator";
$lang["adminmenu"] = "Administration";
$lang["passwordrepeat"] = "Répetez le mot de passe";
$lang["passwordenter"] = "Entrez votre mot de passe";
$lang["usernameenter"] = "Entrez votre nom d'utilisateur";
$lang["adduser"] = "Ajouter un utilisateur";
$lang["userexists"] = "Cet utilisateur existe déjà";
$lang["cancel"] ="Annuler";
$lang["submit"] = "Envoyer";
$lang["oldpassword"] = "Ancien mot de passe";
$lang["newpassword"] = "Nouveau mot de passe";
$lang["newpasswordrepeat"] = "Répétez le mot de passe";
$lang["changepass"] = "Changer le mot de passe";
$lang["gps"] = "GPS";
$lang["network"] = "Réseau";
$lang["deluser"] = "Supprimer l'utilisateur";
$lang["edituser"] = "Modifier l'utilisateur";
$lang["servererror"] = "Erreur serveur";
$lang["allrequired"] = "Tous les champs sont requis";
$lang["passnotmatch"] = "Les mots de passe sont différents";
$lang["actionsuccess"] = "Action effectuée avec succès";
$lang["actionfailure"] = "Echec de l'action";
$lang["notauthorized"] = "User not authorized";
$lang["userdelwarn"] = "Attention !\n\nVous êtes sur le point de supprimer de manière permanente l'utilisateur %s, ainsi que toutes ses pistes et positions.\n\nÊtes-vous sûr ?"; // substitutes user login
$lang["editinguser"] = "Vous êtes en train d'éditer l'utilisateur %s"; // substitutes user login
$lang["selfeditwarn"] = "Vous ne pouvez pas éditer votre propre utilisateur de cette manière";
$lang["apifailure"] = "Navré, impossible de charger l'API de %s"; // substitutes api name (gmaps or openlayers)
$lang["trackdelwarn"] = "Attention !\n\nVous êtes sur le point de supprimer de manière permanente la piste %s et toutes ses positions.\n\nÊtes-vous sûr ?"; // substitutes track name
$lang["editingtrack"] = "Vous êtes en train de modifier la piste %s"; // substitutes track name
$lang["deltrack"] = "Supprimer la piste";
$lang["trackname"] = "Nom de la piste";
$lang["edittrack"] = "Modifier la piste";
$lang["positiondelwarn"] = "Warning!\n\nYou are going to permanently delete position %d of track %s.\n\nAre you sure?"; // substitutes position index and track name
$lang["editingposition"] = "You are editing position #%d of track %s"; // substitutes position index and track name
$lang["delposition"] = "Remove position";
$lang["comment"] = "Comment";
$lang["editposition"] = "Edit position";
$lang["passlenmin"] = "Le mot de passe doit contenir au moins %d caractères"; // substitutes password minimum length
$lang["passrules_1"] = "Il doit contenir au moins une lettre minuscule et une lettre majuscule";
$lang["passrules_2"] = "Il doit contenir au moins une lettre minuscule, une lettre majuscule et un chiffre";
$lang["passrules_3"] = "Il doit contenir au moins une lettre minuscule, une lettre majuscule, un chiffre et un caractère spécial";
$lang["owntrackswarn"] = "Vous pouvez uniquement modifier vos propres pistes";
$lang["gmauthfailure"] = "There may be problem with Google Maps API key on this page";
$lang["gmapilink"] = "You may find more information about API keys on <a target=\"_blank\" href=\"https://developers.google.com/maps/documentation/javascript/get-api-key\">this Google webpage</a>";
$lang["import"] = "Import track";
$lang["iuploadfailure"] = "Uploading failed";
$lang["iparsefailure"] = "Parsing failed";
$lang["idatafailure"] = "No track data in imported file";
$lang["isizefailure"] = "The uploaded file size should not exceed %d bytes"; // substitutes number of bytes
$lang["imultiple"] = "Notice, multiple tracks imported (%d)"; // substitutes number of imported tracks
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
