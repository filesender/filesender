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

define('FILESENDER_BASE', dirname(dirname(__FILE__)));

require_once(FILESENDER_BASE.'/classes/autoload.php');
require_once(FILESENDER_BASE.'/classes/_includes.php');

Logger::setProcess('cli');

/**
 * Create/upgrade Filesender's database
 */

set_error_handler(function($no, $str, $file = '', $line = '') {
    if($no == '2048') return;
    Logger::error('['.$no.'] '.$str.' in '.$file.' at line '.$line);
});

// Get data classes
$classes = array();
foreach(scandir(FILESENDER_BASE.'/classes/data') as $i) {
    if(substr($i, -10) != '.class.php') continue;
    $class = substr($i, 0, -10);
    if($class == 'DBObject') continue;
    $classes[] = $class;
}

foreach($classes as $class) {
    echo 'Checking class '.$class."\n";
    
    $datamap = call_user_func($class.'::getDataMap');
    $table = call_user_func($class.'::getDBTable');
    
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
            
            if(!Database::checkTableColumnFormat($table, $column, $datamap[$column], function($message) {
                echo "\t".$message."\n";
            })) {
                echo 'Column '.$column.' has bad format, updating it'."\n";
                Database::updateTableColumnFormat($table, $column, $datamap[$column]);
            }
        }
    }else{
        echo 'Table is missing, create it'."\n";
        Database::createTable($table, $datamap);
    }
    
    echo 'Done for table '.$table."\n";
}
