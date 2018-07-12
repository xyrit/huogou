CREATE TABLE `lottery_compute` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `period_id` int(11) DEFAULT NULL,
  `data` longtext CHARACTER SET utf8,
  PRIMARY KEY (`id`),
  KEY `period_id` (`period_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2035 DEFAULT CHARSET=utf8;