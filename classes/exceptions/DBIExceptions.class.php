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
 * *    Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 * *    Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in the
 *     documentation and/or other materials provided with the distribution.
 * *    Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 *     names of its contributors may be used to endorse or promote products
 *     derived from this software without specific prior written permission.
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

if (!defined('FILESENDER_BASE'))        // Require environment (fatal)
    die('Missing environment');

/**
 * Misc connexion exception
 */
class DBIConnexionException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $message error message
     */
    public function __construct($message) {
        parent::__construct(
            'failed_to_connect_to_database', // Message to give to the user
            $message // Details to log
        );
    }
}

/**
 * Missing configuration parameter exception
 */
class DBIConnexionMissingParameterException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $parameter name of the required parameter which is missing
     */
    public function __construct($parameter) {
        parent::__construct(
            'dbi_missing_parameter',
            array('parameter' => $parameter)
        );
    }
}

/**
 * Usage exception
 */
class DBIUsageException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $message error message
     */
    public function __construct($message, $details = null) {
        parent::__construct(
            'database_access_failure', // Message to give to the user
            array($message, $details) // Details to log
        );
    }
}




/**
 * Unimplemented case exception, these are thrown by DBLayer when a database
 * backend is being used where there is no explicit code to do something for that
 * database type.
 */
class DBIBackendExplicitHandlerUnimplementedException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $message error message
     */
    public function __construct($message, $details = null) {
        parent::__construct(
            'database_access_failure', // Message to give to the user
            array($message, $details) // Details to log
        );
    }
}
