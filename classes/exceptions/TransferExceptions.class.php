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

if (!defined('FILESENDER_BASE')) {        // Require environment (fatal)
    die('Missing environment');
}

/**
 * Unknown transfer exception
 */
class TransferNotFoundException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $selector column used to select transfer
     */
    public function __construct($selector)
    {
        parent::__construct(
            'transfer_not_found', // Message to give to the user
            array('selector' => $selector) // Real message to log
        );
    }
}

/**
 * Bad status exception
 */
class TransferBadStatusException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $status the bad lang status
     */
    public function __construct($status)
    {
        parent::__construct(
            'bad_transfer_status', // Message to give to the user
            array('status' => $status) // Details to log
        );
    }
}

/**
 * File name has bad characters
 */
class TransferFileNameInvalidException extends DetailedException
{
    /**
     * Constructor
     *
     * @param int $max
     */
    public function __construct($name)
    {
        parent::__construct('transfer_file_name_invalid', array('name' => $name));
    }
}

/**
 * Collection name has bad characters
 */
class TransferCollectionNameInvalidException extends DetailedException
{
    /**
     * Constructor
     *
     * @param int $max
     */
    public function __construct($name)
    {
        parent::__construct('transfer_collection_name_invalid', array('name' => $name));
    }
}

/**
 * Missing too many recipients exception
 */
class TransferTooManyRecipientsException extends DetailedException
{
    /**
     * Constructor
     *
     * @param int $wanted
     * @param int $max
     */
    public function __construct($wanted, $max)
    {
        parent::__construct('transfer_too_many_recipients', array('wanted' => $wanted, 'max' => $max));
    }
}

/**
 * Missing recipients exception
 */
class TransferNoRecipientsException extends DetailedException
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('transfer_no_recipients');
    }
}

/**
 * Mandatory encryption subversion attempt
 */
class TransferMustBeEncryptedException extends DetailedException
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('transfer_must_be_encrypted');
    }
}

/**
 * Missing too many files exception
 */
class TransferTooManyFilesException extends DetailedException
{
    /**
     * Constructor
     *
     * @param int $wanted
     * @param int $max
     */
    public function __construct($wanted, $max)
    {
        parent::__construct('transfer_too_many_files', array('wanted' => $wanted, 'max' => $max));
    }
}

/**
 * Missing files exception
 */
class TransferNoFilesException extends DetailedException
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('transfer_no_files');
    }
}

/**
 * Maximum size exeeded exception
 */
class TransferMaximumSizeExceededException extends DetailedException
{
    /**
     * Constructor
     *
     * @param int $size
     * @param int $max
     */
    public function __construct($size, $max)
    {
        parent::__construct('transfer_maximum_size_exceeded', 'size='.$size.' max='.$max);
    }
}

/**
 * Maximum file size exeeded exception
 */
class TransferMaximumFileSizeExceededException extends DetailedException
{
    /**
     * Constructor
     *
     * @param int $size
     * @param int $max
     */
    public function __construct($size, $max)
    {
        parent::__construct('transfer_maximum_file_size_exceeded', 'size='.$size.' max='.$max);
    }
}

/**
 * Maximum file size exeeded exception
 */
class TransferMaximumEncryptedFileSizeExceededException extends DetailedException
{
    /**
     * Constructor
     *
     * @param int $size
     * @param int $max
     */
    public function __construct($size, $max)
    {
        parent::__construct('transfer_maximum_encrypted_file_size_exceeded', 'size='.$size.' max='.$max);
    }
}

/**
 * Validation failed exception
 */
class TransferRejectedException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $reason
     */
    public function __construct($reason)
    {
        parent::__construct('transfer_rejected', null, array('reason' => $reason));
    }
}

/**
 * Host quota exceeded
 */
class TransferHostQuotaExceededException extends DetailedException
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('transfer_host_quota_exceeded');
    }
}

/**
 * User quota exceeded
 */
class TransferUserQuotaExceededException extends DetailedException
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('transfer_user_quota_exceeded');
    }
}

/**
 * Generic identified transfer exception
 */
class TransferException extends DetailedException
{
    /**
     * Constructor
     *
     * @param Transfer $transfer
     * @param string $msg_code message code to be used to present error
     * @param mixed $internal_details details to log
     * @param mixed $public_details details to give to the user (logged as well)
     */
    public function __construct($transfer, $msg_code, $internal_details = null, $public_details = null)
    {
        $internal_details = $internal_details ? (array)$internal_details : array();
        $internal_details['transfer'] = (string)$transfer;
        
        parent::__construct('transfer_'.$msg_code, $internal_details, $public_details);
    }
}

/**
 * Files incomplete exception
 */
class TransferFilesIncompleteException extends TransferException
{
    /**
     * Constructor
     *
     * @param Transfer $transfer
     */
    public function __construct($transfer)
    {
        parent::__construct($transfer, 'files_incomplete');
    }
}

/**
 * We filter out URLs in the personal message
 */
class TransferMessageBodyCanNotIncludeURLsException extends TransferException
{
    /**
     * Constructor
     *
     * @param Transfer $transfer
     */
    public function __construct($transfer)
    {
        parent::__construct($transfer, 'message_can_not_contain_urls');
    }
}

/**
 * Expired
 */
class TransferExpiredException extends TransferException
{
    /**
     * Constructor
     *
     * @param Transfer $transfer
     */
    public function __construct($transfer)
    {
        parent::__construct($transfer, 'expired');
    }
}

/**
 * Presumed Expired, when a recipient token is given but it is not found in the database
 */
class TransferPresumedExpiredException extends TransferException
{
    /**
     * Constructor
     *
     * @param Transfer $transfer
     */
    public function __construct()
    {
        parent::__construct(null, 'presumed_expired');
    }
}

/**
 * Not available
 */
class TransferNotAvailableException extends TransferException
{
    /**
     * Constructor
     *
     * @param Transfer $transfer
     */
    public function __construct($transfer)
    {
        parent::__construct($transfer, 'not_availabe');
    }
}

/**
 * Expiry extension not allowed
 */
class TransferExpiryExtensionNotAllowedException extends TransferException
{
    /**
     * Constructor
     *
     * @param Transfer $transfer
     */
    public function __construct($transfer)
    {
        parent::__construct($transfer, 'expiry_extension_not_allowed');
    }
}

/**
 * Expiry extension count exceeded
 */
class TransferExpiryExtensionCountExceededException extends TransferException
{
    /**
     * Constructor
     *
     * @param Transfer $transfer
     */
    public function __construct($transfer)
    {
        parent::__construct($transfer, 'expiry_extension_count_exceeded');
    }
}
