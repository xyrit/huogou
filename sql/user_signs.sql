CREATE TABLE `user_signs` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`user_id`  int(10) NOT NULL DEFAULT 0 COMMENT '用户ID' ,
`signed_at`  int(10) NOT NULL DEFAULT 0 COMMENT '签到日期' ,
`continue`  int(10) NOT NULL DEFAULT 0 COMMENT '连续签到天数' ,
`total`  int(10) NOT NULL DEFAULT 0 COMMENT '累计签到天数' ,
PRIMARY KEY (`id`),
UNIQUE INDEX `unique_user_id` (`user_id`)
)ENGINE=InnoDB DEFAULT CHARACTER SET=utf8 COMMENT='用户签到表';