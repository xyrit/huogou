CREATE TABLE `act_lottery_reward_log` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '抽奖用户',
  `activity_id` INT(10) UNSIGNED DEFAULT 0 COMMENT '活动id',
  `reward_id` INT(10) UNSIGNED NOT NULL COMMENT '奖品id',
  `created_at` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY (`activity_id`),
  KEY (`reward_id`)
)ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='中奖记录';