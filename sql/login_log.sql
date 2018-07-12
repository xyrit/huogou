CREATE TABLE `login_log` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '会员id',
  `type` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0pc，1微信，2wap，3安卓，4ios',
  `action` TINYINT(1)  NOT NULL DEFAULT 0 COMMENT '0登录，1登出',
  `ip` bigint(10) DEFAULT '0' COMMENT 'ip',
  `created_at` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `type` (`type`),
  UNIQUE KEY `idx_admin_id` (`user_id`, `created_at`)
)ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='用户登录日志';