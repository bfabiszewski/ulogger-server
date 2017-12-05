<?php
###CHANGE TO MATCH YOUR MQTT SETTINGS
$MQTT_server = "YOUR_MQTT_SERVER_IP";
$MQTT_port = YOUR_MQTT_SERVER_PORT; #probably 1883
$MQTT_username = "YOUR_MQTT_USERNAME";
$MQTT_password = "YOUR_MQTT_PASSWORD";
$MQTT_client_id = "phpMQTT-publisher"; #Should be unique to connect to your server. Can use uniqid()

#Available parameters provided by ulogger, for use in $MQTT_topic or $MQTT_message:
#Latitude: $lat
#Longitude: $lon
#Timestamp: $timestamp
#Altitude: $altitude
#Speed: $speed
#Bearing: $bearing
#Accuracy: $accuracy
#Provider: $provider
#Comment: $comment
#Image ID: $imageId
#Track ID: $trackId
$MQTT_topic = "location/".$MQTT_username; #Use $MQTT_username here to have a unique topic for each ulogger user. To track individual devices, have a unique user for each device
$MQTT_message = "{\"latitude\":$lat,\"longitude\":$lon,\"gps_accuracy\":$accuracy}"; #Write JSON that you want, with $parameters described above, and replace " in JSON with \", then wrap in "";
$MQTT_qos = 0; #Untested in ulogger
$MQTT_retain = 0; #Set to 1 to retain (untested in ulogger)
###

require(__DIR__."/phpMQTT/phpMQTT.php");
$mqtt = new Bluerhinos\phpMQTT($MQTT_server, $MQTT_port, $MQTT_client_id);
if ($mqtt->connect(true, NULL, $MQTT_username, $MQTT_password)) {
	$mqtt->publish($MQTT_topic, $MQTT_message, $MQTT_qos, $MQTT_retain);
	$mqtt->close();
}
?>



