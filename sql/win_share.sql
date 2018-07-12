CREATE TABLE `win_share` (
   `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '中奖分享',
   `user_id` int(10) NOT NULL COMMENT '用户id',
   `share` text COMMENT '分享内容',
   `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否分享',
   `red_id` int(10) NOT NULL DEFAULT '0' COMMENT '红包id',
   `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '新增时间',
   PRIMARY KEY (`id`,`user_id`),
   UNIQUE KEY `id` (`id`)
 ) ENGINE=InnoDB  DEFAULT CHARSET=utf8