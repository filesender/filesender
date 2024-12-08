<?php

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2019, AARNet, Belnet, HEAnet, SURFnet, UNINETT
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
 */
class Browser
{
    private static $instance = null;
    protected $ua = '';
    protected $isChrome  = false;
    protected $isFirefox = false;
    protected $isSafari  = false;
    protected $isEdge  = false;
    protected $allowStreamSaver  = false;
    protected $allowFileSystemWritableFileStream = false;
    
    public function __construct()
    {
        $ua = array_key_exists('HTTP_USER_AGENT', $_SERVER) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $this->ua = $ua;

        $this->isChrome  = preg_match('/[Cc]hrome/',  $ua );
        $this->isFirefox = preg_match('/[Ff]irefox/', $ua );
        $this->isSafari  = preg_match('/[Ss]afari/',  $ua );
        $this->isEdge    = preg_match('/[Ee]dge/',    $ua );

        if( Config::get('streamsaver_enabled')) {

            $this->allowStreamSaver = Config::get('streamsaver_on_unknown_browser');
            
            if( $this->isFirefox ) {
                $this->allowStreamSaver = Config::get('streamsaver_on_firefox');
            }
            if( $this->isChrome ) {
                $this->allowStreamSaver = Config::get('streamsaver_on_chrome');
            }
            if( $this->isEdge ) {
                $this->allowStreamSaver = Config::get('streamsaver_on_edge');
            }
            if( $this->isSafari ) {
                $this->allowStreamSaver = Config::get('streamsaver_on_safari');
            }
            
        }

        if( Config::get('filesystemwritablefilestream_enabled')) {
            $this->allowFileSystemWritableFileStream = true;
        }
        
    }
    static function instance()
    {
        if( !self::$instance )
            self::$instance = new Browser();
        
        return self::$instance;
    }
    public function __get($property)
    {
        if (in_array($property, array(
            'isChrome','isFirefox','allowStreamSaver','allowFileSystemWritableFileStream'
        ))) {
            return $this->$property;
        }
    }

};
