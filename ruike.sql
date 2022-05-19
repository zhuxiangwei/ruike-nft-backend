-- --------------------------------------------------------
-- 主机:                           127.0.0.1
-- 服务器版本:                        10.3.28-MariaDB - MariaDB Server
-- 服务器操作系统:                      Linux
-- HeidiSQL 版本:                  12.0.0.6468
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- 导出 ruike 的数据库结构
DROP DATABASE IF EXISTS `ruike`;
CREATE DATABASE IF NOT EXISTS `ruike` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `ruike`;

-- 导出  表 ruike.advertising 结构
DROP TABLE IF EXISTS `advertising`;
CREATE TABLE IF NOT EXISTS `advertising` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image` text NOT NULL DEFAULT '' COMMENT '广告海报url',
  `url` text NOT NULL DEFAULT '' COMMENT '跳转链接',
  `status` int(11) NOT NULL DEFAULT 0 COMMENT '状态，0有效，1无效',
  `start` int(11) NOT NULL DEFAULT 0 COMMENT '有效期开始，unix时间戳，0无限制',
  `end` int(11) NOT NULL DEFAULT 0 COMMENT '有效期结束，unix时间戳，0无限制',
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='首页广告';

-- 数据导出被取消选择。

-- 导出  表 ruike.author 结构
DROP TABLE IF EXISTS `author`;
CREATE TABLE IF NOT EXISTS `author` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT 0 COMMENT '用户表id',
  `type` int(11) NOT NULL DEFAULT 0 COMMENT '主体类型，0个人，1公司',
  `name` varchar(64) NOT NULL DEFAULT '' COMMENT '主体名称',
  `description` text NOT NULL DEFAULT '' COMMENT '简介',
  `contact` varchar(64) NOT NULL DEFAULT '' COMMENT '联系人',
  `phone` varchar(32) NOT NULL DEFAULT '' COMMENT '联系电话',
  `wechat` varchar(32) NOT NULL DEFAULT '' COMMENT '微信',
  `skill` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT '' COMMENT '擅长方向，逗号分隔',
  `note` text DEFAULT '' COMMENT '留言',
  `certified` int(11) NOT NULL DEFAULT 0 COMMENT '0未认证1审核中3认证失败4认证成功',
  `logo` text NOT NULL DEFAULT '' COMMENT '徽标',
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='发行方';

-- 数据导出被取消选择。

-- 导出  表 ruike.catalog 结构
DROP TABLE IF EXISTS `catalog`;
CREATE TABLE IF NOT EXISTS `catalog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aid` int(11) NOT NULL DEFAULT 0 COMMENT '发行方作者表id',
  `name` varchar(64) NOT NULL DEFAULT '' COMMENT '名称',
  `cover` text NOT NULL DEFAULT '' COMMENT '封面url',
  `total` int(11) NOT NULL DEFAULT 0 COMMENT '份数',
  `best` int(11) NOT NULL DEFAULT 0 COMMENT '精品，0非，1是',
  `recommend` int(11) NOT NULL DEFAULT 0 COMMENT '推荐，0非，1是',
  `price` varchar(128) NOT NULL DEFAULT '0' COMMENT '价格',
  `single` int(11) NOT NULL DEFAULT 0 COMMENT '单品，0非，1是',
  `browse` int(11) NOT NULL DEFAULT 0 COMMENT '浏览量',
  `category` text NOT NULL DEFAULT '' COMMENT 'category集合，逗号分隔',
  `onsale` int(11) NOT NULL DEFAULT 0 COMMENT '销售状态，0在售，1已售',
  `deny` int(11) NOT NULL DEFAULT 0 COMMENT '封禁，0否，1是',
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品编目';

-- 数据导出被取消选择。

-- 导出  表 ruike.category 结构
DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='类别';

-- 数据导出被取消选择。

-- 导出  表 ruike.favorite 结构
DROP TABLE IF EXISTS `favorite`;
CREATE TABLE IF NOT EXISTS `favorite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aid` int(11) NOT NULL DEFAULT 0 COMMENT '作者表id',
  `pid` int(11) NOT NULL DEFAULT 0 COMMENT '商品表id',
  `uid` int(11) NOT NULL DEFAULT 0 COMMENT '收藏者用户表id',
  `type` int(11) NOT NULL DEFAULT 0 COMMENT '类型，0作品，1发行方',
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='收藏';

-- 数据导出被取消选择。

-- 导出  表 ruike.indictment 结构
DROP TABLE IF EXISTS `indictment`;
CREATE TABLE IF NOT EXISTS `indictment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT 0 COMMENT '举报人的用户表id',
  `pid` int(11) NOT NULL DEFAULT 0 COMMENT '商品id',
  `type` text NOT NULL DEFAULT '' COMMENT '举报类型',
  `note` text NOT NULL DEFAULT '' COMMENT '举报内容',
  `images` text NOT NULL DEFAULT '' COMMENT '图片集，url逗号分隔',
  `contact` varchar(64) NOT NULL DEFAULT '' COMMENT '联系方式',
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='举报';

