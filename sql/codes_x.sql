CREATE TABLE `codes_x` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '伙购码ID',
  `code` INT(8) unsigned NOT NULL DEFAULT '0' COMMENT '伙购码',
  `product_id` INT(10) unsigned NOT NULL COMMENT '商品ID',
  `status` TINYINT(1) unsigned NOT NULL DEFAULT '0' COMMENT '伙购码状态，0=未购买,1=已购买',
  PRIMARY KEY (`id`),
  UNIQUE KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '伙购码表';