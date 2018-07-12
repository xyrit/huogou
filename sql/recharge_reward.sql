CREATE TABLE `recharge_reward` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '活动名称',
  `intr` text NOT NULL COMMENT '活动简介',
  `start_time` int(10) NOT NULL COMMENT '开始时间',
  `end_time` int(10) NOT NULL COMMENT '结束时间',
  `status` int(1) NOT NULL COMMENT '状态,0-启动，1-停止，2-过期',
  `prizes` text NOT NULL COMMENT '奖品内容',
  `create_time` int(10) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;