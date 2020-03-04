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
$langSetup["dbconnectfailed"] = "數據庫鏈接失敗";
$langSetup["serversaid"] = "服務器說： %s"; // substitutes server error message
$langSetup["checkdbsettings"] = "請檢查cofig.php檔案中的數據庫配置項。";
$langSetup["dbqueryfailed"] = "數據庫查詢失敗。";
$langSetup["dbtablessuccess"] = "數據庫表創建成功。";
$langSetup["setupuser"] = "現在請建立您的µlogger賬戶。";
$langSetup["congratulations"] = "恭喜！";
$langSetup["setupcomplete"] = "設置已經完成！您可以用您的賬戶在<a href=\"../index.php\">主頁</a> 登錄了。";
$langSetup["disablewarn"] = "重要提示！您必須將setup.php中的配置項設置未禁用（false）或着把setup.php從服務器刪掉。";
$langSetup["disabledesc"] = "如果不處理setup.php將會留下巨大的安全漏洞。任何人都可以像你剛剛做的那樣，清除數據庫、設置新用戶。請務必刪除setup.php或通過把 %s 的值設置會 %s 來禁用它。"; // substitutes variable name and value
$langSetup["setupfailed"] = "很不幸，出現了一些錯誤。您可以在網頁服務器的日志中獲得更多信息。";
$langSetup["welcome"] = "歡迎來到µlogger!";
$langSetup["disabledwarn"] = "由於安全原因這個腳本默認時禁用的。要啟用它，您可以通過編輯 'scripts/setup.php' 把檔案開始部分 %s 的值設置為 %s。"; // substitutes variable name and value
$langSetup["lineshouldread"] = "行： %s 應該修改為： %s";
$langSetup["dorestart"] = "完成後請重新執行本腳本。";
$langSetup["createconfig"] = "請在項目根目錄創建 'config.php' 檔案。 您可以將'config.default.php'複製為'config.php'。請記得修改裡面的數據庫配置和其他你想要修改的項目。";
$langSetup["nodbsettings"] = "您必須在'config.php' file (%s) 中提供你的數據庫鏈接資料。"; // substitutes variable names
$langSetup["scriptdesc"] = "這個腳本將會為ulogger建起其所需的數據庫表格(%s)。 它們將被建立在數據庫 %s 中。 請注意，如果這些表格已經存在則它們會被刪除並重新創建，它們的內容也會隨之被刪除。"; // substitutes table names and db name
$langSetup["scriptdesc2"] = "執行完成後腳本會請您新建一個µlogger的賬戶，您需要為其設置用戶名和密碼。";
$langSetup["startbutton"] = "開始執行";
$langSetup["restartbutton"] = "重新啟動";
$langSetup["optionwarn"] = "PHP configuration option %s must be set to %s."; // substitutes option name and value
$langSetup["extensionwarn"] = "Required PHP extension %s is not available."; // substitutes extension name


