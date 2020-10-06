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
$langSetup["dbconnectfailed"] = "Error de conexión con la base de datos.";
$langSetup["serversaid"] = "Respuesta del servidor: %s"; // substitutes server error message
$langSetup["checkdbsettings"] = "Verifique los parámetros de conexión con la base de datos en el archivo 'config.php'.";
$langSetup["dbqueryfailed"] = "Consulta con la base de datos fallida.";
$langSetup["dbtablessuccess"] = "Tablas creadas con éxito en la base de datos!";
$langSetup["setupuser"] = "Indique el usuario del servidor μlogger.";
$langSetup["congratulations"] = "Enhorabuena!";
$langSetup["setupcomplete"] = "Instalación completa. Acceda a <a href=\"../index.php\">pagina principal</a> y entre con su nuevo usuario.";
$langSetup["disablewarn"] = "IMPORTANTE! Deshabilite el SCRIPT 'setup.php' O BÓRRELO de su servidor.";
$langSetup["disabledesc"] = "Dejar este script accesible es un gran riesgo de seguridad. Cualquiera podría ejecutarlo, borrar su base de datos y crear un nuevo usuario. Borre este archivo o deshabilítelo cambiando el valor %s a %s."; // substitutes variable name and value
$langSetup["setupfailed"] = "Algo ha ido mal. Puede intentar encontrar más información en los logs del servidor web.";
$langSetup["welcome"] = "Bienvenido a µlogger!";
$langSetup["disabledwarn"] = "Por razones de seguridad este script está deshabilitado por defecto. Para activarlo puede editar el archivo 'scripts/setup.php' con un editor de texto y cambiar la variable %s por %s."; // substitutes variable name and value
$langSetup["lineshouldread"] = "Línea: %s debería leer: %s";
$langSetup["dorestart"] = "Ejecute de nuevo este script cuando esté listo.";
$langSetup["createconfig"] = "Cree el archivo 'config.php' en el directorio raíz. Puede crearlo partiendo del archivo de ejemplo 'config.default.php'. Asegúrese de cambiar los valores de la base de datos por los suyos.";
$langSetup["nodbsettings"] = "Indique los parámetros de conexión con la base de datos en el archivo 'config.php' (%s)."; // substitutes variable names
$langSetup["scriptdesc"] = "Este script crea las tablas necesarias para µlogger (%s). Se creará en su base de datos con nombre %s. Atención, si las tablas existen, serán borradas y creadas de nuevo, y su contenido se perderá."; // substitutes table names and db name
$langSetup["scriptdesc2"] = "Cuando finaliza el script se le pedirá que indique un usuario y una contraseña para crear su nuevo usuario en el servidor µlogger.";
$langSetup["startbutton"] = "Iniciar";
$langSetup["restartbutton"] = "Reiniciar";
$langSetup["optionwarn"] = "En la configuración PHP el valor de la opción %s debe ser %s."; // substitutes option name and value
$langSetup["extensionwarn"] = "La extensión PHP %s no está disponible."; // substitutes extension name
$langSetup["notwritable"] = "El directorio '%s' debe tener permisos de escritura para PHP."; // substitutes folder path


