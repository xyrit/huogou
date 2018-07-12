CREATE TABLE `app_install` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '客户端唯一标示',
  `source` varchar(20) NOT NULL DEFAULT '' COMMENT '来源，包名',
  `account` varchar(50) DEFAULT NULL COMMENT '登陆账号',
  `create_time` int(10) NOT NULL COMMENT '第一次打开时间',
  `update_time` int(10) DEFAULT NULL COMMENT '更新时间',
  `install_times` int(5) DEFAULT '0' COMMENT '重复安装次数',
  `login_times` int(5) DEFAULT '0' COMMENT '重复登陆次数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;