<?php

/**
 * Damien Bruchet
 * Date: 2014/08/08
 * Time: 10:45:17
 */



/**
 * Unknown StatLog exception
 */
class StatLogNotFoundException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $selector column used to select user
     */
    public function __construct($selector) {
        parent::__construct(
            'statlog_not_found', // Message to give to the user
            $selector // Real message to log
        );
    }
}