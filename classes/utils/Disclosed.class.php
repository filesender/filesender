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
 * Disclosed application info managment class
 *
 * Handles disclosed info loading, existence checking, values getting
 */
class Disclosed
{
    /**
     * Information cache
     */
    private static $informations = null;
    
    /**
     * Load disclosed information definition
     *
     * @throws ConfigBadParameterException
     */
    private static function load()
    {
        if (!is_null(self::$informations)) {
            return;
        }
        
        self::$informations = array();
        
        // Do we have disclosable ?
        $disclose = Config::get('disclose');
        if (!$disclose) {
            return;
        }
        
        // Cast disclosable definition
        if (!is_array($disclose)) {
            if (!is_string($disclose)) {
                throw new ConfigBadParameterException('disclose');
            }
                
            $d = array_unique(array_filter(array_map('trim', preg_split('/[,;|]/', $disclose))));
            
            $disclose = array();
            foreach ($d as $k) {
                $disclose[$k] = $k;
            }
        }
        
        // Gather disclosable values
        foreach ($disclose as $k => $v) {
            $key = !is_numeric($k) ? $k : (is_string($v) ? $v : null);
            
            if (!$key && is_callable($v)) {
                $data = $v();
                foreach ($data as $k => $v) {
                    self::$informations[$k] = array('value' => $v);
                }
            } elseif (is_callable($v)) {
                self::$informations[$key] = array('callable' => $v);
            } elseif (Config::exists($key)) {
                self::$informations[$key] = array('config' => $v);
            } else { // Specials
                self::$informations[$v] = array('callable' => function () use ($v) {
                    if ($v == 'version') {
                        return Version::get();
                    }
                });
            }
        }
    }
    
    /**
     * Check if info is disclosed
     *
     * @param string $key info key
     *
     * @return bool
     */
    public static function isDisclosed($key)
    {
        // Load disclosables if not already done
        self::load();
        
        return array_key_exists($key, self::$informations);
    }
    
    /**
     * Get a disclosed value
     *
     * @param string $key info key
     *
     * @return mixed
     */
    public static function get($key)
    {
        // Load disclosables if not already done
        self::load();
        
        // Is data disclosable ?
        if (!self::isDisclosed($key)) {
            return null;
        }
        
        // Evaluate value
        if (!array_key_exists('value', self::$informations[$key])) {
            if (array_key_exists('config', self::$informations[$key])) {
                self::$informations[$key]['value'] = Config::get($key);
            } elseif (array_key_exists('callable', self::$informations[$key])) {
                self::$informations[$key]['value'] = call_user_func(self::$informations[$key]['callable']);
            }
        }
        
        return self::$informations[$key]['value'];
    }
    
    /**
     * Get all disclosed informations
     *
     * @return array
     */
    public static function all()
    {
        // Load disclosables if not already done
        self::load();
        
        $info = array();
        
        foreach (self::$informations as $key => $d) {
            $info[$key] = self::get($key);
        }
        
        return $info;
    }
}
