CREATE TABLE `order_manage_group_users` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `group_id` INT(10) NOT NULL COMMENT '小组ID',
  `user_id` INT(10) NOT NULL COMMENT '成员ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单处理小组成员表';