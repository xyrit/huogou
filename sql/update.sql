-- 2015-12-10
ALTER TABLE `share_topic_images`
ADD COLUMN `mobile`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否添加手机图' AFTER `basename`;


-- 2015-12-17
ALTER TABLE `users`
ADD COLUMN `reg_ip`  bigint(10) NOT NULL DEFAULT 0 COMMENT '注册ip' AFTER `protected_status`,
ADD COLUMN `reg_terminal`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '注册终端(1 pc  2 微信 3 ios  4 Android)' AFTER `reg_ip`;


-- 2015-12-18
ALTER TABLE `share_topic_images`
ADD COLUMN `recommend`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否推荐图' AFTER `mobile`,
ADD COLUMN `roll`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否滚动图' AFTER `recommend`,
ADD COLUMN `main`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否主图' AFTER `roll`;

-- 2015-12-18
ALTER TABLE `users` ADD `spread_source` VARCHAR(20)  NULL  DEFAULT NULL  COMMENT '推广来源'  AFTER `reg_terminal`;

-- 2016-01-04
ALTER TABLE `user_profile`
MODIFY COLUMN `qq`  varchar(15) NOT NULL DEFAULT '' COMMENT 'QQ' AFTER `hometown`;

-- 2016-01-04
ALTER TABLE `keywords`
MODIFY COLUMN `type`  varchar(32) NOT NULL DEFAULT '' COMMENT '0话题，1回帖' AFTER `id`,
MODIFY COLUMN `content`  varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '关键字内容' AFTER `type`;

-- 2015-01-05  增加审核人、审核时间
ALTER TABLE `share_topics`
ADD COLUMN `admin_id`  int(10) NOT NULL DEFAULT 0 COMMENT '审核人' AFTER `roll_image`,
ADD COLUMN `checked_at`  int(10) NOT NULL DEFAULT 0 COMMENT '审核时间' AFTER `admin_id`;

-- 2016-01-11
ALTER TABLE `menu`
DROP INDEX `name` ,
ADD UNIQUE INDEX `name` (`name`, `parent_id`) USING BTREE ;

ALTER TABLE `adjust_balance`
ADD COLUMN `status`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态' AFTER `reason`;

