CREATE TABLE `euro_userbuylist` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `game_date` CHAR(8) NOT NULL COMMENT '赛事日期',
  `team` VARCHAR (25) NOT NULL COMMENT '竞猜球队',
  `buy_num` INT(10) UNSIGNED NOT NULL COMMENT '竞猜数量',
  `buy_time` CHAR(16) NOT NULL COMMENT '购买时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_id_game` (`user_id`, `game_date`, `team`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '欧洲杯用户购买纪录表';