// application strings
$lang["title"] = "• μlogger •";
$lang["private"] = "Necesitas un nombre de usuario y contraseña para acceder a esta página.";
$lang["authfail"] = "Nombre de usuario o contraseña erróneos";
$lang["user"] = "Usuario";
$lang["track"] = "Ruta";
$lang["latest"] = "última posición";
$lang["autoreload"] = "autorecarga";
$lang["reload"] = "Recargar ahora";
$lang["export"] = "Descargar datos";
$lang["chart"] = "Gráfico de altitudes";
$lang["close"] = "cerrar";
$lang["time"] = "Hora";
$lang["speed"] = "Velocidad";
$lang["accuracy"] = "Precisión";
$lang["position"] = "Posición";
$lang["altitude"] = "Altitud";
$lang["bearing"] = "Rumbo";
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
$lang["metric"] = "Métricas";
$lang["imperial"] = "Imperiales/US";
$lang["nautical"] = "Náuticas";
$lang["admin"] = "Administrador";
$lang["adminmenu"] = "Administración";
$lang["passwordrepeat"] = "Repita contraseña";
$lang["passwordenter"] = "Introduzca contraseña";
$lang["usernameenter"] = "Introduzca nombre de usuario";
$lang["adduser"] = "Añadir usuario";
$lang["userexists"] = "Usuario ya existe";
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
$lang["notauthorized"] = "Usuario no autorizado";
$lang["userdelwarn"] = "Precaución!\n\nVas a eliminar permanentemente al usuario %s, junto con todas sus rutas y posiciones.\n\n¿Estás seguro?"; // substitutes user login
$lang["editinguser"] = "Estás editando el usuario %s"; // substitutes user login
$lang["selfeditwarn"] = "No puedes editar tu propio usuario";
$lang["apifailure"] = "Upss, no se pueda cargar la API %s"; // substitutes api name (gmaps or openlayers)
$lang["trackdelwarn"] = "Precaución!\n\nVas a eliminar permanentemente la ruta %s y todas sus posiciones.\n\n¿Estás seguro?"; // substitutes track name
$lang["editingtrack"] = "Estás editando la ruta %s"; // substitutes track name
$lang["deltrack"] = "Eliminar ruta";
$lang["trackname"] = "Nombre de ruta";
$lang["edittrack"] = "Editar ruta";
$lang["positiondelwarn"] = "Atención!\n\nVas a eliminar de forma permanente la posición %d de la ruta %s.\n\n¿Estás seguro?"; // substitutes position index and track name
$lang["editingposition"] = "Estás editando la posición %d de la ruta %s"; // substitutes position index and track name
$lang["delposition"] = "Borrar posición";
$lang["delimage"] = "Borrar imagen";
$lang["comment"] = "Comentario";
$lang["image"] = "Imagen";
$lang["editposition"] = "Editar posición";
$lang["passlenmin"] = "La contraseña debe tener al menos %d caracteres"; // substitutes password minimum length
$lang["passrules_1"] = "Debe contener al menos una letra minúscula y una mayúscula.";
$lang["passrules_2"] = "Debe contener al menos una letra minúscula, una mayúscula y un número";
$lang["passrules_3"] = "Debe contener al menos una letra minúscula, una mayúscula, un número y un carácter no alfanumérico";
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
$lang["unitamsl"] = "a.s.l."; // above mean see level
$lang["unitkm"] = "km"; // kilometer
$lang["unitmph"] = "mph"; // mile per hour
$lang["unitft"] = "ft"; // feet
$lang["unitmi"] = "mi"; // mile
$lang["unitkt"] = "kt"; // knot
$lang["unitnm"] = "nm"; // nautical mile
$lang["config"] = "Configuración";
$lang["editingconfig"] = "Parámetros definidos";
$lang["latitude"] = "Latitud inicial";
$lang["longitude"] = "longitud inicial";
$lang["interval"] = "Intervalo (s)";
$lang["googlekey"] = "Clave de la API Google Maps";
$lang["passlength"] = "Longitud mínima de la contraseña";
$lang["passstrength"] = "Complejidad mínima de la contraseña";
$lang["requireauth"] = "Requiere autorización";
$lang["publictracks"] = "Rutas públicas";
$lang["strokeweight"] = "Amplitud del trazo";
$lang["strokeopacity"] = "Opacidad del trazo";
$lang["strokecolor"] = "Color del trazo";
$lang["colornormal"] = "Color del marcador";
$lang["colorstart"] = "Color del marcador de inicio";
$lang["colorstop"] = "Color del marcador de final";
$lang["colorextra"] = "Color del marcador extra";
$lang["colorhilite"] = "Color del marcador de Hilite";
$lang["uploadmaxsize"] = "Tamaño máximo de carga (MB)";
$lang["ollayers"] = "Capa OpenLayers";
$lang["layername"] = "Nombre de la capa";
$lang["layerurl"] = "URL de la capa";
$lang["add"] = "Añadir";
$lang["edit"] = "Editar";
$lang["delete"] = "Borrar";
$lang["settings"] = "Configuración";
$lang["trackcolor"] = "Color de la ruta";
?>
