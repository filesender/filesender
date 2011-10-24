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
// MDB2 based, tested against postgres and mysql

require_once 'MDB2.php';


// Exception class for database errors
class DBALException extends Exception {}

class DBALConnectException extends DBALException {}

class DBAL {
	
	// database settings	
	//$config['pg_host'] = 'localhost';
	//$config['pg_database'] = 'filesender';
	//$config['pg_port'] = '5432';
	//$config['pg_username'] = 'postgres';
	//$config['pg_password'] = 'postgres';
    private static $instance = NULL;
	private static $dbtype = "unknown";

	private static function initDSN() {
		global $config;
		
		if (array_key_exists('dsn',$config)) {
			return $config['dsn'];
		} 
		
		elseif (array_key_exists('db_host',$config)) {
			global $config;
			//Sanity checking....
			if (
				//These must exist at a minimum
				! (array_key_exists('db_type',$config)) ||
				! (array_key_exists('db_database',$config)) ||
				! (array_key_exists('db_username',$config)) ||
				! (array_key_exists('db_password',$config)) ||
				! (array_key_exists('db_port',$config)) 
			) { 
				throw new DBALException( "Incomplete parameter specification for Postgres, using the deprecated pg_* parameters. Please check you config.php");
			} 
			else {
				if ($config['db_type'] == 'pgsql') { 
					return 'pgsql://'.$config['db_username'].':'.$config['db_password'].'@tcp('.$config['db_host'] .':'.$config['db_port'].')/'.$config['db_database']; 
				}
				elseif ($config['db_type'] == 'mysql') {
					$dsn = 'mysql://'.$config['db_username'].':'.$config['db_password'].'@'.$config['db_host'] .':'.$config['db_port'].'/'.$config['db_database'];
					// logEntry("mysql dsn in initDSN = ". $dsn); // DEBUG
					return $dsn;
				}
			}
		}		
		
		elseif (array_key_exists('db_host',$config)) {
			global $config;
			//Sanity checking....
			if (
				//These must exist at a minimum
				! (array_key_exists('db_type',$config)) ||
				! (array_key_exists('db_database',$config)) ||
				! (array_key_exists('db_username',$config)) ||
				! (array_key_exists('db_password',$config)) 

			) { 
				throw new DBALException( "Incomplete parameter specification for Postgres, using the deprecated pg_* parameters. Please check you config.php");
			}
		}
		//Deprecated mode
		elseif (array_key_exists('pg_host',$config)) {
			global $config;
			//Sanity checking....
			if (
				//These must exist at a minimum
				(! (array_key_exists('pg_database',$config))) ||
				(! (array_key_exists('pg_username',$config))) ||
				(! (array_key_exists('pg_password',$config))) 

			) { 
				throw new DBALException ("Incomplete parameter specification for Postgres, using the deprecated pg_* parameters. Please check you config.php");
			}
			//Set to default values if non-existant
			if(!array_key_exists('pg_host',$config)){
				$config['pg_host'] = 'localhost';
			}
			if(!array_key_exists('pg_port',$config)){
				$config['pg_host'] = '5432';
			}
			//And return the DSN string
			return 'pgsql://'.$config['pg_username'].':'.$config['pg_password'].'@tcp('.$config['pg_host'] .':'.$config['pg_port'].')/'.$config['pg_database'];
		}
		else {
			//This wil end up in the DBALException error message on trying to connect
			throw new DBALException( 'no connection specified');
		}
		
	}

    public static function getInstance() {
		//Initialize the config['dsn'] entry
		global $config;
		$config['dsn'] = self::initDSN();
		
		//We have deprecated postgres specific parameters, but catch this as well
		if(!(array_key_exists('db_dateformat',$config)) && (array_key_exists('postgresdateformat',$config))) {
			$config['db_dateformat'] = $config['postgresdateformat'];
		} 
		//Or set sensible defaults if nothing is present
		else if (!(array_key_exists('db_dateformat',$config))) {
			$config['db_dateformat'] = 'Y-m-d H:i:sP';
		}
		
		// logEntry('DSN  substr 0,5 = ' . substr($config['dsn'],0,5)); // DEBUG
		
		//We have no suitable database connection, or escape function
		switch (substr($config['dsn'],0,5)) {
			
			case 'mysql':
					self::$dbtype = 'mysql';				
				
			case 'pgsql':
					self::$dbtype = 'pgsql';
				
		}
			
		// Check for both equality and type	
        if(self::$instance === NULL) {
            self::$instance = new self();
			
        }
        return self::$instance;
    }

