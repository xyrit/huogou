CREATE TABLE `user_tasks` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
`user_id`  int(10) NOT NULL DEFAULT 0 COMMENT '用户ID' ,
`task_id`  int(10) NOT NULL DEFAULT 0 COMMENT '任务ID' ,
`status`  tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '任务状态（0 未完成 1 已完成 2 已领取）' ,
`progress`  int(10) NOT NULL DEFAULT 0 COMMENT '任务进度' ,
`complete_time`  int(10) NOT NULL DEFAULT 0 COMMENT '领取时间' ,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARACTER SET=utf8 COMMENT='用户任务表';