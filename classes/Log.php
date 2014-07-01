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

// --------------------------------
// Functions for log writing.
// --------------------------------
class Log
{
    private static $instance = null;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    public static function getInstance()
    {
        // Check for both equality and type.
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    // --------------------------------
    // Save data to database 'logs' table.
    // --------------------------------
    public function saveLog($dataItem, $logType, $message)
    {
        global $config;

        $authSaml = AuthSaml::getInstance();

        if ($authSaml->isAuth()) {
            $authAttributes = $authSaml->sAuth();
        } else {
            $authAttributes['saml_uid_attribute'] = '';
        }

        if (isset($dataItem) && isset($dataItem['fileuid'])) {
            $logFileUid = $dataItem['fileuid'];
            $logVoucherUid = $dataItem['filevoucheruid'];
            $logFrom = $dataItem['filefrom'];
            $logTo = $dataItem['fileto'];
            $logDate = date($config['db_dateformat'], time());
            $logFileSize = $dataItem['filesize'];
            $logFileName = $dataItem['fileoriginalname'];
            $logMessage = $message;
            $logAuthUserUid = $authAttributes['saml_uid_attribute'];
            $logFileGroupId = $dataItem['filegroupid'];
            $logFileTrackingCode = $dataItem['filetrackingcode'];
            $logDailySummary = $dataItem['filedailysummary'];
        } else {
            $logFileUid = '';
            $logVoucherUid = '';
            $logFrom = '';
            $logTo = '';
            $logDate = date($config['db_dateformat'], time());
            $logFileSize = 0;
            $logFileName = '';
            $logMessage = $message;
            $logAuthUserUid = $authAttributes["saml_uid_attribute"];
            $logFileGroupId = '';
            $logFileTrackingCode = '';
            $logDailySummary = $config['email_me_daily_statistics_default'] ? 'true' : 'false';
        }

        if ($logType == 'Download') {
            // Swap sender and recipient.
            $temp = $logTo;
            $logTo = $logFrom;
            $logFrom = $temp;
        }

        $statement = $this->db->prepare(
            "INSERT INTO logs (
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
                logfiletrackingcode,
                logdailysummary
            ) VALUES (
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
                :logfiletrackingcode,
                :logdailysummary
            )"
        );

        $statement->bindParam(':logfileuid', $logFileUid);
        $statement->bindParam(':logvoucheruid', $logVoucherUid);
        $statement->bindParam(':logtype', $logType);
        $statement->bindParam(':logfrom', $logFrom);
        $statement->bindParam(':logto', $logTo);
        $statement->bindParam(':logdate', $logDate);
        $statement->bindParam(':logfilesize', $logFileSize);
        $statement->bindParam(':logfilename', $logFileName);
        $statement->bindParam(':logmessage', $logMessage);
        $statement->bindParam(':logauthuseruid', $logAuthUserUid);
        $statement->bindParam(':logfilegroupid', $logFileGroupId);
        $statement->bindParam(':logfiletrackingcode', $logFileTrackingCode);
        $statement->bindParam(':logdailysummary', $logDailySummary);


        $this->db->execute($statement);
    }


    // --------------------------------
    // Log file for individual, client specific logging.
    // TODO: Duplicate of ErrorHandler::logEntry()? Is this needed?
    // --------------------------------
    public function logProcess($client, $message)
    {
        global $config;
        global $cron;

        if ($config['debug'] or $config['client_specific_logging']) {
            $ip = $_SERVER['REMOTE_ADDR']; // Capture IP.

            if ($config['dnslookup'] == true) {
                $domain = GetHostByName($ip);
            } else {
                $domain = '';
            }

            $logExt = ".log.txt";

            // Separate cron and normal logs.
            if (isset($cron) && $cron) {
                $logExt = "-CRON.log.txt";
            }

            $message .= '[' . $ip . '(' . $domain . ')] ';
            $dateRef = date('Ymd');
            $date = date('Y/m/d H:i:s');
            $myFile = $config['log_location'] . $dateRef . '-' . $client . $logExt;
            $fh = fopen($myFile, 'a') or die('cannot open file');

            // Don't print errors on screen when there is no session.
            if (session_id()) {
                $sessionId = session_id();
            } else {
                $sessionId = "none";
            }

            $stringData = $date . ' [Session ID: ' . $sessionId . '] ' . $message . "\n";
            fwrite($fh, $stringData);

            fclose($fh);
            closelog();
        }
    }
}

