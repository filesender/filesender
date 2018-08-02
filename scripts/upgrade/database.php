<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2014, AARNet, Belnet, HEAnet, SURFnet, UNINETT
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * *	Redistributions of source code must retain the above copyright
 * 	notice, this list of conditions and the following disclaimer.
 * *	Redistributions in binary form must reproduce the above copyright
 * 	notice, this list of conditions and the following disclaimer in the
 * 	documentation and/or other materials provided with the distribution.
 * *	Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 'AS IS'
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

require_once dirname(__FILE__).'/../../includes/init.php';

Logger::setProcess(ProcessTypes::UPGRADE);


/**
 * Create/upgrade Filesender's database
 */

set_error_handler(function($no, $str, $file = '', $line = '') {
    if($no == '2048') return;
    Logger::error('['.$no.'] '.$str.' in '.$file.' at line '.$line);
});

$currentSchemaVersion = Metadata::getLatestUsedSchemaVersion();
DBI::beginTransaction();

$args = new Args(
    array(
        'h' => 'help',
        'd:' => 'db_database:',
    ));

$args->getopts();
$args->maybeDisplayHelpAndExit(
    'Create or upgrade the database schema for FileSender....'."\n\n" .
    'Usage '.basename(__FILE__).' [-d|--db_database=<name>] '."\n" .
    "\t".'-d|--db_database Name of the database to connect to'."\n" .
    "\t\n"
);
$args->MergeShortToLong();
$db_database = $args->getArg('db_database', false, null );
if( $db_database ) {
    echo "originally set db_database is " . Config::get('db_database') . "\n";
    Config::localOverride('db_database',$db_database );
    echo "newly set db_database is " . Config::get('db_database') . "\n";
}
echo "current db_database is " . Config::get('db_database') . "\n";

// Get data classes
function getClasses() {
    $classes = array();
    $classes[] = 'User'; // This must be before Authentication so users can be setup too
    foreach(scandir(FILESENDER_BASE.'/classes/data') as $i) {
        if(substr($i, -10) != '.class.php') continue;
        $class = substr($i, 0, -10);
        if($class == 'DBObject') continue;
        $classes[] = $class;
    }
    return $classes;
}

$classes = getClasses();




echo "You currently have database major version " . $currentSchemaVersion . "\n";
echo "This execution will move also move to major version " . DatabaseSchemaVersions::VERSION_CURRENT . "\n";
echo "Note that a major migration will only be needed when more advanced SQL is used to migrate\n\n";
if( $currentSchemaVersion == 0 ) {
    $class = 'Metadata';
    updateTable( call_user_func($class.'::getDBTable'),
                 call_user_func($class.'::getDataMap'));
 }
Metadata::add('running database update script on existing schema version ' . $currentSchemaVersion);
echo "running database update script on existing schema version $currentSchemaVersion \n";

function execToColumnValue( $q, $col )
{
    $s = DBI::prepare($q);
    $s->execute(array());
    $r = $s->fetch();
    return $r ? $r[$col] : -1;
}

function ensureAuthSetup( $saml_uid, $comment )
{
    $a = Authentication::ensure( $saml_uid, $comment );
    $user = User::fromAuthId( $a->id );
}

//
// Reserve some auth entries for system specific use
//
function ensureAuthenticationsTableHasReservedIDs()
{
    $tbl_auth = call_user_func('Authentication::getDBTable');
    $q = 'select count(*) as c from ' . $tbl_auth . ' ';
    if( execToColumnValue( $q, 'c' ) < 1) {
        ensureAuthSetup('filesender-upgrade@localhost.localdomain',   'local upgrade script');
        ensureAuthSetup('filesender-cronjob@localhost.localdomain',   'cron job');
        ensureAuthSetup('filesender-authlocal@localhost.localdomain', 'local auth job');
        ensureAuthSetup('filesender-phpunit@localhost.localdomain',   'unit test job');
        ensureAuthSetup('filesender-testdriver@localhost.localdomain',   'a user that runs system tests');
        ensureAuthSetup('filesender-reserved1@localhost.localdomain', 'reserved job 1');
        ensureAuthSetup('filesender-reserved2@localhost.localdomain', 'reserved job 2');
        ensureAuthSetup('filesender-reserved3@localhost.localdomain', 'reserved job 3');
        ensureAuthSetup('filesender-reserved4@localhost.localdomain', 'reserved job 4');
        ensureAuthSetup('filesender-reserved5@localhost.localdomain', 'reserved job 5');
        ensureAuthSetup('filesender-reserved6@localhost.localdomain', 'reserved job 6');
        ensureAuthSetup('filesender-reserved7@localhost.localdomain', 'reserved job 7');
        ensureAuthSetup('filesender-reserved8@localhost.localdomain', 'reserved job 8');
        ensureAuthSetup('filesender-reserved9@localhost.localdomain', 'reserved job 9');
    }
    
}

