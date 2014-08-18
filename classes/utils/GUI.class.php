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
 * GUI utilities
 */
class GUI {
    /**
     * Base web path
     */
    private static $path = null;
    
    /**
     * Get stylesheet(s)
     * 
     * @return array of http file path
     */
    public static function stylesheets() {
        $sources = self::filterSources(array(
            'res/css/reset.css',
            'res/css/smoothness/jquery-ui-1.10.2.custom.min.css',
            'res/css/font-awesome.min.css',
            'res/css/default.css',
            'res/skin/styles.css'
        ));
        
        if(!Config::get('cache_scripts_and_styles')) return $sources;
        
        $cached = self::getCachedItem('styles.css', $sources);
        if($cached) return array($cached);
        
        $content = '';
        foreach($sources as $source) {
            $file = FILESENDER_BASE.'/www/'.$source;
            if(!file_exists($file)) continue;
            $content .= "\n".'/* '.$source.' */'."\n".file_get_contents($file)."\n";
        }
        
        $cached = self::cacheItem('styles.css', $content, $sources);
        if($cached) return array($cached);
        
        return $sources;
    }
    
    /**
     * Include stylesheets
     */
    public static function includeStylesheets() {
        foreach(self::path(self::stylesheets()) as $path)
            echo '<link type="text/css" rel="stylesheet" href="'.$path.'" />'."\n";
    }
    
    /**
     * Get script(s)
     * 
     * @return array of http file path
     */
    public static function scripts() {
        $sources = array(
            'res/js/jquery-1.9.1.min.js',
            'res/js/jquery-ui-1.10.2.custom.min.js',
            'res/js/filesender.js',
            'res/js/lang.js'
        );
        
        if(Config::get('terasender_enabled')) $sources[] = 'res/js/terasender.js';
        
        $sources[] = 'res/skin/script.js';
        
        $sources = self::filterSources($sources);
        
        if(!Config::get('cache_scripts_and_styles')) return $sources;
        
        $cached = self::getCachedItem('script.js', $sources);
        if($cached) return array($cached);
        
        $content = '';
        foreach($sources as $source) {
            $file = FILESENDER_BASE.'/www/'.$source;
            if(!file_exists($file)) continue;
            $content .= "\n".'/* '.$source.' */'."\n".file_get_contents($file)."\n";
        }
        
        $cached = self::cacheItem('script.js', $content, $sources);
        if($cached) return array($cached);
        
        return $sources;
    }
    
    /**
     * Include scripts
     */
    public static function includeScripts() {
        foreach(self::path(self::scripts()) as $path)
            echo '<script type="text/javascript" src="'.$path.'"></script>'."\n";
    }
    
    /**
     * Get favicon
     * 
     * @return http file path
     */
    public static function favicon() {
        $locations = self::filterSources(array(
            'res/images/favicon.ico',
            'res/skin/favicon.ico'
        ));
        
        return array_pop($locations);
    }
    
    /**
     * Include favicon
     */
    public static function includeFavicon() {
        $location = self::favicon();
        if(!$location) return;
        
        echo '<link type="image/x-icon" rel="icon" href="'.self::path($location).'" />'."\n";
    }
    
    /**
     * Get logo
     * 
     * @return http file path
     */
    public static function logo() {
        $locations = self::filterSources(array(
            'res/images/logo.png',
            'res/skin/logo.png'
        ));
        
        return array_pop($locations);
    }
    
    /**
     * Include logo
     */
    public static function includeLogo() {
        $location = self::logo();
        if(!$location) return;
        
        echo '<img id="logo" src="'.self::path($location).'" alt="'.Config::get('site_name').'" />'."\n";
    }
    
    /**
     * Compute base path
     * 
     * @param mixed $location location to append
     * 
     * @return string
     */
    public static function path($location = null) {
        if(is_null(self::$path))
            self::$path = preg_replace('`^(https?://)?([^/]+)/`', '/', Config::get('site_url'));
        
        $path = self::$path;
        
        if(is_array($location)) return array_map(function($l) use($path) {
            return $path.$l;
        }, $location);
        
        return $path.$location;
    }
    
    /**
     * Filter sources based on existance
     * 
     * @param array $sources
     * 
     * @return array
     */
    private static function filterSources($sources) {
        return array_filter($sources, function($source) {
            return file_exists(FILESENDER_BASE.'/www/'.$source);
        });
    }
    
    /**
     * Get sources last update
     * 
     * @param array $sources files paths
     * 
     * @return int
     */
    private static function getLastUpdate($sources) {
        return max(array_map(function($source) {
            return filemtime(FILESENDER_BASE.'/www/'.$source);
        }, $sources));
    }
    
    /**
     * Get cached item
     * 
     * @param string $file
     * @param array $sources source file to check against
     * 
     * @return mixed http file path if cached, null if cache not available
     */
    private static function getCachedItem($file, $sources = array()) {
        $cache = FILESENDER_BASE.'/www/res/cache';
        
        if(!file_exists($cache.'/'.$file)) return null;
        
        if($sources) {
            $cfile = $cache.'/'.$file.'.cached';
            if(!file_exists($cfile)) return null;
            
            $min_age = self::getLastUpdate($sources);
            
            list($age, $orig_sources) = explode('|', trim(file_get_contents($cfile)));
            
            if((int)$age < $min_age) return null;
            
            if($orig_sources != implode(',', $sources)) return null;
        }
        
        return 'res/cache/'.$file;
    }
    
    /**
     * Cache item
     * 
     * @param string $file
     * @param string $content
     * 
     * @return string http file path
     */
    private static function cacheItem($file, $content, $sources = array()) {
        $cache = FILESENDER_BASE.'/www/res/cache/'.$file;
        
        if($fh = fopen($cache, 'w')) {
            fwrite($fh, $content);
            fclose($fh);
        }else return null;
        
        if($fh = fopen($cache.'.cached', 'w')) {
            fwrite($fh, time().'|'.implode(',', $sources));
            fclose($fh);
        }
        
        return 'res/cache/'.$file;
    }
}
