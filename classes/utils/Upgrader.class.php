<?php

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2014, AARNet, Belnet, HEAnet, SURF, UNINETT
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
 * *	Neither the name of AARNet, Belnet, HEAnet, SURF and UNINETT nor the
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

// Require environment (fatal)
if (!defined('FILESENDER_BASE')) {
    die('Missing environment');
}

class Upgrader
{
    /**
     * Tasks to run before and after database update
     */
    private static $tasks = array('pre' => array(), 'post' => array());
    
    /**
     * Look for things to do and load them
     *
     * @param string $from starting version
     * @param string $to target version
     */
    private static function load($from, $to)
    {
        $path = FILESENDER_BASE.'/scripts/upgrade/versions/';
        $files = array();
        foreach (scandir($path) as $i) {
            if (!preg_match('`^upgrade_([0-9]+(?:\.[0-9]+(?:[abr]c?)?)?)_([0-9]+(?:\.[0-9]+(?:[abr]c?)?)?)\.php$`', $i, $m)) {
                continue;
            }
            
            if (version_compare($m[1], $from) < 0) {
                continue;
            } // Starting below from
            if (version_compare($m[2], $to) > 0) {
                continue;
            } // Starting below from
            
            if (version_compare($m[1], $m[2]) >= 0) {
                throw new Exception('Upgrade script '.$i.' is badly designed');
            }
            
            $files[] = array(
                'path' => $path.$i,
                'from' => $m[1],
                'to' => $m[2]
            );
        }
        
        if (!count($files)) {
            throw new Exception('No upgrading tasks found, maybe this is ok, maybe not ...');
        }
        
        usort($files, function ($a, $b) {
            $fvc = version_compare($a['from'], $b['from']);
            
            return ($fvc == 0) ? version_compare($a['to'], $b['to']) : $fvc;
        });
        
        // Scope insulation
        $inc = function ($path) {
            include $path;
        };
        
        foreach ($files as $file) {
            $inc($file['path']);
        }
    }
    
    /**
     * Registers task
     */
    public static function register($position, $task)
    {
        if (!in_array($position, array('pre', 'post'))) {
            throw new Exception('Position must be pre or post');
        }
        
        self::$tasks[$position][] = $task;
    }
    
    /**
     * Run the upgrade
     */
    public static function run()
    {
        echo 'Starting upgrade'."\n";
        
        $diff = Version::compare();
        
        if ($diff > 0) {
            throw new Exception('Code version is older than data version, this is not normal, exiting');
        }
        
        if ($diff == 0) {
            echo 'Already up to date, no need to upgrade'."\n";
            return;
        }
        
        echo 'Upgrading from '.Version::running().' to '.Version::code()."\n";
        
        self::load(Version::running(), Version::code());
        
        // Run pre tasks
        echo 'Running '.count(self::$tasks['pre']).' pre tasks'."\n";
        foreach (self::$tasks['pre'] as $task) {
            $task();
        }
        
        // Upgrade database structure
        echo 'Upgrading database structure'."\n";
        include FILESENDER_BASE.'/scripts/upgrade/database.php';
        
        // Run post tasks
        echo 'Running '.count(self::$tasks['post']).' post tasks'."\n";
        foreach (self::$tasks['post'] as $task) {
            $task();
        }
        
        // Update version number
        //Version::update();
        
        echo 'Done upgrading'."\n";
    }
}
