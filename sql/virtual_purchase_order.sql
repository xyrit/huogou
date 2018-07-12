CREATE TABLE `virtual_purchase_order` (
  `orderid` varchar(30) NOT NULL DEFAULT '' COMMENT '采购订单id',
  `vid` int(5) NOT NULL COMMENT '虚拟商品id',
  `par_value` int(5) NOT NULL COMMENT '面值',
  `status` int(11) DEFAULT '0',
  `nums` int(11) NOT NULL,
  `create_time` int(10) NOT NULL COMMENT '购买时间',
  `update_time` int(10) NOT NULL COMMENT '更新时间',
  `exchange_no` varchar(30) DEFAULT NULL COMMENT '购买id',
  `result` text,
  PRIMARY KEY (`orderid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;