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
  map_api varchar(50) NOT NULL DEFAULT 'openlayers',
  latitude double precision NOT NULL DEFAULT '52.23',
  longitude double precision NOT NULL DEFAULT '21.01',
  google_key varchar(50) DEFAULT NULL,
  require_auth boolean NOT NULL DEFAULT TRUE,
  public_tracks boolean NOT NULL DEFAULT FALSE,
  pass_lenmin int NOT NULL DEFAULT '10',
  pass_strength smallint NOT NULL DEFAULT '2',
  interval_seconds int NOT NULL DEFAULT '10',
  lang varchar(10) NOT NULL DEFAULT 'en',
  units varchar(10) NOT NULL DEFAULT 'metric',
  stroke_weight int NOT NULL DEFAULT '2',
  stroke_color int NOT NULL DEFAULT '16711680',
  stroke_opacity int NOT NULL DEFAULT '100'
);

--
-- Data for table `config`
--

INSERT INTO config DEFAULT VALUES;

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