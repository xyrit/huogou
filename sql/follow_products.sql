CREATE TABLE `follow_products` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` INT(10) UNSIGNED NOT NULL COMMENT '会员ID',
  `product_id` INT(10) UNSIGNED NOT NULL COMMENT '商品ID',
  `follow_time` CHAR(16)  NOT NULL COMMENT '关注时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_id_product_id` (`user_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '关注商品表';