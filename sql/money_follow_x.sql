CREATE TABLE `money_follow_x` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '会员ID',
  `current_money` INT(10) UNSIGNED NOT NULL COMMENT '当前用户余额',
  `money` INT(10) NOT NULL COMMENT '余额值',
  `type` TINYINT(1) NOT NULL COMMENT '余额变更类型',
  `desc` VARCHAR(255) NOT NULL COMMENT '余额变更描述',
  `created_at` INT(10) NOT NULL COMMENT '时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='余额流水表';