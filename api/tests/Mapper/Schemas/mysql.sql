CREATE TABLE `users`
(
    `id`       int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `login`    varchar(15) CHARACTER SET latin1  NOT NULL UNIQUE,
    `password` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
    `admin`    boolean                           NOT NULL DEFAULT FALSE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tracks`
(
    `id`      int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` int(11) NOT NULL,
    `name`    varchar(255)  DEFAULT NULL,
    `comment` varchar(1024) DEFAULT NULL,
    INDEX     `idx_user_id` (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `positions`
(
    `id`       int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `time`     timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_id`  int(11) NOT NULL,
    `track_id` int(11) NOT NULL,
    `latitude` double NOT NULL,
    `longitude` double NOT NULL,
    `altitude` double DEFAULT NULL,
    `speed` double DEFAULT NULL,
    `bearing` double DEFAULT NULL,
    `accuracy` int(11) DEFAULT NULL,
    `provider` varchar(100)       DEFAULT NULL,
    `comment`  varchar(255)       DEFAULT NULL,
    `image`    varchar(100)       DEFAULT NULL,
    INDEX      `idx_ptrack_id` (`track_id`),
    INDEX      `index_puser_id` (`user_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
    FOREIGN KEY (`track_id`) REFERENCES `tracks` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `config`
(
    `name`  varchar(20) PRIMARY KEY,
    `value` tinyblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ol_layers`
(
    `id`       int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name`     varchar(50)  NOT NULL,
    `url`      varchar(255) NOT NULL,
    `priority` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
