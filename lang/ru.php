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
$langSetup["dbconnectfailed"] = "Ошибка подключения к базе данных";
$langSetup["serversaid"] = "Ответ сервера: %s"; // substitutes server error message
$langSetup["checkdbsettings"] = "Пожалуйста проверьте настройки базы данных в 'config.php'.";
$langSetup["dbqueryfailed"] = "Ошибка запроса базы данных";
$langSetup["dbtablessuccess"] = "Таблицы в базе данных успешно созданы!";
$langSetup["setupuser"] = "Теперь настройте пользователя µlogger.";
$langSetup["congratulations"] = "Поздравляем!";
$langSetup["setupcomplete"] = "Настройка завершена. Теперь вы можете открыть <a href=\"../index.php\">главную страницу</a> используя ваш логин и пароль.";
$langSetup["disablewarn"] = "ВНИМАНИЕ! ВЫ ДОЛЖНЫ ОТКЛЮЧИТЬ, ИЛИ УДАЛИТЬ 'setup.php' С ВАШЕГО СЕРВЕРА.";
$langSetup["disabledesc"] = "Оставлять скрипт доступным из вне является критическим риском безопасности. Любой желающий сможет запустить его и удалить вашу базу данных, а также настроить новых пользователей. Удалите этот скрипт, или выключите в настройках для этого поменяйте значение %s обратно в %s."; // substitutes variable name and value
$langSetup["setupfailed"] = "К сожалению что-то пошло не так. Проверьте логи вашего вэб-сервера.";
$langSetup["welcome"] = "Добро пожаловать в µlogger!";
$langSetup["disabledwarn"] = "По соображениям безопасности этот скрипт отключен по умолчанию. Чтобы включить его вы должны отредактировать файл 'scripts/setup.php' и задать значение переменной %s в начале файла как %s."; // substitutes variable name and value
$langSetup["lineshouldread"] = "Строка: %s должна читать: %s";
$langSetup["dorestart"] = "Пожалуйста перезапустите этот скрипт, когда закончите.";
$langSetup["createconfig"] = "Пожалуйста создайте файл 'config.php' в корневой директории. Вы можете просто скопировать его с 'config.default.php' и задать ваши параметры для доступа к вашей базе данных.";
$langSetup["nodbsettings"] = "Вам нужно сконфигурировать доступ к вашей базе данных в 'config.php' (%s)."; // substitutes variable names
$langSetup["scriptdesc"] = "Этот скрипт создаст нужные таблицы для µlogger (%s). Они будут созданы в вашей базе данных %s. Внимание, если таблицы уже существуют, то они будут пересозданы и вся текущая информация в них будет уничтожена."; // substitutes table names and db name
$langSetup["scriptdesc2"] = "Когда все будет готово, скрипт потребует вас предоставь имя пользователя и пароль для вашего µlogger пользователя.";
$langSetup["startbutton"] = "Нажми для старта";
$langSetup["restartbutton"] = "Перезапуск";
$langSetup["optionwarn"] = "Опция %s в конфигурации PHP должна иметь значение %s."; // substitutes option name and value
$langSetup["extensionwarn"] = "Необходимый модуль PHP %s не найден."; // substitutes extension name


