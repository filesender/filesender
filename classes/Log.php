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

/*
 * log functions
 */


class Log {

    private static $instance = NULL;

    public static function getInstance() {
        // Check for both equality and type		
        if(self::$instance === NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    } 
	
	public function __construct() {
	  $this->db = DB::getInstance();
	 }
    //--------------------------------------- NOTE PDO this
    // Save Log Data
    //
    public function saveLog($dataitem,$logType,$message){

		$db = DB::getInstance();
		
        global $config;

        $authsaml = AuthSaml::getInstance();
        if( $authsaml->isAuth()) {
            $authAttributes = $authsaml->sAuth();
        } else {
            $authAttributes["saml_uid_attribute"] = "";
        }
        //$dbCheck = DB_Input_Checks::getInstance();


        // If authenticated also add authID to log
        // add os, browser and html5 version to log message
        if(isset($dataitem['fileuid'])) 
        {
            $logfileuid	= $dataitem['fileuid'];
            $logvoucheruid	= $dataitem['filevoucheruid'];
            $logtype	= $logType;
            $logfrom	= $dataitem['filefrom'];
            $logto	= $dataitem['fileto'];
            $logdate	= date($config['db_dateformat'], time());//use timestamp with timezone $dbCheck->checkString(pg_escape_string($dataitem['logdate']));
            $logfilesize	= $dataitem['filesize'];
            $logfilename	= $dataitem['fileoriginalname'];
            $logmessage	= $message;
            $logauthuseruid	= $authAttributes["saml_uid_attribute"];
            $logfilegroupid = $dataitem['filegroupid'];
            $logfiletrackingcode = $dataitem['filetrackingcode'];
        } else {
            $logfileuid	= "";
            $logvoucheruid	= "";
            $logtype	= $logType;
            $logfrom	= "";
            $logto	= "";
            $logdate	= date($config['db_dateformat'], time());//use timestamp with timezone $dbCheck->checkString(pg_escape_string($dataitem['logdate']));
            $logfilesize	= "";
            $logfilename	= "";
            $logmessage	= $message;
            $logauthuseruid	= $authAttributes["saml_uid_attribute"];
            $logfilegroupid = "";
            $logfiletrackingcode = "";
        }
		
		$pdo = $this->db->connect();
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set Errorhandling to Exception
		$statement = $pdo->prepare("INSERT INTO 
            logs 
            (
                logfileuid,
                logvoucheruid, 
                logtype , 
                logfrom, 
                logto, 
                logdate, 
                logfilesize, 
                logfilename, 
                logmessage,
                logauthuseruid,
                logfilegroupid,
                logfiletrackingcode
            ) 
            VALUES 
            (
                :logfileuid,
                :logvoucheruid, 
                :logtype , 
                :logfrom, 
                :logto, 
                :logdate, 
                :logfilesize, 
                :logfilename, 
                :logmessage,
                :logauthuseruid,
                :logfilegroupid,
                :logfiletrackingcode
            )");
			
			$statement->bindParam(':logfileuid',$logfileuid);
			$statement->bindParam(':logvoucheruid',$logvoucheruid); 
			$statement->bindParam(':logtype', $logtype);
			$statement->bindParam(':logfrom', $logfrom);
			$statement->bindParam(':logto', $logto);
			$statement->bindParam(':logdate',$logdate); 
			$statement->bindParam(':logfilesize', $logfilesize);
			$statement->bindParam(':logfilename', $logfilename);
			$statement->bindParam(':logmessage',$logmessage);
			$statement->bindParam(':logauthuseruid',$logauthuseruid);
            $statement->bindParam(':logfilegroupid',$logfilegroupid);
            $statement->bindParam(':logfiletrackingcode',$logfiletrackingcode);

		
		try 
		{ 	
			$statement->execute(); 
			 return true;
		}
		catch(PDOException $e)
		{ 
			displayError(lang("_ERROR_CONTACT_ADMIN"),$e->getMessage()); 
			return  false;
		}
    }

    // logfile for individual client specific logging
    // calls to this function are form glex/flash if client specific logging is on

    public function logProcess($client,$message)
    {
        global $config;
        global $cron;

        if($config["debug"] or $config["client_specific_logging"])
        {
            $ip = $_SERVER['REMOTE_ADDR']; //capture IP

            if($config['dnslookup'] == true) {
                $domain = GetHostByName($ip);
            } else {
                $domain = "";
            }

            $logext = ".log.txt";
            // seperate cron and normal logs
            if(isset($cron) && $cron) { $logext = "-CRON.log.txt";}

            $message .= "[".$ip."(".$domain.")] ";
            $dateref = date("Ymd");
            $data = date("Y/m/d H:i:s");
            $myFile = $config['log_location'].$dateref."-".$client.$logext;
            $fh = fopen($myFile, 'a') or die("can't open file");
            // don't print errors on screen when there is no session.
            if(session_id()){
                $sessionId = session_id();
            } else {
                $sessionId = "none";
            }
            $stringData = $data.' [Session ID: '.$sessionId.'] '.$message."\n";
            fwrite($fh, $stringData);
            fclose($fh);
            closelog();
        }
    }
}

?>
