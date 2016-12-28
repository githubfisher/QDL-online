/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50540
Source Host           : localhost:3306
Source Database       : qiangdaili

Target Server Type    : MYSQL
Target Server Version : 50540
File Encoding         : 65001

Date: 2016-12-28 15:20:03
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `account`
-- ----------------------------
DROP TABLE IF EXISTS `account`;
CREATE TABLE `account` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '序号',
  `name` varchar(45) NOT NULL COMMENT '账户名',
  `account` varchar(25) NOT NULL COMMENT '账号',
  `bank` varchar(10) NOT NULL COMMENT '银行简写',
  `branch` varchar(90) NOT NULL COMMENT '支行全称',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  `uid` smallint(5) unsigned NOT NULL COMMENT '用户id',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示',
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否默认',
  `times` tinyint(3) unsigned NOT NULL COMMENT '使用次数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of account
-- ----------------------------
INSERT INTO `account` VALUES ('1', '刘羊羊', '621206782998027482', 'ICBC', '北京丰台马家堡支行', '1467168426', '2', '1', '0', '0');
INSERT INTO `account` VALUES ('2', '刘洋洋', '62904847108408070340', 'CCB', '北京支行', '1467169188', '2', '1', '0', '0');
INSERT INTO `account` VALUES ('3', '刘美丽', '4302942141232321156412', 'CITIC', '河北支行', '1467169394', '2', '1', '0', '0');

-- ----------------------------
-- Table structure for `admin`
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员id',
  `user` varchar(10) NOT NULL COMMENT '用户名',
  `pwd` varchar(16) NOT NULL COMMENT '密匙',
  `register_time` int(10) NOT NULL COMMENT '注册时间',
  `dealine_time` int(10) NOT NULL COMMENT '服务截止日期',
  `login_time` int(10) NOT NULL COMMENT '最新登录',
  `login_ip` varchar(15) NOT NULL COMMENT '登录ip',
  `login_times` int(10) NOT NULL COMMENT '登录次数',
  `power_id` varchar(50) NOT NULL COMMENT '权限id',
  `realname` varchar(30) NOT NULL COMMENT '联系人',
  `phone` varchar(11) NOT NULL COMMENT '电话',
  `store_name` varchar(45) NOT NULL COMMENT '店名',
  `qq` varchar(12) NOT NULL COMMENT '客服QQ号',
  `ercode` varchar(100) NOT NULL COMMENT '二维码图片地址',
  `default_account` smallint(5) unsigned NOT NULL COMMENT '默认账户id',
  `withdraw_is` decimal(5,2) NOT NULL COMMENT '可提现金额',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of admin
-- ----------------------------
INSERT INTO `admin` VALUES ('1', 'root', 'root', '1462064460', '0', '1482908482', '222.129.187.98', '4', '', 'root', '13810918651', '爱恋宝贝儿童摄影', '229322285', '/Public/Admin/images/erweicode.png', '0', '0.00');
INSERT INTO `admin` VALUES ('2', 'admin', '1234', '1462064460', '0', '1480391117', '45.76.99.152', '5', '', 'admin', '13810913765', '金色时代儿童摄影广场店', '2918198125', '/Uploads/2016-06-13/123.png', '0', '0.00');

