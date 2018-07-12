CREATE TABLE `activity_jd_log` (
   `id` int(10) NOT NULL AUTO_INCREMENT,
   `red_id` int(10) NOT NULL DEFAULT '0' COMMENT '红包Id',
   `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '领取人Id',
   `remain` int(10) NOT NULL DEFAULT '0' COMMENT '消耗金额',
   `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '添加时间',
   `old_money` int(10) NOT NULL DEFAULT '0' COMMENT '之前的金额',
   PRIMARY KEY (`id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8