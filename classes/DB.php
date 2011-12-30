<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURFnet, UNINETT
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
// uses config db_ settings or overide using single line DNS in config
// Uses PDO to allow mutliple database types

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
		$log =  Log::getInstance();
        if($this->connection){
            return $this->connection;
        }

		$dsn = $this->initDSN();

		try {
		 	$this->connection = new PDO($dsn,$config['db_username'],$config['db_password']);
		}
    	catch(PDOException $e)
    	{
		logEntry($e->getMessage());
    	displayError($e->getMessage());
    	}
		return $this->connection;
    }
	
	public function initDSN()
	{
		 global $config;
		 
		 // check if db_driver_options are available
		 if (!array_key_exists('db_driver_options',$config)) {
				// set default if options don't exist
				$config['db_driver_options'] = "";
			}
		 // use dns if it exists
		 if (array_key_exists('dsn',$config)) {
			 if (
				//These must exist at a minimum
				// may change these to db_???? - ticket to discuss
				(! (array_key_exists('db_username',$config))) ||
				(! (array_key_exists('db_password',$config))) 

			) { 
				throw new DbException ("Incomplete parameter specification for Database, Username and password are required");
			}
			
			return $config['dsn'];
		}
		// default to pgsql if no db_type specified
		if (!array_key_exists('db_type',$config)) {
			
			$config['db_type'] = "pgsql";
		}
		
			//Sanity checking....
			if (
				//These must exist at a minimum
				(! (array_key_exists('db_host',$config))) ||
				(! (array_key_exists('db_database',$config))) ||
				(! (array_key_exists('db_username',$config))) ||
				(! (array_key_exists('db_password',$config))) 

			) { 
				throw new DbException ("Incomplete parameter specification for Database, Please check you config.php");
			}
			// create PDO DSN
			$dbtype = $config['db_type'];
			$dbhost = $config['db_host'];
			$dbdatabase = $config['db_database'];
		 	return "$dbtype:host=$dbhost;dbname=$dbdatabase";
	}

    public function fquery(/* $query, $args */) {
        $args = func_get_args();
        if(isset($args[0]) && is_array($args[0])) $args = $args[0]; // so that args can be passed as an array, as well as seperately.
        $query = $this->buildQuery(array_merge(array($this->connect()), $args));
		$dbcon = $this->connect();
		try
		{
		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
        $result = $dbcon->query($query);
		}
		catch(PDOException $e)
                {
     				displayError($e->getMessage(). " on query: ".$query);
					exit;
                }

		if($result)
            return $result;
        else
			return "";
    }

    public function buildQuery(/* $query, $args */) {
		global $config;
        $args = func_get_args();
        if(isset($args[0]) && is_array($args[0])) $args = $args[0]; // so that args can be passed as an array, as well as seperately.

        $handle = array_shift($args);
        $format = array_shift($args);
		 
        for($i = 0; $i < sizeof($args); $i++) {
			$args[$i] =  $this->connection->quote($args[$i]);
		}
        $query = vsprintf($format, $args);
        return $query;
    }
}

?>
