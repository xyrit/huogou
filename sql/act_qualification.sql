CREATE TABLE `act_qualification` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` INT(10) UNSIGNED NOT NULL  COMMENT 'uid',
  `num` INT(10) UNSIGNED NOT NULL COMMENT '次数',
  `created_at` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY (`user_id`)
)ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='抽奖次数表';