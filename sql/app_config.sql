CREATE TABLE IF NOT EXISTS `app_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `type` varchar(20) NOT NULL COMMENT '类型',
  `content` text COMMENT '内容',
  `auth` varchar(10) DEFAULT NULL COMMENT '操作人',
  `time` datetime DEFAULT NULL COMMENT '操作时间',
  `status` smallint(3) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;