CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `home_id` int(10) unsigned NOT NULL COMMENT '用户主页ID',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `email` varchar(255) DEFAULT NULL COMMENT '邮箱',
  `phone` char(11) DEFAULT NULL COMMENT '手机',
  `nickname` varchar(60) DEFAULT NULL COMMENT '昵称',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像，图片ID',
  `money` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '余额',
  `commission` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '佣金',
  `point` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '积分',
  `experience` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '经验值',
  `pay_password` varchar(255) DEFAULT NULL COMMENT '支付密码',
  `password_reset_token` varchar(255) DEFAULT NULL COMMENT '重置密码token',
  `token` varchar(255) DEFAULT NULL COMMENT '身份token',
  `status` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '用户状态',
  `created_at` int(10) unsigned NOT NULL,
  `updated_at` int(10) unsigned NOT NULL,
  `micro_pay` int(5) DEFAULT '0' COMMENT '小额免支付额度',
  `last_login_ip` bigint(10) DEFAULT '0' COMMENT '最后一次登录ip',
  `protected_status` tinyint(1) DEFAULT '0' COMMENT '登录保护状态 0 未开启  1开启',
  `reg_ip` bigint(10) DEFAULT '0' COMMENT '注册ip',
  `reg_terminal` tinyint(1) NOT NULL DEFAULT '0' COMMENT '注册终端(1 pc  2 微信 3 ios  4 Android)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `home_id` (`home_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `token` (`token`),
  UNIQUE KEY `nickname` (`nickname`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=10000 DEFAULT CHARSET=utf8 COMMENT='用户表';

ALTER TABLE `users` ADD `new_pm` INT(5)  NULL  DEFAULT '0'  COMMENT '新消息数量'  AFTER `spread_source`;
ALTER TABLE `users` ADD `from` TINYINT(1)  NULL  DEFAULT '1'  COMMENT '注册来源 1-伙购 2-滴滴'  AFTER `new_pm`;