<?php

/**
 * Damien Bruchet
 * Date: 2014/08/08
 * Time: 10:45:17
 */



/**
 * Invalid recipeint exception
 */
class InvalidRecipientException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $selector column used to select user
     */
    public function __construct($selector) {
        parent::__construct(
            'notificationmail_invalid_recipient', // Message to give to the user
            $selector // Real message to log
        );
    }
}


/**
 * Invalid address format exception
 */
class InvalidAddressFormatException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $selector column used to select user
     */
    public function __construct($selector) {
        parent::__construct(
            'invalid_address_format', // Message to give to the user
            $selector // Real message to log
        );
    }
}


/**
 * Invalid address format exception
 */
class NoAddressesFoundException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $selector column used to select user
     */
    public function __construct() {
        parent::__construct(
            'no_addresses_found' // Message to give to the user
        );
    }
}








