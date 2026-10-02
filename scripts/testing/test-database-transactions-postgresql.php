<?php

require_once dirname(__FILE__).'/../../includes/init.php';

Logger::setProcess(ProcessTypes::UPGRADE);

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

$v = DBI::beginTransaction();
echo "beginTransaction returned $v\n";
Database::createView('notable','testview2','select 1');
$v = DBI::rollBack();
echo "rollBack returned $v\n";

echo "The following should fail as it is trying to select from a table that is rolled back\n";
try
{
    $s = DBI::prepare("select * from testview2");
    $s->execute(array());
    
    foreach ($s->fetchAll() as $r) {
        echo "if there are not full transactions then r 1 should be 1 " . $r['1'] . "\n";
    }
    echo "If you are seeing this then transactions DO NOT COVER VIEW CREATION    ERROR WITH DB\n";
} catch(Exception $e) {
    echo "got an exception which was expected...\n";
}


DBI::exec("create table IF NOT EXISTS testtesttesttesttesttable (id int,n1 int, n2 int )");
$v = DBI::beginTransaction();
DBI::exec("ALTER TABLE testtesttesttesttesttable ALTER COLUMN n1 TYPE bigint ");

$v = DBI::rollBack();
