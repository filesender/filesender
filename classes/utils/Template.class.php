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
if(!defined('FILESENDER_BASE')) die('Missing environment');

/**
 * Template managment class (resolve, parse, render)
 */
class Template {
    /**
     * Path cache
     */
    private static $templates = null;
    
    /**
     * Resolve template id to path
     * 
     * @param string $id template id
     * 
     * @return string the path
     */
    private static function resolve($id) {
        foreach(array('config/templates', 'templates') as $location) {
            $location = FILESENDER_BASE.'/'.$location;
            if(!is_dir($location)) continue;
            
            if(file_exists($location.'/'.$id.'.php'))
                return $location.'/'.$id.'.php';
        }
        
        throw new TemplateNotFoundException($id);
    }
    
    /**
     * Process a template (catch displayed content)
     * 
     * @param string $id template id
     * @param array $vars template variables
     * 
     * @return string parsed template content
     */
    public static function process($id, $vars = array()) {
        $addctx = true;
        if(substr($id, 0, 1) == '!') {
            $addctx = false;
            $id = substr($id, 1);
        }
        
        $path = self::resolve($id);
        
        $renderer = function($path, $vars) {
            foreach($vars as $k => $v) $$k = $v;
            include $path;
        };
        
        ob_start();
        $renderer($path, $vars);
        $content = ob_get_clean();
        
        // Translation syntax
        $content = preg_replace_callback('`\{(loc|tr|translate):([^}]+)\}`', function($m) {
            return (string)Lang::translate($m[2]);
        }, $content);
        
        // Config syntax
        $content = preg_replace_callback('`\{(cfg|conf|config):([^}]+)\}`', function($m) {
            return Config::get($m[2]);
        }, $content);
        
        // Image syntax
        $content = preg_replace_callback('`\{(img|image):([^}]+)\}`', function($m) {
            return GUI::path('res/images/'.$m[2]);
        }, $content);
        
        // Path syntax
        $content = preg_replace_callback('`\{(path):([^}]+)\}`', function($m) {
            return GUI::path($m[2]);
        }, $content);
        
        if($addctx) $content = "\n".'<!-- template:'.$id.' start -->'."\n".$content."\n".'<!-- template:'.$id.' end -->'."\n";
        
        return $content;
    }
    
    /**
     * Display a template (catch displayed content)
     * 
     * @param string $id template id
     * @param array $vars template variables
     * 
     * @return string parsed template content
     */
    public static function display($id, $vars = array()) {
        echo self::process($id, $vars);
    }
}
