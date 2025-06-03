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

/**
 *  Handles access to the basic upload tracking
 */
class LegacyUploadProgress
{
    /**
     * Build tracking session key
     *
     * @param string $key
     *
     * @return string
     */
    public static function getSessionKey($key)
    {
        $prefix = ini_get('session.upload_progress.prefix');
        $name = ini_get('session.upload_progress.name');
        if (!$name) {
            return null;
        }
        
        return $prefix.$key;
    }
    
    /**
     * Build tracking field
     */
    public static function getTrackingInput()
    {
        $name = ini_get('session.upload_progress.name');
        if (!$name) {
            return '';
        }
        
        return '<input type="hidden" data-role="legacy_upload_tracking_key" name="'.$name.'" value="'.uniqid().'" />';
    }
    
    /**
     * Get tracking data
     *
     * @param string $id
     *
     * @return mixed
     */
    public static function getTrackingData($key)
    {
        $session_key = self::getSessionKey($key);
        if (is_null($session_key)) {
            return null;
        }
        if (!isset($_SESSION)) {
            return null;
        }
        if (!array_key_exists($session_key, $_SESSION)) {
            return null;
        }
        
        $data = $_SESSION[$session_key]['files'][0];
        
        // Remove unecessary data
        unset($data['tmp_name']);
        unset($data['field_name']);
        unset($data['name']);
        
        return $data;
    }
}
