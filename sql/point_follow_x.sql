CREATE TABLE `point_follow_x` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '会员ID',
  `current_point` INT(10) UNSIGNED NOT NULL COMMENT '当前用户积分',
  `point` INT(10) NOT NULL COMMENT '消费积分=负数，获得积分=正数',
  `type` TINYINT(1) NOT NULL COMMENT '积分变更类型',
  `desc` VARCHAR(255) NOT NULL COMMENT '积分变更描述',
  `created_at` INT(10) NOT NULL COMMENT '时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分流水表';