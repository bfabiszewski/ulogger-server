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
$langSetup["dbconnectfailed"] = "Błąd połączenia z bazą danych.";
$langSetup["serversaid"] = "Komunikat serwera: %s"; // substitutes server error message
$langSetup["checkdbsettings"] = "Proszę sprawdzić konfigurację bazy danych w pliku 'config.php'.";
$langSetup["dbqueryfailed"] = "Błąd zapytania do bazy danych.";
$langSetup["dbtablessuccess"] = "Pomyślnie utworzono tablice w bazie danych!";
$langSetup["setupuser"] = "Skonfiguruj teraz swojego użytkownika w µloggerze.";
$langSetup["congratulations"] = "Gratulacje!";
$langSetup["setupcomplete"] = "Konfiguracja zakończona. Możesz teraz przejść do <a href=\"../index.php\">strony głównej</a> i zalogować się na konto utworzonego użytkownika.";
$langSetup["disablewarn"] = "WAŻNE! NAZLEŻY DEZAKTYWOWAĆ SKRYPT 'setup.php' ALBO USUNĄĆ GO Z SERWERA.";
$langSetup["disabledesc"] = "Pozostawienie dostępu do skryptu z przeglądarki stanowi duże zagrożenie. Każdy będzie mógł go uruchomić, usunąć całą bazę danych i dodać nowego użytkownika. Usuń plik lub dezaktywuj go przywracając zmiennej %s wartość %s."; // substitutes variable name and value
$langSetup["setupfailed"] = "Niestety coś poszło nie tak. Może znajdziesz więcej wskazówek w logach serwera www.";
$langSetup["welcome"] = "Witaj w µloggerze!";
$langSetup["disabledwarn"] = "Ze względów bezpieczeństwa ten skrypt jest domyślnie wyłączony. Aby go aktywować należy otworzyć plik 'scripts/setup.php' w edytorze tekstu i zmienić wartość zmiennej %s na początku pliku na %s."; // substitutes variable name and value
$langSetup["lineshouldread"] = "Linia: %s powinna zostać zmieniona na: %s";
$langSetup["dorestart"] = "Uruchom ten skrypt ponownie, kiedy zakończysz.";
$langSetup["createconfig"] = "Utwórz proszę plik 'config.php' w głównym folderze. Możesz skopiować jego początkową zawartość z pliku 'config.default.php'. Pamiętaj, żeby dostosować konfiguracje do swoich potrzeb i ustawień bazy danych.";
$langSetup["nodbsettings"] = "Musisz skonfigurować parametry dostępu do bazy danych w pliku 'config.php' (%s)."; // substitutes variable names
$langSetup["scriptdesc"] = "Ten skrypt utworzy tablice niezbędne do działania aplikacji µlogger (%s). Zostaną one utworzone w bazie danych o nazwie %s. Uwaga, jeśli tablice już istnieją, zostaną usunięte i utworzone ponownie, ich zawartość zostanie skasowana."; // substitutes table names and db name
$langSetup["scriptdesc2"] = "Następnie skrypt poprosi o utworzenie konta do logowania w aplikacji µlogger.";
$langSetup["startbutton"] = "Naciśnij, aby rozpocząć";
$langSetup["restartbutton"] = "Uruchom ponownie";
$langSetup["optionwarn"] = "Opcja %s w ustawieniach PHP musi mieć wartość %s."; // substitutes option name and value
$langSetup["extensionwarn"] = "Wymagane rozszerzenie PHP %s jest niedostępne."; // substitutes extension name


