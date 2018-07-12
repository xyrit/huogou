CREATE TABLE `signs` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`days`  int(10) NOT NULL DEFAULT 0 COMMENT '天数' ,
`type`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '奖励类型（1 福分  2 伙购币 3 红包）' ,
`num`  int(10) NOT NULL DEFAULT 0 COMMENT '奖励数量/红包ID' ,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARACTER SET=utf8 COMMENT='签到表';