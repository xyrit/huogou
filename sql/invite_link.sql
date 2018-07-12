CREATE TABLE `invite_link` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` CHAR(7) NOT NULL COMMENT '专属链接唯一标识',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;