CREATE TABLE `actives` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `title` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '标题',
  `sub_title` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '小标题',
  `flag` TINYINT(1) UNSIGNED DEFAULT 0 COMMENT '0无标签 1New 2Hot',
  `icon` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '图片',
  `url` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '关联地址',
  `status` TINYINT(1) UNSIGNED DEFAULT 0 COMMENT '0禁用 1启用',
  `type` TINYINT(1) UNSIGNED DEFAULT 0 COMMENT '1 h5 2 原生',
  `created_at` INT(10) UNSIGNED COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '活动表';