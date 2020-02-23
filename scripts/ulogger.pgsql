--
-- Database: `ulogger`
--

CREATE DATABASE ulogger WITH ENCODING='UTF8' LC_COLLATE = 'en_US.utf-8' LC_CTYPE = 'en_US.utf-8';
\connect ulogger;


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id serial PRIMARY KEY,
  login varchar(15) NOT NULL UNIQUE,
  password varchar(255) NOT NULL DEFAULT '',
  admin boolean NOT NULL DEFAULT FALSE
);

-- --------------------------------------------------------

--
-- Table structure for table `tracks`
--

DROP TABLE IF EXISTS tracks;
CREATE TABLE tracks (
  id serial PRIMARY KEY,
  user_id int NOT NULL,
  name varchar(255) DEFAULT NULL,
  comment varchar(1024) DEFAULT NULL,
  FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE INDEX idx_user_id ON tracks(user_id);

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

DROP TABLE IF EXISTS positions;
CREATE TABLE positions (
  id serial PRIMARY KEY,
  time timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  user_id int NOT NULL,
  track_id int NOT NULL,
  latitude double precision NOT NULL,
  longitude double precision NOT NULL,
  altitude double precision DEFAULT NULL,
  speed double precision DEFAULT NULL,
  bearing double precision DEFAULT NULL,
  accuracy int DEFAULT NULL,
  provider varchar(100) DEFAULT NULL,
  comment varchar(255) DEFAULT NULL,
  image varchar(100) DEFAULT NULL,
  FOREIGN KEY(user_id) REFERENCES users(id),
  FOREIGN KEY(track_id) REFERENCES tracks(id)
);

CREATE INDEX idx_ptrack_id ON positions(track_id);
CREATE INDEX idx_puser_id ON positions(user_id);

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS config;
CREATE TABLE config (
  name varchar(20) PRIMARY KEY,
  value bytea NOT NULL
);

--
-- Data for table `config`
--

INSERT INTO config (name, value) VALUES
('color_extra', 's:7:"#cccccc";'),
('color_hilite', 's:7:"#feff6a";'),
('color_normal', 's:7:"#ffffff";'),
('color_start', 's:7:"#55b500";'),
('color_stop', 's:7:"#ff6a00";'),
('google_key', 's:0:"";'),
('interval_seconds', 'i:10;'),
('lang', 's:2:"en";'),
('latitude', 'd:52.229999999999997;'),
('longitude', 'd:21.010000000000002;'),
('map_api', 's:10:"openlayers";'),
('pass_lenmin', 'i:10;'),
('pass_strength', 'i:2;'),
('public_tracks', 'b:1;'),
('require_auth', 'b:1;'),
('stroke_color', 's:7:"#ff0000";'),
('stroke_opacity', 'd:1;'),
('stroke_weight', 'i:2;'),
('units', 's:6:"metric";');

-- --------------------------------------------------------

--
-- Table structure for table `ol_layers`
--

DROP TABLE IF EXISTS ol_layers;

CREATE TABLE ol_layers (
  id serial PRIMARY KEY,
  name varchar(50) NOT NULL,
  url varchar(255) NOT NULL,
  priority int NOT NULL DEFAULT '0'
);

--
-- Data for table ol_layers
--

INSERT INTO ol_layers (id, name, url, priority) VALUES
(1, 'OpenCycleMap', 'https://{a-c}.tile.thunderforest.com/cycle/{z}/{x}/{y}.png', 0),
(2, 'OpenTopoMap', 'https://{a-c}.tile.opentopomap.org/{z}/{x}/{y}.png', 0),
(3, 'OpenSeaMap', 'https://tiles.openseamap.org/seamark/{z}/{x}/{y}.png', 0),
(4, 'ESRI', 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', 0),
(5, 'UMP', 'http://{1-3}.tiles.ump.waw.pl/ump_tiles/{z}/{x}/{y}.png', 0),
(6, 'Osmapa.pl', 'http://{a-c}.tile.openstreetmap.pl/osmapa.pl/{z}/{x}/{y}.png', 0);

--
-- This will add default user admin with password admin
-- The password should be changed immediatelly after installation
-- Uncomment if needed
--
-- INSERT INTO `users` (`id`, `login`, `password`) VALUES
-- (1, 'admin', '$2y$10$7OvZrKgonVZM9lkzrTbiou.CVhO3HjPk5y0W9L68fVwPs/osBRIMq');