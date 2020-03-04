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
$langSetup["dbconnectfailed"] = "Connessione al database fallita.";
$langSetup["serversaid"] = "Il server ha risposto: %s"; // substitutes server error message
$langSetup["checkdbsettings"] = "Controllare le impostazione del database in 'config.php'.";
$langSetup["dbqueryfailed"] = "Query al database fallita.";
$langSetup["dbtablessuccess"] = "Tabelle create nel database!";
$langSetup["setupuser"] = "Impostare l'utente µlogger.";
$langSetup["congratulations"] = "Congratulazioni!";
$langSetup["setupcomplete"] = "L'installazione è stata completata. Puoi entrare nella <a href=\"../index.php\">pagina principale</a> con l'utente appena creato.";
$langSetup["disablewarn"] = "ATTENZIONE! ELIMINA O DISABILITA LO SCRIPT 'setup.php' DAL SERVER.";
$langSetup["disabledesc"] = "Lasciare lo script accessibile da browser è un rischio per la sicurezza. Chiunque potrebbe lanciarlo, eliminare il tuo database e creare un nuovo utente. Elimina o disabilita il file impostando %s come %s."; // substitutes variable name and value
$langSetup["setupfailed"] = "Qualcosa è andato storto. Potresti trovare maggiori informazioni nei log del server.";
$langSetup["welcome"] = "Benvenuto su µlogger!";
$langSetup["disabledwarn"] = "Per ragioni di sicurezza questo script è disabilitato di default. Per abilitarlo devi modificare il file 'scripts/setup.php' con un editor di testo ed impostare la variabile %s all'inizio del file in %s."; // substitutes variable name and value
$langSetup["lineshouldread"] = "Linea: %s dovrebbe essere: %s";
$langSetup["dorestart"] = "Riavviare lo script quando hai finito.";
$langSetup["createconfig"] = "Creare il file 'config.php' nella cartella radice. Puoi cominciare copiando il file 'config.default.php'. Modifica i valori per renderli compatibili con il tuo database.";
$langSetup["nodbsettings"] = "Devi provvedere le credenziali del database in 'config.php' (%s)."; // substitutes variable names
$langSetup["scriptdesc"] = "Questo script creerà le tabelle necessarie per µlogger (%s). Saranno create nel database chiamato %s. Attenzione, se le tabelle esistono già saranno ricreate ed il loro contenuto distrutto."; // substitutes table names and db name
$langSetup["scriptdesc2"] = "Quando lo script avrà completato la sua esecuzione dovrai creare l'utente µlogger.";
$langSetup["startbutton"] = "Clicca per cominciare";
$langSetup["restartbutton"] = "Riavvia";
$langSetup["optionwarn"] = "PHP configuration option %s must be set to %s."; // substitutes option name and value
$langSetup["extensionwarn"] = "Required PHP extension %s is not available."; // substitutes extension name