ALTER TABLE `adjust_balance`
ADD COLUMN `note`  varchar(256) NOT NULL DEFAULT '' COMMENT '备注' AFTER `order`;
ALTER TABLE `adjust_balance`
MODIFY COLUMN `user_id`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员id' AFTER `id`,
MODIFY COLUMN `before_money`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '调整前金额' AFTER `type`,
MODIFY COLUMN `money`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '金额' AFTER `before_money`,
MODIFY COLUMN `final_money`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '调整后金额' AFTER `money`,
MODIFY COLUMN `reason`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '原因' AFTER `final_money`,
MODIFY COLUMN `order`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '原始单号' AFTER `status`,
MODIFY COLUMN `admin_id`  int(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '操作人' AFTER `note`,
MODIFY COLUMN `created_at`  int(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `admin_id`;

ALTER TABLE `adjust_balance`
ADD COLUMN `updated_at`  int(10) NOT NULL DEFAULT 0 COMMENT '审核时间' AFTER `created_at`,
ADD COLUMN `approve_admin_id`  int(10) NOT NULL DEFAULT 0 COMMENT '审核人' AFTER `updated_at`;
ALTER TABLE `adjust_balance`
ADD COLUMN `fail_reason`  varchar(255) NOT NULL DEFAULT '' COMMENT '审核失败原因' AFTER `approve_admin_id`;

ALTER TABLE `point_log`
ADD COLUMN `status`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态' AFTER `reason`;

ALTER TABLE `point_log`
ADD COLUMN `note`  varchar(256) NOT NULL DEFAULT '' COMMENT '备注' AFTER `order`;
ALTER TABLE `point_log`
MODIFY COLUMN `user_id`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员id' AFTER `id`,
MODIFY COLUMN `before_point`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '调整前金额' AFTER `type`,
MODIFY COLUMN `point`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '金额' AFTER `before_point`,
MODIFY COLUMN `final_point`  int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '调整后金额' AFTER `point`,
MODIFY COLUMN `reason`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '原因' AFTER `final_point`,
MODIFY COLUMN `order`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '原始单号' AFTER `status`,
MODIFY COLUMN `admin_id`  int(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '操作人' AFTER `note`,
MODIFY COLUMN `created_at`  int(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `admin_id`;

ALTER TABLE `point_log`
ADD COLUMN `updated_at`  int(10) NOT NULL DEFAULT 0 COMMENT '审核时间' AFTER `created_at`,
ADD COLUMN `approve_admin_id`  int(10) NOT NULL DEFAULT 0 COMMENT '审核人' AFTER `updated_at`;
ALTER TABLE `point_log`
ADD COLUMN `fail_reason`  varchar(255) NOT NULL DEFAULT '' COMMENT '审核失败原因' AFTER `approve_admin_id`;


-- 2016-01-14
CREATE TABLE `suppliers` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID' ,
`name`  varchar(64) NOT NULL DEFAULT '' COMMENT '供应商名称' ,
`contact`  varchar(32) NOT NULL DEFAULT '' COMMENT '联系人' ,
`contact_way`  varchar(16) NOT NULL DEFAULT '' COMMENT '联系方式' ,
`address`  varchar(64) NOT NULL DEFAULT '' COMMENT '详细地址' ,
`product_num`  int(10) NOT NULL DEFAULT 0 COMMENT '商品数量' ,
`created_at`  int(10) NOT NULL DEFAULT 0 COMMENT '创建时间' ,
`admin_id`  int(10) NOT NULL DEFAULT 0 COMMENT '创建人' ,
PRIMARY KEY (`id`),
UNIQUE INDEX `Unique` (`name`)
)ENGINE=InnoDB COMMENT='供应商表' DEFAULT CHARACTER SET=utf8;

CREATE TABLE `supplier_products` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID' ,
`supplier_id`  int(10) NOT NULL DEFAULT 0 COMMENT '供应商ID' ,
`product_id`  int(10) NOT NULL DEFAULT 0 COMMENT '商品ID' ,
PRIMARY KEY (`id`),
UNIQUE INDEX `Unique` (`supplier_id`, `product_id`)
)ENGINE=InnoDB COMMENT='供应商商品表' DEFAULT CHARACTER SET=utf8;

CREATE TABLE `purchase_orders` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID' ,
`money`  float(10,2) NOT NULL DEFAULT 0.00 COMMENT '订单金额' ,
`status`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '订单状态(0 提交待审 1 审核通过 2 审核不通过)' ,
`note`  varchar(255) NOT NULL DEFAULT '' COMMENT '备注' ,
`created_at`  int(10) NOT NULL DEFAULT 0 COMMENT '创建时间' ,
`updated_at`  int(10) NOT NULL DEFAULT 0 COMMENT '更新时间' ,
`admin_id`  int(10) NOT NULL DEFAULT 0 COMMENT '创建人' ,
`approved_at`  int(10) NOT NULL DEFAULT 0 COMMENT '审核时间' ,
`approved_admin_id`  int(10) NOT NULL DEFAULT 0 COMMENT '审核人' ,
`stored_at`  int(10) NOT NULL DEFAULT 0 COMMENT '入库时间' ,
`stored_admin_id`  int(10) NOT NULL DEFAULT 0 COMMENT '入库人' ,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARACTER SET=utf8 COMMENT='采购订单表';

CREATE TABLE `purchase_order_items` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID' ,
`purchase_order_id`  int(10) NOT NULL DEFAULT 0 COMMENT '采购订单ID' ,
`product_id`  int(10) NOT NULL DEFAULT 0 COMMENT '商品ID' ,
`product_num`  int(10) NOT NULL DEFAULT 0 COMMENT '商品数量' ,
PRIMARY KEY (`id`),
UNIQUE INDEX `Unique` (`purchase_order_id`, `product_id`)
)ENGINE=InnoDB COMMENT='采购订单商品表' DEFAULT CHARACTER SET=utf8;

CREATE TABLE `product_store_logs` (
`id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID' ,
`product_id`  int(10) NOT NULL DEFAULT 0 COMMENT '商品ID' ,
`type`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '库存变动类型(1 入库  2 出库  3 修改库存)' ,
`num`  int(10) NOT NULL DEFAULT 0 COMMENT '库存变动数量' ,
`final_store`  int(10) NOT NULL DEFAULT 0 COMMENT '剩余库存' ,
`reason`  varchar(255) NOT NULL DEFAULT '' COMMENT '变动原因' ,
`created_at`  int(10) NOT NULL DEFAULT 0 COMMENT '变动时间' ,
`admin_id`  int(10) NOT NULL DEFAULT 0 COMMENT '操作人' ,
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARACTER SET=utf8 COMMENT='商品库存变动记录表';

-- 2016-01-15
ALTER TABLE `supplier_products`
ADD COLUMN `price`  float(10,2) NOT NULL DEFAULT 0.00 COMMENT '采购价' AFTER `product_id`;

ALTER TABLE `purchase_order_items`
ADD COLUMN `supplier_id`  int(10) NOT NULL DEFAULT 0 COMMENT '供应商ID' AFTER `purchase_order_id`,
ADD COLUMN `privilege`  float(10,2) NOT NULL DEFAULT 0.00 COMMENT '优惠金额' AFTER `product_num`;

ALTER TABLE `purchase_order_items`
ADD COLUMN `supplier_price`  float(10,2) NOT NULL DEFAULT 0.00 COMMENT '采购价格' AFTER `product_num`;

-- 2016-01-18
ALTER TABLE `products`
MODIFY COLUMN `delivery_id`  varchar(32) NOT NULL DEFAULT '' COMMENT '发货方式ID' AFTER `updated_at`;

ALTER TABLE `backstage_log_100`
MODIFY COLUMN `module`  int(10) NOT NULL DEFAULT 0 COMMENT '操作模块' AFTER `admin_id`;
ALTER TABLE `backstage_log_101`
MODIFY COLUMN `module`  int(10) NOT NULL DEFAULT 0 COMMENT '操作模块' AFTER `admin_id`;
ALTER TABLE `backstage_log_102`
MODIFY COLUMN `module`  int(10) NOT NULL DEFAULT 0 COMMENT '操作模块' AFTER `admin_id`;
ALTER TABLE `backstage_log_103`
MODIFY COLUMN `module`  int(10) NOT NULL DEFAULT 0 COMMENT '操作模块' AFTER `admin_id`;
ALTER TABLE `backstage_log_104`
MODIFY COLUMN `module`  int(10) NOT NULL DEFAULT 0 COMMENT '操作模块' AFTER `admin_id`;
ALTER TABLE `backstage_log_105`
MODIFY COLUMN `module`  int(10) NOT NULL DEFAULT 0 COMMENT '操作模块' AFTER `admin_id`;
ALTER TABLE `backstage_log_106`
MODIFY COLUMN `module`  int(10) NOT NULL DEFAULT 0 COMMENT '操作模块' AFTER `admin_id`;
ALTER TABLE `backstage_log_107`
MODIFY COLUMN `module`  int(10) NOT NULL DEFAULT 0 COMMENT '操作模块' AFTER `admin_id`;
ALTER TABLE `backstage_log_108`
MODIFY COLUMN `module`  int(10) NOT NULL DEFAULT 0 COMMENT '操作模块' AFTER `admin_id`;
ALTER TABLE `backstage_log_109`
MODIFY COLUMN `module`  int(10) NOT NULL DEFAULT 0 COMMENT '操作模块' AFTER `admin_id`;


ALTER TABLE `orders`
ADD COLUMN `delay`  int(11) NULL DEFAULT 0 AFTER `push_msg`;

ALTER TABLE `orders`
ADD COLUMN `before_status`  tinyint(2) NULL DEFAULT 0 AFTER `delay`;

ALTER TABLE `exchange_orders`
MODIFY COLUMN `order_no`  int(11) NOT NULL DEFAULT 0 COMMENT '原订单号' AFTER `id`;


ALTER TABLE `exchange_orders`
MODIFY COLUMN `user_id`  int(11) NULL DEFAULT 0 COMMENT '用户id' AFTER `created_time`,
ADD COLUMN `platform`  varchar(50) NULL AFTER `confirm_goods_time`,
ADD COLUMN `third_order`  varchar(100) NULL AFTER `platform`,
ADD COLUMN `price`  float(10,2) NULL AFTER `third_order`,
ADD COLUMN `standard`  varchar(255) NULL AFTER `price`,
ADD COLUMN `mark_text`  varchar(255) NULL AFTER `standard`,
ADD COLUMN `bill_time`  int(11) NULL DEFAULT 0 AFTER `mark_text`,
ADD COLUMN `bill`  varchar(50) NULL AFTER `bill_time`,
ADD COLUMN `prepare_time`  int(11) NULL DEFAULT 0 AFTER `bill`,
ADD COLUMN `bill_order`  varchar(255) NULL AFTER `prepare_time`,
ADD COLUMN `prepare_userid`  int(11) NULL DEFAULT 0 AFTER `bill_order`,
ADD COLUMN `send`  varchar(100) NULL AFTER `prepare_userid`,
ADD COLUMN `payment`  varchar(100) NULL AFTER `send`,
ADD COLUMN `deliver_cost`  varchar(50) NULL AFTER `payment`,
ADD COLUMN `confirm_time`  int(11) NULL DEFAULT 0 AFTER `deliver_cost`,
ADD COLUMN `confirm_userid`  int(11) NULL DEFAULT 0 AFTER `confirm_time`,
ADD COLUMN `status`  tinyint(2) NULL DEFAULT 2 AFTER `confirm_userid`;

ALTER TABLE `deliver`
ADD COLUMN `send`  VARCHAR(100) NULL DEFAULT '' AFTER `payment`,
ADD COLUMN `bill_order` VARCHAR(100) NULL DEFAULT '' AFTER `send`,
ADD COLUMN `deliver_cost` VARCHAR(50) NULL DEFAULT '' AFTER `bill_order`;

ALTER TABLE `admin`
ADD COLUMN `privilege`  varchar(1024) NOT NULL DEFAULT '' COMMENT '权限' AFTER `role`;

ALTER TABLE `products`
ADD COLUMN `total`  int(10) NOT NULL DEFAULT 0 COMMENT '库存' AFTER `brand_id`;


-- 2016-01-26
ALTER TABLE `user_app_info`
MODIFY COLUMN `client_id`  varchar(32) NULL COMMENT 'clientid';

-- 2016-01-28
ALTER TABLE `products`
 ADD COLUMN `buy_unit` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '购买单位' AFTER `limit_num`;
ALTER TABLE `periods`
 ADD COLUMN `buy_unit` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '购买单位' AFTER `limit_num`;
ALTER TABLE `current_periods`
 ADD COLUMN `buy_unit` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '购买单位' AFTER `limit_num`;


-- 2016-02-17
ALTER TABLE `payment_order_items_100`
ADD COLUMN `user_id` INT(10) UNSIGNED NOT NULL  DEFAULT '0' COMMENT '用户ID' AFTER `period_number`;
ALTER TABLE `payment_order_items_101`
ADD COLUMN `user_id` INT(10) UNSIGNED NOT NULL  DEFAULT '0' COMMENT '用户ID' AFTER `period_number`;
ALTER TABLE `payment_order_items_102`
ADD COLUMN `user_id` INT(10) UNSIGNED NOT NULL  DEFAULT '0' COMMENT '用户ID' AFTER `period_number`;
ALTER TABLE `payment_order_items_103`
ADD COLUMN `user_id` INT(10) UNSIGNED NOT NULL  DEFAULT '0' COMMENT '用户ID' AFTER `period_number`;
ALTER TABLE `payment_order_items_104`
ADD COLUMN `user_id` INT(10) UNSIGNED NOT NULL  DEFAULT '0' COMMENT '用户ID' AFTER `period_number`;
ALTER TABLE `payment_order_items_105`
ADD COLUMN `user_id` INT(10) UNSIGNED NOT NULL  DEFAULT '0' COMMENT '用户ID' AFTER `period_number`;
ALTER TABLE `payment_order_items_106`
ADD COLUMN `user_id` INT(10) UNSIGNED NOT NULL  DEFAULT '0' COMMENT '用户ID' AFTER `period_number`;
ALTER TABLE `payment_order_items_107`
ADD COLUMN `user_id` INT(10) UNSIGNED NOT NULL  DEFAULT '0' COMMENT '用户ID' AFTER `period_number`;
ALTER TABLE `payment_order_items_108`
ADD COLUMN `user_id` INT(10) UNSIGNED NOT NULL  DEFAULT '0' COMMENT '用户ID' AFTER `period_number`;
ALTER TABLE `payment_order_items_109`
ADD COLUMN `user_id` INT(10) UNSIGNED NOT NULL  DEFAULT '0' COMMENT '用户ID' AFTER `period_number`;
-- 2016-02-17
ALTER TABLE `payment_order_items_100`
ADD COLUMN `source` TINYINT(1) DEFAULT '0' COMMENT '平台来源,pc,触屏版,微信客户端,ios客户端,android客户端' AFTER `item_buy_time`;
ALTER TABLE `payment_order_items_101`
ADD COLUMN `source` TINYINT(1) DEFAULT '0' COMMENT '平台来源,pc,触屏版,微信客户端,ios客户端,android客户端' AFTER `item_buy_time`;
ALTER TABLE `payment_order_items_102`
ADD COLUMN `source` TINYINT(1) DEFAULT '0' COMMENT '平台来源,pc,触屏版,微信客户端,ios客户端,android客户端' AFTER `item_buy_time`;
ALTER TABLE `payment_order_items_103`
ADD COLUMN `source` TINYINT(1) DEFAULT '0' COMMENT '平台来源,pc,触屏版,微信客户端,ios客户端,android客户端' AFTER `item_buy_time`;
ALTER TABLE `payment_order_items_104`
ADD COLUMN `source` TINYINT(1) DEFAULT '0' COMMENT '平台来源,pc,触屏版,微信客户端,ios客户端,android客户端' AFTER `item_buy_time`;
ALTER TABLE `payment_order_items_105`
ADD COLUMN `source` TINYINT(1) DEFAULT '0' COMMENT '平台来源,pc,触屏版,微信客户端,ios客户端,android客户端' AFTER `item_buy_time`;
ALTER TABLE `payment_order_items_106`
ADD COLUMN `source` TINYINT(1) DEFAULT '0' COMMENT '平台来源,pc,触屏版,微信客户端,ios客户端,android客户端' AFTER `item_buy_time`;
ALTER TABLE `payment_order_items_107`
ADD COLUMN `source` TINYINT(1) DEFAULT '0' COMMENT '平台来源,pc,触屏版,微信客户端,ios客户端,android客户端' AFTER `item_buy_time`;
ALTER TABLE `payment_order_items_108`
ADD COLUMN `source` TINYINT(1) DEFAULT '0' COMMENT '平台来源,pc,触屏版,微信客户端,ios客户端,android客户端' AFTER `item_buy_time`;
ALTER TABLE `payment_order_items_109`
ADD COLUMN `source` TINYINT(1) DEFAULT '0' COMMENT '平台来源,pc,触屏版,微信客户端,ios客户端,android客户端' AFTER `item_buy_time`;
-- 2016-02-19
ALTER TABLE `periods` ADD INDEX idx_end_time ( `end_time` );
-- 2016-02-20
ALTER TABLE `user_app_info` ADD INDEX idx_uid ( `uid` );
-- 2016-02-24
ALTER TABLE `notice_messages`
ADD COLUMN `ip` bigint(10) DEFAULT '0' COMMENT 'ip' AFTER `message`;


-- 2016-03-02
ALTER TABLE `share_topic_images`
ADD COLUMN `order`  int(10) NOT NULL DEFAULT 0 COMMENT '排序' AFTER `main`,
ADD COLUMN `is_show`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否显示(1 显示 0 隐藏)' AFTER `order`;

-- 2016-03-07
ALTER TABLE `user_signs`
ADD COLUMN `sum_continues`  int(10) NOT NULL DEFAULT 0 COMMENT '累计连续签到天数' AFTER `continue`,
ADD COLUMN `max_continues`  int(10) NOT NULL DEFAULT 0 COMMENT '最大连续签到天数' AFTER `sum_continues`;

-- 2016-03-14
alter table virtual_purchase_order add index `idx_purchase_id` (`purchaseid`);

-- 2016-04-01
ALTER TABLE `share_topics`
ADD COLUMN `is_show`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否在前端显示（1 是  0 否）' AFTER `checked_at`;

-- 2016-04-08
ALTER TABLE `user_app_info`
ADD COLUMN `new_order_tip`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否有新获得商品通知' AFTER `new_pm`;
ALTER TABLE `user_app_info`
ADD COLUMN `new_act_order_tip`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否有新活动中奖通知' AFTER `new_order_tip`;

-- 2016-04-14
ALTER TABLE `share_comments`
ADD COLUMN `status`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '审核状态' AFTER `ip`;
ALTER TABLE `share_replys`
ADD COLUMN `status`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '审核状态' AFTER `ip`;

-- 2016-04-22
ALTER TABLE `products`
ADD COLUMN `face_value`  int(10) NOT NULL DEFAULT 0 COMMENT '面值' AFTER `price`;

-- 2016-05-19
ALTER TABLE `periods`
ADD COLUMN `result_time` char(16) NOT NULL COMMENT '出中奖结果时间' AFTER `price`;
ALTER TABLE `lottery_compute`
ADD COLUMN `expect` CHAR(12) DEFAULT NULL COMMENT '时时彩期数';
ALTER TABLE `lottery_compute`
ADD COLUMN `shishi_num`  CHAR(8) DEFAULT NULL COMMENT '时时彩开奖号码';

-- 2016-05-21
ALTER TABLE `user_virtual_address`
CHANGE  `type` `type` varchar(10) NOT NULL COMMENT '充值类型（支付宝:tb QQ:qb 话费:dh）';
ALTER TABLE `user_virtual_address`
CHANGE  `contact` `name` varchar(32) DEFAULT NULL COMMENT '姓名';

ALTER TABLE `virtual_product_info`
CHANGE  `type` `type` varchar(10) NOT NULL COMMENT '充值类型（支付宝:tb QQ:qb 话费:dh）';
ALTER TABLE `virtual_product_info`
CHANGE  `contact` `name` varchar(32) DEFAULT NULL COMMENT '姓名';
ALTER TABLE `virtual_product_info` DROP `note`;



-- 2016-06-06
ALTER TABLE `banners`
ADD COLUMN `from` tinyint(1) NOT NULL DEFAULT 1 COMMENT '站点来源,1=伙购,2=滴滴夺宝';
ALTER TABLE `actives`
ADD COLUMN `from` tinyint(1) NOT NULL DEFAULT 1 COMMENT '站点来源,1=伙购,2=滴滴夺宝';
ALTER TABLE `app_config`
ADD COLUMN `from` tinyint(1) NOT NULL DEFAULT 1 COMMENT '站点来源,1=伙购,2=滴滴夺宝';

ALTER TABLE `users`
ADD UNIQUE `phone_from` (`phone`,`from`);
ALTER TABLE `users`
ADD UNIQUE `email_from` (`email`,`from`);

ALTER TABLE `share_topics`
ADD COLUMN `from` tinyint(1) NOT NULL DEFAULT 1 COMMENT '站点来源,1=伙购,2=滴滴夺宝';
ALTER TABLE `honour_desc`
ADD COLUMN `from` tinyint(1) NOT NULL DEFAULT 1 COMMENT '站点来源,1=伙购,2=滴滴夺宝';

ALTER TABLE `products`
ADD COLUMN `display` tinyint(1) NOT NULL DEFAULT 0 COMMENT '显示地址,0=全部,1=伙购,2=滴滴夺宝';

ALTER TABLE `periods`
ADD COLUMN `period_no` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '期号';
ALTER TABLE `current_periods`
ADD COLUMN `period_no` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '期号';

--2016年7月5日 14:50:46
ALTER TABLE `wx_orders` ADD partner_trade_no VARCHAR(30) NOT NULL DEFAULT '' COMMENT '中奖单号';

--2016年7月29日 09:42:59
ALTER TABLE `jdcard_buyback_list` ADD period_id int(10) NOT NULL DEFAULT '0' COMMENT '期数id';
ALTER TABLE `jdcard_buyback_list` ADD order_type int(1) NOT NULL DEFAULT '0' COMMENT '活动id 无活动则为0';
ALTER TABLE `jdcard_buyback_list` ADD rate int(3) NOT NULL DEFAULT '0' COMMENT '折扣';