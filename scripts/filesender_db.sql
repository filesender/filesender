CREATE TABLE files
( 
  fileto character varying(250),
  filesubject character varying(250),
  filevoucheruid character varying(60),
  filemessage text,
  filefrom character varying(250),
  filesize bigint,
  fileoriginalname character varying(500),
  filestatus character varying(60),
  fileip4address character varying(24),
  fileip6address character varying(45),
  filesendersname character varying(250),
  filereceiversname character varying(250),
  filevouchertype character varying(60),
  fileuid character varying(60),
  fileid serial NOT NULL,
  fileexpirydate timestamp without time zone,
  fileactivitydate timestamp without time zone,
  fileauthuseruid character varying(500),
  filecreateddate timestamp without time zone,
  fileauthurl character varying(500),
  fileauthuseremail character varying(255),
  filegroupid character varying(60),
  CONSTRAINT files_pkey PRIMARY KEY (fileid)
);

CREATE SEQUENCE log_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 207
  CACHE 1;

CREATE TABLE logs
(
  logid integer NOT NULL DEFAULT nextval('log_id_seq'::regclass),
  logfileuid character varying(60),
  logtype character varying(60),
  logfrom character varying(250),
  logto character varying(250),
  logdate timestamp without time zone,
  logtime time with time zone,
  logfilesize bigint,
  logfilename character varying(250),
  logsessionid character varying(60),
  logmessage text,
  logvoucheruid character varying(60),
  logauthuseruid character varying(500),
  CONSTRAINT logs_pkey PRIMARY KEY (logid)
);
