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

// Require environment (fatal)
if (!defined('FILESENDER_BASE')) 
    die('Missing environment');

/**
 * Base REST exception
 */
class RestException extends DetailedException {
    /**
     * Holds context information
     */
    private static $context = array();
    
    /**
     * Set a context element
     * 
     * @param string $key context entry key
     * @param mixed $value context entry
     */
    public static function setContext($key, $value) {
        self::$context[$key] = $value;
    }
    
    /**
     * Constructor
     * 
     * @param string $message message id to return to the interface
     * @param int $code http error code
     * @param mixed $details details about what happened (for logging)
     */
    public function __construct($message, $code = 0, $details = '') {
        parent::__construct(
            $message,
            $details,
            self::$context
        );
    }
}

/**
 * REST authentication required
 */
class RestAuthenticationRequiredException extends RestException {
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct('rest_authentication_required', 403);
    }
}

/**
 * REST admin required
 */
class RestAdminRequiredException extends RestException {
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct('rest_admin_required', 403);
    }
}

/**
 * REST ownership required
 */
class RestOwnershipRequiredException extends RestException {
    /**
     * Constructor
     * 
     * @param string $uid user trying to get access
     * @param mixed $resource the wanted resource selector
     */
    public function __construct($uid, $resource) {
        parent::__construct('rest_ownership_required', 403, array($uid, $resource));
    }
}

/**
 * REST missing parameter
 */
class RestMissingParameterException extends RestException {
    /**
     * Constructor
     * 
     * @param string $name name of the missing parameter
     */
    public function __construct($name) {
        parent::__construct('rest_missing_parameter', 400, $name);
    }
}

/**
 * REST bad parameter
 */
class RestBadParameterException extends RestException {
    /**
     * Constructor
     * 
     * @param string $name name of the bad parameter
     */
    public function __construct($name) {
        parent::__construct('rest_bad_parameter', 400, $name);
    }
}
