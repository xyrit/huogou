CREATE TABLE `act_lottery_log_x` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '抽奖用户',
  `activity_id` INT(10) UNSIGNED DEFAULT 0 COMMENT '活动id',
  `reward_id` INT(10) UNSIGNED NOT NULL COMMENT '奖品id',
  `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0未中奖，1已中奖',
  `created_at` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
)ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='抽奖记录';