// application strings
$lang["title"] = "• μlogger •";
$lang["private"] = "Вам необходимо имя пользователя и пароль чтобы посетить эту страницу";
$lang["authfail"] = "Неправильное имя пользователя, или пароль";
$lang["user"] = "Пользователь";
$lang["track"] = "Трек";
$lang["latest"] = "Последняя известная позиция";
$lang["autoreload"] = "Автоматическое обновление";
$lang["reload"] = "Обновить сейчас";
$lang["export"] = "Экспортировать трек";
$lang["chart"] = "График высот";
$lang["close"] = "закрыть";
$lang["time"] = "Время";
$lang["speed"] = "Скорость";
$lang["accuracy"] = "Точность";
$lang["position"] = "Позиция";
$lang["altitude"] = "Высота";
$lang["bearing"] = "Bearing";
$lang["ttime"] = "Общее время";
$lang["aspeed"] = "Среднее время";
$lang["tdistance"] = "Общая дистанция";
$lang["pointof"] = "Точка %d из %d"; // e.g. Point 3 of 10
$lang["summary"] = "Статистика поездки";
$lang["suser"] = "выберите пользователя";
$lang["logout"] = "Выход";
$lang["login"] = "Войти";
$lang["username"] = "Имя пользователя";
$lang["password"] = "Пароль";
$lang["language"] = "Язык";
$lang["newinterval"] = "Введите значение нового интервала (в секундах)";
$lang["api"] = "API карт";
$lang["units"] = "Единицы измерения";
$lang["metric"] = "Метрическая система единиц";
$lang["imperial"] = "Английская система единиц";
$lang["nautical"] = "Естественные системы единиц";
$lang["admin"] = "Administrator";
$lang["adminmenu"] = "Администрирование";
$lang["passwordrepeat"] = "Повторите пароль";
$lang["passwordenter"] = "Введите пароль";
$lang["usernameenter"] = "Введите имя пользователя";
$lang["adduser"] = "Добавить пользователя";
$lang["userexists"] = "Пользователь существует";
$lang["cancel"] ="Отмена";
$lang["submit"] = "Отправить";
$lang["oldpassword"] = "Текущий пароль";
$lang["newpassword"] = "Новый пароль";
$lang["newpasswordrepeat"] = "Повторите новый пароль";
$lang["changepass"] = "Изменить пароль";
$lang["gps"] = "GPS";
$lang["network"] = "Сеть";
$lang["deluser"] = "Удалить пользователя";
$lang["edituser"] = "Редактировать пользователя";
$lang["servererror"] = "Серверная ошибка";
$lang["allrequired"] = "Все поля необходимы для заполнения";
$lang["passnotmatch"] = "Пароли не совпадают";
$lang["actionsuccess"] = "Выполнено успешно";
$lang["actionfailure"] = "Что-то пошло не так";
$lang["notauthorized"] = "Пользователь не авторизован";
$lang["userdelwarn"] = "Внимание!\n\nВы хотите безвозвратно удалить пользователя %s, вместе со всеми его треками и локациями.\n\nВы уверены?"; // substitutes user login
$lang["editinguser"] = "Вы редактируете пользователя %s"; // substitutes user login
$lang["selfeditwarn"] = "Вы не можете редактировать ваших собственных пользователей при помощи этого инструмента";
$lang["apifailure"] = "Извините, не могу загрузить %s API"; // substitutes api name (gmaps or openlayers)
$lang["trackdelwarn"] = "Внимание!\n\nВы хотите безвозвратно удалить трек %s, вместе со всеми его локациями.\n\nВы уверены?"; // substitutes track name
$lang["editingtrack"] = "Вы редактируете трек %s"; // substitutes track name
$lang["deltrack"] = "Удалить трек";
$lang["trackname"] = "Имя трека";
$lang["edittrack"] = "Редактировать трек";
$lang["positiondelwarn"] = "Warning!\n\nYou are going to permanently delete position %d of track %s.\n\nAre you sure?"; // substitutes position index and track name
$lang["editingposition"] = "You are editing position #%d of track %s"; // substitutes position index and track name
$lang["delposition"] = "Remove position";
$lang["comment"] = "Comment";
$lang["editposition"] = "Edit position";
$lang["passlenmin"] = "Длина пароля должна быть минимум %dсимволов."; // substitutes password minimum length
$lang["passrules_1"] = "Он должен содержать по меньшей мере один маленький символ, один большой символ";
$lang["passrules_2"] = "Он должен содержать по меньшей мере один маленький символ, один большой символ и одну цифру";
$lang["passrules_3"] = "Он должен содержать по меньшей мере один маленький символ, один большой символ, одну цифру и один специальный символ.";
$lang["owntrackswarn"] = "Вы можете редактировать только свои собственные треки";
$lang["gmauthfailure"] = "Возможна проблема с ключём API от Google Maps.";
$lang["gmapilink"] = "Вы сможете найти больше информации о ключах API на <a target=\"_blank\" href=\"https://developers.google.com/maps/documentation/javascript/get-api-key\">справочной странице Google</a>";
$lang["import"] = "Импортировать трек";
$lang["iuploadfailure"] = "Ошибка выгрузки";
$lang["iparsefailure"] = "Ошибка анализа";
$lang["idatafailure"] = "Импортированный трек не содержит данных";
$lang["isizefailure"] = "Размер выгружаемого файла не может превышать %d байт "; // substitutes number of bytes
$lang["imultiple"] = " Уведомление, несколько треков импортированы (%d)"; // substitutes number of imported tracks
$lang["allusers"] = "Все пользователи";
$lang["unitday"] = "сут"; // abbreviation for days, like 4 d 11:11:11
$lang["unitkmh"] = "км/ч"; // kilometer per hour
$lang["unitm"] = "м"; // meter
$lang["unitkm"] = "км"; // kilometer
$lang["unitmph"] = "mph"; // mile per hour
$lang["unitft"] = "ft"; // feet
$lang["unitmi"] = "mi"; // mile
$lang["unitkt"] = "kt"; // knot
$lang["unitnm"] = "nm"; // nautical mile
$lang["config"] = "Настройки";
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
$lang["settings"] = "Настройки";
?>