//
// Main updates
//
function ensureAllTables()
{
    $classes = getClasses();
    
    foreach($classes as $class) {
        echo 'Checking class '.$class."\n";
        
        $datamap = call_user_func($class.'::getDataMap');
        $viewmap = call_user_func($class.'::getViewMap');
        $secindexmap = call_user_func($class.'::getSecondaryIndexMap');
        $table = call_user_func($class.'::getDBTable');
        
        // Check if table exists
        echo 'Working on table '.$table."\n";
        updateTable( $table, $datamap );

        // reserve some IDs if we might have just made the auths table
        if( $class == 'Authentication' ) {
            ensureAuthenticationsTableHasReservedIDs();
        }
        echo 'Done for table '.$table."\n";

        echo 'Checkindex secondary indexes for table '.$table."\n";
        foreach($secindexmap as $index => $definition) {
            $index = $table . '_' . $index;
            echo 'checking ' . $index . "\n";
            $problems = Database::checkTableSecondaryIndexFormat( $table, $index, $definition, function($message) {
                echo "\t".$message."\n";
            });
            if( $problems ) {
                echo "update index " . $index . " on table " . $table . "\n";
                if( $problems != DatabaseSecondaryIndexStatuses::NOTFOUND ) {
                    echo "drop index " . $index . " on table " . $table . "\n";
                    Database::dropTableSecondaryIndex(   $table, $index );
                }
                echo "create index " . $index . " on table " . $table . "\n";
                Database::createTableSecondaryIndex( $table, $index, $definition );
            }
        } 
        echo 'Done for secondary indexes for table '.$table."\n";
    }
}


function renameColumn( $tableName, $oldname, $newname, $mysqltypestring  )
{
    $dbtype = Config::get('db_type');
    
    if( $dbtype == 'mysql' ) {
        $q = 'ALTER TABLE '.$tableName.' CHANGE COLUMN '.$oldname.'  '.$newname . ' '. $mysqltypestring;
    } else {
        $q = 'ALTER TABLE '.$tableName.' RENAME COLUMN '.$oldname.' TO '.$newname;
    }
    echo "renaming column $tableName.$oldname to $tableName.$newname\n";
//    echo "$dbtype  $q \n";
    DBI::exec( $q );
}

function updateIntoTable( $updateTable, $fromTable, $setClause, $whereClause )
{
    $dbtype = Config::get('db_type');
    
    $q = 'update '.$updateTable.' totable ';
    if( $dbtype == 'mysql' ) {
        $q .= ' , ' . $fromTable . ' fromtable ';
    }
    $q .= $setClause;
    if( $dbtype == 'pgsql' ) {
        $q .= ' FROM '.$fromTable.' fromtable ';
    }
    $q .= ' ' . $whereClause;
    echo "updating information in $updateTable using information from $fromTable\n";
    echo "$dbtype  $q \n";
    DBI::exec( $q );
}

