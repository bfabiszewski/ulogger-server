<?php
declare(strict_types = 1);

/**
 * @package    μlogger
 * @copyright  2017–2024 Bartek Fabiszewski (www.fabiszewski.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 */

// default language for translations

// strings only used in setup
$langSetup["dbconnectfailed"] = "数据库连接失败。";
$langSetup["serversaid"] = "服务器说：%s"; // substitutes server error message
$langSetup["checkdbsettings"] = "请检查 'config.php' 文件中的数据库设置。";
$langSetup["dbqueryfailed"] = "数据库查询失败。";
$langSetup["dbtablessuccess"] = "数据库表成功创建！";
$langSetup["setupuser"] = "现在请设置你的 µlogger 用户。";
$langSetup["congratulations"] = "恭喜您！";
$langSetup["setupcomplete"] = "设置现在已完成。您可以前往 <a href=\"../index.php\">主页面</a> 并使用您的新用户账号登录。";
$langSetup["disablewarn"] = "重要！您必须禁用 'setup.php' 脚本或将其从您的服务器中移除。";
$langSetup["disabledesc"] = "让脚本在浏览器中可访问是一个重大安全隐患。任何人都能够运行它，删除您的数据库并设置新的用户账号。删除该文件或通过将 %s 值设置回 %s 来禁用它。"; // substitutes variable name and value
$langSetup["setupfailed"] = "不幸的是，发生了错误。您可以尝试在您的 Web 服务器日志中查找更多信息。";
$langSetup["welcome"] = "欢迎使用 µlogger！";
$langSetup["disabledwarn"] = "出于安全原因，该脚本默认被禁用。要启用它，您必须在文本编辑器中编辑 'scripts/setup.php' 文件，并将文件开头的 %s 变量设置为 %s。"; // substitutes variable name and value
$langSetup["lineshouldread"] = "行：%s 应该读取：%s";
$langSetup["dorestart"] = "完成后，请重新启动此脚本。";
$langSetup["createconfig"] = "请在根文件夹中创建 'config.php' 文件。您可以从 'config.default.php' 复制开始。确保您调整配置值以匹配您的需求和数据库设置。";
$langSetup["nodbsettings"] = "您必须在 'config.php' 文件中提供您的数据库凭据 (%s)。"; // substitutes variable names
$langSetup["scriptdesc"] = "该脚本将设置 µlogger 所需的表 (%s)。它们将在名为 %s 的数据库中创建。警告，如果表已经存在，它们将被删除并重新创建，其内容将被销毁。"; // substitutes table names and db name
$langSetup["scriptdesc2"] = "完成后，脚本将要求您提供 µlogger 用户的用户名和密码。";
$langSetup["startbutton"] = "按下开始";
$langSetup["restartbutton"] = "重启";
$langSetup["optionwarn"] = "PHP 配置选项 %s 必须设置为 %s。"; // substitutes option name and value
$langSetup["extensionwarn"] = "所需的 PHP 扩展 %s 不可用。"; // substitutes extension name
$langSetup["notwritable"] = "文件夹 '%s' 必须可被 PHP 写入。"; // substitutes folder path


