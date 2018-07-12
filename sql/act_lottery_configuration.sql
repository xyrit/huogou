CREATE TABLE `act_lottery_configuration` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` VARCHAR(100) NOT NULL COMMENT '标题',
  `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1启用，2停用，3已过期',
  `start_time` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '开始时间',
  `end_time` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '结束时间',
  `validity_start` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '有效开始时间',
  `validity_end` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '有效结束时间',
  `content` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '奖品内容',
  `url` VARCHAR(100) COMMENT '关联地址',
  `introduce` VARCHAR(255) COMMENT '说明',
  `consume` INT(10) NOT NULL DEFAULT 0 COMMENT '是否消耗福分',
  `created_at` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY (`status`)
)ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='抽奖配置';