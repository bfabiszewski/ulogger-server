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

$langSetup["dbconnectfailed"] = "Błąd połączenia z bazą danych.";
$langSetup["serversaid"] = "Komunikat serwera: %s";
$langSetup["checkdbsettings"] = "Proszę sprawdzić konfigurację bazy danych w pliku 'config.php'.";
$langSetup["dbqueryfailed"] = "Błąd zapytania do bazy danych.";
$langSetup["dbtablessuccess"] = "Pomyślnie utworzono tablice w bazie danych!";
$langSetup["setupuser"] = "Skonfiguruj teraz swojego użytkownika w µloggerze.";
$langSetup["congratulations"] = "Gratulacje!";
$langSetup["setupcomplete"] = "Konfiguracja zakończona. Możesz teraz przejść do <a href=\"../index.php\">strony głównej</a> i zalogować się na konto utworzonego użytkownika.";
$langSetup["disablewarn"] = "WAŻNE! NAZLEŻY DEZAKTYWOWAĆ SKRYPT 'setup.php' ALBO USUNĄĆ GO Z SERWERA.";
$langSetup["disabledesc"] = "Pozostawienie dostępu do skryptu z przeglądarki stanowi duże zagrożenie. Każdy będzie mógł go uruchomić, usunąć całą bazę danych i dodać nowego użytkownika. Usuń plik lub dezaktywuj go przywracając zmiennej %s wartość %s.";
$langSetup["setupfailed"] = "Niestety coś poszło nie tak. Może znajdziesz więcej wskazówek w logach serwera www.";
$langSetup["welcome"] = "Witaj w µloggerze!";
$langSetup["disabledwarn"] = "Ze względów bezpieczeństwa ten skrypt jest domyślnie wyłączony. Aby go aktywować należy otworzyć plik 'scripts/setup.php' w edytorze tekstu i zmienić wartość zmiennej %s na początku pliku na %s.";
$langSetup["lineshouldread"] = "Linia: %s powinna zostać zmieniona na: %s";
$langSetup["passfuncwarn"] = "Zainstalowana wersja PHP nie zawiera funkcji obsługujących hasła, dostępnych od wersji PHP 5.5. Musisz włączyć bibliotekę 'password_compat'.";
$langSetup["passfunchack"] = "Otwórz proszę plik 'helpers/user.php' w edytorze tekstu i odkomentuj linię włączającą 'helpers/password.php'.";
$langSetup["dorestart"] = "Uruchom ten skrypt ponownie, kiedy zakończysz.";
$langSetup["createconfig"] = "Utwórz proszę plik 'config.php' w głównym folderze. Możesz skopiować jego początkową zawartość z pliku 'config.default.php'. Pamiętaj, żeby dostosować konfiguracje do swoich potrzeb i ustawień bazy danych.";
$langSetup["nodbsettings"] = "Musisz skonfigurować parametry dostępu do bazy danych w pliku 'config.php' (%s).";
$langSetup["scriptdesc"] = "Ten skrypt utworzy tablice niezbędne do działania aplikacji µlogger (%s). Zostaną one utworzone w bazie danych o nazwie %s. Uwaga, jeśli tablice już istnieją, zostaną usunięte i utworzone ponownie, ich zawartość zostanie skasowana.";
$langSetup["scriptdesc2"] = "Następnie skrypt poprosi o utworzenie konta do logowania w aplikacji µlogger.";
$langSetup["startbutton"] = "Naciśnij, aby rozpocząć";
$langSetup["restartbutton"] = "Uruchom ponownie";

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
$lang["altitude"] = "Wysokość";
$lang["ttime"] = "Czas podróży";
$lang["aspeed"] = "Średnia prędkość";
$lang["tdistance"] = "Odległość";
$lang["suser"] = "wybierz login";
$lang["pointof"] = "Punkt %d z %d";
$lang["summary"] = "Podsumowanie";
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
$lang["userdeletewarn"] = "Uwaga!\n\nZamierzasz całkowicie usunąć użytkownika %s, razem ze wszystkimi jego trasami i pozycjami.\n\nCzy na pewno?";
$lang["editinguser"] = "Edytujesz użytkownika %s";
$lang["selfeditwarn"] = "Nie można edytować własnego użytkownika za pomocą tego narzędzia";
$lang["apifailure"] = "Niestety ładowanie API %s nie powiodło się";
$lang["trackdelwarn"] = "Uwaga!\n\nZamierzasz całkowicie usunąć trasę %s wraz ze wszystkimi pozycjami.\n\nCzy na pewno?";
$lang["editingtrack"] = "Edytujesz trasę %s";
$lang["deltrack"] = "Usuń trasę";
$lang["trackname"] = "Nazwa trasy";
$lang["edittrack"] = "Edytuj trasę";
$lang["passlenmin"] = "Hasło musi się składać z minimum %d znaków";
$lang["passrules"][1] = "Powinno ono zawierać przynajmniej jedną małą i jedną wielką literę";
$lang["passrules"][2] = "Powinno ono zawierać przynajmniej jedną małą, jedną wielką literę i jedną cyfrę";
$lang["passrules"][3] = "Powinno ono zawierać przynajmniej jedną małą, jedną wielką literę, jedną cyfrę i jeden znak specjalny (nie alfanumeryczny)";
$lang["owntrackswarn"] = "Możesz edytować tylko swoje własne trasy";
$lang["gmauthfailure"] = "Prawdopodobnie na tej stronie występuje problem z kluczem API Google Maps";
$lang["gmapilink"] = "Więcej informacji o kluczach API znajdziesz <a target=\"_blank\" href=\"https://developers.google.com/maps/documentation/javascript/get-api-key\">pod tym linkiem</a>";
$lang["import"] = "Importuj trasę";
$lang["iuploadfailure"] = "Błąd przesyłania pliku";
$lang["iparsefailure"] = "Błąd parsowania pliku";
$lang["idatafailure"] = "Brak trasy w importowanym pliku";
$lang["isizefailure"] = "Wielkość importowanego pliku nie może przekraczać %d bajtów";
$lang["imultiple"] = "Uwaga, zaimportowano kilka tras (%d)";
?>
