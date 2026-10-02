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
 * Enum like type base
 *
 * Should be used for any limited values option handling
 */
abstract class Enum
{
    /**
     * Defined values cache
     */
    private static $constCache = null;
    
    /**
     * Called child class name
     */
    private static $calledClass = null;
    
    /**
     * Get defined values
     *
     * @return array
     */
    private static function getConstants()
    {
        // Use Reflexion to get constants defined in child class
        if (self::$calledClass != get_called_class() || self::$constCache === null) {
            $reflect = new ReflectionClass(get_called_class());
            self::$constCache = $reflect->getConstants();
        }
        
        return self::$constCache;
    }
    
    /**
     * Check if a constant name is known
     *
     * @param string $name constant name
     * @param bool $strict check case
     *
     * @return bool
     */
    public static function isValidName($name, $strict = false)
    {
        $constants = self::getConstants();
        
        if ($strict) {
            return array_key_exists($name, $constants);
        }
        
        $keys = array_map('strtolower', array_keys($constants));
        return in_array(strtolower($name), $keys);
    }
    
    /**
     * Check if a value is known
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function isValidValue($value)
    {
        $values = array_values(self::getConstants());
        return in_array($value, $values, true);
    }
    
    /**
     * Get defined values (shorthand)
     *
     * @return array
     */
    public static function all()
    {
        return self::getConstants();
    }

    /**
     * get an array of the config keys from all()
     */
    public static function getConfigKeys()
    {
        $constants = self::getConstants();
        $keys = array_map('strtolower', array_keys($constants));
        return $keys;
    }

    /**
     * helper function for logging invalid keys. This returns the prefix
     * string and all the possible config keys for this enum type to help
     * the administrator.
     */
    public static function getConfigKeysAsLogString($msgprefix = ' possible keys are: ')
    {
        $vv = implode(' ', GuestOptions::getConfigKeys());
        return $msgprefix . print_r($vv, true);
    }
}
