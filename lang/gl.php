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
$langSetup["dbconnectfailed"] = "Fallou a conexión á base datos.";
$langSetup["serversaid"] = "Mensaxe do servidor: %s"; // substitutes server error message
$langSetup["checkdbsettings"] = "Comproba os axustes da base de datos no ficheiro 'config.php'";
$langSetup["dbqueryfailed"] = "Fallou a consulta á base de datos.";
$langSetup["dbtablessuccess"] = "Creáronse correctamente as táboas da base de datos!";
$langSetup["setupuser"] = "Configura agora o teu acceso a µlogger";
$langSetup["congratulations"] = "Parabéns!";
$langSetup["setupcomplete"] = "Configuración completa. Xa podes ir á <a href=\"../index.php\">páxina principal</a> e acceder coa túa nova conta.";
$langSetup["disablewarn"] = "IMPORTANTE! DEBES DESACTIVAR O SCRIPT 'setup.php' OU ELIMINALO DO TEU SERVIDOR.";
$langSetup["disabledesc"] = "É un risco de seguridade importante deixar que o script sexa accesible desde un navegador. Calquera podería utilizalo para eliminar a base de datos e configurar unha nova conta de usuaria. Elimina o ficheiro ou desactívao axustando o valor de %s a %s."; // substitutes variable name and value
$langSetup["setupfailed"] = "Desgraciadamente algo fallou. Podes atopar máis información no rexistro do teu servidor web.";
$langSetup["welcome"] = "Benvida a µlogger!";
$langSetup["disabledwarn"] = "Este script está desactivado por defecto por razóns de seguridade. Para activalo tes que editar o ficheiro 'scripts/setup.php' nun editor de texto e establecer a variable %s ao principio do ficheiro como %s."; // substitutes variable name and value
$langSetup["lineshouldread"] = "Liña: %s debería verse como: %s";
$langSetup["dorestart"] = "Por favor reinicia o script após cando o fagas.";
$langSetup["createconfig"] = "Por favor crea o ficheiro 'config.php' no cartafol raíz. Comeza copiándoo desde 'config.default.php'. Pon tino en axustar os valores de configuración acorde ás túas necesidades e a configuración da túa base de datos.";
$langSetup["nodbsettings"] = "Debes proporcionar unhas credenciais para a base de datos no ficheiro 'config.php' (%s)."; // substitutes variable names
$langSetup["scriptdesc"] = "Este script configurará as táboas que µlogger precisa (%s). Van ser creadas na túa base de datos chamada %s. Aviso, se a táboa xa existe será eliminada e creada de novo, vaise destruír o seu contido."; // substitutes table names and db name
$langSetup["scriptdesc2"] = "Ao rematar o script vaiche pedir un identificador e contrasinal para o teu usuario µlogger.";
$langSetup["startbutton"] = "Preme para comezar";
$langSetup["restartbutton"] = "Reiniciar";
$langSetup["optionwarn"] = "A opción de configuración PHP %s debe establecerse como %s."; // substitutes option name and value
$langSetup["extensionwarn"] = "Non está dispoñible a extensión PHP %s e é un requerimento."; // substitutes extension name
$langSetup["notwritable"] = "PHP ten que poder escribir no cartafol '%s'."; // substitutes folder path


