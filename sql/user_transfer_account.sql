CREATE TABLE `user_transfer_account` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '用户id',
  `to_userid` INT(10) UNSIGNED NOT NULL COMMENT 'to id',
  `account` INT(10) UNSIGNED NOT NULL COMMENT '金额',
  `comment` VARCHAR(255) DEFAULT '' COMMENT '备注',
  `created_at` INT(10) UNSIGNED NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
)ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='转账表';
