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
$langSetup["dbconnectfailed"] = "Tietokantayhteys epäonnistui.";
$langSetup["serversaid"] = "Palvelimen palaute: %s"; // substitutes server error message
$langSetup["checkdbsettings"] = "Tarkista tietokanta-asetukset tiedostosta 'config.php'.";
$langSetup["dbqueryfailed"] = "Tietokantakysely epäonnistui.";
$langSetup["dbtablessuccess"] = "Tietokannan taulut luotu onnistuneesti!";
$langSetup["setupuser"] = "Määritä nyt µlogger-käyttäjäsi.";
$langSetup["congratulations"] = "Onnittelut";
$langSetup["setupcomplete"] = "Asennus on nyt valmis. Voit mennä <a href=\"../index.php\">pääsivulle</a> ja kirjautua uudella käyttäjätunnuksellasi.";
$langSetup["disablewarn"] = "TÄRKEÄÄ!  OTA POIS KÄYTÖSTÄ SKRIPTI 'setup.php' TAI POISTA SE PALVELIMELTASI.";
$langSetup["disabledesc"] = "Skriptin jättäminen selaimesta käytettäväksi on merkittävä tietoturvariski. Kuka tahansa voi ajaa sen, poistaa tietokantasi ja määrittää uuden käyttäjätunnuksen. Poista tiedosto tai ota se pois käytöstä asettamalla arvo%s takaisin arvoon %s."; // substitutes variable name and value
$langSetup["setupfailed"] = "Valitettavasti jotain meni pieleen. Voit yrittää löytää lisätietoja web-palvelimesi lokeista.";
$langSetup["welcome"] = "Tervetuloa µloggeriin!";
$langSetup["disabledwarn"] = "Tietoturvasyistä tämä skripti on oletuksena otettu pois käytöstä. Ota se käyttöön muokkaamalla tiedostoa 'scripts/setup.php'  tekstieditorissa ja asettamalla  %s muuttuja tiedoston alussa arvoon %s."; // substitutes variable name and value
$langSetup["lineshouldread"] = "Rivin: %s tulisi olla: %s";
$langSetup["dorestart"] = "Käynnistä tämä skripti uudelleen, kun olet valmis.";
$langSetup["createconfig"] = "Luo tiedosto 'config.php' juurihakemistoon  Voit tehdä tämän kopioimalla tiedoston 'config.default.php'. Muuta määritystiedoston arvoja vastaamaan tarpeitasi ja tietokannan määrityksiäsi.";
$langSetup["nodbsettings"] = "Tietokannan käyttäjätiedot tulee määritellä tiedostossa 'config.php' (%s)."; // substitutes variable names
$langSetup["scriptdesc"] = "Tämä skripti määrittää tarvittavat taulut µloggerille (%s). Taulut luodaan tietokantaasi nimeltä %s. Varoitus: jos taulut ovat jo olemassa, ne poistetaan ja luodaan uudelleen ja taulujen sisältö tyhjenee kokonaan."; // substitutes table names and db name
$langSetup["scriptdesc2"] = "Skripti kysyy lopuksi käyttäjätunnuksen ja salasanan µlogger-käyttäjällesi.";
$langSetup["startbutton"] = "Paina käynnistääksesi";
$langSetup["restartbutton"] = "Käynnistä uudelleen";
$langSetup["optionwarn"] = "PHP:n määritysten valinta %s tulee asettaa arvoon %s."; // substitutes option name and value
$langSetup["extensionwarn"] = "Vaadittu PHP-laajennus %s ei ole käytettävissä."; // substitutes extension name
$langSetup["notwritable"] = "Hakemisto '%s' täytyy olla PHP:n kirjoitettavissa."; // substitutes folder path


