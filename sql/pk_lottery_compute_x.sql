CREATE TABLE `pk_lottery_compute_x` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `period_id` int(11) NOT NULL,
  `data` longtext CHARACTER SET utf8,
  PRIMARY KEY (`id`),
  UNIQUE KEY `period_id` (`period_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;