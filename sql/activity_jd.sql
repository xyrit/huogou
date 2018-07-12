CREATE TABLE `activity_jd` (
   `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '京东E卡送红包',
   `money` int(10) NOT NULL DEFAULT '0' COMMENT '红包金额',
   `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户id',
   `up_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
   `pkperiod_id` int(10) NOT NULL DEFAULT '0' COMMENT 'pk最后的期数',
   `period_id` int(10) NOT NULL DEFAULT '0' COMMENT '最后的期数',
   PRIMARY KEY (`id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8