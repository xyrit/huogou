CREATE TABLE `euro_cup_orders` (
  `id` INT(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `status` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '订单状态,0未支付，1已支付,2支付失败',
  `game_date` CHAR(8) NOT NULL COMMENT '赛事日期',
  `team` VARCHAR (25) NOT NULL COMMENT '竞猜球队',
  `money` INT(10) NOT NULL COMMENT '竞猜金额',
  `payment_order_id` CHAR (25) NOT NULL COMMENT '支付订单ID',
  `created_at` INT(10) UNSIGNED NOT NULL COMMENT '创建时间',
  `pay_at` INT(10) UNSIGNED NOT NULL COMMENT '支付时间',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '欧洲杯竞猜订单';