-- ----------------------------
-- Table structure for `alipayment`
-- ----------------------------
DROP TABLE IF EXISTS `alipayment`;
CREATE TABLE `alipayment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '支付设置id',
  `payment_name` varchar(45) NOT NULL COMMENT '客户命名',
  `uid` smallint(6) NOT NULL COMMENT '用户id',
  `partner` varchar(50) NOT NULL,
  `key` varchar(50) NOT NULL COMMENT '密匙',
  `account` varchar(50) NOT NULL COMMENT '支付宝账号',
  `add_time` int(10) NOT NULL COMMENT '添加时间',
  `modify_time` int(10) NOT NULL COMMENT '修改时间',
  `is_open` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否开启',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of alipayment
-- ----------------------------
INSERT INTO `alipayment` VALUES ('1', '默认支付宝', '1', '2088021265703813', 'key1', 'zhouxin4000191951@qq.com', '1466741246', '0', '1');
INSERT INTO `alipayment` VALUES ('2', '测试支付宝支付2', '2', '2088021265703813', 'key', 'account', '1466741246', '0', '1');
INSERT INTO `alipayment` VALUES ('3', '测试支付宝支付', '1', 'partner3', 'key3', 'account3', '1466741246', '0', '1');

-- ----------------------------
-- Table structure for `draw_money`
-- ----------------------------
DROP TABLE IF EXISTS `draw_money`;
CREATE TABLE `draw_money` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '申请提现序号',
  `uid` smallint(6) DEFAULT NULL,
  `payment` varchar(10) DEFAULT NULL COMMENT '支付方式名称',
  `payment_id` smallint(4) DEFAULT NULL COMMENT '支付方式id',
  `sply_time` int(10) DEFAULT NULL COMMENT '申请时间',
  `sply_total_sum` decimal(5,2) DEFAULT NULL COMMENT '申请转账金额',
  `sply_status` tinyint(1) DEFAULT '0' COMMENT '申请状态 1为完成 0为未转账',
  `transfer_time` int(10) unsigned DEFAULT NULL COMMENT '转账时间',
  `remarks` varchar(255) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of draw_money
-- ----------------------------

-- ----------------------------
-- Table structure for `program`
-- ----------------------------
DROP TABLE IF EXISTS `program`;
CREATE TABLE `program` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '项目id',
  `pro_name` varchar(90) NOT NULL COMMENT '项目名称',
  `pro_title` varchar(45) NOT NULL COMMENT '项目标题',
  `pro_content` text NOT NULL COMMENT '项目内容',
  `pro_deadline` int(10) NOT NULL COMMENT '项目截止日期',
  `pro_thumb` varchar(100) NOT NULL COMMENT '缩略图',
  `uid` smallint(6) NOT NULL COMMENT '用户id',
  `templet_id` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '模板id',
  `start_time` int(10) NOT NULL COMMENT '项目开始开始时间',
  `finish_time` int(10) NOT NULL COMMENT '项目结束时间',
  `is_pay` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否支付',
  `pro_price` text NOT NULL COMMENT '各地区价格',
  `is_self_pay` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否使用自有支付方式，需要绑定',
  `is_open_horn` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否开启喇叭',
  `is_open_countd` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否开启倒计时',
  `is_open_cservice` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否开启客服，需要设置',
  `countd_position` tinyint(1) NOT NULL DEFAULT '1' COMMENT '倒计时位置',
  `is_start` tinyint(1) NOT NULL COMMENT '是否开启',
  `is_limit_num` tinyint(1) NOT NULL COMMENT '是否限制数量',
  `limit_num` text NOT NULL COMMENT '限制数量设置',
  `weipay_id` tinyint(3) unsigned NOT NULL COMMENT '微信支付设置id',
  `otherpay_id` tinyint(3) unsigned NOT NULL COMMENT '其他支付（支付宝）设置id',
  `weixin_url` varchar(255) NOT NULL COMMENT '微信转发url',
  `other_url` varchar(255) NOT NULL COMMENT '其他浏览器URL',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of program
-- ----------------------------
INSERT INTO `program` VALUES ('2', '测试项目名称', '这是项目标题', '&lt;p&gt;&lt;img src=&quot;/Uploads/2016-06-29/1467203767640097.jpg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-06-29/1467203755137198.jpg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-06-29/1467203751198505.jpg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-06-29/1467203763914081.jpg&quot; alt=&quot;1467203763914081.jpg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-06-29/1467203743797938.jpg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;', '1467302400', '/Uploads/2016-06-24/S576cddb533c9d.jpg', '2', '1', '1466352000', '1469980800', '1', '1999', '0', '1', '1', '1', '1', '1', '1', '1', '1', '1', 'http://qdl.bjletu.com/index.php/Home?id=2&weixin=123', 'http://qdl.bjletu.com/index.php/Home/?id=2');
INSERT INTO `program` VALUES ('3', '项目有图片', '', '&lt;p&gt;&lt;img src=&quot;/Uploads/2016-06-29/1467203743797938.jpg&quot; title=&quot;1467203743797938.jpg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-06-29/1467203746625208.jpg&quot; title=&quot;1467203746625208.jpg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-06-29/1467203751198505.jpg&quot; title=&quot;1467203751198505.jpg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-06-29/1467203755137198.jpg&quot; title=&quot;1467203755137198.jpg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-06-29/1467203763914081.jpg&quot; title=&quot;1467203763914081.jpg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-06-29/1467203767640097.jpg&quot; title=&quot;1467203767640097.jpg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;', '0', '/Uploads/2016-06-13/123.png', '2', '1', '1467129600', '1468944000', '0', '1999', '0', '1', '1', '1', '1', '1', '1', '1', '1', '1', 'http://qdl.bjletu.com/index.php/Home?id=3&weixin=123', 'http://qdl.bjletu.com/index.php/Home/?id=3');
INSERT INTO `program` VALUES ('4', '智诚新项目', '', '&lt;p&gt;大发发&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-06-29/1467203755137198.jpg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-06-29/1467203751198505.jpg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-06-29/1467203743797938.jpg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;', '0', '/Uploads/2016-07-15/S5788b33485081.jpg', '2', '1', '-28800', '-28800', '0', '', '0', '1', '1', '1', '1', '1', '0', '', '0', '0', 'http://qdl.bjletu.com/index.php/Home/?id=4&weixin=wx6', 'http://qdl.bjletu.com/index.php/Home/?id=4');
INSERT INTO `program` VALUES ('5', '幸孕帮买断测试', '幸孕帮买断测试', '&lt;p&gt;幸孕帮买断测试&lt;/p&gt;&lt;p&gt;幸孕帮买断测试&lt;/p&gt;&lt;p&gt;幸孕帮买断测试&lt;/p&gt;&lt;p&gt;幸孕帮买断测试&lt;/p&gt;&lt;p&gt;幸孕帮买断测试&lt;br/&gt;&lt;/p&gt;', '0', '/Uploads/2016-11-29/S583cfa594519d.jpg', '2', '1', '0', '0', '0', '', '0', '1', '1', '1', '1', '1', '0', '', '1', '0', 'http://qdl.bjletu.com/index.php/Home/?id=5&weixin=wx6', 'http://qdl.bjletu.com/index.php/Home/?id=5');
INSERT INTO `program` VALUES ('6', '幸孕帮买断测试2', '幸孕帮买断测试2', '&lt;p&gt;&lt;img src=&quot;/Uploads/2016-11-29/1480391408278975.jpg&quot; title=&quot;1480391408278975.jpg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-11-29/1480391411627575.jpg&quot; title=&quot;1480391411627575.jpg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-11-29/1480391417939125.jpg&quot; title=&quot;1480391417939125.jpg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-11-29/1480391426975616.jpg&quot; title=&quot;1480391426975616.jpg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;', '0', '/Uploads/2016-11-29/S583cfad70b26f.jpg', '2', '1', '0', '0', '0', '', '0', '1', '1', '1', '1', '1', '0', '', '1', '0', 'http://qdl.bjletu.com/index.php/Home/?id=6&weixin=wx6', 'http://qdl.bjletu.com/index.php/Home/?id=6');
INSERT INTO `program` VALUES ('7', '幸孕帮买断测试', '幸孕帮买断测试', '&lt;p&gt;&lt;img src=&quot;/Uploads/2016-11-29/1480391956221183.jpeg&quot; title=&quot;1480391956221183.jpeg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-11-29/1480391956589879.jpeg&quot; title=&quot;1480391956589879.jpeg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-11-29/1480391958153967.jpeg&quot; title=&quot;1480391958153967.jpeg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-11-29/1480391958570300.jpeg&quot; title=&quot;1480391958570300.jpeg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-11-29/1480391959469218.jpeg&quot; title=&quot;1480391959469218.jpeg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-11-29/1480391959128695.jpeg&quot; title=&quot;1480391959128695.jpeg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-11-29/1480391960313615.jpeg&quot; title=&quot;1480391960313615.jpeg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-11-29/1480391962594225.jpeg&quot; title=&quot;1480391962594225.jpeg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-11-29/1480391964418268.jpeg&quot; title=&quot;1480391964418268.jpeg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-11-29/1480391964480621.jpeg&quot; title=&quot;1480391964480621.jpeg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/Uploads/2016-11-29/1480391964132043.jpeg&quot; title=&quot;1480391964132043.jpeg&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;', '0', '/Uploads/2016-11-29/S583cfd04897ed.jpg', '2', '1', '1479571200', '1480521600', '0', '', '0', '1', '1', '1', '1', '1', '0', '', '1', '0', 'http://qdl.bjletu.com/index.php/Home/?id=7&weixin=wx6', 'http://qdl.bjletu.com/index.php/Home/?id=7');

-- ----------------------------
-- Table structure for `sply`
-- ----------------------------
DROP TABLE IF EXISTS `sply`;
CREATE TABLE `sply` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '申请id',
  `uid` smallint(5) unsigned NOT NULL COMMENT '用户id',
  `pro_id` smallint(5) unsigned NOT NULL COMMENT '项目id',
  `is_pay` tinyint(4) NOT NULL COMMENT '是否需要支付',
  `price` decimal(10,0) NOT NULL COMMENT '支付金额',
  `payment` varchar(10) NOT NULL COMMENT '支付方式',
  `pay_is` tinyint(4) NOT NULL COMMENT '支付完成？',
  `pay_time` int(10) NOT NULL COMMENT '支付时间',
  `pay_money` decimal(10,0) NOT NULL COMMENT '支付金额',
  `sply_time` int(10) NOT NULL COMMENT '申请时间',
  `sply_area` varchar(90) NOT NULL COMMENT '申请区域',
  `sply_store_name` varchar(45) NOT NULL COMMENT '店铺名',
  `sply_name` varchar(15) NOT NULL COMMENT '申请人',
  `sply_phone` varchar(11) NOT NULL COMMENT '申请电话',
  `sply_email` varchar(25) DEFAULT NULL COMMENT '申请邮件',
  `sply_qq` varchar(12) DEFAULT NULL COMMENT '申请QQ',
  `sply_ip` varchar(15) NOT NULL COMMENT '申请IP',
  `is_qd` tinyint(1) NOT NULL COMMENT '是否抢定',
  `payment_id` tinyint(1) unsigned NOT NULL COMMENT '支付设置id',
  `openid` varchar(50) NOT NULL COMMENT '微信id',
  `is_show` tinyint(1) DEFAULT '1' COMMENT '是否显示',
  `withdraw_is` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否提现',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=176 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sply
-- ----------------------------
INSERT INTO `sply` VALUES ('165', '2', '3', '0', '0', 'nopay', '2', '0', '0', '1468583916', '甘肃省/金昌市/永昌县', '乐乐', '18637773177', '18637773177', null, '510490424', '61.148.242.56', '0', '0', '', '1', '0');
INSERT INTO `sply` VALUES ('166', '2', '5', '0', '0', 'nopay', '2', '0', '0', '1480391432', '北京市/北京市/朝阳区', '智诚', '李达', '15865541245', null, '3034546399', '123.118.141.227', '0', '0', '', '1', '0');
INSERT INTO `sply` VALUES ('167', '2', '6', '0', '0', 'nopay', '2', '0', '0', '1480391621', '北京市/北京市/朝阳区', '测试申请', '刘明', '13812531478', null, '223366855', '111.161.57.31', '0', '0', '', '1', '0');
INSERT INTO `sply` VALUES ('168', '2', '7', '0', '0', 'nopay', '2', '0', '0', '1480392092', '北京市/北京市/朝阳区', '测试项目', '刘宝保', '13548671123', null, '225466319', '111.161.57.31', '1', '0', '', '1', '0');
INSERT INTO `sply` VALUES ('169', '2', '7', '0', '0', 'nopay', '2', '0', '0', '1480392115', '河南省/郑州市/管城回族区', '麦菲儿', '杨少', '15978828002', null, '575801269', '117.136.61.54', '1', '0', '', '1', '0');
INSERT INTO `sply` VALUES ('170', '2', '7', '0', '0', 'nopay', '2', '0', '0', '1480392117', '河南省/郑州市/二七区', '那个地儿', '天空', '13283898006', null, '78039151', '61.158.152.213', '0', '0', '', '1', '0');
INSERT INTO `sply` VALUES ('171', '2', '7', '0', '0', 'nopay', '2', '0', '0', '1480392147', '北京市/北京市/东城区', '乐乐', '周鑫', '18637773177', null, '5104904242', '111.161.57.49', '0', '0', '', '1', '0');
INSERT INTO `sply` VALUES ('172', '2', '7', '0', '0', 'nopay', '2', '0', '0', '1480392309', '北京市/北京市/朝阳区', '重复神器', '刘妹妹', '1366469799', null, '1367994', '111.161.57.31', '0', '0', '', '1', '0');
INSERT INTO `sply` VALUES ('173', '2', '7', '0', '0', 'nopay', '2', '0', '0', '1480392334', '河南省/郑州市/管城回族区', '麦菲儿', '杨少', '15978828003', null, '575801269', '117.136.61.54', '0', '0', '', '1', '0');
INSERT INTO `sply` VALUES ('174', '2', '7', '0', '0', 'nopay', '2', '0', '0', '1480392336', '河南省/郑州市/二七区', '十月天使', '赵', '12345678910', null, '254521', '223.104.19.60', '0', '0', '', '1', '0');
INSERT INTO `sply` VALUES ('175', '2', '7', '0', '0', 'nopay', '2', '0', '0', '1480392535', '河南省/郑州市/二七区', '十月天使', '杨二牛', '15978828005', null, '575801269', '117.136.61.54', '0', '0', '', '1', '0');

-- ----------------------------
-- Table structure for `transfer_account`
-- ----------------------------
DROP TABLE IF EXISTS `transfer_account`;
CREATE TABLE `transfer_account` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '序号',
  `account` varchar(25) NOT NULL COMMENT '账号',
  `bank` varchar(10) NOT NULL COMMENT '银行简写',
  `subbranch` varchar(45) NOT NULL COMMENT '支行全称',
  `name` varchar(45) NOT NULL COMMENT '户主',
  `uid` smallint(6) NOT NULL COMMENT '用户id',
  `is_use` tinyint(1) NOT NULL COMMENT '是否使用中',
  PRIMARY KEY (`id`,`account`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of transfer_account
-- ----------------------------

-- ----------------------------
-- Table structure for `weipayment`
-- ----------------------------
DROP TABLE IF EXISTS `weipayment`;
CREATE TABLE `weipayment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '支付设置id',
  `payment_name` varchar(45) NOT NULL COMMENT '客户命名',
  `uid` smallint(6) NOT NULL COMMENT '用户id',
  `key` varchar(10) NOT NULL COMMENT '支付方式',
  `appid` varchar(50) NOT NULL,
  `appsecret` varchar(50) NOT NULL,
  `mchid` varchar(50) NOT NULL,
  `add_time` int(10) NOT NULL COMMENT '添加时间',
  `modify_time` int(10) NOT NULL COMMENT '修改时间',
  `is_open` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否开启',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of weipayment
