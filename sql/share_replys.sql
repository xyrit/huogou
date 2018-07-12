CREATE TABLE `share_replys` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '晒单回复ID',
  `share_comment_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '晒单评论ID',
  `content` text NOT NULL COMMENT '发布内容',
  `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `floor` int unsigned NOT NULL DEFAULT '0' COMMENT '第几楼',
  `reply_floor` int unsigned NOT NULL DEFAULT '0' COMMENT '回复第几楼',
  `ip` varchar(20) NOT NULL DEFAULT '' COMMENT 'ip',
  `created_at` int unsigned NOT NULL DEFAULT '0' COMMENT '发布时间',
  PRIMARY KEY (`id`),
  KEY `idx_comment_id` (`share_comment_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='晒单回复表';
