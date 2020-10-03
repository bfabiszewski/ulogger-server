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
$langSetup["dbconnectfailed"] = "Error de connexió amb la base de dades.";
$langSetup["serversaid"] = "Resposta del servidor: %s"; // substitutes server error message
$langSetup["checkdbsettings"] = "Revisar els paràmetres de connexió amb la base de dades a 'config.php'.";
$langSetup["dbqueryfailed"] = "Error en consulta a la base de dades.";
$langSetup["dbtablessuccess"] = "Taules creades correctament!";
$langSetup["setupuser"] = "Indiqueu l'usuari de µlogger.";
$langSetup["congratulations"] = "Enhorabona!";
$langSetup["setupcomplete"] = "Instal·lació completa. Aneu a <a href=\"../index.php\">main page</a> i entreu amb el vostre usuari.";
$langSetup["disablewarn"] = "IMPORTANT! Desactivi el script 'setup.php' o esborri'l del servidor.";
$langSetup["disabledesc"] = "Deixar aquest script accessible és un risc de seguretat molt greu. Qualsevol persona podria executar-lo, esborrar la base de dades i crear un nou usuari. Esborri el fitxer o desactivi'l posant el valor %s a %s."; // substitutes variable name and value
$langSetup["setupfailed"] = "Alguna cosa ha fallat. Provi a trobar més informació als logs del servidor web.";
$langSetup["welcome"] = "Benvingut a µlogger!";
$langSetup["disabledwarn"] = "Per raons de seguretat aquest script està desactivat per defecte. Per activar-lo ha d'editar el fitxer 'scripts/setup.php' amb un editor de text i canviar la variable %s per %s."; // substitutes variable name and value
$langSetup["lineshouldread"] = "Línia: %s hauria de llegir: %s";
$langSetup["dorestart"] = "Executi de nou aquest script quan estigui preparat.";
$langSetup["createconfig"] = "Crear el fitxer 'config.php' al directori arrel. Pot crear-lo a partir del fitxer 'config.default.php'. Assegureu-vos de modificar les dades relatives a la connexió amb la base de dades per les seves.";
$langSetup["nodbsettings"] = "Ha d'indicar les credencials de la base de dades al fitxer 'config.php'  (%s)."; // substitutes variable names
$langSetup["scriptdesc"] = "Aquest script crearà les taules necessàries per a µlogger (%s). Es crearan a la base de dades %s. Atenció, si les taules existeixen s'esborraran i seran creades de nou, destruint les dades existents."; // substitutes table names and db name
$langSetup["scriptdesc2"] = "Al finalitzar l'execució es demanarà un usuari i contrasenya per accedir al servidor.";
$langSetup["startbutton"] = "Iniciar instal·lació";
$langSetup["restartbutton"] = "Reiniciar";
$langSetup["optionwarn"] = "L'opció de PHP %s ha de ser %s."; // substitutes option name and value
$langSetup["extensionwarn"] = "Extensió PHP %s no disponible."; // substitutes extension name
$langSetup["notwritable"] = "PHP ha de poder escriure al directori '%s'."; // substitutes folder path


