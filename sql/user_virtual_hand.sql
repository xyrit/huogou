CREATE TABLE `user_virtual_hand` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单号',
  `type` varchar(10) NOT NULL DEFAULT '' COMMENT '充值卡类型（tb  qb  dh）',
  `account` varchar(64) NOT NULL DEFAULT '' COMMENT '账号',
  `name` varchar(64) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态（0 初始化  1 审核通过  2 审核失败）',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `checked_at` int(11) NOT NULL DEFAULT '0' COMMENT '审核时间',
  `checked_admin` int(11) NOT NULL DEFAULT '0' COMMENT '审核人',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_order_id` (`order_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='虚拟物品手动发货表';
ALTER TABLE `user_virtual_hand` CHANGE `type` `type` VARCHAR(20)  CHARACTER SET utf8  NOT NULL  DEFAULT ''  COMMENT '充值卡类型（tb  qb  dh mobile_online）'