CREATE TABLE `mobile_card_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mobile` varchar(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `orderid` int(10) NOT NULL COMMENT '中奖订单号ID',
  `province` varchar(20) DEFAULT '' COMMENT '省份',
  `type` varchar(10) DEFAULT '' COMMENT '充值卡类型',
  `product_id` varchar(10) DEFAULT '' COMMENT '商品ID',
  `face_value` int(3) DEFAULT NULL COMMENT '面值',
  `message` varchar(50) NOT NULL DEFAULT '' COMMENT '返回的信息',
  `result` varchar(600) NOT NULL DEFAULT '' COMMENT '返回结果',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;