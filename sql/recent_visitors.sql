CREATE TABLE `recent_visitors` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '访客ID',
  `visited_user_id` INT(10) UNSIGNED NOT NULL COMMENT '被访问的用户ID',
  `created_at` INT(10) UNSIGNED NOT NULL COMMENT '访问时间',
  PRIMARY KEY (`id`),
  KEY `idx_visited_user_id` (`visited_user_id`)
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='最近访客表';