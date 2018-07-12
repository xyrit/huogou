CREATE TABLE `share_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '晒单评论ID',
  `share_topic_id` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '晒单话题ID',
  `content` text NOT NULL COMMENT '内容',
  `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `ip` varchar(20) NOT NULL DEFAULT '' COMMENT 'ip',
  `created_at` int unsigned NOT NULL DEFAULT '0' COMMENT '发布时间',
  PRIMARY KEY (`id`),
  KEY `idx_topic_id` (`share_topic_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='晒单评论表';
