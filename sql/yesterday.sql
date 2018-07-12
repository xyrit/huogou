CREATE TABLE `yesterday` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `member` INT(10) NOT NULL DEFAULT 0 COMMENT '新增会员',
  `income` INT(10) NOT NULL DEFAULT 0 COMMENT '收入',
  `recharge` INT(10) NOT NULL DEFAULT 0 COMMENT '充值',
  `lottery` INT(10) NOT NULL DEFAULT 0 COMMENT '开奖',
  `delivery` INT(10) NOT NULL DEFAULT 0 COMMENT '发货',
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='昨日统计表';