CREATE TABLE `oauth` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '会员ID',
  `source` TINYINT(1) UNSIGNED NOT NULL COMMENT '提供商标识ID',
  `source_id` VARCHAR(255) NOT NULL COMMENT '会员在提供商的唯一ID',
  `unionid` VARCHAR(255) NOT NULL COMMENT '会员在提供商多个应用下的唯一ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_source_id_source` (`source_id`, `source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='第三方验证登录表';