CREATE TABLE `purchase` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL COMMENT '提交人id',
  `type` int(1) NOT NULL COMMENT '类型，1-实物，2-虚拟卡(接口购买)',
  `product_id` int(10) DEFAULT '0' COMMENT '商品id',
  `product_name` varchar(255) NOT NULL DEFAULT '' COMMENT '商品名称',
  `nums` int(10) NOT NULL COMMENT '购买数量',
  `per_money` float(6,2) NOT NULL COMMENT '单价',
  `total` float(10,2) NOT NULL COMMENT '总价格',
  `status` int(2) NOT NULL DEFAULT '0' COMMENT '状态，0-未确认，1-未付款，2-未收货，3-完成,-1-驳回',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `last_update_time` int(10) NOT NULL COMMENT '最后更新时间',
  `schedule` text COMMENT '进度',
  `extra` text COMMENT '额外内容',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;