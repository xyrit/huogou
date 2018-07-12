CREATE TABLE `act_qualification_log` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '用户',
  `num` INT(10) UNSIGNED DEFAULT 0 COMMENT '获得次数',
  `type` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1充值抽奖，2分享抽奖',
  `created_at` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY (`user_id`),
  KEY (`type`)
)ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='获得抽奖资格记录表';