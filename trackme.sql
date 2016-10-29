--
-- Database layout inherited from TrackMe
-- Some tables/columns are not used by the viewer at the moment.
-- Kept for compatibility with old data.
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE DATABASE IF NOT EXISTS `trackme` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `trackme`;

CREATE TABLE IF NOT EXISTS `cellids` (
  `ID` int(11) NOT NULL auto_increment,
  `CellID` varchar(255) NOT NULL,
  `Latitude` double NOT NULL,
  `Longitude` double NOT NULL,
  `DateAdded` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `SignalStrength` int(11) default NULL,
  `SignalStrengthMax` int(11) default NULL,
  `SignalStrengthMin` int(11) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `Index_CellID` (`CellID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `icons` (
  `ID` int(11) NOT NULL auto_increment,
  `Name` varchar(255) NOT NULL,
  `URL` varchar(512) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `positions` (
  `ID` int(11) NOT NULL auto_increment,
  `FK_Users_ID` int(11) NOT NULL,
  `FK_Trips_ID` int(11) default NULL,
  `FK_Icons_ID` int(11) default NULL,
  `Latitude` double NOT NULL,
  `Longitude` double NOT NULL,
  `Altitude` double default '0',
  `Speed` double default '0',
  `Angle` double default NULL,
  `DateAdded` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `DateOccurred` timestamp NULL default '0000-00-00 00:00:00',
  `Comments` varchar(255) default NULL,
  `ImageURL` varchar(255) default NULL,
  `SignalStrength` int(11) default NULL,
  `SignalStrengthMax` int(11) default NULL,
  `SignalStrengthMin` int(11) default NULL,
  `BatteryStatus` tinyint(4) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `Index_FK_Trips_ID` (`FK_Trips_ID`),
  KEY `Index_FK_Users_ID` (`FK_Users_ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `trips` (
  `ID` int(11) NOT NULL auto_increment,
  `FK_Users_ID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Comments` varchar(1024) default NULL,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Index_FK_Users_ID_Name` (`FK_Users_ID`,`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users` (
  `ID` int(11) NOT NULL auto_increment,
  `username` varchar(15) NOT NULL,
  `password` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `Index_username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `icons` VALUES (1, 'Snack', 'http://maps.google.com/mapfiles/ms/micons/snack_bar.png');
INSERT INTO `icons` VALUES (2, 'Hiking', 'http://maps.google.com/mapfiles/ms/micons/hiker.png');
INSERT INTO `icons` VALUES (3, 'Lodging', 'http://maps.google.com/mapfiles/ms/micons/lodging.png');
INSERT INTO `icons` VALUES (4, 'Restaurant', 'http://maps.google.com/mapfiles/ms/micons/restaurant.png');
INSERT INTO `icons` VALUES (5, 'POI', 'http://maps.google.com/mapfiles/ms/micons/POI.png');
INSERT INTO `icons` VALUES (6, 'Arts', 'http://maps.google.com/mapfiles/ms/micons/arts.png');
INSERT INTO `icons` VALUES (7, 'Bar', 'http://maps.google.com/mapfiles/ms/micons/bar.png');
INSERT INTO `icons` VALUES (8, 'Blue-dot', 'http://maps.google.com/mapfiles/ms/micons/blue-dot.png');
INSERT INTO `icons` VALUES (9, 'Bus', 'http://maps.google.com/mapfiles/ms/micons/bus.png');
INSERT INTO `icons` VALUES (10, 'Taxi', 'http://maps.google.com/mapfiles/ms/micons/cabs.png');
INSERT INTO `icons` VALUES (11, 'Camera', 'http://maps.google.com/mapfiles/ms/micons/camera.png');
INSERT INTO `icons` VALUES (12, 'Camping', 'http://maps.google.com/mapfiles/ms/micons/campground.png');
INSERT INTO `icons` VALUES (13, 'Caution', 'http://maps.google.com/mapfiles/ms/micons/caution.png');
INSERT INTO `icons` VALUES (14, 'Coffee House', 'http://maps.google.com/mapfiles/ms/micons/coffeehouse.png');
INSERT INTO `icons` VALUES (15, 'Store', 'http://maps.google.com/mapfiles/ms/micons/convienancestore.png');
INSERT INTO `icons` VALUES (16, 'Cycling', 'http://maps.google.com/mapfiles/ms/micons/cycling.png');
INSERT INTO `icons` VALUES (17, 'Dollar', 'http://maps.google.com/mapfiles/ms/micons/dollar.png');
INSERT INTO `icons` VALUES (18, 'Drinking water', 'http://maps.google.com/mapfiles/ms/micons/drinking_water.png');
INSERT INTO `icons` VALUES (19, 'Electronics', 'http://maps.google.com/mapfiles/ms/micons/electronics.png');
INSERT INTO `icons` VALUES (20, 'Falling Rocks', 'http://maps.google.com/mapfiles/ms/micons/fallingrocks.png');
INSERT INTO `icons` VALUES (21, 'Ferry', 'http://maps.google.com/mapfiles/ms/micons/ferry.png');
INSERT INTO `icons` VALUES (22, 'Fire Dept.', 'http://maps.google.com/mapfiles/ms/micons/firedept.png');
INSERT INTO `icons` VALUES (23, 'Fishing', 'http://maps.google.com/mapfiles/ms/micons/fishing.png');
INSERT INTO `icons` VALUES (24, 'Flag', 'http://maps.google.com/mapfiles/ms/micons/flag.png');
INSERT INTO `icons` VALUES (25, 'Gas', 'http://maps.google.com/mapfiles/ms/micons/gas.png');
INSERT INTO `icons` VALUES (26, 'Grocery Store', 'http://maps.google.com/mapfiles/ms/micons/grocerystore.png');
INSERT INTO `icons` VALUES (27, 'Helicopter', 'http://maps.google.com/mapfiles/ms/micons/helicopter.png');
INSERT INTO `icons` VALUES (28, 'Horseback riding', 'http://maps.google.com/mapfiles/ms/micons/horsebackriding.png');
INSERT INTO `icons` VALUES (29, 'Hospital', 'http://maps.google.com/mapfiles/ms/micons/hospitals.png');
INSERT INTO `icons` VALUES (30, 'Hot springs', 'http://maps.google.com/mapfiles/ms/micons/hotsprings.png');
INSERT INTO `icons` VALUES (31, 'Info', 'http://maps.google.com/mapfiles/ms/micons/info.png');
INSERT INTO `icons` VALUES (32, 'Info 2', 'http://maps.google.com/mapfiles/ms/micons/info_circle.png');
INSERT INTO `icons` VALUES (33, 'Man', 'http://maps.google.com/mapfiles/ms/micons/man.png');
INSERT INTO `icons` VALUES (34, 'Marina', 'http://maps.google.com/mapfiles/ms/micons/marina.png');
INSERT INTO `icons` VALUES (35, 'Mechanic', 'http://maps.google.com/mapfiles/ms/micons/mechanic.png');
INSERT INTO `icons` VALUES (36, 'Motorcycling', 'http://maps.google.com/mapfiles/ms/micons/motorcycling.png');
INSERT INTO `icons` VALUES (37, 'Parking', 'http://maps.google.com/mapfiles/ms/micons/parkinglot.png');
INSERT INTO `icons` VALUES (38, 'Partly Cloudy', 'http://maps.google.com/mapfiles/ms/micons/partly_cloudy.png');
INSERT INTO `icons` VALUES (39, 'Phone', 'http://maps.google.com/mapfiles/ms/micons/phone.png');
INSERT INTO `icons` VALUES (40, 'Picnic', 'http://maps.google.com/mapfiles/ms/micons/picnic.png');
INSERT INTO `icons` VALUES (41, 'Plane', 'http://maps.google.com/mapfiles/ms/micons/plane.png');
INSERT INTO `icons` VALUES (42, 'Police', 'http://maps.google.com/mapfiles/ms/micons/police.png');
INSERT INTO `icons` VALUES (43, 'Post Office', 'http://maps.google.com/mapfiles/ms/micons/postoffice-us.png');
INSERT INTO `icons` VALUES (44, 'Question mark', 'http://maps.google.com/mapfiles/ms/micons/question.png');
INSERT INTO `icons` VALUES (45, 'Rail', 'http://maps.google.com/mapfiles/ms/micons/rail.png');
INSERT INTO `icons` VALUES (46, 'Rainy', 'http://maps.google.com/mapfiles/ms/micons/rainy.png');
INSERT INTO `icons` VALUES (47, 'Ranger Station', 'http://maps.google.com/mapfiles/ms/micons/rangerstation.png');
INSERT INTO `icons` VALUES (48, 'Recycle', 'http://maps.google.com/mapfiles/ms/micons/recycle.png');
INSERT INTO `icons` VALUES (49, 'Snow', 'http://maps.google.com/mapfiles/ms/micons/snowflake_simple.png');
INSERT INTO `icons` VALUES (50, 'Sport', 'http://maps.google.com/mapfiles/ms/micons/sportvenue.png');
INSERT INTO `icons` VALUES (51, 'Subway', 'http://maps.google.com/mapfiles/ms/micons/subway.png');
INSERT INTO `icons` VALUES (52, 'Sunny', 'http://maps.google.com/mapfiles/ms/micons/sunny.png');
INSERT INTO `icons` VALUES (53, 'Swimming', 'http://maps.google.com/mapfiles/ms/micons/swimming.png');
INSERT INTO `icons` VALUES (54, 'Toilets', 'http://maps.google.com/mapfiles/ms/micons/toilets.png');
INSERT INTO `icons` VALUES (55, 'Trail', 'http://maps.google.com/mapfiles/ms/micons/trail.png');
INSERT INTO `icons` VALUES (56, 'Tree', 'http://maps.google.com/mapfiles/ms/micons/tree.png');
INSERT INTO `icons` VALUES (57, 'Truck', 'http://maps.google.com/mapfiles/ms/micons/truck.png');
INSERT INTO `icons` VALUES (58, 'Volcano', 'http://maps.google.com/mapfiles/ms/micons/volcano.png');
INSERT INTO `icons` VALUES (59, 'Water', 'http://maps.google.com/mapfiles/ms/micons/water.png');
INSERT INTO `icons` VALUES (60, 'Waterfalls', 'http://maps.google.com/mapfiles/ms/micons/waterfalls.png');
INSERT INTO `icons` VALUES (61, 'Wheel Chair', 'http://maps.google.com/mapfiles/ms/micons/wheel_chair_accessible.png');
INSERT INTO `icons` VALUES (62, 'Woman', 'http://maps.google.com/mapfiles/ms/micons/woman.png');
INSERT INTO `icons` VALUES (63, 'Monster gate', 'http://img.gamespot.com/gamespot/shared/user/emblem_e3monster_s.jpg');
