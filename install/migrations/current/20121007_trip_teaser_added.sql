ALTER TABLE `%SQL_PREFIX%trips` ADD `teaser` varchar(140) NULL DEFAULT NULL  AFTER `title`;
ALTER TABLE `%SQL_PREFIX%trips` ADD `keywords` varchar(160) NULL DEFAULT NULL  AFTER `teaser`;
