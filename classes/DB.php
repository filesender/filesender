<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2011, AARNet, HEAnet, SURFnet, UNINETT
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
 * *	Neither the name of AARNet, HEAnet, SURFnet and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
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

// functions for database connection
// uses config.php settings for database access
// postgress only

// Exception class for database errors
class DbException extends Exception {}

class DbConnectException extends DbException {}

class DB {

    private static $instance = NULL;
    public $connection = NULL;

    public static function getInstance() {
        // Check for both equality and type		
        if(self::$instance === NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // database connection 
    public function connect() {
        
		global $config;

        if($this->connection){
            return $this->connection;
        }	
        $this->connection 
            = pg_connect(sprintf("host=%s port=%s user=%s password=%s dbname=%s", 
            $config['pg_host'],
            $config['pg_port'],
            $config['pg_username'],
            $config['pg_password'],
            $config['pg_database']));
        if(!$this->connection)
            throw new DbConnectException(sprintf('$self->connect(): failed to connect to database on %s', $config['pg_host']));
        return $this->connection;
    }

    public function fquery(/* $query, $args */) {
        $args = func_get_args();
        if(isset($args[0]) && is_array($args[0])) $args = $args[0]; // so that args can be passed as an array, as well as seperately.
        $query = $this->buildQuery(array_merge(array($this->connect()), $args));
        $result = $this->query($query);
        if($result)
            return $result;
        else
            throw new DbException(sprintf('$self->fquery(): postgres error: running query: \"%s\"', $query));
    }

    // check if we're connected, and do query
    private function query($query) {
        return $this->doQuery($this->connect(), $query);
    }

    public function buildQuery(/* $query, $args */) {
        $args = func_get_args();
        if(isset($args[0]) && is_array($args[0])) $args = $args[0]; // so that args can be passed as an array, as well as seperately.

        $handle = array_shift($args);
        $format = array_shift($args);

        for($i = 0; $i < sizeof($args); $i++) {
            $args[$i] = pg_escape_string($handle, (string)$args[$i]);
        }

        $query = vsprintf($format, $args);
        return $query;
    }

    // do the actual query
    public function doQuery($handle, $query) {
        $result = pg_query($handle, $query);
        if($result === false)
            throw new DbException("Error executing query: " . $query);
        return $result;
    }

}

?>
