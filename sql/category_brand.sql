CREATE TABLE `category_brand` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `cat_id` INT(10) UNSIGNED NOT NULL COMMENT '商品分类ID',
  `brand_id` INT(10) UNSIGNED NOT NULL COMMENT '品牌ID',
  `brand_order` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
  `product_num` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品数量',
  PRIMARY KEY (`id`),
  UNIQUE KEY (`cat_id`, `brand_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品分类品牌关联表';