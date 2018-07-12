CREATE TABLE `olympic_rank` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `gold` INT(10) NOT NULL COMMENT '金牌数量',
  `silver` INT(10) NOT NULL COMMENT '银牌数量',
  `bronze` INT(10) NOT NULL COMMENT '铜牌数量',
  `score` INT(10) UNSIGNED NOT NULL COMMENT '积分',
  `created_at` CHAR(16) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '奥运会排行榜';