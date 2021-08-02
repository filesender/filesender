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
 * Unknown file exception
 */
class FileNotFoundException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $selector column used to select file
     */
    public function __construct($selector)
    {
        parent::__construct(
            'file_not_found', // Message to give to the user
            array('selector' => $selector) // Real message to log
        );
    }
}

/**
 * Unknown file extension exception
 */
class FileExtensionNotAllowedException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $selector column used to select file
     */
    public function __construct($selector)
    {
        parent::__construct(
            'file_extension_not_allowed', // Message to give to the user
            array('selector' => $selector) // Real message to log
        );
    }
}

/**
 * Bad mime type exception
 */
class FileInvalidMimeTypeException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $selector column used to select file
     */
    public function __construct($selector)
    {
        parent::__construct(
            'invalid_mime_type', // Message to give to the user
            array('selector' => $selector) // Real message to log
        );
    }
}



/**
 * Generic identified file exception
 */
class FileException extends DetailedException
{
    /**
     * Constructor
     *
     * @param File $file
     * @param string $msg_code message code to be used to present error
     * @param mixed $internal_details details to log
     * @param mixed $public_details details to give to the user (logged as well)
     */
    public function __construct($file, $msg_code, $internal_details = null, $public_details = null)
    {
        $internal_details = $internal_details ? (array)$internal_details : array();
        $internal_details['file'] = (string)$file;
        $internal_details['transfer'] = (string)$file->transfer;
        
        parent::__construct('file_'.$msg_code, $internal_details, $public_details);
    }
}

/**
 * Unknown file exception
 */
class FileBadHashException extends FileException
{
    /**
     * Constructor
     *
     * @param File $file
     * @param string $hash the bad hash
     */
    public function __construct($file, $hash)
    {
        parent::__construct($file, 'bad_hash', array('hash' => $hash));
    }
}

/**
 * A File has multiple paths where there should only be one file exception
 */
class FileMultiplePathException extends FileException
{
    /**
     * Constructor
     *
     * @param File $file
     * @param string $hash the bad hash
     */
    public function __construct($file, $hash)
    {
        parent::__construct($file, 'multiple_path', array('hash' => $hash));
    }
}

/**
 * Chunk out of bounds exception
 */
class FileChunkOutOfBoundsException extends FileException
{
    /**
     * Constructor
     *
     * @param File $file
     * @param int $offset
     * @param int $length
     * @param int $max
     */
    public function __construct($file, $offset, $length, $max)
    {
        parent::__construct($file, 'chunk_out_of_bounds', array('offset' => $offset, 'length' => $length, 'max' => $max));
    }
}

/**
 * File integrity check failed
 */
class FileIntegrityCheckFailedException extends FileException
{
    /**
     * Constructor
     *
     * @param File $file
     * @param string $reason
     */
    public function __construct($file, $reason)
    {
        parent::__construct($file, 'integrity_check_failed', array('reason' => $reason));
    }
}
