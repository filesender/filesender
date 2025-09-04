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
 * Generic identified forward exception
 */
class ForwardException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $msg_code message code to be used to present error
     * @param mixed $internal_details details to log
     * @param mixed $public_details details to give to the user (logged as well)
     */
    public function __construct($msg_code, $internal_details = null, $public_details = null)
    {
        $internal_details = $internal_details ? (array)$internal_details : array();
        parent::__construct('forward_'.$msg_code, $internal_details, $public_details);
    }
}

/**
 * Forward incomplete exception
 */
class ForwardFilesIncompleteException extends ForwardException
{
    /**
     * Constructor
     *
     * @param string $server
     */
    public function __construct()
    {
        parent::__construct($server, 'files_incomplete');
    }
}

/**
 * Forward file uid exist exception
 */
class ForwardFileUidExistException extends ForwardException
{
    /**
     * Constructor
     *
     * @param string $server
     */
    public function __construct()
    {
        parent::__construct($server, 'file_uid_exist');
    }
}

/**
 * Forward file can't rename exception
 */
class ForwardFileCannotRenameException extends ForwardException
{
    /**
     * Constructor
     *
     * @param string $server
     */
    public function __construct($src, $dst)
    {
        parent::__construct($server, 'file_cannot_rename: '.$src.' -> '.$dst);
    }
}