-- ----------------------------
INSERT INTO `weipayment` VALUES ('1', '默认微信支付', '1', '13131', 'wx426b3015555a46be', 'e10adc3949ba59abbe56e057f20f883e', '1131', '1466734185', '1466741469', '1');
INSERT INTO `weipayment` VALUES ('2', '测试微信支付2', '2', 'apikey', 'wx426b3015555a46be', 'appsecret', 'mchid', '1466741216', '0', '1');

-- ----------------------------
-- Table structure for `withdraw`
-- ----------------------------
DROP TABLE IF EXISTS `withdraw`;
CREATE TABLE `withdraw` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '序号',
  `uid` smallint(5) unsigned NOT NULL COMMENT '用户id',
  `sum` decimal(8,2) NOT NULL COMMENT '提现金额',
  `sply_time` int(10) unsigned NOT NULL COMMENT '申请时间',
  `account_id` smallint(5) unsigned NOT NULL COMMENT '转账账号',
  `status` tinyint(1) NOT NULL COMMENT '状态',
  `modify_time` int(10) unsigned NOT NULL COMMENT '处理时间',
  `modify_admin` varchar(15) NOT NULL COMMENT '处理管理员',
  `remark` varchar(60) NOT NULL COMMENT '备注',
  `sply_ids` text NOT NULL COMMENT '申请订单id',
  `count_timestamp` int(10) NOT NULL COMMENT '统计时间戳',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of withdraw
-- ----------------------------
INSERT INTO `withdraw` VALUES ('1', '2', '1000.00', '1467196775', '3', '0', '1469175042', 'root', '', '11 3 ', '1467196772');