function updateTable( $table, $datamap )
{
    // Check if table exists
    echo 'Look for table '.$table."\n";
    if(Database::tableExists($table)) {
        echo 'Table found, check columns'."\n";
        
        $existing_columns = Database::getTableColumns($table);
        echo 'Found '.count($existing_columns).' columns in existing table : '.implode(', ', $existing_columns)."\n";
        
        $required_columns = array_keys($datamap);
        echo 'Found '.count($required_columns).' columns in required table : '.implode(', ', $required_columns)."\n";
        
        $missing = array();
        foreach($required_columns as $c) if(!in_array($c, $existing_columns)) $missing[] = $c;
        if(count($missing)) {
            echo 'Found '.count($missing).' missing columns in existing table : '.implode(', ', $missing)."\n";
            foreach($missing as $column) Database::createTableColumn($table, $column, $datamap[$column]);
        }
        
        $useless = array();
        foreach($existing_columns as $c) if(!in_array($c, $required_columns)) $useless[] = $c;
        if(count($useless)) {
            echo 'Found '.count($useless).' useless columns in existing table : '.implode(', ', $useless)."\n";
            foreach($useless as $column) Database::removeTableColumn($table, $column);
        }
        
        echo 'Check column format'."\n";
        foreach($required_columns as $column) {
            if(in_array($column, $missing)) continue; // Already created with the right format
            
            $problems = Database::checkTableColumnFormat($table, $column, $datamap[$column], function($message) {
                echo "\t".$message."\n";
            });
            
            if($problems) {
                echo 'Column '.$column.' has bad format, updating it'."\n";
                Database::updateTableColumnFormat($table, $column, $datamap[$column], $problems);
            }
        }
        
    }else{
        echo 'Table is missing, create it'."\n";
        Database::createTable($table, $datamap);
    }
}

