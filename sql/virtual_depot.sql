CREATE TABLE `virtual_depot` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `orderid` varchar(30) NOT NULL DEFAULT '',
  `card` varchar(30) NOT NULL DEFAULT '' COMMENT '卡号',
  `pwd` varchar(30) NOT NULL DEFAULT '' COMMENT '密码',
  `par_value` int(5) NOT NULL COMMENT '面值',
  `status` int(1) DEFAULT '0' COMMENT '状态，0-未发出，1-已发出',
  `type` varchar(10) DEFAULT NULL COMMENT '类型',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;