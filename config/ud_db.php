<?php
	// upgrade database tables
	// creates table with coumn if it does not exist
	// table, coumn name, type, default
	$db = DB::getInstance();
	
	createColumn("files", "filegroupuid","varchar(60)","DEFAULT NULL");
	createColumn("logs", "logsent", "BOOLEAN","DEFAULT 0");

	// check column does not exists before adding it
function createColumn($table,$columnName,$type,$default)
{
	$db = DB::getInstance();
	$statement =  $db->fquery("SELECT * FROM ".$table);
	$statement->execute();
	$total_column = $statement->columnCount();
	for ($counter = 0; $counter <= $total_column; $counter ++) {
    $meta = $statement->getColumnMeta($counter);
    if($meta['name'] == $columnName)
	{
	return false;
	}
	}
	$statement =   $db->fquery("ALTER TABLE ".$table." ADD ".$columnName." ".$type." ".$default);
	$statement->execute();
	return true;
}


?>