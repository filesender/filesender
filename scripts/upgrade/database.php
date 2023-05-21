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

$majorMigrationPerformed = false;

/**
 * Create/upgrade Filesender's database
 */

set_error_handler(function($no, $str, $file = '', $line = '') {
    if($no == '2048') return;
    Logger::error('['.$no.'] '.$str.' in '.$file.' at line '.$line);
});


$args = new Args(
    array(
        'h' => 'help',
        'd:' => 'db_database:',
        'm' => 'mariadb_skip_text_character_set_check'
    ));

$args->getopts();
$args->maybeDisplayHelpAndExit(
    'Create or upgrade the database schema for FileSender....'."\n\n" .
    'Usage '.basename(__FILE__).' [-d|--db_database=<name>] '."\n" .
    "\t".'-d|--db_database Name of the database to connect to'."\n" .
    "\t".'-m|--mariadb_skip_text_character_set_check Skip checking the text character encoding for columns'."\n" .
    "\t\n"
);
$args->MergeShortToLong();
$arg_mariadb_skip_text_character_set_check = $args->getArg('mariadb_skip_text_character_set_check', false, false );
if( $arg_mariadb_skip_text_character_set_check == '1' ) {
    $arg_mariadb_skip_text_character_set_check = true;
}
$db_database = $args->getArg('db_database', false, null );
if( $db_database ) {
    echo "originally set db_database is " . Config::get('db_database') . "\n";
    Config::localOverride('db_database',$db_database );
    echo "newly set db_database is " . Config::get('db_database') . "\n";
}
echo "current db_database is " . Config::get('db_database') . "\n";
$dbtype = Config::get('db_type');

//
// A quick sanity check that we know what db and dbname to connect 
//
if( $dbtype != 'mysql' && $dbtype != 'pgsql' ) {
    echo "ERROR: Please set the db_type to your database type in config.php\n";
    exit;
}
if( Config::get('db_database') == '' ) {
    echo "ERROR: Please set the db_database to your database name in config.php\n";
    exit;
}


$currentSchemaVersion = Metadata::getLatestUsedSchemaVersion();
if( $dbtype != 'mysql' ) {
    DBI::beginTransaction();
}



