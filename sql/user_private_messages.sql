CREATE TABLE `user_private_messages` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` INT(10) UNSIGNED NOT NULL  COMMENT '第一人称用户ID',
  `reply_userid` INT(10) UNSIGNED NOT NULL COMMENT '回复的用户ID',
  `content` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '内容',
  `created_at` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户私信表';