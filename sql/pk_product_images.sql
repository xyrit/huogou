CREATE TABLE `pk_product_images` (
   `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
   `product_id` int(10) unsigned NOT NULL COMMENT '活动商品ID',
   `basename` varchar(255) NOT NULL COMMENT '带后缀的图片名',
   PRIMARY KEY (`id`),
   KEY `idx_product_id` (`product_id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动商品相册表'