// Get data classes
function getClasses() {
    $classes = array();
    // This must be before Authentication so users can be setup too    
    array_push($classes, 'User');
    foreach(scandir(FILESENDER_BASE.'/classes/data') as $i) {
        if(substr($i, -10) != '.class.php') continue;
        $class = substr($i, 0, -10);
        if($class == 'DBObject') continue;
        $classes[] = $class;
    }
    return $classes;
}
// Get data constant classes
function getConstantClasses() {
    $classes = array();
    // This must be before Authentication so users can be setup too    
    foreach(scandir(FILESENDER_BASE.'/classes/data/constants') as $i) {
        if(substr($i, -10) != '.class.php') continue;
        $class = substr($i, 0, -10);
        if($class == 'DBConstant') continue;
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

function ensureAuthSetup( $saml_uid, $comment, $addUser )
{
    $a = Authentication::ensure( $saml_uid, $comment );
    // this is hard to do during the migration when user_id might be still around.
    if( $addUser ) {
        $user = User::fromAuthId( $a->id );
    }
}

//
// Reserve some auth entries for system specific use
//
function ensureAuthenticationsTableHasReservedIDs( $addUser = false )
{
    $tbl_auth = call_user_func('Authentication::getDBTable');
    $q = 'select count(*) as c from ' . $tbl_auth . ' ';
    if( execToColumnValue( $q, 'c' ) < 1) {
        ensureAuthSetup('filesender-upgrade@localhost.localdomain',   'local upgrade script', $addUser );
        ensureAuthSetup('filesender-cronjob@localhost.localdomain',   'cron job', $addUser );
        ensureAuthSetup('filesender-authlocal@localhost.localdomain', 'local auth job', $addUser );
        ensureAuthSetup('filesender-phpunit@localhost.localdomain',   'unit test job', $addUser );
        ensureAuthSetup('filesender-testdriver@localhost.localdomain',   'a user that runs system tests', $addUser );
        ensureAuthSetup('filesender-reserved1@localhost.localdomain', 'reserved job 1', $addUser );
        ensureAuthSetup('filesender-reserved2@localhost.localdomain', 'reserved job 2', $addUser );
        ensureAuthSetup('filesender-reserved3@localhost.localdomain', 'reserved job 3', $addUser );
        ensureAuthSetup('filesender-reserved4@localhost.localdomain', 'reserved job 4', $addUser );
        ensureAuthSetup('filesender-reserved5@localhost.localdomain', 'reserved job 5', $addUser );
        ensureAuthSetup('filesender-reserved6@localhost.localdomain', 'reserved job 6', $addUser );
        ensureAuthSetup('filesender-reserved7@localhost.localdomain', 'reserved job 7', $addUser );
        ensureAuthSetup('filesender-reserved8@localhost.localdomain', 'reserved job 8', $addUser );
        ensureAuthSetup('filesender-reserved9@localhost.localdomain', 'reserved job 9', $addUser );
    }
    
}

function ensureTable( $class )
{
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
        // We are ok to make new recrods in the UserPreferences table as it should be handled
        // before the Authentication table and will have the right schema.
        $createUserPreferencesRecordToo = true;
        ensureAuthenticationsTableHasReservedIDs( $createUserPreferencesRecordToo );
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

//
// Main updates
//
function ensureAllTables()
{
    $classes = getConstantClasses();
    foreach($classes as $class) {
        ensureTable( $class );
        call_user_func($class.'::ensure');
    }

    $classes = getClasses();
    foreach($classes as $class) {
        ensureTable( $class );
        if( $class == 'AggregateStatisticMetadata' ) {
            call_user_func($class.'::ensure');
        }
    }
}


function verifyTableCharacterSetForTable( $class )
{
    echo 'verifyTableCharacterSetForTable class '.$class."\n";
    
    $datamap = call_user_func($class.'::getDataMap');
    $viewmap = call_user_func($class.'::getViewMap');
    $secindexmap = call_user_func($class.'::getSecondaryIndexMap');
    $table = call_user_func($class.'::getDBTable');
    
    // Check if table exists
    echo 'Working on table '.$table."\n";


    
    
    $sql = "SELECT column_name as col,character_set_name as charset FROM information_schema.`COLUMNS` ";
    $sql .= " WHERE table_schema = \"" . Config::get('db_database') . "\" ";
    $sql .= " AND table_name = \"$table\" ";

    $s = DBI::prepare($sql);
    $s->execute(array());
    $records = $s->fetchAll();
    foreach ($records as $r) {
        echo " " . $r['col'] . ' = ' . $r['charset'] . "\n";
        $datamap[$r['col']]['db_charset'] = $r['charset'];
    }

    $dmcols = array_keys($datamap);
    
    foreach( $dmcols as $column ) {
        $d = $datamap[$column];
        $dt = $d['type'];
        if( $dt == 'string' || $dt == 'text'  ) {
            echo " column $column is a string! \n";
            echo "    database has it as " . $d['db_charset'] . "\n";
            if( $d['db_charset'] != 'utf8mb4' ) {
                echo "\n";
                echo "-----------------------------------------------------------------------\n";
                echo "\n";
                echo "ERROR When using mysql or mariadb the database and tables should use \n";
                echo " the utf8mb4 TEXT CHARACTER SET.\n";
                echo "\n";
                echo "WARNING The database table $table has a string/text column with an incorrect character set!\n";
                echo "        expecting utf8mb4 \n";
                echo "        found     " . $d['db_charset'] . " \n";
                echo "WARNING   please update column $column to use utf8mb4 \n";
                echo "\n";
                echo "Because columns, tables, and databases might derive their TEXT CHARACTER SET\n";
                echo "automatic changes have not been added to the script to fix this issue.\n";
                echo "\n";
                echo "You can use:\n";
                echo "  SHOW create table $table\n";
                echo "in a mysql console to see if specific columns have TEXT CHARACTER SET explicitly set\n";
                echo "otherwise please create a backup and test the following SQL:\n";
                echo "  ALTER table $table CONVERT TO CHARACTER SET utf8mb4\n";
                echo "\n";
                echo "-----------------------------------------------------------------------\n";
                echo "\n";
                echo "ERROR When using mysql or mariadb the database and tables should use \n";
                echo "ERROR  the utf8mb4 TEXT CHARACTER SET.\n";
                echo "\n";
                echo "ERROR See above for details \n";
                echo "ERROR if you wish to ignore this check please use:\n";
                echo "php database.php --mariadb_skip_text_character_set_check \n";
                echo "-----------------------------------------------------------------------\n";
exit;
            }
        }
    }
    
}

//
// For mariadb we make sure that the character encoding is utf8mb4
//
function verifyTableCharacterSets()
{
    global $arg_mariadb_skip_text_character_set_check;
    $dbtype = Config::get('db_type');
    
    if( $dbtype == 'mysql' ) {
        
        if( $arg_mariadb_skip_text_character_set_check ) {
            echo "verifyTableCharacterSets() explicitly told to not test database schema character sets... skipping.\n";
            return;
        }
        
        $classes = array_merge(getConstantClasses(), getClasses());
        foreach($classes as $class) {
            verifyTableCharacterSetForTable( $class );
        }
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

//
// Create foreign keys if they are not there already
//
function ensureFK()
{
    $dbtype = Config::get('db_type');

    $fks = array();
    array_push( $fks,
                new DatabaseForeignKey(
                    'users.authid refers to authentications.id',
	            call_user_func('User::getDBTable'), 'UserPreferences_authid', 'authid',
	            call_user_func('Authentication::getDBTable'), 'id' ));
    array_push( $fks,
                new DatabaseForeignKey(
                    'transfers.userid refers to users.id',
	            call_user_func('Transfer::getDBTable'), 'Transfers_userid', 'userid',
	            call_user_func('User::getDBTable'), 'id' ));
    array_push( $fks,
                new DatabaseForeignKey(
                    'guests.userid refers to users.id',
	            call_user_func('Guest::getDBTable'), 'Guests_userid', 'userid',
	            call_user_func('User::getDBTable'), 'id' ));
    array_push( $fks,
                new DatabaseForeignKey(
                    'clientlogs.userid refers to users.id',
	            call_user_func('ClientLog::getDBTable'), 'ClientLog_userid', 'userid',
	            call_user_func('User::getDBTable'), 'id' ));

    array_push( $fks,
                new DatabaseForeignKey(
                    'aggregatestatistics.epochtype refers to dbconstantepochtypes.id',
	            call_user_func('AggregateStatistic::getDBTable'), 'AggregateStatistic_epochtype', 'epochtype',
	            call_user_func('DBConstantEpochType::getDBTable'), 'id' ));
    array_push( $fks,
                new DatabaseForeignKey(
                    'aggregatestatistics.eventtype refers to dbconstanteventtypes.id',
	            call_user_func('AggregateStatistic::getDBTable'), 'AggregateStatistic_eventtype', 'eventtype',
	            call_user_func('DBConstantStatsEvent::getDBTable'), 'id' ));
    array_push( $fks,
                new DatabaseForeignKey(
                    'statlogs.browser refers to dbconstantbrowsertype.id',
	            call_user_func('StatLog::getDBTable'), 'statlogs_browsertype', 'browser',
	            call_user_func('DBConstantBrowserType::getDBTable'), 'id' ));
    array_push( $fks,
                new DatabaseForeignKey(
                    'statlogs.os refers to dbconstantoperatingsystem.id',
	            call_user_func('StatLog::getDBTable'), 'statlogs_operatingsystem', 'os',
	            call_user_func('DBConstantOperatingSystem::getDBTable'), 'id' ));
    array_push( $fks,
                new DatabaseForeignKey(
                    'transfers.password_encoding refers to dbconstantpasswordencoding.id',
	            call_user_func('Transfer::getDBTable'), 'transfer_passwordencoding', 'password_encoding',
	            call_user_func('DBConstantPasswordEncoding::getDBTable'), 'id' ));


    array_push( $fks,
                new DatabaseForeignKey(
                    'downloadonetimepasswords.tid refers to transfers.id',
	            call_user_func('DownloadOneTimePassword::getDBTable'), 'DownloadOneTimePassword_tid', 'tid',
	            call_user_func('Transfer::getDBTable'), 'id' ));
    array_push( $fks,
                new DatabaseForeignKey(
                    'downloadonetimepasswords.rid refers to recipients.id',
	            call_user_func('DownloadOneTimePassword::getDBTable'), 'DownloadOneTimePassword_rid', 'rid',
	            call_user_func('Recipient::getDBTable'), 'id' ));
    
    
    foreach ( $fks as $fk ) {
        $fk->ensure();
    }
    
    

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
            if( $schemaVersion < DatabaseSchemaVersions::VERSION_CURRENT ) {
                $majorMigrationPerformed = true;
            }
            
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
                    // First, ensure the dbid column exists using the same mechanism that would
                    // normally create it
                    $classesToMigrate = array('User','Guest','ClientLog','Transfer');
                    foreach($classesToMigrate as $class) {
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


                    // now we can create the few system default users
                    ensureAuthenticationsTableHasReservedIDs();
                    
                    
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

                    // NOTE NOTE NOTE
                    //
                    // FOREIGN KEY constraints are added at the end of the script
                    // so that column types might change, eg, add not null after migration

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

    verifyTableCharacterSets();
    
    
} catch(Exception $e) {
    echo "Error, Rolling database changes back....\n";
    $dbtype = Config::get('db_type');
    if( $dbtype == 'mysql' ) {
        echo " As this script changes schema items rollback is less useful on MariaDB\n";
        echo '    " MariaDB (...) supports rollback of SQL-data change statements, but not of SQL-Schema statements."  ' . "\n";
        echo "    --  https://mariadb.com/kb/en/rollback/ \n";
        echo "\n";
        echo "as this script performs mostly DDL statements transactions are not used for mariadb\n";
        echo "you should either get a full run of this script or compare the output to a backup\n";
    } else {
        echo " This should leave the database state as it was before you started the script\n";
        DBI::rollBack();
    }
    if( $majorMigrationPerformed ) {
        echo "\n";
        echo "NOTE: As this was a major database schema update you might like to compare with a backup\n";
        echo "\n";
    }        
    $uid = ($e instanceof LoggingException) ? $e->getUid() : 'no available uid';
    echo 'Encountered exception : ' . $e->getMessage() . ', see logs for details (uid: '.$uid.') ...\n';
    exit(1);
}

echo "\n\n";
echo "All core code worked (leaving foreign keys), commit to database starting...\n";
$dbtype = Config::get('db_type');
if( $dbtype != 'mysql' ) {
    DBI::commit();
}
echo "Commit went well, those changes are now permanent\n";



echo "Performing FOREIGN KEY creation...\n";
ensureFK();


echo "Everything went well\n";
echo "Database structure is up to date\n";
