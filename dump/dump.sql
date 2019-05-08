CREATE TABLE `servers` (
  `hostid` int(11) NOT NULL,
  `host` varchar(255) NOT NULL,
  `ip` varchar(50) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'OK',
  `message` text,
  `priority` smallint(6) DEFAULT '-1',
  `diod_id` int(11),
  PRIMARY KEY (`hostid`),
  UNIQUE KEY `servers_name_uindex` (`host`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

CREATE TABLE `history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hostid` int(11) NOT NULL,
  `message` text,
  `priority` smallint(6) DEFAULT NULL,
  `date` datetime NOT NULL,
  `status` varchar(50) DEFAULT 'OK',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8

