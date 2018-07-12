CREATE TABLE `user_app_info` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` INT(10) NOT NULL COMMENT '用户ID',
  `client_id` VARCHAR(32) NOT NULL DEFAULT '' COMMENT 'clientid',
  `status` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `source` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '设备类型，3=IOS,4=ANDROID',
  `created_at` INT(10) UNSIGNED NOT NULL,
  `updated_at` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户app信息表';

/* 上午10:54:44 dev */ ALTER TABLE `user_app_info` ADD `new_pm` INT(1)  NULL  DEFAULT '0'  COMMENT '新推送'  AFTER `updated_at`;
