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
$langSetup["passfuncwarn"] = "Your PHP version does not support password functions that ship with PHP 5.5. You have to include password_compat library.";
$langSetup["passfunchack"] = "Please edit 'helpers/user.php' file and uncomment line including 'helpers/password.php'.";
$langSetup["dorestart"] = "Please restart this script when you are done.";
$langSetup["createconfig"] = "Please create 'config.php' file in root folder. You may start by copying it from 'config.default.php'. Make sure that you adjust config values to match your needs and your database setup.";
$langSetup["nodbsettings"] = "You must provide your database credentials in 'config.php' file (%s)."; // substitutes variable names
$langSetup["scriptdesc"] = "This script will set up tables needed for µlogger (%s). They will be created in your database named %s. Warning, if the tables already exist they will be dropped and recreated, their content will be destroyed."; // substitutes table names and db name
$langSetup["scriptdesc2"] = "When done the script will ask you to provide user name and password for your µlogger user.";
$langSetup["startbutton"] = "Press to start";
$langSetup["restartbutton"] = "Restart";


// application strings
$lang["title"] = "• μlogger •";
$lang["private"] = "You need login and password to access this page.";
$lang["authfail"] = "Wrong username or password";
$lang["user"] = "User";
$lang["track"] = "Track";
$lang["latest"] = "latest position";
$lang["autoreload"] = "autoreload";
$lang["reload"] = "Reload now";
$lang["export"] = "Export track";
$lang["chart"] = "Altitudes chart";
$lang["close"] = "close";
$lang["time"] = "Time";
$lang["speed"] = "Speed";
$lang["accuracy"] = "Accuracy";
$lang["altitude"] = "Altitude";
$lang["ttime"] = "Total time";
$lang["aspeed"] = "Average speed";
$lang["tdistance"] = "Total distance";
$lang["pointof"] = "Point %d of %d"; // e.g. Point 3 of 10
$lang["summary"] = "Trip summary";
$lang["suser"] = "select user";
$lang["logout"] = "Log out";
$lang["login"] = "Log in";
$lang["username"] = "Username";
$lang["password"] = "Password";
$lang["language"] = "Language";
$lang["newinterval"] = "Enter new interval value (seconds)";
$lang["api"] = "Map API";
$lang["units"] = "Units";
$lang["metric"] = "Metric";
$lang["imperial"] = "Imperial/US";
$lang["adminmenu"] = "Administration";
$lang["passwordrepeat"] = "Repeat password";
$lang["passwordenter"] = "Enter password";
$lang["usernameenter"] = "Enter username";
$lang["adduser"] = "Add user";
$lang["userexists"] = "User exists";
$lang["cancel"] ="Cancel";
$lang["submit"] = "Submit";
$lang["oldpassword"] = "Old password";
$lang["newpassword"] = "New password";
$lang["newpasswordrepeat"] = "Repeat new password";
$lang["changepass"] = "Change password";
$lang["gps"] = "GPS";
$lang["network"] = "Network";
$lang["deluser"] = "Remove user";
$lang["edituser"] = "Edit user";
$lang["servererror"] = "Server error";
$lang["allrequired"] = "All fields are required";
$lang["passnotmatch"] = "Passwords don't match";
$lang["actionsuccess"] = "Action completed successfully";
$lang["actionfailure"] = "Something went wrong";
$lang["userdelwarn"] = "Warning!\n\nYou are going to permanently delete user %s, together with all their routes and positions.\n\nAre you sure?"; // substitutes user login
$lang["editinguser"] = "You are editing user %s"; // substitutes user login
$lang["selfeditwarn"] = "Your can't edit your own user with this tool";
$lang["apifailure"] = "Sorry, can't load %s API"; // substitures api name (gmaps or openlayers)
$lang["trackdelwarn"] = "Warning!\n\nYou are going to permanently delete track %s and all its positions.\n\nAre you sure?"; // substitutes track name
$lang["editingtrack"] = "You are editing track %s"; // substitutes track name
$lang["deltrack"] = "Remove track";
$lang["trackname"] = "Track name";
$lang["edittrack"] = "Edit track";
$lang["passlenmin"] = "Password must be at least %d characters"; // substitutes password minimum length
$lang["passrules"][1] = "It should contain at least one lower case letter, one upper case letter";
$lang["passrules"][2] = "It should contain at least one lower case letter, one upper case letter and one digit";
$lang["passrules"][3] = "It should contain at least one lower case letter, one upper case letter, one digit and one non-alphanumeric character";
$lang["owntrackswarn"] = "Your can only edit your own tracks";
$lang["gmauthfailure"] = "There may be problem with Google Maps API key on this page";
$lang["gmapilink"] = "You may find more information about API keys on <a target=\"_blank\" href=\"https://developers.google.com/maps/documentation/javascript/get-api-key\">this Google webpage</a>";
$lang["import"] = "Import track";
$lang["iuploadfailure"] = "Uploading failed";
$lang["iparsefailure"] = "Parsing failed";
$lang["idatafailure"] = "No track data in imported file";
$lang["isizefailure"] = "The uploaded file size should not exceed %d bytes"; // substitutes number of bytes
$lang["imultiple"] = "Notice, multiple tracks imported (%d)"; // substitutes number of imported tracks
?>
