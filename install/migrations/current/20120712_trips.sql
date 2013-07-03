CREATE TABLE `%SQL_PREFIX%trips` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL,
  `title` varchar(128) DEFAULT NULL,
  `color` varchar(32) DEFAULT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `%SQL_PREFIX%trip_points` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `trip_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `lat` decimal(10,6) NOT NULL,
  `long` decimal(10,6) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `date` datetime NOT NULL,
  `position` INT(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `%SQL_PREFIX%trip_markers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `trippoint_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `date` date NOT NULL,
  `title` varchar(64) NOT NULL,
  `description` text DEFAULT NULL,
  `galleryalbum_id` int(11) DEFAULT NULL,
  `blog_id` int(11) DEFAULT NULL,
  `content_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
