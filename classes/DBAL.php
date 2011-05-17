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

    private static $instance = NULL;

    public static function getInstance() {
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
				//Directly perform the query, we just have a static string
				$res = $mdb2->queryAll($args[0]);
				// Always check that result is not an error
				if (PEAR::isError($res)) {
				    throw new DBALException("Error executing query: " . $res->getMessage());
				}
				//$res is a two-dimensional array, with each second dimension an associative array as per the query
				return $res;
				
				
			default:
				//More than one argument, create a prepared statement using substitution. This automatically escapes the substituted values
				$format = array_shift($args);
				//Loop over the remaining fields, and quote them
		        for($i = 0; $i < sizeof($args); $i++) {
		            $args[$i] = $mdb2->quote($args[$i]);
		        }
				//Now create the final query
		        $query = vsprintf($format, $args);
				//...and execute
				$res = $mdb2->queryAll($query);
				// Always check that result is not an error
				if (PEAR::isError($res)) {
				    throw new DBALException("Error executing query: " . $res->getMessage());
				}

				//$res is a two-dimensional array, with each second dimension an associative array as per the query
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
				//Directly perform the query, we just have a static string
				$res = $mdb2->exec($args[0]);
				// Always check that result is not an error
				if (PEAR::isError($res)) {
				    throw new DBALException("Error executing query: " . $res->getMessage());
				}
				//$res is an integer denoting the number of rows affected
				return $res;
				
				
			default:
				//More than one argument, create a prepared statement using substitution. This automatically escapes the substituted values
				$format = array_shift($args);
				//Loop over the remaining fields, and quote them
		        for($i = 0; $i < sizeof($args); $i++) {
		            $args[$i] = $mdb2->quote($args[$i]);
		        }
				//Now create the final query
		        $query = vsprintf($format, $args);
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
