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
// Error handling functions.
// Catch errors (and don't display them) unless debug is true.
// Log to syslog and to config log folder.
// --------------------------------

if (Config::get('displayerrors')) {
    ini_set('display_errors', 'On');
} else {
    ini_set('display_errors', 'Off');
}

if (Config::get('debug') == true || Config::get('debug') == 1) {
    // If debug mode is on then set the custom error handler.
    ini_set('log_errors', 'On');

    if (defined('E_DEPRECATED')) {
        set_error_handler('customError', E_ALL & ~E_DEPRECATED);
    } else {
        set_error_handler('customError', E_ALL);
    }

    set_exception_handler('customException');
}

function customException(Exception $exception)
{
    $exceptionMsg = sprintf(
        'Exception: [%s] %s : %s [%s] ',
        $exception->getCode(),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine()
    );

    syslog((int)$exception->getCode(), $exceptionMsg); // Log to syslog.
    logEntry($exceptionMsg, 'E_ERROR'); // Log to local log file.
    exit;
}

function customError($errNum, $errStr, $errFile, $errLine)
{
    $errMsg = "Error: [$errNum] $errStr : $errFile [$errLine] ";

    syslog($errNum, $errMsg); // Log to syslog.
    logEntry($errMsg, "E_ERROR"); // Log to local log file.
    return;
}

function logEntry($message, $type = 'E_NOTICE')
{
    global $cron, $log;

    $message = $type . ': ' . $message;

    if (Config::get('debug') && $type == 'E_NOTICE' || $type == 'E_ERROR') {
        if (Config::exists('log_location')) {
            date_default_timezone_set(Config::get('Default_TimeZone'));

            if (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR']; // Capture IP.

                if (Config::get('dnslookup') == true) {
                    $domain = GetHostByName($ip);
                } else {
                    $domain = '';
                }
            } else {
                $ip = 'none';
                $domain = 'none';
            }

            $logExt = '.log.txt';

            if (isset($cron) && $cron) {
                // Separate CRON logs from normal logs.
                $logExt = '-CRON.log.txt';
            }

            $message .= '[' . $ip . '(' . $domain . ')] ';
            $dateRef = date('Ymd');
            $date = date('Y/m/d H:i:s');
            $myFile = Config::get('log_location') . $dateRef . $logExt;
            $fh = fopen($myFile, 'a') or die("can't open file");

            // Don't print errors on screen when there is no session.
            if (session_id()) {
                $sessionId = session_id();
            } else {
                $sessionId = 'none';
            }

            $stringData = $date . ' [Session ID: ' . $sessionId . '] ' . $message . "\n";

            fwrite($fh, $stringData);
            fclose($fh);
            closelog();
            
             // write error log to database
            if ($type == 'E_ERROR') {
               $log->saveLog(NULL, "Error", $date . ":". $message);
            }
        }
    }
}

function displayError($errorMessage, $detailedErrorMessage)
{

    echo '<br /><div id="errmessage">' . htmlspecialchars($errorMessage) . '</div>';

    if (Config::get('displayerrors')) {
        echo '<br /><div id="errmessage">' . htmlspecialchars($detailedErrorMessage) . '</div>';
    }
}

