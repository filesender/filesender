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
 * Generic path related filesystem based storage exception
 */
class StorageFilesystemException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $msg_code message code to be used to present error
     * @param string $path
     * @param mixed $file
     */
    public function __construct($msg_code, $path, $file = null)
    {
        $info = array('path' => $path);
        
        if ($file) {
            $info['file'] = (string)$file;
            
            if (is_object($file) && ($file instanceof File)) {
                $info['transfer'] = (string)$file->transfer;
            }
        }
        
        parent::__construct('storage_filesystem_'.$msg_code, $info);
    }
}

/**
 * Cannot create path exception
 */
class StorageFilesystemCannotCreatePathException extends StorageFilesystemException
{
    /**
     * Constructor
     *
     * @param string $path
     * @param File $file
     */
    public function __construct($path, $file = null)
    {
        parent::__construct('cannot_create_path', $path, $file);
    }
}

/**
 * File not found
 */
class StorageFilesystemFileNotFoundException extends StorageFilesystemException
{
    /**
     * Constructor
     *
     * @param string $path
     * @param File $file
     */
    public function __construct($path, $file = null)
    {
        parent::__construct('file_not_found', $path, $file);
    }
}

/**
 * Cannot read exception
 */
class StorageFilesystemCannotReadException extends StorageFilesystemException
{
    /**
     * Constructor
     *
     * @param string $path
     * @param File $file
     */
    public function __construct($path, $file = null)
    {
        parent::__construct('cannot_read', $path, $file);
    }
}

/**
 * Cannot delete exception
 */
class StorageFilesystemCannotDeleteException extends StorageFilesystemException
{
    /**
     * Constructor
     *
     * @param string $path
     * @param File $file
     */
    public function __construct($path, $file = null)
    {
        $e = error_get_last();
        if (self::additionalLoggingDesired('StorageFilesystemCannotDeleteException')) {
            Logger::warn("StorageFilesystemCannotDeleteException reason: " . json_encode($e, JSON_PRETTY_PRINT, 10 ));
        }
        parent::__construct('cannot_delete', $path, $file);
    }
}

/**
 * Cannot write exception
 */
class StorageFilesystemCannotWriteException extends StorageFilesystemException
{
    /**
     * Constructor
     *
     * @param string $path
     * @param File $file
     */
    public function __construct($path, $file = null, $data = null, $offset = 0, $written = 0)
    {
        $e = error_get_last();
        
        if (self::additionalLoggingDesired('StorageFilesystemCannotWriteException')) {
            Logger::warn("StorageFilesystemCannotWriteException reason: " . json_encode($e, JSON_PRETTY_PRINT, 10 ));
            $msg = 'StorageFilesystemCannotWriteException';
            $this->additionalLogFile($msg, $file);
            if ($data) {
                $this->log($msg, 'data size:' . strlen($data));
            }
            $this->log($msg, 'offset:  ' . $offset);
            $this->log($msg, 'written: ' . $written);
        }
        parent::__construct('cannot_write', $path, $file);
    }
}

/**
 * Cannot write exception
 */
class StorageFilesystemOutOfSpaceException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $path
     */
    public function __construct($needed_space, $free_space)
    {
        parent::__construct(
            'storage_filesystem_out_of_space', // Message to give to the user
            array('needed' => $needed_space, 'free' => $free_space) // Details to log
        );
    }
}

/**
 * Bad filesystem name resolver target exception
 */
class StorageFilesystemBadResolverTargetException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $what
     */
    public function __construct($what)
    {
        parent::__construct(
            'storage_filesystem_bad_resolver_target', // Message to give to the user
            array('what' => str_replace(array("\n", "\t"), ' ', print_r($what, true)))
        );
    }
}

/**
 * Cannot resolve filesystem
 */
class StorageFilesystemCannotResolveException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $cmd
     * @param string $ret
     * @param array $out
     */
    public function __construct($cmd, $ret, $out)
    {
        parent::__construct(
            'storage_filesystem_cannot_get_usage', // Message to give to the user
            array('cmd' => $cmd, 'ret' => $ret, 'out' => implode('<nl>', $out)) // Details to log
        );
    }
}

/**
 * Bad disk usage output exception
 */
class StorageFilesystemBadResolverOutputException extends DetailedException
{
    /**
     * Constructor
     *
     * @param string $cmd
     * @param string $line
     */
    public function __construct($cmd, $line)
    {
        parent::__construct(
            'storage_filesystem_bad_usage_output', // Message to give to the user
            array('cmd' => $cmd, 'line' => $line)
        );
    }
}
