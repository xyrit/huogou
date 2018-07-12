CREATE TABLE `user_limits` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
  `private_letter` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '私信',
  `position` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '地理位置',
  `friend_search` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '好友搜索',
  `ucenter_buylist` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '个人主页伙购记录',
  `buylist_number` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '个人主页伙购记录所有人可见条数',
  `ucenter_orderlist` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '个人主页获得的商品',
  `orderlist_number` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '个人主页获得的商品所有人可见条数',
  `ucenter_sharelist` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '个人主页晒单',
  `sharelist_number` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '个人主页晒单所有人可见条数',
  `receive_sysinfo` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '接受系统消息',
  `receive_wchat` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '接受微信或邮件信息',
  `created_at` INT(10) UNSIGNED NOT NULL,
  `updated_at` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='隐私设置表';