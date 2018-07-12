CREATE TABLE `group_users` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `group_id` INT(10) UNSIGNED NOT NULL COMMENT '圈子ID',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `created_at` INT(10) UNSIGNED NOT NULL COMMENT '加入时间',
  PRIMARY KEY (`id`),
  KEY `idx_group_id` (`group_id`,`user_id`)
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='圈子成员表';