// application strings
$lang["title"] = "• μlogger •";
$lang["private"] = "Cal usuari i contrasenya per accedir a aquesta pàgina.";
$lang["authfail"] = "Usuari o contrasenya erronis";
$lang["user"] = "Usuari";
$lang["track"] = "Track";
$lang["latest"] = "última posició";
$lang["autoreload"] = "autorecàrrega";
$lang["reload"] = "Recarregar ara";
$lang["export"] = "Descarregar dades";
$lang["chart"] = "Gràfic de altituds";
$lang["close"] = "tancar";
$lang["time"] = "Hora";
$lang["speed"] = "Velocitat";
$lang["accuracy"] = "Precisió";
$lang["position"] = "Posició";
$lang["altitude"] = "Altitud";
$lang["bearing"] = "Bearing";
$lang["ttime"] = "Temps total";
$lang["aspeed"] = "Velocitat mitja";
$lang["tdistance"] = "Distància total";
$lang["pointof"] = "Punt %d de %d"; // e.g. Point 3 of 10
$lang["summary"] = "Resum del viatge";
$lang["suser"] = "seleccioni usuari";
$lang["logout"] = "Tancar sessió";
$lang["login"] = "Identificar-se";
$lang["username"] = "Usuari";
$lang["password"] = "Contrasenya";
$lang["language"] = "Llengua";
$lang["newinterval"] = "Indiqui nou valor per l'interval (segons)";
$lang["api"] = "Mapa API";
$lang["units"] = "Unitats";
$lang["metric"] = "Mètriques";
$lang["imperial"] = "Imperials/US";
$lang["nautical"] = "Nàutiques";
$lang["admin"] = "Administrador";
$lang["adminmenu"] = "Administració";
$lang["passwordrepeat"] = "Repeteixi contrasenya";
$lang["passwordenter"] = "Indiqui contrasenya";
$lang["usernameenter"] = "Indiqui usuari";
$lang["adduser"] = "Afegir usuari";
$lang["userexists"] = "L'usuari ja existeix";
$lang["cancel"] ="Cancel·lar";
$lang["submit"] = "Enviar";
$lang["oldpassword"] = "Contrasenya anterior";
$lang["newpassword"] = "Nova contrasenya";
$lang["newpasswordrepeat"] = "Repetir la nova contrasenya";
$lang["changepass"] = "Canviar contrasenya";
$lang["gps"] = "GPS";
$lang["network"] = "Xarxa";
$lang["deluser"] = "Esborrar usuari";
$lang["edituser"] = "Editar usuari";
$lang["servererror"] = "Error del servidor";
$lang["allrequired"] = "Tots els camps són necessaris";
$lang["passnotmatch"] = "Les contrasenyes no coincideixen";
$lang["actionsuccess"] = "Acció completada correctament";
$lang["actionfailure"] = "Hi ha hagut un error";
$lang["notauthorized"] = "Usuari no autoritzat";
$lang["userdelwarn"] = "Precaució!\n\nEsborraràs permanentement l'usuari %s, totes les seves rutes i posicions.\n\nEstàs segur?"; // substitutes user login
$lang["editinguser"] = "Estàs editant l'usuari %s"; // substitutes user login
$lang["selfeditwarn"] = "No pots editar el teu propi usuari";
$lang["apifailure"] = "Upss, no es pot carregar la API %s"; // substitutes api name (gmaps or openlayers)
$lang["trackdelwarn"] = "Precaució!\n\nEliminaràs permanentement la ruta %s i totes les seces posicions.\n\nEstàs segur?"; // substitutes track name
$lang["editingtrack"] = "Estás editant la ruta %s"; // substitutes track name
$lang["deltrack"] = "Eliminar ruta";
$lang["trackname"] = "Nom de la ruta";
$lang["edittrack"] = "Editar ruta";
$lang["positiondelwarn"] = "Precaució!\n\nEsborraràs de forma permanent la posició %d del track %s.\n\nEstàs segur?"; // substitutes position index and track name
$lang["editingposition"] = "Estàs editant la posició #%d del track %s"; // substitutes position index and track name
$lang["delposition"] = "Esborrar posició";
$lang["delimage"] = "Esborrar imatge";
$lang["comment"] = "Comentari";
$lang["image"] = "Imatge";
$lang["editposition"] = "Editar posició";
$lang["passlenmin"] = "La contrasenya ha de tenir almenys %d caràcters"; // substitutes password minimum length
$lang["passrules_1"] = "Ha de tenir almenys una lletra minúscula i una majúscula.";
$lang["passrules_2"] = "Ha de tenir almenys una lletra minúscula, una majúscula i un número";
$lang["passrules_3"] = "Ha de tenir almenys una lletra minúscula i una majúscula, un número i un caràcter no alfanumèric";
$lang["owntrackswarn"] = "Només pots editar les teves rutes";
$lang["gmauthfailure"] = "Es possible que hi hagi un problema amb la clau de la API de Google Maps";
$lang["gmapilink"] = "Pots trobar informació sobre les claus API a <a target=\"_blank\" href=\"https://developers.google.com/maps/documentation/javascript/get-api-key\">aquesta pàgina de Google</a>";
$lang["import"] = "Importar ruta";
$lang["iuploadfailure"] = "Hi ha hagut un error en la càrrega";
$lang["iparsefailure"] = "Hi ha hagut un error en l'análisi";
$lang["idatafailure"] = "No hi ha dades de ruta en l'arxiu importat";
$lang["isizefailure"] = "El tamany de l'arxiu no pot superar els %d bytes"; // substitutes number of bytes
$lang["imultiple"] = "Vàries rutes importades (%d)"; // substitutes number of imported tracks
$lang["allusers"] = "Tots els usuaris";
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
$lang["config"] = "Configuració";
$lang["editingconfig"] = "Paràmetres definits";
$lang["latitude"] = "Latitud inicial";
$lang["longitude"] = "Longitud inicial";
$lang["interval"] = "Interval (s)";
$lang["googlekey"] = "Clau de la API Google Maps";
$lang["passlength"] = "Llargària mínima de la contrasenya";
$lang["passstrength"] = "Complexitat mínima de la contrasenya";
$lang["requireauth"] = "Necessita autorització";
$lang["publictracks"] = "Tracks públics";
$lang["strokeweight"] = "Amplada de la traça";
$lang["strokeopacity"] = "Opacitat de la traça";
$lang["strokecolor"] = "Color de la traça";
$lang["colornormal"] = "Color de la marca";
$lang["colorstart"] = "Color de la marca d'inici";
$lang["colorstop"] = "Color de la marca de final";
$lang["colorextra"] = "Color de la marca extra";
$lang["colorhilite"] = "Color de la marca Hilite";
$lang["uploadmaxsize"] = "Tamany màxim de càrrega (MB)";
$lang["ollayers"] = "Capa OpenLayers";
$lang["layername"] = "Nom de la capa";
$lang["layerurl"] = "URL de la capa";
$lang["add"] = "Afegir";
$lang["edit"] = "Editar";
$lang["delete"] = "Esborrar";
$lang["settings"] = "Paràemetres";
$lang["trackcolor"] = "Color del track";
?>