-- 数据导出被取消选择。

-- 导出  表 ruike.invite 结构
DROP TABLE IF EXISTS `invite`;
CREATE TABLE IF NOT EXISTS `invite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT 0 COMMENT '用户表id',
  `accept` int(11) NOT NULL DEFAULT 0 COMMENT '邀请链接注册用户id',
  `gift` int(11) NOT NULL DEFAULT 0 COMMENT '奖励的订单id',
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='邀请';

-- 数据导出被取消选择。

-- 导出  表 ruike.login 结构
DROP TABLE IF EXISTS `login`;
CREATE TABLE IF NOT EXISTS `login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '' COMMENT '用户名',
  `uid` int(11) NOT NULL DEFAULT 0 COMMENT '用户表id',
  `result` int(11) NOT NULL DEFAULT 0 COMMENT '登录结果，0成功，1失败',
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='登录历史';

-- 数据导出被取消选择。

-- 导出  表 ruike.message 结构
DROP TABLE IF EXISTS `message`;
CREATE TABLE IF NOT EXISTS `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from` int(11) NOT NULL DEFAULT 0 COMMENT '发送方用户表id',
  `to` int(11) NOT NULL DEFAULT 0 COMMENT '接收方用户表id',
  `content` text NOT NULL DEFAULT '' COMMENT '消息内容',
  `type` int(11) NOT NULL DEFAULT 0 COMMENT '类型，0用户消息，1系统消息',
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='消息';

-- 数据导出被取消选择。

-- 导出  表 ruike.order 结构
DROP TABLE IF EXISTS `order`;
CREATE TABLE IF NOT EXISTS `order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller` int(11) NOT NULL DEFAULT 0 COMMENT '卖方用户表id',
  `buyer` int(11) NOT NULL DEFAULT 0 COMMENT '买方用户表id',
  `pid` int(11) NOT NULL DEFAULT 0 COMMENT '商品表id',
  `uuid` varchar(128) NOT NULL DEFAULT '' COMMENT '唯一编号',
  `type` int(11) NOT NULL DEFAULT 0 COMMENT '类型，0购买，1赠送，2空投，3奖励',
  `hash` varchar(256) NOT NULL DEFAULT '0x71C7656EC7ab88b098defB751B7401B5f6d8976F' COMMENT '交易哈希',
  `price` varchar(128) NOT NULL DEFAULT '0' COMMENT '价格',
  `status` int(11) NOT NULL DEFAULT 0 COMMENT '状态，0待支付，1成功，2失败',
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单';

-- 数据导出被取消选择。

-- 导出  表 ruike.ownership 结构
DROP TABLE IF EXISTS `ownership`;
CREATE TABLE IF NOT EXISTS `ownership` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT 0 COMMENT '用户表id',
  `pid` int(11) NOT NULL DEFAULT 0 COMMENT '商品表id',
  `type` int(11) NOT NULL DEFAULT 0 COMMENT '类型，0持有，1已赠送，2已出售',
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='所有权变更';

-- 数据导出被取消选择。

-- 导出  表 ruike.posters 结构
DROP TABLE IF EXISTS `posters`;
CREATE TABLE IF NOT EXISTS `posters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image` text NOT NULL DEFAULT '' COMMENT '海报图片文件名',
  `left` int(11) NOT NULL DEFAULT 0 COMMENT '二维码图片在海报的位置-左',
  `top` int(11) NOT NULL DEFAULT 0 COMMENT '二维码图片在海报的位置-上',
  `width` int(11) NOT NULL DEFAULT 0 COMMENT '二维码图片的宽度',
  `height` int(11) NOT NULL DEFAULT 0 COMMENT '二维码图片的高度',
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分享海报';

-- 数据导出被取消选择。

