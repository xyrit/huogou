CREATE TABLE `cards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `batch_id` int(11) unsigned NOT NULL COMMENT '批次id',
  `card` varchar(50) NOT NULL COMMENT '充值卡号',
  `pwd` varchar(255) NOT NULL COMMENT '充值卡密码',
  `price` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '金额',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0=未使用,1=已使用,3已失效',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '使用的用户ID',
  `used_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用时间',
  PRIMARY KEY (`id`),
  KEY `idx_batch_id` (`batch_id`),
  KEY `idx_card` (`card`),
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='充值卡';
