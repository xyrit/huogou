CREATE TABLE `deliver` (
  `id` char(25) NOT NULL COMMENT '中奖订单号',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单状态',
  `confirm_userid` int(10) NOT NULL COMMENT '确认人',
  `confirm_time` int(11) unsigned NOT NULL COMMENT '确认时间',
  `platform` varchar(100) NOT NULL DEFAULT '0' COMMENT '第三方发货平台',
  `third_order` varchar(255) NOT NULL DEFAULT '' COMMENT '第三方订单号',
  `price` int(10) NOT NULL DEFAULT '0' COMMENT '成本',
  `standard` varchar(255) NOT NULL DEFAULT ''  COMMENT '规格',
  `mark_text` text COMMENT '订单备注',
  `deliver_company` varchar(100) NOT NULL DEFAULT '0' COMMENT '快递公司',
  `deliver_order` varchar(255) NOT NULL DEFAULT '0'  COMMENT '快递订单号',
  `prepare_userid` int(10) NOT NULL DEFAULT '0'  COMMENT '备货人',
  `prepare_time` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '备货时间',
  `deliver_userid` int(10) NOT NULL DEFAULT '0'  COMMENT '发货人',
  `deliver_time` int(10) unsigned NOT NULL DEFAULT '0'  COMMENT '发货时间',
  `is_exchange` tinyint(1) unsigned DEFAULT '0' COMMENT '换货否',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='发货明细表';