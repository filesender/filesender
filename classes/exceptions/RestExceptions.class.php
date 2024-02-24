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
if (!defined('FILESENDER_BASE')) {
    die('Missing environment');
}

/**
 * Base REST exception
 */
class RestException extends DetailedException
{
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
    public static function setContext($key, $value)
    {
        self::$context[$key] = $value;
    }
    
    /**
     * Constructor
     *
     * @param string $message message id to return to the interface
     * @param int $code http error code
     * @param mixed $details details about what happened (for logging)
     */
    public function __construct($message, $code = 0, $details = '')
    {
        parent::__construct(
            $message,
            $details,
            self::$context
        );
        $this->code = $code;
    }
}

/**
 * security token is invalid
 */
class RestInvalidSecurityTokenException extends RestException
{
    /**
     * Constructor
     *
     * @param mixed $details details about what happened (for logging)
     */
    public function __construct($details = '')
    {
        parent::__construct(
            'rest_security_token_did_not_match',
            400,
            $details
        );
    }
}

/**
 * xsrf token is invalid
 */
class RestInvalidXSRFTokenException extends RestException
{
    /**
     * Constructor
     *
     * @param mixed $details details about what happened (for logging)
     */
    public function __construct($details = '')
    {
        parent::__construct(
            'rest_xsrf_token_did_not_match',
            400,
            $details
        );
    }
}

/**
 * REST authentication required
 */
class RestAuthenticationRequiredException extends RestException
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('rest_authentication_required', 403);
    }
}

/**
 * REST roundtrip tokens to not match expected value
 */
class RestRoundTripTokensInvalidException extends RestException
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('rest_roundtrip_token_invalid', 403);
    }
}

/**
 * REST admin required
 */
class RestAdminRequiredException extends RestException
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('rest_admin_required', 403);
    }
}

/**
 * REST ownership required
 */
class RestOwnershipRequiredException extends RestException
{
    /**
     * Constructor
     *
     * @param string $uid user trying to get access
     * @param mixed $resource the wanted resource selector
     */
    public function __construct($uid, $resource)
    {
        parent::__construct('rest_ownership_required', 403, array('uid' => $uid, 'ressource' => $resource));
    }
}

/**
 * Used when Transfer::havePermission() returns false. 
 * Similar to throwing RestOwnershipRequiredException but
 * user info does not need to be passed it is taken from 
 * active environment.
 */
class RestTransferPermissionRequiredException extends RestException
{
    /**
     * Constructor
     *
     * @param mixed $resource the wanted resource selector
     */
    public function __construct($resource)
    {
        $user = Auth::user();
        $uid = $user->id;
        if (Auth::isGuest()) {
            $guest = AuthGuest::getGuest();
            $uid = $guest->id;
        }
        parent::__construct('rest_ownership_required', 403, array('uid' => $uid, 'ressource' => $resource));
    }
}

/**
 * REST missing parameter
 */
class RestMissingParameterException extends RestException
{
    /**
     * Constructor
     *
     * @param string $name name of the missing parameter
     */
    public function __construct($name)
    {
        parent::__construct('rest_missing_parameter', 400, array('parameter' => $name));
    }
}

/**
 * REST use POST for this feature now
 */
class RestUsePOSTException extends RestException
{
    /**
     * Constructor
     *
     * @param string $name name of the missing parameter
     */
    public function __construct()
    {
        parent::__construct('rest_use_post', 403, array());
    }
}

/**
 * REST bad parameter
 */
class RestBadParameterException extends RestException
{
    /**
     * Constructor
     *
     * @param string $name name of the bad parameter
     */
    public function __construct($name)
    {
        parent::__construct('rest_bad_parameter', 400, array('parameter' => $name));
    }
}

/**
 * REST sanity check failed
 */
class RestSanityCheckFailedException extends RestException
{
    /**
     * Constructor
     *
     * @param string $check name of the check
     * @param mixed $value value of the bad data
     * @param mixed $expected expected value
     * @param file   the file   that caused to error (optional, log only)
     * @param client the client that caused to error (optional, log only)
     */
    public function __construct($check, $value, $expected, $file = null, $client = null)
    {

        // extra logging options for the admin
        if (self::additionalLoggingDesired('RestSanityCheckFailedException')) {
            if ($file) {
                $this->log('EXCEPT-REST-CHUNK-SIZE', 'file size:' . $file->size);
                $this->log('EXCEPT-REST-CHUNK-SIZE', 'file name:' . $file->name);
                $this->log('EXCEPT-REST-CHUNK-SIZE', 'file uid:' . $file->uid);
            }
            if ($client) {
                if ($client['X-Filesender-Chunk-Offset']) {
                    $this->log('EXCEPT-REST-CHUNK-SIZE', 'chunk offset:' . $client['X-Filesender-Chunk-Offset']);
                }
                if ($client['X-Filesender-Chunk-Size']) {
                    $this->log('EXCEPT-REST-CHUNK-SIZE', 'chunk size:' . $client['X-Filesender-Chunk-Size']);
                }
                if ($_SERVER['HTTP_USER_AGENT']) {
                    $this->log('EXCEPT-REST-CHUNK-SIZE', 'user agent:' . $_SERVER['HTTP_USER_AGENT']);
                }
            }
        }
        parent::__construct('rest_sanity_check_failed', 400, 'check "'.$check.'", "'.$expected.'" value was expected but got "'.$value.'" instead');
    }
}

/**
 * Cannot add data to complete transfer exception
 */
class RestCannotAddDataToCompleteTransferException extends RestException
{
    /**
     * Constructor
     *
     * @param int $target_type
     * @param int $target_id
     */
    public function __construct($target_type, $target_id)
    {
        parent::__construct('cannot_add_data_to_complete_transfer', 400, array('target_type' => $target_type, 'target_id' => $target_id));
    }
}

/**
 * REST error if the user or guest can not be found.
 */
class RestUnknownPrincipalException extends RestException
{
    /**
     * Constructor
     *
     * @param string $name name of the missing parameter
     */
    public function __construct()
    {
        parent::__construct('rest_unknown_principal', 404, array());
    }
}


/**
 * REST error if the data sent is too old
 */
class RestDataStaleException extends RestException
{
    /**
     * Constructor
     *
     * @param string $name name of the missing parameter
     */
    public function __construct()
    {
        parent::__construct('rest_data_stale', 404, array());
    }
}
