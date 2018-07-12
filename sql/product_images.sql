CREATE TABLE `product_images` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `product_id` INT(10) UNSIGNED NOT NULL COMMENT '商品ID',
  `basename` VARCHAR (255) NOT NULL COMMENT '带后缀的图片名',
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品相册表';