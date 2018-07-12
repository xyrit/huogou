CREATE TABLE `friends` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '第一人称用户ID',
  `friend_userid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '对方ID',
  `dateline` int unsigned NOT NULL COMMENT '成为好友时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  UNIQUE KEY `idx_friend_userid` (`user_id`, `friend_userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='好友表';