CREATE TABLE `group_topic_comments` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '圈子帖子ID',
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0未审核，1通过，2不通过',
  `topic_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '圈子话题ID',
  `message` TEXT NOT NULL COMMENT '发布内容',
  `user_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '发布用户ID',
  `is_topic` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '状态，0=是回复,1=是话题',
  `reply_floor` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '回复第几楼',
  `reply_userid` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '回复userid',
  `ip` VARCHAR(20) NOT NULL DEFAULT '' COMMENT 'ip',
  `created_at` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '发布时间',
  PRIMARY KEY (`id`),
  KEY `idx_topic_id` (`topic_id`,`created_at`)
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='圈子帖子表';