CREATE TABLE `act_lottery_reward` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `rand` VARCHAR(50) COMMENT '等级',
  `lottery_id` INT(10) COMMENT '抽奖关联id',
  `name` VARCHAR(100) NOT NULL COMMENT '标题',
  `content` VARCHAR(100) NOT NULL COMMENT '奖品',
  `num` INT(10) NOT NULL DEFAULT 0 COMMENT '奖品数量',
  `left` INT(10) NOT NULL DEFAULT 0 COMMENT '剩余数量',
  `probability` FLOAT(5, 2) NOT NULL COMMENT '概率',
  `del` TINYINT(1) DEFAULT 0 COMMENT '1删除',
  `type` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '奖品类型',
  `basename` VARCHAR(100) COMMENT '图片',
  `created_at` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY (`lottery_id`),
  KEY (`name`)
)ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='奖品表';