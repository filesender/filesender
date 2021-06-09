<?php
/**
 * This file has implementation for csrfpDefaultLogger class
 */
include __DIR__ ."/LoggerInterface.php";

if (!defined('__CSRF_PROTECTOR_csrfpDefaultLogger_')) {
    // to avoid multiple declaration errors
    define('__CSRF_PROTECTOR_csrfpDefaultLogger_', true);

    class logDirectoryNotFoundException extends \exception {};
    class logFileWriteError extends \exception {};

    /**
     * Default logger class for CSRF Protector
     * This is a file based logger class
     */
    class csrfpDefaultLogger implements LoggerInterface {
        /**
         * Variable: $logDirectory
         * directory for file based logging
         */
        private $logDirectory;

        /**
         * Constructor
         * 
         * Parameters:
         * $path - the path for logs to be stored (relative or absolute)
         * 
         * Returns:
         * void
         *
         * Throws:
         * logDirectoryNotFoundException - if log directory is not found
         */
        function __construct($path) {
            //// Check for relative path
            $this->logDirectory = __DIR__ . "/../" . $path;
            

            //// If the relative log directory path does not
            //// exist try as an absolute path
            if (!is_dir($this->logDirectory)) {
                $this->logDirectory = $path;
            }

            if (!is_dir($this->logDirectory)) {
                throw new logDirectoryNotFoundException("OWASP CSRFProtector: Log Directory Not Found!");
            }
        }

        /**
         * logging method
         *
         * Parameters:
         * $message - the log message
         * $context - context array
         * 
         * Return:
         * void
         *
         * Throws:
         * logFileWriteError - if unable to log an attack
         */
        public function log($message, $context = array()) {
            // Append to the log file, or create it if it does not exist create
            $logFile = fopen($this->logDirectory ."/" . date("m-20y") . ".log", "a+");

            //throw exception if above fopen fails
            if (!$logFile) {
                throw new logFileWriteError("OWASP CSRFProtector: Unable to write to the log file");    
            }

            $context['timestamp'] = time();
            $context['message'] = $message;

            //convert log array to JSON format to be logged
            $context = json_encode($context) .PHP_EOL;

            //append log to the file
            fwrite($logFile, $context);

            //close the file handler
            fclose($logFile);
        }
    }
}