// application strings
$lang["title"] = "• μlogger •";
$lang["private"] = "您需要用户名和密码才能访问此页面。";
$lang["authfail"] = "用户名或密码错误";
$lang["user"] = "用户";
$lang["track"] = "轨迹";
$lang["latest"] = "最新位置";
$lang["autoreload"] = "自动重载";
$lang["reload"] = "立即重载";
$lang["export"] = "导出轨迹";
$lang["chart"] = "高度图表";
$lang["close"] = "关闭";
$lang["time"] = "时间";
$lang["speed"] = "速度";
$lang["accuracy"] = "精度";
$lang["position"] = "位置";
$lang["altitude"] = "海拔";
$lang["bearing"] = "方位";
$lang["ttime"] = "总时间";
$lang["aspeed"] = "平均速度";
$lang["tdistance"] = "总距离";
$lang["pointof"] = "第 %d 点，共 %d 点"; // e.g. Point 3 of 10
$lang["summary"] = "行程总结";
$lang["suser"] = "选择用户";
$lang["logout"] = "退出登录";
$lang["login"] = "登录";
$lang["username"] = "用户名";
$lang["password"] = "密码";
$lang["language"] = "语言";
$lang["newinterval"] = "输入新的间隔值（秒）";
$lang["api"] = "地图 API";
$lang["units"] = "单位";
$lang["metric"] = "公制";
$lang["imperial"] = "英制/美国制";
$lang["nautical"] = "海洋";
$lang["admin"] = "管理员";
$lang["adminmenu"] = "管理";
$lang["passwordrepeat"] = "重复密码";
$lang["passwordenter"] = "输入密码";
$lang["usernameenter"] = "输入用户名";
$lang["adduser"] = "添加用户";
$lang["userexists"] = "用户已存在";
$lang["cancel"] ="取消";
$lang["submit"] = "提交";
$lang["oldpassword"] = "旧密码";
$lang["newpassword"] = "新密码";
$lang["newpasswordrepeat"] = "重复新密码";
$lang["changepass"] = "更改密码";
$lang["gps"] = "GPS";
$lang["network"] = "网络";
$lang["deluser"] = "删除用户";
$lang["edituser"] = "编辑用户";
$lang["servererror"] = "服务器错误";
$lang["allrequired"] = "所有字段都是必填的";
$lang["passnotmatch"] = "密码不匹配";
$lang["oldpassinvalid"] = "错误的旧密码";
$lang["passempty"] = "密码为空";
$lang["loginempty"] = "登录为空";
$lang["passstrengthwarn"] = "无效的密码强度";
$lang["actionsuccess"] = "操作成功完成";
$lang["actionfailure"] = "出了点问题";
$lang["notauthorized"] = "用户未授权";
$lang["userunknown"] = "未知用户";
$lang["userdelwarn"] = "警告！\n\n您将永久删除用户 %s，以及他们的所有路线和位置。\n\n您确定吗？"; // substitutes user login
$lang["editinguser"] = "您正在编辑用户 %s"; // substitutes user login
$lang["selfeditwarn"] = "对不起，您不能使用该工具编辑自己的用户";
$lang["apifailure"] = "抱歉，无法加载 %s API"; // substitutes api name (gmaps or openlayers)
$lang["trackdelwarn"] = "警告！\n\n您将永久删除轨迹 %s及其所有位置。\n\n您确定吗？"; // substitutes track name
$lang["editingtrack"] = "您正在编辑轨迹 %s"; // substitutes track name
$lang["deltrack"] = "删除轨迹";
$lang["trackname"] = "轨迹名称";
$lang["edittrack"] = "编辑轨迹";
$lang["positiondelwarn"] = "警告！\n\n您将永久删除轨迹 %s 的位置 %d。\n\n您确定吗？"; // substitutes position index and track name
$lang["editingposition"] = "您正在编辑轨迹 %s 的位置 #%d"; // substitutes position index and track name
$lang["delposition"] = "删除位置";
$lang["delimage"] = "删除图像";
$lang["comment"] = "评论";
$lang["image"] = "图像";
$lang["editposition"] = "编辑位置";
$lang["passlenmin"] = "密码长度必须至少为 %d 个字符"; // substitutes password minimum length
$lang["passrules_1"] = "它应该至少包含一个小写字母和一个大写字母";
$lang["passrules_2"] = "它应该至少包含一个小写字母、一个大写字母和一个数字";
$lang["passrules_3"] = "它应该至少包含一个小写字母、一个大写字母、一个数字和一个非字母数字字符";
$lang["owntrackswarn"] = "您只能编辑自己的轨迹";
$lang["gmauthfailure"] = "此页面可能存在 Google Maps API 密钥问题";
$lang["gmapilink"] = "您可以在<a target=\"_blank\" href=\"https://developers.google.com/maps/documentation/javascript/get-api-key\">这 Google 网页</a>上找到有关 API 密钥的更多信息";
$lang["import"] = "导入轨迹";
$lang["iuploadfailure"] = "上传失败";
$lang["iparsefailure"] = "解析失败";
$lang["idatafailure"] = "导入文件中没有轨迹数据";
$lang["isizefailure"] = "上传的文件大小不得超过 %d 字节"; // substitutes number of bytes
$lang["imultiple"] = "注意，导入了多个轨迹（%d）"; // substitutes number of imported tracks
$lang["allusers"] = "所有用户";
$lang["unitday"] = "天"; // abbreviation for days, like 4 d 11:11:11
$lang["unitkmh"] = "公里/小时"; // kilometer per hour
$lang["unitm"] = "米"; // meter
$lang["unitamsl"] = "海平面以上"; // above mean see level
$lang["unitkm"] = "公里"; // kilometer
$lang["unitmph"] = "英里/小时"; // mile per hour
$lang["unitft"] = "英尺"; // feet
$lang["unitmi"] = "英里"; // mile
$lang["unitkt"] = "节"; // knot
$lang["unitnm"] = "海里"; // nautical mile
$lang["config"] = "设置";
$lang["editingconfig"] = "默认应用设置";
$lang["latitude"] = "初始纬度";
$lang["longitude"] = "初始经度";
$lang["interval"] = "间隔（秒）";
$lang["googlekey"] = "Google Maps API 密钥";
$lang["passlength"] = "密码最小长度";
$lang["passstrength"] = "密码最低强度";
$lang["requireauth"] = "需要授权";
$lang["publictracks"] = "公共轨迹";
$lang["strokeweight"] = "描边重量";
$lang["strokeopacity"] = "描边不透明度";
$lang["strokecolor"] = "描边颜色";
$lang["colornormal"] = "标记颜色";
$lang["colorstart"] = "起始标记颜色";
$lang["colorstop"] = "停止标记颜色";
$lang["colorextra"] = "额外标记颜色";
$lang["colorhilite"] = "高亮标记颜色";
$lang["uploadmaxsize"] = "最大上传大小（MB）";
$lang["ollayers"] = "OpenLayers 图层";
$lang["layername"] = "图层名称";
$lang["layerurl"] = "图层网址";
$lang["add"] = "添加";
$lang["edit"] = "编辑";
$lang["delete"] = "删除";
$lang["settings"] = "设置";
$lang["trackcolor"] = "轨迹颜色";
?>
