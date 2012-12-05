	--
-- Notes: pgSQL upgrade from 1.1.x DB to 1.5
-- Version: 1.5

--
-- Notes: The fileauthuserid column in the files table now has a size of 500, consistent with the corresponding column in the logs table and to allow for SAML attributes with very large values.
-- Required: Upgrading from 1.0.x/1.1.x

ALTER TABLE files ALTER fileauthuseruid TYPE character varying(500);
ALTER TABLE files ALTER fileip6address TYPE character varying(45);

--
-- Notes: Due to a change in the back-end workflow the database type of the fileto and corresponding logto columns needs to be changed from varchar(250) to text. Not doing so will break uploads to multiple recipients exceeding the 250 character limit when combined.
-- Required: Upgrading from 1.5-beta1

ALTER TABLE files ALTER fileto TYPE text;
ALTER TABLE logs ALTER logto TYPE text;

-- Notes: add stats table
-- Required: For all upgrades from 1.x

            CREATE TABLE stats
		(
			statid integer NOT NULL DEFAULT nextval('log_id_seq'::regclass),
			statfileuid character varying(60),
			statlogtype character varying(60),
			statfromdomain character varying(250),
			stattodomain text,
			statdate timestamp without time zone,
			statfilesize bigint,
			statsessionid character varying(60),
			statvoucheruid character varying(60),
			statauthuseruid character varying(500),
			CONSTRAINT stats_pkey PRIMARY KEY (statid)
		);
 

-- Notes: Add fileoptions to files table
-- Required: For all upgrades from 1.x

	ALTER TABLE files ADD COLUMN fileoptions text;


-- Notes: Config table added to store dbversion number
-- Required: Upgrading from 1.x

	CREATE TABLE config
		(
		configID integer NOT NULL DEFAULT nextval('log_id_seq'::regclass),
		configField varchar(45) NOT NULL,
		configValue text NOT NULL,
		CONSTRAINT config_pkey PRIMARY KEY (configID)
		);
		
-- Notes: Set Config dbversion
-- Required: For all upgrades from 1.x

DELETE FROM config WHERE configField = 'DBVersion';
INSERT INTO config ( configField,configValue)  VALUES ('DBVersion','1.5');