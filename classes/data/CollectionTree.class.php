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
 *  Represents a directory tree Collection of subdirs and files
 *  It creates a File of mime type 'text/directory' so
 *  a uuid can be associated with a CollectionTree
 */
class CollectionTree extends Collection
{
    const FILE_MIME_TYPE = 'text/directory';
    
    /**
     * Properties
     */
    protected $uid = null;
   
    /**
     * Related objects cache
     */
    protected $fileCache = null;
    
    /**
     * Loads the extra objects associated with a Collection of type
     *
     * @throws TreeFileCollectionException
     */
    protected function loadInfo()
    {
        if (is_null($this->filesCache)) {
            $this->filesCache = FileCollection::fromCollection($this);
        }
    
        $this->fileCache = File::fromId(key($this->filesCache));
        $this->uid = $this->fileCache->uid;
    }

    /**
     * Process the info value on a newly created CollectionTree
     */
    protected function processInfo()
    {
        $this->fileCache = $this->transfer->addFile($this->info, 0, CollectionTree::FILE_MIME_TYPE);
        $this->uid = $this->fileCache->uid;
        $this->addFile($this->fileCache);
    }

    /**
     * Getter
     *
     * @param string $property property to get
     *
     * @throws PropertyAccessException
     *
     * @return property value
     */
    public function __get($property)
    {
        if (in_array($property, array(
            'uid', 'file'
        ))) {
            if (is_null($this->uid)) {
                $this->loadInfo();
            }
               
            if ($property == 'file') {
                return $this->fileCache;
            } else {
                return $this->$property;
            }
        }
        return parent::__get($property);
    }
}