// application strings
$lang["title"] = "• μlogger •";
$lang["private"] = "請登錄";
$lang["authfail"] = "賬號或密碼錯誤";
$lang["user"] = "用戶";
$lang["track"] = "路徑";
$lang["latest"] = "最新位置";
$lang["autoreload"] = "自動刷新";
$lang["reload"] = "刷新";
$lang["export"] = "導出路徑";
$lang["chart"] = "海拔圖表";
$lang["close"] = "關閉";
$lang["time"] = "時間";
$lang["speed"] = "速度";
$lang["accuracy"] = "精度";
$lang["position"] = "Position";
$lang["altitude"] = "海拔";
$lang["bearing"] = "Bearing";
$lang["ttime"] = "總時間";
$lang["aspeed"] = "平均速度";
$lang["tdistance"] = "總距離";
$lang["pointof"] = "第 %d / %d 個點"; // e.g. Point 3 of 10
$lang["summary"] = "行程總結";
$lang["suser"] = "選擇用戶";
$lang["logout"] = "登出";
$lang["login"] = "登錄";
$lang["username"] = "用戶名";
$lang["password"] = "密碼";
$lang["language"] = "語言";
$lang["newinterval"] = "請輸入新的刷新頻率（秒）";
$lang["api"] = "地圖接口";
$lang["units"] = "單位";
$lang["metric"] = "公制";
$lang["imperial"] = "英制/美國";
$lang["nautical"] = "Nautical";
$lang["admin"] = "Administrator";
$lang["adminmenu"] = "管理員";
$lang["passwordrepeat"] = "請再次輸入密碼";
$lang["passwordenter"] = "請輸入密碼";
$lang["usernameenter"] = "請輸入用戶名";
$lang["adduser"] = "添加用戶";
$lang["userexists"] = "用戶離開了。";
$lang["cancel"] ="取消";
$lang["submit"] = "提交";
$lang["oldpassword"] = "舊密碼";
$lang["newpassword"] = "新密碼";
$lang["newpasswordrepeat"] = "請再次輸入密碼";
$lang["changepass"] = "修改密碼";
$lang["gps"] = "GPS";
$lang["network"] = "網絡";
$lang["deluser"] = "刪除用戶";
$lang["edituser"] = "修改用戶";
$lang["servererror"] = "服務器發生錯誤";
$lang["allrequired"] = "所有欄目均為必填";
$lang["passnotmatch"] = "密碼不匹配";
$lang["actionsuccess"] = "完成";
$lang["actionfailure"] = "失敗";
$lang["notauthorized"] = "User not authorized";
$lang["userdelwarn"] = "注意！\n\n您即將永久刪除用戶 %s 以及他/她的所有路徑和位置資料。\n\n確定刪除嗎？"; // substitutes user login
$lang["editinguser"] = "您正在編輯用戶 %s"; // substitutes user login
$lang["selfeditwarn"] = "您不能用此工具編輯您的用戶";
$lang["apifailure"] = "對不起，無法加載 %s 的接口"; // substitutes api name (gmaps or openlayers)
$lang["trackdelwarn"] = "注意！\n\n您即將刪除路徑%s和此路徑的所有位置。\n\n確定刪除嗎？"; // substitutes track name
$lang["editingtrack"] = "您正在編輯路徑%s"; // substitutes track name
$lang["deltrack"] = "刪除路徑";
$lang["trackname"] = "路徑名稱";
$lang["edittrack"] = "修改路徑";
$lang["positiondelwarn"] = "Warning!\n\nYou are going to permanently delete position %d of track %s.\n\nAre you sure?"; // substitutes position index and track name
$lang["editingposition"] = "You are editing position #%d of track %s"; // substitutes position index and track name
$lang["delposition"] = "Remove position";
$lang["comment"] = "Comment";
$lang["editposition"] = "Edit position";
$lang["passlenmin"] = "密碼至少要有 %d 位哦"; // substitutes password minimum length
$lang["passrules_1"] = "密碼至少有一個小寫字母和一個大寫字母哦";
$lang["passrules_2"] = "密碼至少有一個數字、一個小寫字母和一個大寫字母哦";
$lang["passrules_3"] = "密碼至少有一個數字、一個小寫字母、一個大寫字母和一個符號哦";
$lang["owntrackswarn"] = "您只能編輯自己的路徑";
$lang["gmauthfailure"] = "Google Maps API密鑰似乎有問題哦";
$lang["gmapilink"] = "您可以在<a target=\"_blank\" href=\"https://developers.google.com/maps/documentation/javascript/get-api-key\">Google webpage</a>獲取Maps API密鑰的相關諮詢。";
$lang["import"] = "導入路徑";
$lang["iuploadfailure"] = "上傳失敗";
$lang["iparsefailure"] = "解析失敗";
$lang["idatafailure"] = "在上傳的檔案中未發現路徑數據";
$lang["isizefailure"] = "上傳的檔案大小不能超過 %d 比特哦"; // substitutes number of bytes
$lang["imultiple"] = "恭喜，%d 條路徑已導入"; // substitutes number of imported tracks
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
