CREATE TABLE `lottery_compute_x` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `period_id` int(11) DEFAULT NULL,
  `data` longtext,
  `expect` char(12) DEFAULT NULL COMMENT '时时彩期数',
  `shishi_num` char(8) DEFAULT NULL COMMENT '时时彩开奖号码',
  PRIMARY KEY (`id`),
  KEY `period_id` (`period_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;