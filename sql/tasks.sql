CREATE TABLE `tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '任务名',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '类型（1 新手 2 日常 3 成长）',
  `level` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '子类型（1 称号 2 充值 3 等级）',
  `num` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '需要数量',
  `award_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '奖励类型（1 福分 2 伙购币 3 红包）',
  `award_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '奖励数量（红包时为红包ID）',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='任务表';