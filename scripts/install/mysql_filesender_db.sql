--
-- Create Database: `filesender`
--

CREATE DATABASE `filesender` ;

USE `filesender`;

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `fileto` text DEFAULT NULL,
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
  `fileexpirydate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `fileactivitydate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `fileauthuseruid` varchar(500) DEFAULT NULL,
  `filecreateddate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `fileauthurl` varchar(500) DEFAULT NULL,
  `fileauthuseremail` varchar(255) DEFAULT NULL,
  `filegroupid` varchar(60) DEFAULT NULL,
  `filetrackingcode` varchar(5) DEFAULT NULL,
  `filedownloadconfirmations` varchar(5) DEFAULT NULL,
  `fileenabledownloadreceipts` varchar(5) DEFAULT NULL,
  `filedailysummary` varchar(5) DEFAULT NULL,
  `filenumdownloads` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`fileid`),
  UNIQUE KEY `fileid` (`fileid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `logid` int(11) NOT NULL AUTO_INCREMENT,
  `logfileuid` varchar(60) DEFAULT NULL,
  `logtype` varchar(60) DEFAULT NULL,
  `logfrom` varchar(250) DEFAULT NULL,
  `logto` text DEFAULT NULL,
  `logdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `logtime` time DEFAULT NULL,
  `logfilesize` bigint(20) DEFAULT NULL,
  `logfilename` varchar(250) DEFAULT NULL,
  `logsessionid` varchar(60) DEFAULT NULL,
  `logmessage` text,
  `logvoucheruid` varchar(60) DEFAULT NULL,
  `logauthuseruid` varchar(500) DEFAULT NULL,
  `logfilegroupid` varchar(60) DEFAULT NULL,
  `logfiletrackingcode` varchar(5) DEFAULT NULL,
  `logdailysummary` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`logid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

CREATE INDEX id_logvoucheruid ON logs (logvoucheruid);

-- Use the following to grant the filesender user the
-- right permissions and set the password.
-- Note that these commands are commented out and should
-- be adapted and used from your favourite interface to
-- mysql. Other methods are possible.
/* 
grant INSERT,DELETE,UPDATE,SELECT on filesender.* to filesender@localhost;
set password for filesender@localhost = password('yoursecretpassword');
flush privileges;
*/
