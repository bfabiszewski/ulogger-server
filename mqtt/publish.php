<?php
###CHANGE TO MATCH YOUR MQTT SETTINGS
$MQTT_server = "YOUR_MQTT_SERVER_IP";
$MQTT_port = YOUR_MQTT_SERVER_PORT; #probably 1883
$MQTT_username = "YOUR_MQTT_USERNAME";
$MQTT_password = "YOUR_MQTT_PASSWORD";
$MQTT_client_id = "phpMQTT-publisher"; #Should be unique to connect to your server. Can use uniqid()
###

require(__DIR__."/phpMQTT/phpMQTT.php");
$mqtt = new Bluerhinos\phpMQTT($MQTT_server, $MQTT_port, $MQTT_client_id);
if ($mqtt->connect(true, NULL, $MQTT_username, $MQTT_password)) {
	#$mqtt->publish("location/phone", "{\"Latitude\":\"$lat\",\"Longitude\":\"$lon\",\"Timestamp\":\"$timestamp\",\"Altitude\":\"$altitude\",\"Speed\":\"$speed\",\"Bearing\":\"$bearing\",\"Accuracy\":\"$accuracy\",\"Provider\":\"$provider\",\"Comment\":\"$comment\",\"ImageID\":\"$imageId\",\"trackId\":\"$trackId\"}", 0);
	$mqtt->publish("location/phone", "{\"latitude\":$lat,\"longitude\":$lon,\"gps_accuracy\":$accuracy}", 0);
	$mqtt->close();
}
?>
