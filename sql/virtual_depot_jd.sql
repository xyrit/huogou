CREATE TABLE `virtual_depot_jd` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `card` varchar(30) NOT NULL DEFAULT '' COMMENT '卡号',
  `pwd` varchar(30) NOT NULL DEFAULT '' COMMENT '密码',
  `par_value` int(11) NOT NULL COMMENT '面值',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态，0-未发出，1-已发出',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '导入时间',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='京东充值卡仓库表';