// application strings
$lang["title"] = "• μlogger •";
$lang["private"] = "Precisar ter un identificador e contrasinal para acceder a esta páxina.";
$lang["authfail"] = "Credenciais incorrectas";
$lang["user"] = "Identificador";
$lang["track"] = "Ruta";
$lang["latest"] = "última posición";
$lang["autoreload"] = "recarga automática";
$lang["reload"] = "Recargar agora";
$lang["export"] = "Exportar ruta";
$lang["chart"] = "Gráfica coa altitude";
$lang["close"] = "pechar";
$lang["time"] = "Tempo";
$lang["speed"] = "Velocidade";
$lang["accuracy"] = "Precisión";
$lang["position"] = "Posición";
$lang["altitude"] = "Altitude";
$lang["bearing"] = "Dirección";
$lang["ttime"] = "Tempo total";
$lang["aspeed"] = "Velocidade media";
$lang["tdistance"] = "Distancia total";
$lang["pointof"] = "Punto %d de %d"; // e.g. Point 3 of 10
$lang["summary"] = "Resumo da viaxe";
$lang["suser"] = "elexir usuaria";
$lang["logout"] = "Pechar sesión";
$lang["login"] = "Acceder";
$lang["username"] = "Identificador";
$lang["password"] = "Contrasinal";
$lang["language"] = "Idioma";
$lang["newinterval"] = "Escribir novo valor para intervalo (segundos)";
$lang["api"] = "API do mapa";
$lang["units"] = "Unidades";
$lang["metric"] = "Métrico";
$lang["imperial"] = "Imperial/USA";
$lang["nautical"] = "Náutico";
$lang["admin"] = "Administradora";
$lang["adminmenu"] = "Administración";
$lang["passwordrepeat"] = "Repetir contrasinal";
$lang["passwordenter"] = "Escribir contrasinal";
$lang["usernameenter"] = "Escribir identificador";
$lang["adduser"] = "Engadir usuaria";
$lang["userexists"] = "Xa existe a usuaria";
$lang["cancel"] ="Desbotar";
$lang["submit"] = "Enviar";
$lang["oldpassword"] = "Contrasinal antigo";
$lang["newpassword"] = "Novo contrasinal";
$lang["newpasswordrepeat"] = "Repetir novo contrasinal";
$lang["changepass"] = "Cambiar contrasinal";
$lang["gps"] = "GPS";
$lang["network"] = "Rede";
$lang["deluser"] = "Eliminar usuaria";
$lang["edituser"] = "Editar usuaria";
$lang["servererror"] = "Erro no servidor";
$lang["allrequired"] = "Todos os campos son requeridos";
$lang["passnotmatch"] = "Os contrasinais non concordan";
$lang["oldpassinvalid"] = "O contrasinal antigo non é correcto";
$lang["passempty"] = "Contrasinal baleiro";
$lang["loginempty"] = "Identificador baleiro";
$lang["passstrengthwarn"] = "A fortaleza do contrasinal non é válida";
$lang["actionsuccess"] = "Acción realizada correctamente";
$lang["actionfailure"] = "Algo fallou";
$lang["notauthorized"] = "Usuaria non autorizada";
$lang["userunknown"] = "Usuaria descoñecida";
$lang["userdelwarn"] = "Aviso!\n\nVas a eliminar de xeito definitivo a usuaria %s, xunto con todas as súas rutas e posicións.\n\nTes certeza?"; // substitutes user login
$lang["editinguser"] = "Estás a editar a usuaria %s"; // substitutes user login
$lang["selfeditwarn"] = "Non podes editar a túa propia usuaria con esta ferramenta";
$lang["apifailure"] = "Lamentámolo, a API %s non cargou"; // substitutes api name (gmaps or openlayers)
$lang["trackdelwarn"] = "Aviso!\n\nVas a eliminar de xeito definitivo a ruta %s e todas as súas posicións.\n\nTes certeza?"; // substitutes track name
$lang["editingtrack"] = "Estás a editar a ruta %s"; // substitutes track name
$lang["deltrack"] = "Desbotar ruta";
$lang["trackname"] = "Nome da ruta";
$lang["edittrack"] = "Editar ruta";
$lang["positiondelwarn"] = "Aviso!\n\nVas a eliminar de xeito definitivo a posición %d da ruta %s.\n\nEliminámola?"; // substitutes position index and track name
$lang["editingposition"] = "Estás a editar a posición #%d da ruta %s"; // substitutes position index and track name
$lang["delposition"] = "Eliminar posición";
$lang["delimage"] = "Eliminar imaxe";
$lang["comment"] = "Comentar";
$lang["image"] = "Imaxe";
$lang["editposition"] = "Editar posición";
$lang["passlenmin"] = "O contasinal ten que ter %d caracteres polo menos"; // substitutes password minimum length
$lang["passrules_1"] = "Debe conten polo menos unha letra minúscula e unha maiúscula";
$lang["passrules_2"] = "Debe conter polo menos unha minúscula, unha maiúscula e un número";
$lang["passrules_3"] = "Debe conter polo menos unha letra minúscula, unha maiúscula, un díxito e un caracter non alfanumérico.";
$lang["owntrackswarn"] = "Só podes editar as túas propias rutas";
$lang["gmauthfailure"] = "Podería haber algún problema coa chave API de Google Maps nesta páxina";
$lang["gmapilink"] = "Podes atopar máis información acerca das chaves da API nesta <a target=\"_blank\" href=\"https://developers.google.com/maps/documentation/javascript/get-api-key\">páxina de Google</a>";
$lang["import"] = "Importar ruta";
$lang["iuploadfailure"] = "Fallou a subida";
$lang["iparsefailure"] = "Fallou o procesado";
$lang["idatafailure"] = "Non hai datos de ruta no ficheiro importado";
$lang["isizefailure"] = "O tamaño do ficheiro subido non pode exceder os %d bytes"; // substitutes number of bytes
$lang["imultiple"] = "Aviso, importáronse varias rutas (%d)"; // substitutes number of imported tracks
$lang["allusers"] = "Todas as usuarias";
$lang["unitday"] = "d"; // abbreviation for days, like 4 d 11:11:11
$lang["unitkmh"] = "km/h"; // kilometer per hour
$lang["unitm"] = "m"; // meter
$lang["unitamsl"] = "s.n.m."; // above mean see level
$lang["unitkm"] = "km"; // kilometer
$lang["unitmph"] = "mph"; // mile per hour
$lang["unitft"] = "ft"; // feet
$lang["unitmi"] = "mi"; // mile
$lang["unitkt"] = "kt"; // knot
$lang["unitnm"] = "nm"; // nautical mile
$lang["config"] = "Axustes";
$lang["editingconfig"] = "Axustes por defecto da aplicación";
$lang["latitude"] = "Latitude inicial";
$lang["longitude"] = "Lonxitude inicial";
$lang["interval"] = "Intervalo (s)";
$lang["googlekey"] = "Chave da API de Google Maps";
$lang["passlength"] = "Lonxitude mínima do contrasinal";
$lang["passstrength"] = "Fortaleza mínima do contrasinal";
$lang["requireauth"] = "Requerir autorización";
$lang["publictracks"] = "Rutas públicas";
$lang["strokeweight"] = "Grosor do trazo";
$lang["strokeopacity"] = "Opacidade do trazo";
$lang["strokecolor"] = "Cor do trazo";
$lang["colornormal"] = "Cor do marcador";
$lang["colorstart"] = "Cor do marcador de inicio";
$lang["colorstop"] = "Cor do marcador de fin";
$lang["colorextra"] = "Cor de marcador extra";
$lang["colorhilite"] = "Cor de marcador destacado";
$lang["uploadmaxsize"] = "Tamaño máximo da subida (MB)";
$lang["ollayers"] = "Capa OpenLayers";
$lang["layername"] = "Nome da capa";
$lang["layerurl"] = "URL da capa";
$lang["add"] = "Engadir";
$lang["edit"] = "Editar";
$lang["delete"] = "Desbotar";
$lang["settings"] = "Axustes";
$lang["trackcolor"] = "Cor da ruta";
?>
