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


--
-- This will add default user admin with password admin
-- The password should be changed immediatelly after installation
-- Uncomment if needed
--
-- INSERT INTO `users` (`id`, `login`, `password`) VALUES
-- (1, 'admin', '$2y$10$7OvZrKgonVZM9lkzrTbiou.CVhO3HjPk5y0W9L68fVwPs/osBRIMq');