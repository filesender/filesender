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
 * Cannot create path exception
 */
class StorageFilesystemCannotCreatePathException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $path
     */
    public function __construct($path) {
        parent::__construct(
            'storage_filesystem_cannot_create_path', // Message to give to the user
            'path = '.$path // Details to log
        );
    }
}

/**
 * File not found
 */
class StorageFilesystemFileNotFoundException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $path
     */
    public function __construct($path) {
        parent::__construct(
            'storage_filesystem_file_not_found', // Message to give to the user
            'path = '.$path // Details to log
        );
    }
}

/**
 * Cannot read exception
 */
class StorageFilesystemCannotReadException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $path
     */
    public function __construct($path) {
        parent::__construct(
            'storage_filesystem_cannot_read', // Message to give to the user
            'path = '.$path // Details to log
        );
    }
}

/**
 * Cannot delete exception
 */
class StorageFilesystemCannotDeleteException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $path
     */
    public function __construct($path) {
        parent::__construct(
            'storage_filesystem_cannot_delete', // Message to give to the user
            'path = '.$path // Details to log
        );
    }
}

/**
 * Cannot write exception
 */
class StorageFilesystemCannotWriteException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $path
     */
    public function __construct($path) {
        parent::__construct(
            'storage_filesystem_cannot_write', // Message to give to the user
            'path = '.$path // Details to log
        );
    }
}

/**
 * Cannot write exception
 */
class StorageFilesystemOutOfSpaceException extends DetailedException {
    /**
     * Constructor
     * 
     * @param string $path
     */
    public function __construct($needed_space, $free_space) {
        parent::__construct(
            'storage_filesystem_out_of_space', // Message to give to the user
            'needed space = '.$needed_space.', free space = '.$free_space // Details to log
        );
    }
}
