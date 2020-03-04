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
$lang["private"] = "Necesitas un nombre de usuario y contraseña para acceder a esta página.";
$lang["authfail"] = "Nombre de usuasrio o contraseña erroneos";
$lang["user"] = "Usuario";
$lang["track"] = "Rastro";
$lang["latest"] = "última posición";
$lang["autoreload"] = "autorecarga";
$lang["reload"] = "Recargar ahora";
$lang["export"] = "Descargar datos";
$lang["chart"] = "Gráfico de altitudes";
$lang["close"] = "cerrar";
$lang["time"] = "Hora";
$lang["speed"] = "Velocidad";
$lang["accuracy"] = "Precisión";
$lang["position"] = "Position";
$lang["altitude"] = "Altitud";
$lang["bearing"] = "Bearing";
$lang["ttime"] = "Tiempo total";
$lang["aspeed"] = "Velocidad media";
$lang["tdistance"] = "Distancia total";
$lang["pointof"] = "Punto %d de %d"; // e.g. Point 3 of 10
$lang["summary"] = "Resumen del viaje";
$lang["suser"] = "seleccione usuario";
$lang["logout"] = "Cerrar sesión";
$lang["login"] = "Identificarse";
$lang["username"] = "Nombre de usuario";
$lang["password"] = "Contraseña";
$lang["language"] = "Lenguaje";
$lang["newinterval"] = "Introduzca nuevo valor para el intervalo (segundos)";
$lang["api"] = "Mapa API";
$lang["units"] = "Unidades";
$lang["metric"] = "Metricas";
$lang["imperial"] = "Imperiales/US";
$lang["nautical"] = "Nautical";
$lang["admin"] = "Administrator";
$lang["adminmenu"] = "Administración";
$lang["passwordrepeat"] = "Repita contraseña";
$lang["passwordenter"] = "Introduzca contraseña";
$lang["usernameenter"] = "Introduzca nombre de usuario";
$lang["adduser"] = "Añadir usuario";
$lang["userexists"] = "Ususario ya existe";
$lang["cancel"] ="Cancelar";
$lang["submit"] = "Enviar";
$lang["oldpassword"] = "Contraseña vieja";
$lang["newpassword"] = "Nueva contraseña";
$lang["newpasswordrepeat"] = "Repita nueva contraseña";
$lang["changepass"] = "Cambiar contraseña";
$lang["gps"] = "GPS";
$lang["network"] = "Red";
$lang["deluser"] = "Eliminar usuario";
$lang["edituser"] = "Editar usuario";
$lang["servererror"] = "Error del servidor";
$lang["allrequired"] = "Todos los campos son necesarios";
$lang["passnotmatch"] = "Las contraseñas no coinciden";
$lang["actionsuccess"] = "Acción completada correctamente";
$lang["actionfailure"] = "Ha ocurrido un error";
$lang["notauthorized"] = "User not authorized";
$lang["userdelwarn"] = "Precaución!\n\nVas a eliminar permanentemente al usuario %s, junto con todas sus rutas y posiciones.\n\n¿Estás seguro?"; // substitutes user login
$lang["editinguser"] = "Estás editando el usuario %s"; // substitutes user login
$lang["selfeditwarn"] = "No puedes editar tu propio usuario";
$lang["apifailure"] = "Upss, no se pueda cargar la API %s"; // substitutes api name (gmaps or openlayers)
$lang["trackdelwarn"] = "Precaución!\n\nVas a eliminar permanentemente la ruta %s y todas sus posiciones.\n\n¿Estás seguro?"; // substitutes track name
$lang["editingtrack"] = "Estás editando la ruta %s"; // substitutes track name
$lang["deltrack"] = "Eliminar ruta";
$lang["trackname"] = "Nombre de ruta";
$lang["edittrack"] = "Editar ruta";
$lang["positiondelwarn"] = "Warning!\n\nYou are going to permanently delete position %d of track %s.\n\nAre you sure?"; // substitutes position index and track name
$lang["editingposition"] = "You are editing position #%d of track %s"; // substitutes position index and track name
$lang["delposition"] = "Remove position";
$lang["comment"] = "Comment";
$lang["editposition"] = "Edit position";
$lang["passlenmin"] = "La contraseña debe tener al menos %d caracteres"; // substitutes password minimum length
$lang["passrules_1"] = "Debe contener al menos una letra minúscula y una mayúscula.";
$lang["passrules_2"] = "Debe contener al menos una letra minúscula, una mayúscula y un número";
$lang["passrules_3"] = "Debe contener al menos una letra minúscula, una mayúscula, un número y un caracter no alfanumérico";
$lang["owntrackswarn"] = "Solo puedes editar tus propias rutas";
$lang["gmauthfailure"] = "Es posible que haya un problema con la clave de la API de Google Maps";
$lang["gmapilink"] = "Puedes encontrar más información sobre las claves de API en <a target=\"_blank\" href=\"https://developers.google.com/maps/documentation/javascript/get-api-key\">esta página de Google</a>";
$lang["import"] = "Importar ruta";
$lang["iuploadfailure"] = "Ha ocurrido un error en la carga";
$lang["iparsefailure"] = "Ha ocurrido un error en el análisis";
$lang["idatafailure"] = "No hay datos de ruta en el archivo importado";
$lang["isizefailure"] = "EL tamaño del archivo no debe superar los %d bytes"; // substitutes number of bytes
$lang["imultiple"] = "Varias rutas importadas (%d)"; // substitutes number of imported tracks
$lang["allusers"] = "Todos los usuarios";
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
