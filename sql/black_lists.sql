CREATE TABLE `black_lists` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
`type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '黑名单类型（1 晒单评论）',
`user_id` int(11) NOT NULL DEFAULT 0 COMMENT '用户ID',
`created_at` int(11) NOT NULL DEFAULT 0,
PRIMARY KEY (`id`),
UNIQUE KEY `user_id_type` (`user_id`, `type`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='黑名单表';