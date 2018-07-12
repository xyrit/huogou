CREATE TABLE `point_log` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '会员id',
  `type` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0加，1减',
  `before_point` INT(10) UNSIGNED NOT NULL COMMENT '调整前金额',
  `point` INT(10) UNSIGNED NOT NULL COMMENT '金额',
  `final_point` INT(10) UNSIGNED NOT NULL COMMENT '调整后金额',
  `reason` VARCHAR(255) NOT NULL COMMENT '原因',
  `order` VARCHAR(255) NOT NULL COMMENT '原始单号',
  `admin_id` INT(5) UNSIGNED NOT NULL COMMENT '操作人',
  `created_at` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_admin_id` (`admin_id`, `created_at`)
)ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='福分调整';