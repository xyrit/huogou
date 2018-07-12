CREATE TABLE `groups` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` VARCHAR(20) NOT NULL COMMENT '圈子名称',
  `intro` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '圈子介绍',
  `adminuser` VARCHAR(50) NOT NULL COMMENT '圈主',
  `user_count` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '成员数量',
  `topic_count` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '话题数量',
  `digest_count` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '精华数量',
  `notice` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '圈子公告',
  `picture` VARCHAR(255) NOT NULL DEFAULT 0 COMMENT '圈子封面',
  `group_closed` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否关闭圈子, 不允许成员加入',
  `topic_closed` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否允许发表话题',
  `comment_closed` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否关闭回复',
  `verify_status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '帖子和回复是否需要审核才能显示',
  `created_at` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`)
)ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='圈子表';