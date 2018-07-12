CREATE TABLE `invite` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `invite_uid` INT(10) UNSIGNED NOT NULL COMMENT '被邀请用户ID',
  `status` TINYINT(1) UNSIGNED NOT NULL COMMENT '状态，0=未参与购买，1=已参与购买',
  `invite_time` INT(10) UNSIGNED NOT NULL COMMENT '邀请时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `invite_uid` (`invite_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;