CREATE TABLE `invite_commission` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) NOT NULL COMMENT '用户id',
  `action_user_id` INT(10) NOT NULL COMMENT '动作用户id',
  `money` INT(10) UNSIGNED NOT NULL COMMENT '实际支付金额(元)',
  `commission` INT(10) NOT NULL COMMENT '佣金(分)',
  `type` TINYINT(1) UNSIGNED NOT NULL COMMENT '佣金类型',
  `desc` VARCHAR(255) NOT NULL COMMENT '佣金描述',
  `created_time` INT(10) NOT NULL COMMENT '时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`, `type`)
) ENGINE=INNODB DEFAULT CHARSET=UTF8 COMMENT='佣金明细表';