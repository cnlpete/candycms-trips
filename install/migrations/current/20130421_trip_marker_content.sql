ALTER TABLE `%SQL_PREFIX%trip_markers` CHANGE `description` `content` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '';

ALTER TABLE `%SQL_PREFIX%trip_markers` DROP `galleryalbum_id`;
ALTER TABLE `%SQL_PREFIX%trip_markers` DROP `blog_id`;
ALTER TABLE `%SQL_PREFIX%trip_markers` DROP `content_id`;
