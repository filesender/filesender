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

// Require environment (fatal)
if (!defined('FILESENDER_BASE')) {
    die('Missing environment');
}

/**
 * A program that can inspect a given file and record findings in the AVResult object/tabble
 */
abstract class AVProgram 
{
    static private $allPrograms = null;
    static private $programList = null;
    abstract public function inspect( $file );

    protected $name = "";
    protected $url  = "";
    protected $matchlist = array();
    protected $bytesToConsider = 8*1024;

    public static function getActiveProgramList()
    {
        $desiredList = Config::getArray("avprogram_list");
        print_r($desiredList);
        
        if( !self::$allPrograms ) {
            self::$allPrograms = array(
                DBConstantAVProgram::TEST         => new AVProgramTest(),
                DBConstantAVProgram::ALWAYS_FAIL  => new AVProgramAlwaysFail(),
                DBConstantAVProgram::ALWAYS_PASS  => new AVProgramAlwaysPass(),
                DBConstantAVProgram::ALWAYS_ERROR => new AVProgramAlwaysError(),
                DBConstantAVProgram::URL          => new AVProgramURL(),
                DBConstantAVProgram::TOOBIG       => new AVProgramTooBig(),
                DBConstantAVProgram::MIME         => new AVProgramMIME(),
                DBConstantAVProgram::ENCRYPTED    => new AVProgramEncrypted(),
            );
        }
        if( !self::$programList ) {
            foreach( self::$allPrograms as $name => $obj ) {
                if( in_array( $name, $desiredList ) || array_key_exists( $name, $desiredList )) {
                    // check for parameters
                    if( array_key_exists( $name, $desiredList )) {
                        if( is_array($desiredList[$name])) {
                            $obj = clone $obj;
                            $a = $desiredList[$name];
                            if( array_key_exists('name',$a)) {
                                $obj->name = $a['name'];
                            }
                            if( array_key_exists('url',$a)) {
                                $obj->url = $a['url'];
                            }
                            if( array_key_exists('matchlist',$a)) {
                                $obj->matchlist = $a['matchlist'];
                            }
                            if( array_key_exists('bytesToConsider',$a)) {
                                // Get bytesToConsider with explicit min value.
                                $obj->bytesToConsider = Utilities::clampMin($a['bytesToConsider'],
                                                                            $obj->bytesToConsider);
                            }
                            echo " bytesToConsider  $obj->bytesToConsider \n";
                        }
                    }
                    self::$programList[] = $obj;
                }
            }
        }
        return self::$programList;
    }
};