// application strings
$lang["title"] = "• μlogger •";
$lang["private"] = "Tämän sivun käyttämiseen tarvitaan käyttäjätunnus ja salasana.";
$lang["authfail"] = "Väärä käyttäjätunnus tai salasana";
$lang["user"] = "Käyttäjä";
$lang["track"] = "Reitti";
$lang["latest"] = "viimeisin sijainti";
$lang["autoreload"] = "automaattinen uudelleenlataus";
$lang["reload"] = "Lataa uudelleen nyt";
$lang["export"] = "Vie reitti";
$lang["chart"] = "Korkeuskartta";
$lang["close"] = "sulje";
$lang["time"] = "Aika";
$lang["speed"] = "Nopeus";
$lang["accuracy"] = "Tarkkuus";
$lang["position"] = "Sijainti";
$lang["altitude"] = "Korkeus";
$lang["bearing"] = "Suuntima";
$lang["ttime"] = "Kokonaisaika";
$lang["aspeed"] = "Keskinopeus";
$lang["tdistance"] = "Kokonaismatka";
$lang["pointof"] = "Piste %d / %d"; // e.g. Point 3 of 10
$lang["summary"] = "Matkan yhteenveto";
$lang["suser"] = "valitse käyttäjä";
$lang["logout"] = "Kirjaudu ulos";
$lang["login"] = "Kirjaudu sisään";
$lang["username"] = "Käyttäjätunnus";
$lang["password"] = "Salasana";
$lang["language"] = "Kieli";
$lang["newinterval"] = "Anna uusi aikaväli (sekunteina)";
$lang["api"] = "Kartta-API";
$lang["units"] = "Yksiköt";
$lang["metric"] = "Metrijärjestelmä";
$lang["imperial"] = "Anglosaksinen mittajärjestelmä";
$lang["nautical"] = "Meripeninkulmat";
$lang["admin"] = "Ylläpitäjä";
$lang["adminmenu"] = "Ylläpito";
$lang["passwordrepeat"] = "Toista salasana";
$lang["passwordenter"] = "Anna salasana";
$lang["usernameenter"] = "Anna käyttäjätunnus";
$lang["adduser"] = "Lisää käyttäjä";
$lang["userexists"] = "Käyttäjä on jo olemassa";
$lang["cancel"] ="Peruuta";
$lang["submit"] = "Lähetä";
$lang["oldpassword"] = "Vanha salasana";
$lang["newpassword"] = "Uusi salasana";
$lang["newpasswordrepeat"] = "Toista uusi salasana";
$lang["changepass"] = "Vaihda salasana";
$lang["gps"] = "GPS";
$lang["network"] = "Verkko";
$lang["deluser"] = "Poista käyttäjä";
$lang["edituser"] = "Muokkaa käyttäjää";
$lang["servererror"] = "Palvelinvirhe";
$lang["allrequired"] = "Kaikki kentät ovat pakollisia";
$lang["passnotmatch"] = "Salasanat eivät täsmää";
$lang["oldpassinvalid"] = "Väärä vanha salasana";
$lang["passempty"] = "Tyhjä salasana";
$lang["loginempty"] = "Tyhjä käyttäjätunnus";
$lang["passstrengthwarn"] = "Virheellinen salasanan vahvuus";
$lang["actionsuccess"] = "Toiminto suoritettu onnistuneesti";
$lang["actionfailure"] = "Jotain meni pieleen";
$lang["notauthorized"] = "Käyttäjällä ei ole käyttöoikeuksia";
$lang["userunknown"] = "Tuntematon käyttäjä";
$lang["userdelwarn"] = "Varoitus!\n\nOlet pysyvästi poistamassa käyttäjän %s ja kaikki käyttäjän reitit sekä sijainnit.\n\nOletko varma?"; // substitutes user login
$lang["editinguser"] = "Muokkaat käyttäjää %s"; // substitutes user login
$lang["selfeditwarn"] = "Et voi muokata omaa käyttäjätunnustasi tällä työkalulla";
$lang["apifailure"] = "Valitettavasti rajapintaa %s ei voida ladata."; // substitutes api name (gmaps or openlayers)
$lang["trackdelwarn"] = "Varoitus!\n\nOlet pysyvästi poistamassa reitin %s ja kaikki sen sijainnit.\n\nOletko varma?"; // substitutes track name
$lang["editingtrack"] = "Muokkaat reittiä %s"; // substitutes track name
$lang["deltrack"] = "Poista reitti";
$lang["trackname"] = "Reitin nimi";
$lang["edittrack"] = "Muokkaa reittiä";
$lang["positiondelwarn"] = "Varoitus!\n\nOlet pysyvästi poistamassa sijainnin %d reitistä %s.\n\nOletko varma?"; // substitutes position index and track name
$lang["editingposition"] = "Muokkaat sijaintia %d reitistä %s"; // substitutes position index and track name
$lang["delposition"] = "Poista sijainti";
$lang["delimage"] = "Poista kuva";
$lang["comment"] = "Kommentti";
$lang["image"] = "Kuva";
$lang["editposition"] = "Muokkaa sijaintia";
$lang["passlenmin"] = "Salasanassa tulee olla vähintään %d merkkiä"; // substitutes password minimum length
$lang["passrules_1"] = "Salasanan tulisi sisältää ainakin yksi pieni ja yksi iso kirjain";
$lang["passrules_2"] = "Salasanan tulisi sisältää ainakin yksi pieni kirjain, yksi iso kirjain ja yksi numero";
$lang["passrules_3"] = "Salasanan tulisi sisältää ainakin yksi pieni kirjain, yksi iso kirjain, yksi numero ja yksi ei-aakkosnumeerinen merkki";
$lang["owntrackswarn"] = "Voit muokata vain omia reittejäsi";
$lang["gmauthfailure"] = "Tämän sivun Google Maps API-avaimessa saattaa olla ongelma";
$lang["gmapilink"] = "Voit katsoa lisätietoja API-avaimista  <a target=\"_blank\" href=\"https://developers.google.com/maps/documentation/javascript/get-api-key\">tältä Googlen verkkosivulta</a>";
$lang["import"] = "Tuo reitti";
$lang["iuploadfailure"] = "Verkkoon lataaminen epäonnistui";
$lang["iparsefailure"] = "Jäsentäminen epäonnistui";
$lang["idatafailure"] = "Tuodussa tiedostossa ei ole reittitietoja";
$lang["isizefailure"] = "Lähetetyn tiedoston koon ei tulisi ylittää %d tavua"; // substitutes number of bytes
$lang["imultiple"] = "Huom: useampi reitti tuotu (%d)"; // substitutes number of imported tracks
$lang["allusers"] = "Kaikki käyttäjät";
$lang["unitday"] = "pv"; // abbreviation for days, like 4 d 11:11:11
$lang["unitkmh"] = "km/h"; // kilometer per hour
$lang["unitm"] = "m"; // meter
$lang["unitamsl"] = "mpy"; // above mean see level
$lang["unitkm"] = "km"; // kilometer
$lang["unitmph"] = "mailia tunnissa"; // mile per hour
$lang["unitft"] = "jalkaa"; // feet
$lang["unitmi"] = "mi"; // mile
$lang["unitkt"] = "kt"; // knot
$lang["unitnm"] = "nm"; // nautical mile
$lang["config"] = "Asetukset";
$lang["editingconfig"] = "Sovelluksen oletusasetukset";
$lang["latitude"] = "Leveysasteen oletusarvo";
$lang["longitude"] = "Pituusasteen oletusarvo";
$lang["interval"] = "Väli (s)";
$lang["googlekey"] = "Google Maps API-avain";
$lang["passlength"] = "Salasanan vähimmäispituus";
$lang["passstrength"] = "Salasanan vähimmäisvahvuus";
$lang["requireauth"] = "Vaadi tunnistautumista";
$lang["publictracks"] = "Julkiset reitit";
$lang["strokeweight"] = "Viivan paksuus";
$lang["strokeopacity"] = "Viivan läpinäkyvyys";
$lang["strokecolor"] = "Viivan väri";
$lang["colornormal"] = "Merkinnän väri";
$lang["colorstart"] = "Alkumerkinnän väri";
$lang["colorstop"] = "Loppumerkinnän väri";
$lang["colorextra"] = "Lisämerkinnän väri";
$lang["colorhilite"] = "Merkinnän korostusväri";
$lang["uploadmaxsize"] = "Latauksen maksimikoko (Mt)";
$lang["ollayers"] = "OpenLayers-taso";
$lang["layername"] = "Tason nimi";
$lang["layerurl"] = "Tason URL";
$lang["add"] = "Lisää";
$lang["edit"] = "Muokkaa";
$lang["delete"] = "Poista";
$lang["settings"] = "Asetukset";
$lang["trackcolor"] = "Reitin väri";
?>
