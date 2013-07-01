-- 
-- Use this code to generate an SQLite database.
--
-- NOTE
-- Because SQLite does dynamic typing, almost all columns
-- are TEXT types. See http://www.sqlite.org/datatype3.html.
-- 
CREATE TABLE files (
	fileto TEXT,
	filesubject TEXT,
	filevoucheruid TEXT,
	filemessage TEXT,
	filefrom TEXT,
	filesize INTEGER,
	fileoriginalname TEXT,
	filestatus TEXT,
	fileip4address TEXT,
	fileip6address TEXT,
	filesendersname TEXT,
	filereceiversname TEXT,
	filevouchertype TEXT,
	fileuid TEXT,
	fileid INTEGER PRIMARY KEY AUTOINCREMENT,
	fileexpirydate TEXT,
	fileactivitydate TEXT,
	fileauthuseruid TEXT,
	filecreateddate TEXT,
	fileauthurl TEXT,
	fileauthuseremail TEXT,
	filegroupid TEXT,
  filetrackingcode TEXT,
);

CREATE TABLE logs (
	logid INTEGER PRIMARY KEY AUTOINCREMENT,
	logfileuid TEXT,
	logtype TEXT,
	logfrom TEXT,
	logto TEXT,
	logdate TEXT,
	logtime TEXT,
	logfilesize INTEGER,
	logfilename TEXT,
	logsessionid TEXT,
	logmessage TEXT,
	logvoucheruid TEXT,
	logauthuseruid TEXT
);

CREATE TRIGGER UPDATE_LOGDATE AFTER UPDATE ON logs
	BEGIN
		UPDATE logs SET logdate = datetime('now')
		WHERE rowid = new.rowid;
	END
;
