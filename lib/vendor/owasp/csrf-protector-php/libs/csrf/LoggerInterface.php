<?php
/**
 * This file has implementation for LoggerInterface interface
 */

if (!defined('__CSRF_PROTECTOR_loggerInterface__')) {
    // to avoid multiple declaration errors
    define('__CSRF_PROTECTOR_loggerInterface__', true);

    /**
     * Interface for logger class
     */
    interface LoggerInterface {
        /**
         * logging method
         *
         * Parameters:
         * $message - the log message
         * $context - context array
         * 
         * Return:
         * void
         */
        public function log($message, $context = array());
    }
}