// application strings
$lang["title"] = "• μlogger •";
$lang["private"] = "Aby się zalogować musisz podać login i hasło";
$lang["authfail"] = "błędny login lub hasło";
$lang["user"] = "Użytkownik";
$lang["track"] = "Trasa";
$lang["latest"] = "ostatnia pozycja";
$lang["autoreload"] = "odświeżaj";
$lang["reload"] = "Odśwież teraz";
$lang["export"] = "Eksportuj trasę";
$lang["chart"] = "Wykres przewyższeń";
$lang["close"] = "zamknij";
$lang["time"] = "Czas";
$lang["speed"] = "Prędkość";
$lang["accuracy"] = "Dokładność";
$lang["position"] = "Pozycja";
$lang["altitude"] = "Wysokość";
$lang["bearing"] = "Azymut";
$lang["ttime"] = "Czas podróży";
$lang["aspeed"] = "Średnia prędkość";
$lang["tdistance"] = "Odległość";
$lang["pointof"] = "Punkt %d z %d"; // e.g. Point 3 of 10
$lang["summary"] = "Podsumowanie";
$lang["suser"] = "wybierz login";
$lang["logout"] = "Wyloguj";
$lang["login"] = "Zaloguj";
$lang["username"] = "Login";
$lang["password"] = "Hasło";
$lang["language"] = "Język";
$lang["newinterval"] = "Podaj częstotliwość odświeżania (w sekundach)";
$lang["api"] = "Map API";
$lang["units"] = "Jednostki";
$lang["metric"] = "Metryczne";
$lang["imperial"] = "Anglosaskie";
$lang["nautical"] = "Nawigacyjne";
$lang["admin"] = "Administrator";
$lang["adminmenu"] = "Administracja";
$lang["passwordrepeat"] = "Powtórz hasło";
$lang["passwordenter"] = "Podaj hasło";
$lang["usernameenter"] = "Podaj użytkownika";
$lang["adduser"] = "Dodaj użytkownika";
$lang["userexists"] = "Użytkownik istnieje";
$lang["cancel"] ="Anuluj";
$lang["submit"] = "Zatwierdź";
$lang["oldpassword"] = "Obecne hasło";
$lang["newpassword"] = "Nowe hasło";
$lang["newpasswordrepeat"] = "Powtórz nowe hasło";
$lang["changepass"] = "Zmień hasło";
$lang["gps"] = "GPS";
$lang["network"] = "Sieć";
$lang["deluser"] = "Usuń użytkownika";
$lang["edituser"] = "Edytuj użytkownika";
$lang["servererror"] = "Błąd serwera";
$lang["allrequired"] = "Wszystkie pola są wymagane";
$lang["passnotmatch"] = "Hasła nie pasują do siebie";
$lang["actionsuccess"] = "Operacja zakończona pomyślnie";
$lang["actionfailure"] = "Wystąpił błąd";
$lang["notauthorized"] = "Brak autoryzacji";
$lang["userdelwarn"] = "Uwaga!\n\nTa operacja nieodwracalnie usunie użytkownika %s wraz ze wszystkimi jego trasami i pozycjami.\n\nCzy na pewno?"; // substitutes user login
$lang["editinguser"] = "Edytujesz użytkownika %s"; // substitutes user login
$lang["selfeditwarn"] = "Nie można edytować własnego użytkownika za pomocą tego narzędzia";
$lang["apifailure"] = "Niestety ładowanie API %s nie powiodło się"; // substitutes api name (gmaps or openlayers)
$lang["trackdelwarn"] = "Uwaga!\n\nZamierzasz całkowicie usunąć trasę %s wraz ze wszystkimi pozycjami.\n\nCzy na pewno?"; // substitutes track name
$lang["editingtrack"] = "Edytujesz trasę %s"; // substitutes track name
$lang["deltrack"] = "Usuń trasę";
$lang["trackname"] = "Nazwa trasy";
$lang["edittrack"] = "Edytuj trasę";
$lang["positiondelwarn"] = "Uwaga!\n\nZamierzasz całkowicie usunąć pozycję %d z trasy %s.\n\nCzy na pewno?"; // substitutes position index and track name
$lang["editingposition"] = "Edytujesz pozycję #%d z trasy %s"; // substitutes position index and track name
$lang["delposition"] = "Usuń pozycję";
$lang["comment"] = "Komentarz";
$lang["editposition"] = "Edytuj pozycję";
$lang["passlenmin"] = "Hasło musi się składać z minimum %d znaków"; // substitutes password minimum length
$lang["passrules_1"] = "Powinno ono zawierać przynajmniej jedną małą i jedną wielką literę";
$lang["passrules_2"] = "Powinno ono zawierać przynajmniej jedną małą, jedną wielką literę i jedną cyfrę";
$lang["passrules_3"] = "Powinno ono zawierać przynajmniej jedną małą, jedną wielką literę, jedną cyfrę i jeden znak specjalny (nie alfanumeryczny)";
$lang["owntrackswarn"] = "Możesz edytować tylko swoje własne trasy";
$lang["gmauthfailure"] = "Prawdopodobnie na tej stronie występuje problem z kluczem API Google Maps";
$lang["gmapilink"] = "Więcej informacji o kluczach API znajdziesz <a target=\"_blank\" href=\"https://developers.google.com/maps/documentation/javascript/get-api-key\">pod tym linkiem</a>";
$lang["import"] = "Importuj trasę";
$lang["iuploadfailure"] = "Błąd przesyłania pliku";
$lang["iparsefailure"] = "Błąd parsowania pliku";
$lang["idatafailure"] = "Brak trasy w importowanym pliku";
$lang["isizefailure"] = "Wielkość importowanego pliku nie może przekraczać %d bajtów"; // substitutes number of bytes
$lang["imultiple"] = "Uwaga, zaimportowano kilka tras (%d)"; // substitutes number of imported tracks
$lang["allusers"] = "Wszyscy";
$lang["unitday"] = "d"; // abbreviation for days, like 4 d 11:11:11
$lang["unitkmh"] = "km/h"; // kilometer per hour
$lang["unitm"] = "m"; // meter
$lang["unitkm"] = "km"; // kilometer
$lang["unitmph"] = "mph"; // mile per hour
$lang["unitft"] = "ft"; // feet
$lang["unitmi"] = "mi"; // mile
$lang["unitkt"] = "kt"; // knot
$lang["unitnm"] = "Mm"; // nautical mile
$lang["config"] = "Ustawienia";
$lang["editingconfig"] = "Domyślne ustawienia aplikacji";
$lang["latitude"] = "Początkowa szerokość";
$lang["longitude"] = "Początkowa długość";
$lang["interval"] = "Interwał";
$lang["googlekey"] = "Klucz API Google Maps";
$lang["passlength"] = "Minimalna długość hasła";
$lang["passstrength"] = "Minimalna siła hasła";
$lang["requireauth"] = "Wymagaj logowania";
$lang["publictracks"] = "Publiczne trasy";
$lang["strokeweight"] = "Grubość linii";
$lang["strokeopacity"] = "Przezroczystość linii";
$lang["strokecolor"] = "Kolor linii";
$lang["colornormal"] = "Kolor znacznika";
$lang["colorstart"] = "Kolor znacznika początkowego";
$lang["colorstop"] = "Kolor znacznika końcowego";
$lang["colorextra"] = "Kolor znacznika specjalnego";
$lang["colorhilite"] = "Kolor znacznika zaznaczonego";
$lang["ollayers"] = "Warstwa OpenLayers";
$lang["layername"] = "Nazwa warstwy";
$lang["layerurl"] = "URL warstwy";
$lang["add"] = "Dodaj";
$lang["edit"] = "Edytuj";
$lang["delete"] = "Usuń";
$lang["settings"] = "Ustawienia";
?>
