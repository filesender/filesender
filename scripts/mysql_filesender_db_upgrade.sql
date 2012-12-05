--
-- Notes: mySQL upgrade from 1.1.x DB to 1.5beta2
-- Version: 1.5

USE `filesender`;

--
-- Notes: The fileauthuserid column in the files table now has a size of 500, consistent with the corresponding column in the logs table and to allow for SAML attributes with very large values.
-- Required: Upgrading from 1.0.x/1.1.x

	 ALTER TABLE files CHANGE fileauthuseruid fileauthuseruid character varying(500) null;

--
-- Notes: Due to a change in the back-end workflow the database type of the fileto and corresponding logto columns needs to be changed from varchar(250) to text. Not doing so will break uploads to multiple recipients exceeding the 250 character limit when combined.
-- Required: Upgrading from 1.5-beta1

	ALTER TABLE files CHANGE fileto fileto text default null;
	ALTER TABLE logs CHANGE logto logto text default null;

-- Notes: Config table added to store dbversion number
-- Required: Upgrading from 1.x

CREATE TABLE IF NOT EXISTS `config` (
  `configID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `configField` varchar(45) NOT NULL,
  `configValue` text NOT NULL,
  PRIMARY KEY (`configID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Notes: Add fileoptions to files table
-- Required: For all upgrades from 1.x

alter table files add column fileoptions text;

-- Notes: Set Config dbversion
-- Required: For all upgrades from 1.x

DELETE FROM config WHERE configField = 'DBVersion';
INSERT INTO config ( configField,configValue)  VALUE ('DBVersion','1.5');

-- Notes: add stats table
-- Required: For all upgrades from 1.x

CREATE TABLE IF NOT EXISTS `stats` (
  `statid` int(11) NOT NULL AUTO_INCREMENT,
  `statfileuid` varchar(60) DEFAULT NULL,
  `statlogtype` varchar(60) DEFAULT NULL,
  `statfromdomain` varchar(250) DEFAULT NULL,
  `stattodomain` text,
  `statdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `statfilesize` bigint(20) DEFAULT NULL,
  `statsessionid` varchar(60) DEFAULT NULL,
  `statvoucheruid` varchar(60) DEFAULT NULL,
  `statauthuseruid` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`statid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