-- 导出  表 ruike.production 结构
DROP TABLE IF EXISTS `production`;
CREATE TABLE IF NOT EXISTS `production` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL DEFAULT 0 COMMENT '编目表id',
  `index` int(11) NOT NULL DEFAULT 1 COMMENT '系列编号',
  `uuid` varchar(128) NOT NULL DEFAULT '' COMMENT '唯一编号',
  `aid` int(11) NOT NULL DEFAULT 0 COMMENT '发行方作者表id',
  `oid` int(11) NOT NULL DEFAULT 0 COMMENT '拥有者用户表id',
  `name` varchar(64) NOT NULL DEFAULT '' COMMENT '名称',
  `description` text NOT NULL DEFAULT 'NFT，全称为Non-Fungible Token，指非同质化代币，是用于表示数字资产（包括jpeg和视频剪辑形式）的唯一加密货币令牌。NFT可以买卖，就像有形资产一样。' COMMENT '简介',
  `images` text NOT NULL DEFAULT 'http://116.205.243.252/cover.jpeg,http://116.205.243.252/cover.jpeg,http://116.205.243.252/cover.jpeg,http://116.205.243.252/cover.jpeg,http://116.205.243.252/cover.jpeg' COMMENT '作品图片集，url逗号分割',
  `type` int(11) NOT NULL DEFAULT 0 COMMENT '类型，0图片，1音频，2视频，3动画',
  `bcid` varchar(128) NOT NULL DEFAULT '0x71C7656EC7ab88b098defB751B7401B5f6d8976F' COMMENT '链上权证地址',
  `url` text NOT NULL DEFAULT '' COMMENT '链接',
  `price` varchar(128) NOT NULL DEFAULT '0' COMMENT '价格',
  `browse` int(11) NOT NULL DEFAULT 0 COMMENT '浏览量',
  `onsale` int(11) NOT NULL DEFAULT 0 COMMENT '销售状态，0在售，1已售',
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='商品';

-- 数据导出被取消选择。

-- 导出  表 ruike.request 结构
DROP TABLE IF EXISTS `request`;
CREATE TABLE IF NOT EXISTS `request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT 0 COMMENT '申请人的用户表id',
  `type` int(11) NOT NULL DEFAULT 0 COMMENT '申请业务类型，0账户实名认证，1作者申请入驻',
  `status` int(11) NOT NULL DEFAULT 0 COMMENT '状态，0审核中，1审核失败，2审核成功',
  `commit` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '提交时间',
  `update` int(11) NOT NULL DEFAULT 0 COMMENT '更新时间',
  `note` text NOT NULL DEFAULT '' COMMENT '备注，比如审核不通过的原因',
  `images` text NOT NULL DEFAULT '' COMMENT '作品图片集，url逗号分割',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='发行方申请';

-- 数据导出被取消选择。

-- 导出  表 ruike.user 结构
DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(128) NOT NULL DEFAULT '' COMMENT '唯一编号',
  `name` varchar(64) NOT NULL DEFAULT '' COMMENT '用户名，一般是手机号',
  `password` varchar(128) NOT NULL DEFAULT '' COMMENT '密码加盐的SHA256哈希',
  `bcid` varchar(128) NOT NULL DEFAULT '0x71C7656EC7ab88b098defB751B7401B5f6d8976F' COMMENT '链上账户身份地址',
  `nick` varchar(64) NOT NULL DEFAULT '' COMMENT '昵称',
  `description` text NOT NULL DEFAULT '' COMMENT '简介',
  `avatar` text NOT NULL DEFAULT '' COMMENT '头像图片url',
  `realname` varchar(32) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `idcardno` varchar(32) NOT NULL DEFAULT '' COMMENT '证件号码',
  `certified` int(11) NOT NULL DEFAULT 0 COMMENT '0未认证1审核中3认证失败4认证成功',
  `privacy` char(3) NOT NULL DEFAULT '111' COMMENT '隐私选项',
  `deny` int(11) NOT NULL DEFAULT 0 COMMENT '封禁，0否，1是',
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_uuid_IDX` (`uuid`) USING BTREE,
  KEY `user_name_IDX` (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户';

-- 数据导出被取消选择。

-- 导出  触发器 ruike.order_uuid_trigger 结构
DROP TRIGGER IF EXISTS `order_uuid_trigger`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='IGNORE_SPACE,STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER order_uuid_trigger
before INSERT
ON `order` FOR EACH ROW
SET new.uuid=REPLACE(UUID(),'-','')//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- 导出  触发器 ruike.production_uuid_trigger 结构
DROP TRIGGER IF EXISTS `production_uuid_trigger`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='IGNORE_SPACE,STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER production_uuid_trigger
before INSERT
ON production FOR EACH ROW
SET new.uuid=REPLACE(UUID(),'-','')//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- 导出  触发器 ruike.user_uuid_trigger 结构
DROP TRIGGER IF EXISTS `user_uuid_trigger`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='IGNORE_SPACE,STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER user_uuid_trigger
before INSERT
ON `user` FOR EACH ROW
SET new.uuid=REPLACE(UUID(),'-','')//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
