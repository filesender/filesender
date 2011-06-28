delimiter $$

CREATE DATABASE `filesender` /*!40100 DEFAULT CHARACTER SET latin1 */$$
ALTER SCHEMA `filesender`  DEFAULT CHARACTER SET utf8 ;

CREATE TABLE `files` (
  `fileto` varchar(250) DEFAULT NULL,
  `filesubject` varchar(250) DEFAULT NULL,
  `filevoucheruid` varchar(60) DEFAULT NULL,
  `filemessage` text,
  `filefrom` varchar(250) DEFAULT NULL,
  `filesize` bigint(20) DEFAULT NULL,
  `fileoriginalname` varchar(500) DEFAULT NULL,
  `filestatus` varchar(60) DEFAULT NULL,
  `fileip4address` varchar(24) DEFAULT NULL,
  `fileip6address` varchar(45) DEFAULT NULL,
  `filesendersname` varchar(250) DEFAULT NULL,
  `filereceiversname` varchar(250) DEFAULT NULL,
  `filevouchertype` varchar(60) DEFAULT NULL,
  `fileuid` varchar(60) DEFAULT NULL,
  `fileid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fileexpirydate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `fileactivitydate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `fileauthuseruid` varchar(500) DEFAULT NULL,
  `filecreateddate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `fileauthurl` varchar(500) DEFAULT NULL,
  `fileauthuseremail` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`fileid`),
  UNIQUE KEY `fileid` (`fileid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1$$

CREATE TABLE `logs` (
  `logid` int(11) NOT NULL AUTO_INCREMENT,
  `logfileuid` varchar(60) DEFAULT NULL,
  `logtype` varchar(60) DEFAULT NULL,
  `logfrom` varchar(250) DEFAULT NULL,
  `logto` varchar(250) DEFAULT NULL,
  `logdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `logtime` time DEFAULT NULL,
  `logfilesize` bigint(20) DEFAULT NULL,
  `logfilename` varchar(250) DEFAULT NULL,
  `logsessionid` varchar(60) DEFAULT NULL,
  `logmessage` text,
  `logvoucheruid` varchar(60) DEFAULT NULL,
  `logauthuseruid` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`logid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1$$
