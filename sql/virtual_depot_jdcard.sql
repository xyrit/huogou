CREATE TABLE `virtual_depot_jdcard` (
   `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '京东卡密仓库',
   `cardno` varchar(50) NOT NULL DEFAULT '' COMMENT '卡号(加密)',
   `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
   `expirationtime` varchar(30) NOT NULL DEFAULT '' COMMENT '过期时间',
   `status` int(1) NOT NULL DEFAULT '0' COMMENT '卡号状态 0-未使用  1-已使用',
   `buyback` int(10) NOT NULL DEFAULT '0' COMMENT '回购次数',
   `backtime` int(10) NOT NULL DEFAULT '0' COMMENT '近期回购时间',
   `denomination` int(10) NOT NULL DEFAULT '0' COMMENT '面额',
   `card_type` varchar(20) NOT NULL DEFAULT '' COMMENT '类型 jd-京东',
   `cardpws` varchar(50) NOT NULL COMMENT '卡密(加密)',
   PRIMARY KEY (`id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8