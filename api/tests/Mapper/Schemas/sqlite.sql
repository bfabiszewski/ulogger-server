CREATE TABLE `users`
(
    `id`       integer PRIMARY KEY AUTOINCREMENT,
    `login`    varchar(15)  NOT NULL UNIQUE,
    `password` varchar(255) NOT NULL DEFAULT '',
    `admin`    integer      NOT NULL DEFAULT 0
);

CREATE TABLE `tracks`
(
    `id`      integer PRIMARY KEY AUTOINCREMENT,
    `user_id` integer NOT NULL,
    `name`    varchar(255)  DEFAULT NULL,
    `comment` varchar(1024) DEFAULT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
);
CREATE INDEX `idx_user_id` ON `tracks` (`user_id`);

CREATE TABLE `positions`
(
    `id`       integer PRIMARY KEY AUTOINCREMENT,
    `time`     timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id`  integer   NOT NULL,
    `track_id` integer   NOT NULL,
    `latitude` double NOT NULL,
    `longitude` double NOT NULL,
    `altitude` double DEFAULT NULL,
    `speed` double DEFAULT NULL,
    `bearing` double DEFAULT NULL,
    `accuracy` integer            DEFAULT NULL,
    `provider` varchar(100)       DEFAULT NULL,
    `comment`  varchar(255)       DEFAULT NULL,
    `image`    varchar(100)       DEFAULT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
    FOREIGN KEY (`track_id`) REFERENCES `tracks` (`id`)
);
CREATE INDEX `idx_ptrack_id` ON `positions` (`track_id`);
CREATE INDEX `idx_puser_id` ON `positions` (`user_id`);

CREATE TABLE `config`
(
    `name`  varchar(20) PRIMARY KEY,
    `value` tinyblob NOT NULL
);

CREATE TABLE `ol_layers`
(
    `id`       integer PRIMARY KEY AUTOINCREMENT,
    `name`     varchar(50)  NOT NULL,
    `url`      varchar(255) NOT NULL,
    `priority` integer      NOT NULL DEFAULT '0'
);
