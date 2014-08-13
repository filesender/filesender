<?php

/**
 * Damien Bruchet
 * Date: 2014/08/08
 * Time: 10:45:17
 */



/**
 * Unknown AuditLog exception
 */
class AuditLogNotFoundException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $selector column used to select user
     */
    public function __construct($selector) {
        parent::__construct(
            'auditlog_not_found', // Message to give to the user
            $selector // Real message to log
        );
    }
}

/**
 * Unknown AuditLog exception
 */
class AuditLogNotEnabledException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $selector column used to select user
     */
    public function __construct($selector) {
        parent::__construct(
            'auditlog_not_enabled', // Message to give to the user
            $selector // Real message to log
        );
    }
}



/**
 * Bad AuditLog event type exception
 */
class BadAuditLogEventTypeException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $selector column used to select user
     */
    public function __construct($selector) {
        parent::__construct(
            'bad_auditlog_event_type', // Message to give to the user
            $selector // Real message to log
        );
    }
}