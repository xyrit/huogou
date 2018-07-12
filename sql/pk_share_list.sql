CREATE TABLE `pk_share_list` (
   `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '用户pk分享表',
   `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户id',
   `headimg` varchar(50) NOT NULL DEFAULT '' COMMENT '用户头像',
   `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
   `size` int(1) NOT NULL DEFAULT '1' COMMENT '大小  1大 2小',
   `product_id` int(10) NOT NULL DEFAULT '0' COMMENT '商品id',
   `product_img` varchar(50) NOT NULL DEFAULT '' COMMENT '商品图片',
   `product_name` varchar(50) NOT NULL DEFAULT '' COMMENT '商品名称',
   `product_price` int(10) NOT NULL DEFAULT '0' COMMENT '价格',
   `status` int(1) NOT NULL DEFAULT '0' COMMENT '是否分享成功',
   PRIMARY KEY (`id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8