// application strings
$lang["title"] = "• μlogger •";
$lang["private"] = "Devi accedere per visualizzare questa pagina.";
$lang["authfail"] = "Nome utente o password errati.";
$lang["user"] = "Utente";
$lang["track"] = "Traccia";
$lang["latest"] = "ultima posizione";
$lang["autoreload"] = "auto-ricarica";
$lang["reload"] = "Ricarica ora";
$lang["export"] = "Scarica dati";
$lang["chart"] = "Grafico altitudine";
$lang["close"] = "chiudi";
$lang["time"] = "Ora";
$lang["speed"] = "Velocità";
$lang["accuracy"] = "Precisione";
$lang["position"] = "Position";
$lang["altitude"] = "Altitudine";
$lang["bearing"] = "Bearing";
$lang["ttime"] = "Tempo totale";
$lang["aspeed"] = "Velocità media";
$lang["tdistance"] = "Distanza totale";
$lang["pointof"] = "Point %d of %d"; // e.g. Point 3 of 10
$lang["summary"] = "Sommario";
$lang["suser"] = "scegli utente";
$lang["logout"] = "Esci";
$lang["login"] = "Entra";
$lang["username"] = "Nome Utente";
$lang["password"] = "Password";
$lang["language"] = "Lingua";
$lang["newinterval"] = "Immetti nuovo intervallo (secondi)";
$lang["api"] = "API Mappe";
$lang["units"] = "Unità";
$lang["metric"] = "Metriche";
$lang["imperial"] = "Imperiali";
$lang["nautical"] = "Nautical";
$lang["admin"] = "Administrator";
$lang["adminmenu"] = "Amministazione";
$lang["passwordrepeat"] = "Ripeti password";
$lang["passwordenter"] = "Immetti password";
$lang["usernameenter"] = "Immetti nome utente";
$lang["adduser"] = "Aggiungi utente";
$lang["userexists"] = "L'utente esiste già";
$lang["cancel"] ="Annulla";
$lang["submit"] = "Invia";
$lang["oldpassword"] = "Vecchia password";
$lang["newpassword"] = "Nuova password";
$lang["newpasswordrepeat"] = "Ripeti nuova password";
$lang["changepass"] = "Cambia password";
$lang["gps"] = "GPS";
$lang["network"] = "Rete";
$lang["deluser"] = "Rimuovi utente";
$lang["edituser"] = "Modifica utente";
$lang["servererror"] = "Errore del server";
$lang["allrequired"] = "Tutti i campi sono obbligatori";
$lang["passnotmatch"] = "Le password sono diverse";
$lang["actionsuccess"] = "Completato con successo";
$lang["actionfailure"] = "Qualcosa è andato storto";
$lang["notauthorized"] = "User not authorized";
$lang["userdelwarn"] = "Attenzione!\n\nStai eliminando permanentemente l'utente %s, con tutte le tracce e le posizioni.\n\nSei sicuro?"; // substitutes user login
$lang["editinguser"] = "Stai modificando l'utente %s"; // substitutes user login
$lang["selfeditwarn"] = "Non puoi modificare il tuo utente con questo strumento";
$lang["apifailure"] = "Spiacente, impossibile caricare l'API %s"; // substitutes api name (gmaps or openlayers)
$lang["trackdelwarn"] = "Attenzione!\n\nStai per eliminare la traccia %s e tutte le sue posizioni.\n\nSei sicuro?"; // substitutes track name
$lang["editingtrack"] = "Stai modificando la traccia %s"; // substitutes track name
$lang["deltrack"] = "Elimina traccia";
$lang["trackname"] = "Nome traccia";
$lang["edittrack"] = "Modifica traccia";
$lang["positiondelwarn"] = "Warning!\n\nYou are going to permanently delete position %d of track %s.\n\nAre you sure?"; // substitutes position index and track name
$lang["editingposition"] = "You are editing position #%d of track %s"; // substitutes position index and track name
$lang["delposition"] = "Remove position";
$lang["comment"] = "Comment";
$lang["editposition"] = "Edit position";
$lang["passlenmin"] = "La password deve essere almeno di %d caratteri"; // substitutes password minimum length
$lang["passrules_1"] = "Dovrebbe contenere almeno una lettera minuscola e una lettera maiuscola";
$lang["passrules_2"] = "Dovrebbe contenere almeno una lettera minuscola, una lettera maiuscola e un numero";
$lang["passrules_3"] = "Dovrebbe contenere almeno una lettera minuscola, una lettera maiuscola, un numero ed un carattere non alfanumerico";
$lang["owntrackswarn"] = "Puoi modificare solo le tue tracce";
$lang["gmauthfailure"] = "In questa pagina potrebbe esserci un problema con le API di Google Maps.";
$lang["gmapilink"] = "Puoi trovare maggiori informazioni sulle chiavi API a <a target=\"_blank\" href=\"https://developers.google.com/maps/documentation/javascript/get-api-key\">questo indirizzo</a>.";
$lang["import"] = "Importa traccia";
$lang["iuploadfailure"] = "Caricamento fallito";
$lang["iparsefailure"] = "Analisi fallita";
$lang["idatafailure"] = "Nessun tracciato nel file importato";
$lang["isizefailure"] = "La dimensione del file caricato non deve superare i %d byte"; // substitutes number of bytes
$lang["imultiple"] = "Tracce multiple importate (%d)"; // substitutes number of imported tracks
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
