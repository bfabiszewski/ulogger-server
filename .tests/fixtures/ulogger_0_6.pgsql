/*
 * μlogger
 *
 * Copyright(C) 2020 Bartek Fabiszewski (www.fabiszewski.net)
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

--
-- Database schema for μlogger 0.6
--

-- --------------------------------------------------------

--
-- Drop tables if exist
--

DROP TABLE IF EXISTS positions;
DROP TABLE IF EXISTS tracks;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS config;
DROP TABLE IF EXISTS ol_layers;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE users (
    id serial PRIMARY KEY,
    login varchar(15) NOT NULL UNIQUE,
    password varchar(255) NOT NULL DEFAULT ''
);

-- --------------------------------------------------------

--
-- Table structure for table `tracks`
--

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
    image_id int DEFAULT NULL,
    FOREIGN KEY(user_id) REFERENCES users(id),
    FOREIGN KEY(track_id) REFERENCES tracks(id)
);

CREATE INDEX idx_ptrack_id ON positions(track_id);
CREATE INDEX idx_puser_id ON positions(user_id);
