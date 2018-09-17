CREATE TABLE `dbd_spxx` (
`id`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
`userNo`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`spcs`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`productName`  varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`startTime`  datetime NULL DEFAULT NULL ,
`endTime`  datetime NULL DEFAULT NULL ,
`startPrice`  int(10) NULL DEFAULT NULL ,
`minPrice`  int(10) NULL DEFAULT NULL ,
`maxPrice`  int(10) NULL DEFAULT NULL ,
`cappedPrice`  int(10) NULL DEFAULT NULL ,
`category1`  int(10) NULL DEFAULT NULL ,
`category1Name`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`category2`  int(10) NULL DEFAULT NULL ,
`category2Name`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`category3`  int(10) NULL DEFAULT NULL ,
`category3Name`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`primaryPic`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`currentPrice`  varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`recordCount`  int(10) NULL DEFAULT NULL ,
`bidder`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`status`  char(1) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '1 即将夺宝 2 正在夺宝' ,
`sc`  char(2) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'N' COMMENT '收藏'
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
ROW_FORMAT=DYNAMIC
;