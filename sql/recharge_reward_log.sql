CREATE TABLE `recharge_reward_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `number` int(10) NOT NULL COMMENT '充值活动ID',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `level` int(2) NOT NULL COMMENT '完成等级',
  `prize` varchar(30) NOT NULL COMMENT '奖品',
  `amount` int(5) NOT NULL COMMENT '充值金额',
  `create_time` int(10) NOT NULL COMMENT '领取时间',
  `notice` int(1) NOT NULL COMMENT '提示状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;