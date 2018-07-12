CREATE TABLE `carts` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '用户ID',
  `product_id` BIGINT(20) UNSIGNED NOT NULL COMMENT '商品ID',
  `period_number` INT(10) UNSIGNED NOT NULL COMMENT '商品第几期',
  `period_id` BIGINT(20) UNSIGNED NOT NULL COMMENT '期数ID',
  `is_buy` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否购买',
  `nums` BIGINT(20) UNSIGNED NOT NULL COMMENT '购码数量',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_id_product_id` (`user_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '购物车';
ALTER TABLE `carts` ADD `limit_nums` INT(10)  NULL  DEFAULT '0'  COMMENT '限购数量' AFTER `nums`;
ALTER TABLE `carts` ADD `buy_unit` INT(5)  NULL  DEFAULT '1'  COMMENT '购买单位' AFTER `limit_nums`;