SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `session` (
  `session_id` char(64) NOT NULL,
  `data` text,
  `user_agent` char(64) DEFAULT NULL,
  `ip_address` varchar(46) DEFAULT NULL,
  `time_updated` int DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  KEY `time_updated_idx` (`time_updated`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(60) NULL DEFAULT NULL,
  `last_name` varchar(60) NULL DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `role` char(1) NULL DEFAULT NULL,
  `active` enum('Y', 'N') NOT NULL DEFAULT 'Y',
  `created_by` int NOT NULL DEFAULT 1,
  `created_date` datetime NOT NULL,
  `updated_by` int NOT NULL DEFAULT 1,
  `updated_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_uq` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `media` (
  `id` int NOT NULL AUTO_INCREMENT,
  `filename` varchar(20) NOT NULL,
  `width` int NULL DEFAULT NULL,
  `height` int NULL DEFAULT NULL,
  `feature` enum('Y', 'N') NOT NULL DEFAULT 'N',
  `caption` varchar(100) NULL DEFAULT NULL,
  `mime_type` varchar(255) NULL DEFAULT NULL,
  `optimized` varchar(50) NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT 1,
  `created_date` datetime NOT NULL,
  `updated_by` int NOT NULL DEFAULT 1,
  `updated_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `optimized_idx` (`optimized`),
  KEY `caption_idx` (`caption`),
  FULLTEXT KEY `media_ft` (`caption`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `media_category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` varchar(100) NOT NULL,
  `created_by` int NOT NULL DEFAULT 1,
  `created_date` datetime NOT NULL,
  `updated_by` int NOT NULL DEFAULT 1,
  `updated_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category_idx` (`category`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `media_category_map` (
  `id` int NOT NULL AUTO_INCREMENT,
  `media_id` int NOT NULL,
  `category_id` int NOT NULL,
  `media_sort` smallint NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_id_category_id_uq` (`media_id`, `category_id`),
  KEY `category_id_idx` (`category_id`),
  CONSTRAINT `media_category_map_media_id_fk` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE CASCADE,
  CONSTRAINT `media_category_map_category_id_fk` FOREIGN KEY (`category_id`) REFERENCES `media_category` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `collection` (
  `id` int NOT NULL AUTO_INCREMENT,
  `collection_slug` varchar(100) NULL DEFAULT NULL,
  `collection_title` varchar(60) NOT NULL,
  `collection_definition` varchar(60) NOT NULL,
  `created_by` int NOT NULL DEFAULT 1,
  `created_date` datetime NOT NULL,
  `updated_by` int NOT NULL DEFAULT 1,
  `updated_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `collection_slug_uq` (`collection_slug`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `page` (
  `id` int NOT NULL AUTO_INCREMENT,
  `collection_id` int NULL DEFAULT NULL,
  `page_slug` varchar(100) NOT NULL,
  `template` varchar(200) NOT NULL,
  `title` varchar(60) NOT NULL,
  `sub_title` varchar(150) NULL DEFAULT NULL,
  `meta_description` varchar(320) NULL DEFAULT NULL,
  `published_date` date NULL DEFAULT NULL,
  `media_id` int NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT 1,
  `created_date` datetime NOT NULL,
  `updated_by` int NOT NULL DEFAULT 1,
  `updated_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `page_slug_idx` (`page_slug`),
  UNIQUE KEY `collection_id_page_slug_uq` (`collection_id`,`page_slug`),
  KEY `published_date_idx` (`published_date`),
  KEY `media_id_idx` (`media_id`),
  FULLTEXT KEY `page_ft` (`title`,`sub_title`,`meta_description`),
  CONSTRAINT `page_media_id_fk` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL,
  CONSTRAINT `page_collection_id_fk` FOREIGN KEY (`collection_id`) REFERENCES `collection` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `navigation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `navigator` varchar(60) NOT NULL DEFAULT 'main',
  `parent_id` int NULL DEFAULT NULL,
  `sort` smallint NULL DEFAULT 1,
  `page_id` int NULL DEFAULT NULL,
  `collection_id` int NULL DEFAULT NULL,
  `title` varchar(60) NULL DEFAULT NULL,
  `url` varchar(2000) NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT 1,
  `created_date` datetime NOT NULL,
  `updated_by` int NOT NULL DEFAULT 1,
  `updated_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `navigator_idx` (`navigator`),
  KEY `page_id_idx` (`page_id`),
  KEY `parent_id_idx` (`parent_id`),
  CONSTRAINT `navigation_page_id_fk` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE,
  CONSTRAINT `navigation_parent_id_fk` FOREIGN KEY (`parent_id`) REFERENCES `navigation` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `page_element` (
  `id` int NOT NULL AUTO_INCREMENT,
  `page_id` int NOT NULL,
  `block_key` varchar(60) NOT NULL,
  `template` varchar(200) NOT NULL,
  `element_sort` smallint NOT NULL DEFAULT 1,
  `title` varchar(200) NULL DEFAULT NULL,
  `content` mediumtext NULL DEFAULT NULL,
  `excerpt` varchar(60) NULL DEFAULT NULL,
  `collection_id` int NULL DEFAULT NULL,
  `gallery_id` int NULL DEFAULT NULL,
  `media_id` int NULL DEFAULT NULL,
  `embedded` varchar(1000) NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT 1,
  `created_date` datetime NOT NULL,
  `updated_by` int NOT NULL DEFAULT 1,
  `updated_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `page_id_idx` (`page_id`),
  KEY `media_id_idx` (`media_id`),
  FULLTEXT KEY `page_element_ft` (`title`,`content`),
  CONSTRAINT `page_element_page_id_fk` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE,
  CONSTRAINT `page_element_media_id_fk` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`) ON DELETE SET NULL,
  CONSTRAINT `page_element_collection_id_fk` FOREIGN KEY (`collection_id`) REFERENCES `collection` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `message` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NULL DEFAULT NULL,
  `email` varchar(100) NULL DEFAULT NULL,
  `message` varchar(1000) NULL DEFAULT NULL,
  `is_read` enum('Y','N','A') NOT NULL DEFAULT 'N',
  `context` varchar(100) NULL DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT 1,
  `created_date` datetime NOT NULL,
  `updated_by` int NOT NULL DEFAULT 1,
  `updated_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `is_read_idx` (`is_read`),
  KEY `created_date_idx` (`created_date`),
  FULLTEXT KEY `message_ft` (`name`,`email`,`message`,`context`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `message_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `message_id` int DEFAULT NULL,
  `data_key` varchar(60) NOT NULL,
  `data_value` varchar(4000) DEFAULT NULL,
  `created_by` int NOT NULL DEFAULT 1,
  `created_date` datetime NOT NULL,
  `updated_by` int NOT NULL DEFAULT 1,
  `updated_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `message_idx` (`message_id`),
  FULLTEXT KEY `data_value_ft` (`data_value`),
  CONSTRAINT `message_id_fk` FOREIGN KEY (`message_id`) REFERENCES `message` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `data_store` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` varchar(60) NOT NULL,
  `page_id` int DEFAULT NULL,
  `element_id` int DEFAULT NULL,
  `setting_key` varchar(60) NOT NULL,
  `setting_value` varchar(4000) DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT 1,
  `created_date` datetime NOT NULL,
  `updated_by` int(11) NOT NULL DEFAULT 1,
  `updated_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category_idx` (`category`),
  KEY `page_id_category_idx` (`page_id`, `category`),
  KEY `element_id_category_idx` (`element_id`, `category`),
  CONSTRAINT `page_id_fk` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE,
  CONSTRAINT `element_id_fk` FOREIGN KEY (`element_id`) REFERENCES `page_element` (`id`) ON DELETE CASCADE,
  FULLTEXT KEY `data_store_ft` (`setting_value`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4;

INSERT INTO `page` (`id`, `page_slug`, `template`, `title`, `sub_title`, `meta_description`, `published_date`, `media_id`, `created_by`, `created_date`, `updated_by`, `updated_date`)
VALUES
  (1,'home','heroPage','Home',NULL,'All about this page for SEO.','2018-12-27',NULL,1,now(),1,now());

INSERT INTO `page_element` (`id`, `page_id`, `block_key`, `template`, `element_sort`, `title`, `content`, `excerpt`, `gallery_id`, `media_id`, `embedded`, `created_by`, `created_date`, `updated_by`, `updated_date`)
VALUES
  (1,1,'heroBlock','hero/hero',1,'Welcome to PitonCMS','<p>A flexible content management system for your personal website.</p>','A flexible content management system for your personal',NULL,NULL,NULL,1,now(),1,now()),
  (2,1,'contentBlock','text/text',1,'Where to Start?','<p>Congratulations! You have successfully installed PitonCMS. </p>\n<p>To start, you will want to read the documentation on how to setup and configure your new site <a href=\"https://github.com/pitoncms\" target=\"_blank\">here</a>. Follow the easy step-by-step process for creating your own personalized theme.  </p>','Congratulations! You have successfully installed PitonCMS.',NULL,NULL,NULL,1,now(),1,now());

INSERT INTO `data_store` (`category`,`page_id`, `element_id`, `setting_key`, `setting_value`, `created_by`, `created_date`, `updated_by`, `updated_date`)
VALUES
  ('site', NULL, NULL, 'theme', 'default', 1, now(), 1, now()),
  ('page', 1, NULL, 'ctaTitle', 'Read more on Github', 1, now(), 1, now()),
  ('page', 1, NULL, 'ctaTarget', 'https://github.com/PitonCMS/Piton', 1, now(), 1, now()),
  ('element', NULL, 1, 'overlay', '100', 1, now(), 1, now()),
  ('element', NULL, 1, 'overlayColor', '51, 102, 153', 1, now(), 1, now()),
  ('element', NULL, 1, 'placement', 'Center', 1, now(), 1, '2021-01-10 13:23:42');
  ('piton', NULL, NULL, 'appAlert', NULL, 1, now(), 1, now());