	//This one is used for select like queries and returns a result
    public function query(/* $query, $args */) {
		//First, get our config
		global $config;
		//Next get a connection
		//We use singleton, so that subsequent calls reuse the same connection from the MDB2 factory (preventing connection exhaustion)
		$mdb2 = MDB2::singleton($config['dsn'],array('result_buffering' => false,));
		//$mdb2->setCharset(‘utf8′);
		//Check to see that nothing went wrong while connecting.
		if (PEAR::isError($mdb2)) {
			throw new DBALConnectException(sprintf('MDB2::singleton: failed to connect to database on %s', $config['dsn']));
		}
		$mdb2->setFetchMode(MDB2_FETCHMODE_ASSOC);
		//Now, get our arguments
        $args = func_get_args();
 		switch(func_num_args()) {
			
			//Empty query??
			case 0: 
				//Nothing in, nothing out.
				return array();
			case 1:
				//Directly perform the query, we just have a static string.
				$res = $mdb2->queryAll($args[0]);
				// Always check that result is not an error
				if (PEAR::isError($res)) {
				    throw new DBALException("Error executing query: " . $res->getMessage());
				}
				return $res;
				
				
			default:
				//More than one argument, create a statement using substitution. This automatically escapes the substituted values
				$format = array_shift($args);

				//Now create the final query, but first escape the args
				//using a very long switch cause decent higher order functions don't work
				$safer_args = $args;
				switch (self::$dbtype){
					
					case 'mysql':
						$safer_args = array_map('mysql_real_escape_string',$args);
					
					case 'pgsql':
						$safer_args = array_map('pg_escape_string',$args);
						
					case 'unknown':
						$safer_args = array_map('addslashes',$args);
				}
		        $query = vsprintf($format, $safer_args);
				//...and execute
				$res = $mdb2->queryAll($query);
				// Always check that result is not an error
				if (PEAR::isError($res)) {
				    throw new DBALException("Error executing query: " . $res->getMessage());
				}
				return $res;
		}
    }

	//This one is used fir INSERT, UPDATE, DELETE queries
	public function exec(/* $query, $args */) {
		//First, get our config
		global $config;
		//Next get a connection
		//We use singleton, so that subsequent calls reuse the same connection from the MDB2 factory (preventing connection exhaustion)
		$mdb2 =& MDB2::singleton($config['dsn'],array('result_buffering' => false,));
		//Check to see that nothing went wrong while connecting.
		if (PEAR::isError($mdb2)) {
			throw new DBALConnectException(sprintf('MDB2::singleton: failed to connect to database on %s', $config['dsn']));
		}
		//Now, get our arguments
        $args = func_get_args();
 		switch(func_num_args()) {
			
			//Empty query??
			case 0: 
				//Nothing in, nothing out.
				return array();
				
			case 1:
				//Directly perform the query, we just have a static string.
		        $query = $args[0];
				$res = $mdb2->queryAll($query);
				// Always check that result is not an error
				if (PEAR::isError($res)) {
				    throw new DBALException("Error executing query: " . $res->getMessage());
				}
				return $res;
				
				
			default:
				//More than one argument, create a prepared statement using substitution. This automatically escapes the substituted values
				$format = array_shift($args);
				//Now create the final query, but first escape the args
				//using a very long switch cause decent higher order functions don't work
				$safer_args = $args;
				switch (self::$dbtype){
					
					case 'mysql':
						$safer_args = array_map('mysql_real_escape_string',$args);
					
					case 'pgsql':
						$safer_args = array_map('pg_escape_string',$args);
						
					case 'unknown':
						$safer_args = array_map('addslashes',$args);
				}
		        $query = vsprintf($format, $safer_args);
				//...and execute
				$res = $mdb2->exec($query);
				// Always check that result is not an error
				if (PEAR::isError($res)) {
				    throw new DBALException("Error executing query: " . $res->getMessage());
				}

				//$res is an integer denoting the number of rows affected
				return $res;
		}
	}


}

?>
