CREATE TABLE `share_topic_images` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `share_topic_id` INT(10) UNSIGNED NOT NULL COMMENT '晒单话题ID',
  `basename` VARCHAR (255) NOT NULL COMMENT '带后缀的图片名',
  PRIMARY KEY (`id`),
  KEY `idx_share_topic_id` (`share_topic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='晒单相册表';