try {


    //
    // Remove all views
    //
    echo "Removing views from database so base tables are free to change\n";
    echo "database views will be added back later in this script\n";
    foreach($classes as $class) {
        echo 'Checking class '.$class."\n";
        
        $datamap = call_user_func($class.'::getDataMap');
        $viewmap = call_user_func($class.'::getViewMap');
        $secindexmap = call_user_func($class.'::getSecondaryIndexMap');
        $table = call_user_func($class.'::getDBTable');

        echo 'Removing views for table '.$table."\n";
        print_r($viewmap);
        foreach($viewmap as $viewname => $maker) {
        echo 'Removing view for '.$viewname."\n";
            foreach($maker as $dbtype => $def) {
                if( $dbtype == Config::get('db_type')) {
                    echo "removing view $viewname \n";
                    Database::dropView($table,$viewname);
                }
            }
        }
        echo 'Done removing views for table '.$table."\n";
    }

    

    echo "checking for major schema migrations\n";
    //
    // If there is no userpreferences table then we
    // consider it an empty database and do not need
    // to 'migrate' to a newer schema.
    //
    $tbl_user = call_user_func('User::getDBTable');
    if( Database::tableExists($tbl_user)) {
        
        // Perform larger migrations
        if( $currentSchemaVersion != DatabaseSchemaVersions::VERSION_CURRENT ) {
            $schemaVersion = $currentSchemaVersion;
            $dbtype = Config::get('db_type');
            echo "test2 $schemaVersion \n";
            for( ; $schemaVersion <= DatabaseSchemaVersions::VERSION_CURRENT; $schemaVersion++ ) {

                echo "checking for $schemaVersion \n";
                //
                // Version 22
                // ----------
                // The UserPreferences table had the 'id' as a varchar which was the saml auth id
                // and also the primary key. Other tables stored user_id which was also a large
                // varchar and referenced the same saml auth id. In addition to being a slower design
                // and not enforcing RI, this stored personal information around in various places
                // in the database.
                //
                // This update migrated the user_id to an Authentications table
                // and made the old user_id references a link to the UserPreferences.id for that user.
                // The saml information is then associated with a specific user using UserPreferences.authid.
                // These id columns are bigint (8 byte numbers) and should index a lot better than email addresses.
                //
                if( $schemaVersion == DatabaseSchemaVersions::VERSION_22 )
                {
                    echo "Migrating database schema to version 22.\n";
                    echo "this will take some time to perform...\n";
                    $tbl_auth      = call_user_func('Authentication::getDBTable');
                    $tbl_transfers = call_user_func('Transfer::getDBTable');
                    $tbl_guests    = call_user_func('Guest::getDBTable');
                    $tbl_clientlog = call_user_func('ClientLog::getDBTable');
                    $tbl_user      = call_user_func('User::getDBTable');

                    
                    echo "tbl_clientlog $tbl_clientlog\n";
                    echo "clientlogs table already exists? " . Database::tableExists($tbl_clientlog) . "\n";
                    if( !Database::tableExists($tbl_clientlog)) {
                        echo "Making old clientlogs so it can be migrated\n";
                        updateTable( $tbl_clientlog,
                                     array(
                                         'id' => array(
                                             'type' => 'uint',
                                             'size' => 'big',
                                             'primary' => true,
                                             'autoinc' => true
                                         ),
                                         'user_id' => array(
                                             'type' => 'string',
                                             'size' => 190
                                         ),
                                         'created' => array(
                                             'type' => 'datetime'
                                         ),
                                         'message' => array(
                                             'type' => 'text'
                                         )
                                     )
                        );
                    }
                    
                    $class = 'Authentication';
                    // add new authentication table
                    echo "Adding Authentications table...\n";
                    updateTable( call_user_func($class.'::getDBTable'),
                                 call_user_func($class.'::getDataMap'));
                    ensureAuthenticationsTableHasReservedIDs();
                    // First, ensure the dbid column exists using the same mechanism that would
                    // normally create it
                    $classes = array('User','Guest','ClientLog','Transfer');
                    foreach($classes as $class) {
                        $datamap = call_user_func($class.'::getDataMap');
                        $table   = call_user_func($class.'::getDBTable');
                        if( $class == 'User' ) {
                            $column = 'authid';
                        } else {
                            $column = 'userid';
                        }
                        echo "Adding new column $column to $class table...\n";
                        Database::createTableColumn($table, $column, array('type' => 'uint','size' => 'big','null'=>true));
                    }

                    // rename the UserPreferences table id column to user_id and create new
                    // autoinc integer 'id' column in UserPreferences
                    renameColumn( $tbl_user, 'id', 'user_id', 'varchar(190)' );
                    echo "Changing primary primary key column in $tbl_user\n";
                    $class = 'User';
                    $datamap = call_user_func($class.'::getDataMap');
                    $table   = call_user_func($class.'::getDBTable');
                    $column  = 'authid';
                    if( $dbtype == 'pgsql' ) {
                        DBI::exec( 'ALTER TABLE '.$table.' DROP CONSTRAINT '.$table.'_pkey ' . "\n" );
                    } else {
                        DBI::exec( 'ALTER TABLE '.$table.' drop primary key ');
                    }
                    
                    echo "Adding new auto inc primary key column to $tbl_user\n";
                    Database::createTableColumn($tbl_user, 'id', array('type' => 'uint','size' => 'big','addprimary' => true,'autoinc'=>true));


                    
                    
                    echo "Adding entries to Authentications table with user_id auth information...\n";
                    $q = 'insert into '.$tbl_auth.' (saml_user_identification_uid) select distinct user_id from '
                       . '( (select user_id from '.$tbl_transfers.') UNION '
                       . '  (select user_id from '.$tbl_guests.')    UNION '
                       . '  (select user_id from '.$tbl_clientlog.') UNION '
                       . '  (select user_id from '.$tbl_user.') '
                       . ' ) as dd ';
                    echo $q;
                    DBI::exec($q);

                    echo "Setting timestamps to something valid in Authentications table...\n";
                    DBI::exec('update '.$tbl_auth.' set created = now(), last_activity = now();');

                    // link user.authid to the auth table
                    updateIntoTable( $tbl_user,      $tbl_auth,
                                     'SET authid      = fromtable.id',
                                     'WHERE fromtable.saml_user_identification_uid = totable.user_id' );

                    // make sure every entry in the auth table
                    // has an entry in the userprefs table
                    echo "Ensuring every auth entry has a matching entry in the users table...\n";
                    $q = 'INSERT into '.$tbl_user.' '
                       . ' (user_id,aup_ticked,guest_preferences,transfer_preferences,frequent_recipients,created,authid) '
                       . " (select saml_user_identification_uid,false,'','','',now(),id from ".$tbl_auth.' where id not in (select authid from '.$tbl_user.' where authid is not null));';
                    echo "SQL $q\n";
                    DBI::exec($q);

                    // remake links to userpreferences.id
                    // based on the old auth information scattered
                    // through the old columns in the tables
                    updateIntoTable( $tbl_transfers, $tbl_user, 'SET userid      = fromtable.id', 'WHERE fromtable.user_id = totable.user_id' );
                    updateIntoTable( $tbl_guests,    $tbl_user, 'SET userid      = fromtable.id', 'WHERE fromtable.user_id = totable.user_id' );
                    updateIntoTable( $tbl_clientlog, $tbl_user, 'SET userid      = fromtable.id', 'WHERE fromtable.user_id = totable.user_id' );
                    
                    
                    // now we want to convert the userpreferences column
                    // over to a primary key, not null for future enjoyment.
                    $class = 'User';
                    $datamap = call_user_func($class.'::getDataMap');
                    $table = call_user_func($class.'::getDBTable');
                    $column = 'authid';
                    if( $dbtype == 'pgsql' ) {

                        DBI::exec( 'CREATE UNIQUE INDEX '.$table.'_id_idx ON '.$table.' (id);');
                        DBI::exec( 'ALTER TABLE '.$table. "\n"
                                 . ' ADD CONSTRAINT '.$table.'_pkey PRIMARY KEY USING INDEX '.$table.'_id_idx ');

                    } else {
                        DBI::exec( 'ALTER TABLE '.$table.' drop primary key, add primary key( id )');
                    }
                    
                    DBI::exec( 'ALTER TABLE '.$tbl_user.     ' ADD FOREIGN KEY (authid) REFERENCES '.$tbl_auth.' (id)  on delete cascade on update restrict ;');
                    DBI::exec( 'ALTER TABLE '.$tbl_transfers.' ADD FOREIGN KEY (userid) REFERENCES '.$tbl_user.' (id)  on delete cascade on update restrict ;');
                    DBI::exec( 'ALTER TABLE '.$tbl_guests.   ' ADD FOREIGN KEY (userid) REFERENCES '.$tbl_user.' (id)  on delete cascade on update restrict ;');
                    DBI::exec( 'ALTER TABLE '.$tbl_clientlog.' ADD FOREIGN KEY (userid) REFERENCES '.$tbl_user.' (id)  on delete cascade on update restrict ;');

                    Database::removeTableColumn($tbl_user,      'user_id');
                    Database::removeTableColumn($tbl_transfers, 'user_id');
                    Database::removeTableColumn($tbl_guests,    'user_id');
                    Database::removeTableColumn($tbl_clientlog, 'user_id');
                }
            }
            
            echo "Major updates completed, normal updates will now be performed....\n";
        }
    }

    //
    // Main updates
    //
    ensureAllTables();

    //
    // Remake all the views. This is done last because the view might reference other
    // tables and might rely on the other tables schema having been updated already
    //
    foreach($classes as $class) {
        echo 'Checking class '.$class."\n";
        
        $datamap = call_user_func($class.'::getDataMap');
        $viewmap = call_user_func($class.'::getViewMap');
        $secindexmap = call_user_func($class.'::getSecondaryIndexMap');
        $table = call_user_func($class.'::getDBTable');

        echo 'Updating views for table '.$table."\n";
        print_r($viewmap);
        foreach($viewmap as $viewname => $maker) {
        echo 'Updating views for '.$viewname."\n";
            foreach($maker as $dbtype => $def) {
                if( $dbtype == Config::get('db_type')) {
                    echo "Updating views $dbtype $def \n";
                    Database::createView($table,$viewname,$def);
                }
            }
        }
        echo 'Done updating views for table '.$table."\n";
    }

    
} catch(Exception $e) {
    echo "Error, Rolling database changes back....\n";
    echo " This should leave the database state as it was before you started the script\n";
    DBI::rollBack();
    $uid = ($e instanceof LoggingException) ? $e->getUid() : 'no available uid';
    die('Encountered exception : '.$e->getMessage().', see logs for details (uid: '.$uid.') ...');
}

echo "\n\n";
echo "All code worked, commit to database starting...\n";
DBI::commit();
echo "Everything went well\n";
echo "Database structure is up to date\n";
