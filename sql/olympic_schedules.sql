CREATE TABLE `olympic_schedules` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `date` INT(10) UNSIGNED NOT NULL COMMENT '日期',
  `name` VARCHAR (60) NOT NULL COMMENT '赛程名称',
  `created_at` INT(10) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '奥运会赛程';