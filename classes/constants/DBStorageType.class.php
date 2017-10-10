<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2014, AARNet, Belnet, HEAnet, SURFnet, UNINETT
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * *	Redistributions of source code must retain the above copyright
 * 	notice, this list of conditions and the following disclaimer.
 * *	Redistributions in binary form must reproduce the above copyright
 * 	notice, this list of conditions and the following disclaimer in the
 * 	documentation and/or other materials provided with the distribution.
 * *	Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 'AS IS'
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


/**
 * Class containing issues that might be found when looking at a secondary index
 */
class DBStorageType extends Enum {
    
    const NOTFOUND           = 0;
    const FILESYSTEM         = 1;
    const FILESYSTEMCHUNKED  = 2;

    private static $configClass = null;
    
    public static function getDatamap() {
        return array(
            'type' => 'int',
            'size' => 'small',
            'default' => self::NOTFOUND
        );
    }

    public static function defaultValue() {
        $type = Config::get('storage_type');
        if(!$type)
            throw new ConfigMissingParameterException('storage_type');
        if( $type == 'filesystem' || $type == 'Filesystem' )
            return self::FILESYSTEM;
        if( $type == 'filesystemChunked' || $type == 'FilesystemChunked' )
            return self::FILESYSTEMCHUNKED;
        
        // default
        return self::FILESYSTEM;
    }
    
    public static function defaultClassName() {
        if( $configClass ) {
            return $configClass;
        }
        $type = Config::get('storage_type');
        if(!$type)
            throw new ConfigMissingParameterException('storage_type');
        
        // Build storage underlying class name and check if it exists
        $class = 'Storage'.ucfirst($type);
        
        if(!class_exists($class))
            throw new ConfigBadParameterException('storage_type');

        $configClass = $class;
        return $configClass;
    }
    
    public static function toClassName( $v ) {
        if( $v == self::FILESYSTEM ) {
            return "StorageFilesystem";
        }
        if( $v == self::FILESYSTEMCHUNKED ) {
            return "StorageFilesystemChunked";
        }
        if( $v == self::NOTFOUND ) {
            return self::defaultClassName();
        }
    }
}
