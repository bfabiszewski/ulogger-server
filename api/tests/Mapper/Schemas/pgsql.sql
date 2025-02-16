CREATE TABLE users
(
    id       serial PRIMARY KEY,
    login    varchar(15)  NOT NULL UNIQUE,
    password varchar(255) NOT NULL DEFAULT '',
    admin    boolean      NOT NULL DEFAULT FALSE
);

CREATE TABLE tracks
(
    id      serial PRIMARY KEY,
    user_id int NOT NULL,
    name    varchar(255)  DEFAULT NULL,
    comment varchar(1024) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id)
);
CREATE INDEX idx_user_id ON tracks (user_id);

CREATE TABLE positions
(
    id        serial PRIMARY KEY,
    time      timestamp(0)     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    user_id   int              NOT NULL,
    track_id  int              NOT NULL,
    latitude  double precision NOT NULL,
    longitude double precision NOT NULL,
    altitude  double precision          DEFAULT NULL,
    speed     double precision          DEFAULT NULL,
    bearing   double precision          DEFAULT NULL,
    accuracy  int                       DEFAULT NULL,
    provider  varchar(100)              DEFAULT NULL,
    comment   varchar(255)              DEFAULT NULL,
    image     varchar(100)              DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users (id),
    FOREIGN KEY (track_id) REFERENCES tracks (id)
);
CREATE INDEX idx_ptrack_id ON positions (track_id);
CREATE INDEX idx_puser_id ON positions (user_id);

CREATE TABLE config
(
    name  varchar(20) PRIMARY KEY,
    value bytea NOT NULL
);
CREATE TABLE ol_layers
(
    id       serial PRIMARY KEY,
    name     varchar(50)  NOT NULL,
    url      varchar(255) NOT NULL,
    priority int          NOT NULL DEFAULT '0'
);
