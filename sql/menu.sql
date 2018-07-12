/*
Navicat MySQL Data Transfer

Source Server         : 10.0.10.212
Source Server Version : 50709
Source Host           : 10.0.10.212:3306
Source Database       : huogou

Target Server Type    : MYSQL
Target Server Version : 50709
File Encoding         : 65001

Date: 2016-01-27 11:33:58
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for menu
-- ----------------------------
DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL COMMENT '父类别ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '菜单名',
  `route` varchar(50) NOT NULL DEFAULT '' COMMENT '路由id',
  `pass` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否需要验证',
  `show` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `updated_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`parent_id`) USING BTREE,
  KEY `route` (`route`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=utf8 COMMENT='导航菜单表';

-- ----------------------------
-- Records of menu
-- ----------------------------
INSERT INTO `menu` VALUES ('5', '0', '系统管理', '', '1', '1', '1', '0', '1450947817');
INSERT INTO `menu` VALUES ('6', '5', '账号管理', 'employee/index', '1', '1', '1', '0', '1452744843');
INSERT INTO `menu` VALUES ('7', '6', '新增员工', 'employee/add', '1', '0', '1', '0', '1451379822');
INSERT INTO `menu` VALUES ('8', '6', '员工编辑', 'employee/edit', '1', '0', '1', '0', '1451388439');
INSERT INTO `menu` VALUES ('9', '5', '菜单管理', 'auth/index', '1', '1', '1', '0', '1450951289');
INSERT INTO `menu` VALUES ('12', '0', '商品管理', 'product/index', '1', '1', '1', '0', '1450952464');
INSERT INTO `menu` VALUES ('13', '12', '商品列表', 'product/index', '1', '1', '1', '0', '1451293799');
INSERT INTO `menu` VALUES ('14', '13', '新增商品', 'product/add', '1', '0', '1', '7', '1452218700');
INSERT INTO `menu` VALUES ('15', '9', '新增菜单', 'auth/add', '1', '0', '1', '0', '1451461837');
INSERT INTO `menu` VALUES ('16', '0', '订单管理', 'order/index', '1', '1', '1', '0', '1451007722');
INSERT INTO `menu` VALUES ('17', '16', '中奖订单', 'order/index', '1', '1', '1', '0', '1451007745');
INSERT INTO `menu` VALUES ('18', '16', '伙购订单', 'order/all-order', '1', '1', '1', '0', '1451007773');
INSERT INTO `menu` VALUES ('19', '16', '充值订单', 'order/recharge', '1', '1', '1', '0', '1451007812');
INSERT INTO `menu` VALUES ('20', '16', '晒单管理', 'share/index', '1', '1', '1', '0', '1451007847');
INSERT INTO `menu` VALUES ('22', '16', '福分流水', 'point/index', '1', '1', '1', '0', '1451007910');
INSERT INTO `menu` VALUES ('23', '16', '支付订单', 'order/count', '1', '1', '1', '0', '1452664230');
INSERT INTO `menu` VALUES ('24', '13', '编辑商品', 'product/edit', '1', '0', '1', '0', '1452218932');
INSERT INTO `menu` VALUES ('25', '12', '商品分类', 'product-category/index', '1', '1', '1', '0', '1451293923');
INSERT INTO `menu` VALUES ('26', '25', '新增分类', 'product-category/add\r\n', '1', '0', '1', '0', '1452219048');
INSERT INTO `menu` VALUES ('27', '12', '商品品牌', 'brand/index', '1', '1', '1', '0', '1451293947');
INSERT INTO `menu` VALUES ('28', '27', '新增品牌', 'brand/add', '1', '0', '1', '0', '1452219119');
INSERT INTO `menu` VALUES ('31', '5', '角色管理', 'role/index', '1', '1', '1', '0', '1451357278');
INSERT INTO `menu` VALUES ('37', '31', '新增角色', 'role/add', '1', '0', '1', '0', '1451454873');
INSERT INTO `menu` VALUES ('38', '9', 'ajax获取菜单列表', 'auth/menu-list', '0', '0', '1', '0', '1451458124');
INSERT INTO `menu` VALUES ('39', '9', 'ajax获取路由列表', 'auth/route-list', '0', '0', '1', '0', '1451458156');
INSERT INTO `menu` VALUES ('40', '31', 'ajax获取角色列表', 'role/list', '0', '0', '1', '0', '1451463592');
INSERT INTO `menu` VALUES ('41', '31', 'ajax获取角色权限', 'role/get-role-privilege', '0', '0', '1', '0', '1451464091');
INSERT INTO `menu` VALUES ('45', '5', '小组管理', 'order-manage-group/index', '1', '1', '1', '0', '1451529766');
INSERT INTO `menu` VALUES ('46', '6', 'ajax获取员工列表', 'employee/list', '0', '0', '1', '0', '1451532016');
INSERT INTO `menu` VALUES ('47', '45', '新增小组', 'order-manage-group/add', '1', '0', '1', '0', '1451544793');
INSERT INTO `menu` VALUES ('48', '45', '编辑小组', 'order-manage-group/edit', '1', '0', '1', '0', '1451544820');
INSERT INTO `menu` VALUES ('49', '45', 'ajax获取小组成员', 'order-manage-group/user-list', '0', '0', '1', '0', '1451544865');
INSERT INTO `menu` VALUES ('50', '27', 'ajax获取品牌列表', 'brand/list', '0', '0', '1', '0', '1451553269');
INSERT INTO `menu` VALUES ('51', '17', '其他确认情况', 'order/confirm', '1', '0', '1', '0', '1451873793');
INSERT INTO `menu` VALUES ('52', '17', '确认地址', 'order/confirm-order', '1', '0', '1', '0', '1451873840');
INSERT INTO `menu` VALUES ('53', '5', '关键字过滤', 'keywords/index', '1', '1', '1', '0', '1451873862');
INSERT INTO `menu` VALUES ('54', '53', '新增关键字', 'keywords/add', '1', '0', '1', '0', '1451873893');
INSERT INTO `menu` VALUES ('55', '53', '编辑关键字', 'keywords/edit', '1', '0', '1', '0', '1451873916');
INSERT INTO `menu` VALUES ('56', '53', '删除关键字', 'keywords/del', '1', '0', '1', '0', '1451873939');
INSERT INTO `menu` VALUES ('57', '17', 'ajax修改支付订单', 'order/change', '0', '0', '1', '0', '1451875090');
INSERT INTO `menu` VALUES ('58', '17', '订单备注', 'order/remark', '0', '0', '1', '0', '1451875131');
INSERT INTO `menu` VALUES ('59', '53', '导入关键字', 'keywords/import', '1', '0', '1', '0', '1451875167');
INSERT INTO `menu` VALUES ('61', '53', '导出关键字', 'keywords/import', '1', '0', '1', '0', '1451875279');
INSERT INTO `menu` VALUES ('64', '5', '日志查询', '', '1', '1', '1', '0', '1451901793');
INSERT INTO `menu` VALUES ('65', '64', '后台操作日志', 'log/backstage-log', '1', '1', '1', '0', '1451901844');
INSERT INTO `menu` VALUES ('66', '64', '通知发送日志', 'log/message-log', '1', '1', '1', '0', '1451901875');
INSERT INTO `menu` VALUES ('67', '64', '用户登录日志', 'log/login-log', '1', '1', '1', '0', '1451901897');
INSERT INTO `menu` VALUES ('68', '64', 'ajax获取通知日志类型', 'log/get-log-type', '0', '0', '1', '0', '1451913754');
INSERT INTO `menu` VALUES ('69', '45', 'ajax获取订单小组列表', 'order-manage-group/list', '0', '0', '1', '0', '1452047142');
INSERT INTO `menu` VALUES ('70', '25', '编辑分类', 'product-category/edit', '1', '0', '1', '0', '1452219262');
INSERT INTO `menu` VALUES ('71', '25', '删除分类', 'product-category/del', '1', '0', '1', '0', '1452219340');
INSERT INTO `menu` VALUES ('72', '25', 'ajax获取分类列表', 'product-category/all-list', '0', '0', '1', '0', '1452219463');
INSERT INTO `menu` VALUES ('73', '27', '编辑品牌', 'brand/edit', '1', '0', '1', '0', '1452219516');
INSERT INTO `menu` VALUES ('74', '27', '删除品牌', 'brand/del', '1', '0', '1', '0', '1452219541');
INSERT INTO `menu` VALUES ('75', '13', '上下架商品', 'product/market', '1', '0', '1', '0', '1452219686');
INSERT INTO `menu` VALUES ('77', '17', '设置订单异常', 'win/refuse', '1', '0', '1', '0', '1452219804');
INSERT INTO `menu` VALUES ('78', '17', '区域', 'win/area-list', '0', '0', '1', '0', '1452219998');
INSERT INTO `menu` VALUES ('79', '13', '删除商品', 'product/del', '1', '0', '1', '0', '1452220317');
INSERT INTO `menu` VALUES ('80', '13', '检查推荐商品', 'product/check-recommand', '0', '0', '1', '0', '1452220551');
INSERT INTO `menu` VALUES ('81', '13', '上传商品图片', 'product/upload-image', '0', '0', '1', '0', '1452220440');
INSERT INTO `menu` VALUES ('82', '13', '上传商品介绍图片', 'product/upload-info-image', '0', '0', '1', '0', '1452220545');
INSERT INTO `menu` VALUES ('83', '13', '删除关联商品图片', 'product/delete-product-image', '0', '0', '1', '0', '1452220788');
INSERT INTO `menu` VALUES ('84', '13', '删除未关联商品图片', 'product/delete-image', '0', '0', '1', '0', '1452220824');
INSERT INTO `menu` VALUES ('85', '0', '会员管理', '0', '1', '1', '1', '0', '1452238421');
INSERT INTO `menu` VALUES ('86', '85', '会员列表', 'member/index', '1', '1', '1', '0', '1452238486');
INSERT INTO `menu` VALUES ('87', '85', '编辑会员', 'member/edit', '1', '0', '1', '0', '1452499013');
INSERT INTO `menu` VALUES ('88', '85', '冻结/解冻会员', 'member/change-status', '1', '0', '1', '0', '1452499110');
INSERT INTO `menu` VALUES ('89', '85', '发站内信', 'member/send-message', '1', '0', '1', '0', '1452500376');
INSERT INTO `menu` VALUES ('90', '85', '邀请列表', 'member/invite', '1', '0', '1', '0', '1452500690');
INSERT INTO `menu` VALUES ('91', '85', '账户余额', 'member/money', '1', '0', '1', '0', '1453191380');
INSERT INTO `menu` VALUES ('92', '85', '福分余额', 'member/point', '1', '0', '1', '0', '1452500928');
INSERT INTO `menu` VALUES ('93', '85', '佣金余额', 'member/commission', '1', '0', '1', '0', '1452500953');
INSERT INTO `menu` VALUES ('94', '85', '中奖次数', 'member/winning', '1', '0', '1', '0', '1452500973');
INSERT INTO `menu` VALUES ('95', '0', '运营管理', '0', '1', '1', '1', '0', '1452501428');
INSERT INTO `menu` VALUES ('96', '95', '余额调整', 'money/index', '1', '1', '1', '0', '1452505312');
INSERT INTO `menu` VALUES ('97', '96', '增加余额调整', 'money/add', '1', '0', '1', '0', '1452505450');
INSERT INTO `menu` VALUES ('98', '96', '修改余额调整', 'money/edit', '1', '0', '1', '0', '1452505486');
INSERT INTO `menu` VALUES ('100', '95', '福分调整', 'point/index', '1', '1', '1', '0', '1452505552');
INSERT INTO `menu` VALUES ('101', '100', '增加福分调整', 'point/add', '1', '0', '1', '0', '1452505671');
INSERT INTO `menu` VALUES ('102', '100', '修改福分调整', 'point/edit', '1', '0', '1', '0', '1452505688');
INSERT INTO `menu` VALUES ('103', '109', '财务审核', 'point/finance-approve', '1', '0', '1', '0', '1452506674');
INSERT INTO `menu` VALUES ('104', '95', '佣金管理', 'commission/index', '1', '1', '1', '0', '1452506276');
INSERT INTO `menu` VALUES ('105', '104', '运营审核', 'commission/operate-approve', '1', '0', '1', '0', '1452506357');
INSERT INTO `menu` VALUES ('106', '110', '财务审核', 'commission/finance-approve', '1', '0', '1', '0', '1452506743');
INSERT INTO `menu` VALUES ('107', '0', '财务管理', '0', '1', '1', '1', '0', '1452506454');
INSERT INTO `menu` VALUES ('108', '107', '余额调整', 'money/index', '1', '1', '1', '0', '1452506484');
INSERT INTO `menu` VALUES ('109', '107', '福分调整', 'point/index', '1', '1', '1', '0', '1452506507');
INSERT INTO `menu` VALUES ('110', '107', '佣金管理', 'commission/index', '1', '1', '1', '0', '1452506527');
INSERT INTO `menu` VALUES ('112', '108', '财务审核', 'money/finance-approve', '1', '0', '1', '0', '1452680050');
INSERT INTO `menu` VALUES ('113', '96', 'ajax获取用户信息', 'money/user-info', '0', '0', '1', '0', '1452679803');
INSERT INTO `menu` VALUES ('114', '17', '添加收货地址', 'win/ship-info', '1', '0', '1', '0', '1452679862');
INSERT INTO `menu` VALUES ('115', '17', '备货', 'win/deliver', '1', '0', '1', '0', '1452679935');
INSERT INTO `menu` VALUES ('116', '17', '发货', 'win/send', '1', '0', '1', '0', '1452679999');
INSERT INTO `menu` VALUES ('117', '17', '修改订单', 'win/modify', '1', '0', '1', '0', '1452680051');
INSERT INTO `menu` VALUES ('118', '17', '换货', 'win/change-status', '1', '0', '1', '0', '1452680077');
INSERT INTO `menu` VALUES ('119', '17', '设置异常', 'win/unusual', '1', '0', '1', '0', '1452680105');
INSERT INTO `menu` VALUES ('120', '0', '圈子管理', 'group/index', '1', '1', '1', '0', '1452735472');
INSERT INTO `menu` VALUES ('121', '120', '圈子列表', 'group/index', '1', '1', '1', '0', '1452735498');
INSERT INTO `menu` VALUES ('122', '120', '话题列表', 'group/topic', '1', '1', '1', '0', '1452735518');
INSERT INTO `menu` VALUES ('123', '120', '回帖列表', 'group/comment', '1', '1', '1', '0', '1452735544');
INSERT INTO `menu` VALUES ('124', '95', '投诉建议', 'suggestion/index', '1', '1', '1', '0', '1452744340');
INSERT INTO `menu` VALUES ('125', '124', '删除投诉', 'suggestion/del', '1', '0', '1', '0', '1452751427');
INSERT INTO `menu` VALUES ('126', '0', '仓储管理', '0', '1', '1', '1', '0', '1452758240');
INSERT INTO `menu` VALUES ('127', '126', '库存清单', 'product/store-list', '1', '1', '1', '0', '1452758287');
INSERT INTO `menu` VALUES ('128', '126', '采购入库', 'purchase/store', '1', '1', '1', '0', '1452758315');
INSERT INTO `menu` VALUES ('129', '127', '修改库存', 'product/edit-store', '1', '0', '1', '0', '1452758402');
INSERT INTO `menu` VALUES ('130', '127', '库存详情', 'product/store-view', '1', '0', '1', '0', '1452758431');
INSERT INTO `menu` VALUES ('131', '128', '入库', 'purchase/enter-store', '1', '0', '1', '0', '1452758457');
INSERT INTO `menu` VALUES ('132', '0', '采购管理', '0', '1', '1', '1', '0', '1452758492');
INSERT INTO `menu` VALUES ('133', '132', '供应商管理', 'supplier/index', '1', '1', '1', '0', '1452758539');
INSERT INTO `menu` VALUES ('134', '133', '新增供应商', 'supplier/add', '1', '0', '1', '0', '1452758566');
INSERT INTO `menu` VALUES ('135', '133', '详情', 'supplier/view', '1', '0', '1', '0', '1452758588');
INSERT INTO `menu` VALUES ('136', '133', '修改供应商', 'supplier/edit', '1', '0', '1', '0', '1452758612');
INSERT INTO `menu` VALUES ('137', '133', '删除供应商', 'supplier/del', '1', '0', '1', '0', '1452758636');
INSERT INTO `menu` VALUES ('138', '132', '采购订单', 'purchase/index', '1', '1', '1', '0', '1452758659');
INSERT INTO `menu` VALUES ('139', '138', '新增采购订单', 'purchase/add', '1', '0', '1', '0', '1452758699');
INSERT INTO `menu` VALUES ('140', '138', '修改采购订单', 'purchase/edit', '1', '0', '1', '0', '1452758718');
INSERT INTO `menu` VALUES ('141', '138', '采购订单详情', 'purchase/view', '1', '0', '1', '0', '1452758739');
INSERT INTO `menu` VALUES ('142', '121', '修改圈子', 'group/edit', '1', '0', '1', '0', '1452766896');
INSERT INTO `menu` VALUES ('143', '122', '话题审核', 'group/verify', '1', '0', '1', '0', '1452766935');
INSERT INTO `menu` VALUES ('144', '122', '话题删除', 'group/del-topic', '1', '0', '1', '0', '1452766970');
INSERT INTO `menu` VALUES ('145', '123', '回帖审核', 'group/verify-comment', '1', '0', '1', '0', '1452767004');
INSERT INTO `menu` VALUES ('146', '123', '回帖删除', 'group/del-comment', '1', '0', '1', '0', '1452767029');
INSERT INTO `menu` VALUES ('147', '122', '修改话题', 'group/topic-edit', '1', '0', '1', '0', '1452767091');
INSERT INTO `menu` VALUES ('148', '107', '采购审核', 'finance/purchase-verify-list', '1', '1', '1', '0', '1453084892');
INSERT INTO `menu` VALUES ('149', '107', '采购详情', 'finance/purchase-verify-view', '1', '0', '1', '0', '1453084935');
INSERT INTO `menu` VALUES ('150', '95', 'banner列表', 'banner/index', '1', '1', '1', '0', '1453109591');
INSERT INTO `menu` VALUES ('151', '150', '新增banner', 'banner/add', '1', '0', '1', '0', '1453109616');
INSERT INTO `menu` VALUES ('152', '150', 'banner修改', 'banner/edit', '1', '0', '1', '0', '1453109634');
INSERT INTO `menu` VALUES ('153', '150', 'banner删除', 'banner/del', '1', '0', '1', '0', '1453109653');
INSERT INTO `menu` VALUES ('155', '95', 'qq群管理', 'qq/index', '1', '1', '1', '0', '1453778140');
INSERT INTO `menu` VALUES ('156', '155', '新增qq群', 'qq/add', '1', '0', '1', '0', '1453778163');
INSERT INTO `menu` VALUES ('157', '155', '默认qq群', 'qq/set-default', '1', '0', '1', '0', '1453778198');
INSERT INTO `menu` VALUES ('158', '0', '土豪榜', 'rich/index', '1', '1', '1', '0', '1453795985');
INSERT INTO `menu` VALUES ('159', '158', '新增配置', 'rich/add', '1', '0', '1', '0', '1453796014');
INSERT INTO `menu` VALUES ('160', '158', '配置列表', 'rich/index', '1', '1', '1', '0', '1453796765');
