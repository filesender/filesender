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
 * *	Redistributions of source code must retain the above copyright
 * 	notice, this list of conditions and the following disclaimer.
 * *	Redistributions in binary form must reproduce the above copyright
 * 	notice, this list of conditions and the following disclaimer in the
 * 	documentation and/or other materials provided with the distribution.
 * *	Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
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
//if(!defined('FILESENDER_BASE')) die('Missing environment');
//define('FILESENDER_BASE', dirname( __DIR__ ));

/**
 * Test suite support functions
 */
class TestSuiteSupport
{

    //
    // Server side callable only, not directly from the test suite
    //
    public static function serverside_guard_prefix()
    {
        $fn = FILESENDER_BASE . "/tmp/testsute-guards/";
        if (!is_dir($fn)) {
            // This has to be widely writable so the web server and user
            // running the test suite can both access the path
            $old = umask(0);
            mkdir($fn, 0777);
            umask($old);
        }
        return $fn;
    }

    //
    // Server side callable only, not directly from the test suite
    //
    public static function serverside_clear($fn)
    {
        $fn = self::serverside_guard_prefix() . $fn;
        if (file_exists($fn)) {
            unlink($fn);
        }
    }

    //
    // Server side callable only, not directly from the test suite
    //
    public static function serverside_clear_all()
    {
        self::deleteDirectory(self::serverside_guard_prefix());
        self::serverside_guard_prefix();
    }

    //
    // Server side callable only, not directly from the test suite
    //
    public static function serverside_guard_first_call($fn)
    {
        $fn = self::serverside_guard_prefix() . $fn;
        if (file_exists($fn)) {
            return false;
        }
        touch($fn);
        return true;
    }

    public static function function_override_clear_all()
    {
        self::changeConfigValue("PUT_PERFORM_TESTSUITE", "''");
    }

    public static function function_override_set($k, $v)
    {
        self::changeConfigValue($k, "'" . $v . "'");
    }

    public static function changeConfigValue($type, $value)
    {
        //read the entire string
        $str=file_get_contents('config/config.php');

        //replace something in the file string
        $str=preg_replace("/\\\$config\['".$type."'\]\s*=\s*(.*);/", "\$config['".$type."'] = $value;", $str, -1, $count);

        if ($count == 0) {
            throw new \Exception($type .' config could not be set to value '. $value ."Regex: /\\\$config\['".$type."'\] = (.*);/\n");
        }

        //write the entire string
        file_put_contents('config/config.php', $str);

        sleep(2);
    }

    public static function deleteDirectory($dir)
    {
        if (file_exists($dir)) {
            $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator(
                $it,
                                                   RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            rmdir($dir);
        }
    }

    public static function evalOverride($key)
    {
        $v = Config::get($key);
        if( strlen($v) ) {
            eval($v);
        }
    }
}
