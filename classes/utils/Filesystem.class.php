<?php

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURF, UNINETT
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
 * *    Neither the name of AARNet, Belnet, HEAnet, SURF and UNINETT nor the
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

require_once(FILESENDER_BASE.'/lib/random_compat/lib/random.php');

/**
 * Filesystem functions holder
 */
class Filesystem
{

    /**
     * Deletes a file
     *
     * @param String $path - full path to file to delete
     * @param File   $file - optional, used only in logging.
     *
     * @throws StorageFilesystemCannotDeleteException
     */
    public static function deleteFile($file_path, File $file = null)
    {
        if (!file_exists($file_path)) {
            return;
        }
        
        if (is_link($file_path)) {
            if (!unlink($file_path)) {
                throw new StorageFilesystemCannotDeleteException($file_path, $file);
            }
            
            return;
        }

        if (ShredFile::shouldUseShredFile()) {
            Logger::info('deleteFile() creating a new shred file for file: ' . $file_path);
            $shredfile = ShredFile::create($file_path);
            $shredfile->save();
            return;
        }
        
        $rm_command = Config::get('storage_filesystem_file_deletion_command');
        
        if ($rm_command) {
            $cmd = str_replace('{path}', escapeshellarg($file_path), $rm_command);
            exec($cmd, $out, $ret);
            
            if ($ret) {
                throw new StorageFilesystemCannotDeleteException($file_path, $file);
            }
        } else {
            if (!unlink($file_path)) {
                throw new StorageFilesystemCannotDeleteException($file_path, $file);
            }
        }
    }

    /**
     * Deletes a whole directory tree
     *
     * @param String $path - full path to delete recursively
     * @param File   $file - optional, used only in logging.
     *
     * @throws StorageFilesystemCannotDeleteException
     */
    public static function deleteTreeRecursive($file_path, File $file = null)
    {
        if (!file_exists($file_path)) {
            return;
        }
        
        if (is_link($file_path)) {
            if (!unlink($file_path)) {
                throw new StorageFilesystemCannotDeleteException($file_path, $file);
            }
            return;
        }
        
        $rm_command = Config::get('storage_filesystem_tree_deletion_command');
        $cmd = str_replace('{path}', escapeshellarg($file_path), $rm_command);
        exec($cmd, $out, $ret);
        
        if ($ret) {
            throw new StorageFilesystemCannotDeleteException($file_path, $file);
        }
    }

    public static function getTempDirectory()
    {
        return Config::get('tmp_path');
    }
}
