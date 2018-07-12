CREATE TABLE user_task_follow_x (
  id int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  user_id int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  content varchar(100) NOT NULL DEFAULT '' COMMENT '任务内容',
  source tinyint(1) NOT NULL DEFAULT '0' COMMENT '终端来源',
  type tinyint(1) NOT NULL DEFAULT '0' COMMENT '任务类型（1 签到 2 新手 3 日常 4 成长）',
  level tinyint(1) NOT NULL DEFAULT '0' COMMENT '成长任务类型（1 称号 2 充值 3 等级 ）',
  created_at int(10) NOT NULL DEFAULT '0' COMMENT '时间',
  PRIMARY KEY (id),
  KEY idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员任务流水表';