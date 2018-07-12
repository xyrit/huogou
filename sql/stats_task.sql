CREATE TABLE stats_task (
  id int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  date int(8) unsigned NOT NULL DEFAULT '0' COMMENT '日期',
  type tinyint(1) NOT NULL DEFAULT '0' COMMENT '任务类型（1 签到 2 新手 3 日常 4 成长）',
  level tinyint(1) NOT NULL DEFAULT '0' COMMENT '成长任务类型（1 称号 2 充值 3 等级 ）',
  cate tinyint(1) NOT NULL DEFAULT '0' COMMENT '称号类型（1 登荣誉榜  2 土豪君 3 沙发君 4 收尾军）',
  num int(10) NOT NULL DEFAULT '0' COMMENT '成长任务需要完成的数量',
  count int(10) NOT NULL DEFAULT '0' COMMENT '总数',
  PRIMARY KEY (id),
  KEY idx_date (date, type, level, cate, num)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员任务统计表';