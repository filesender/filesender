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
     * Current application page
     */
    private static $current_page = null;
    
    /**
     * Application pages allowed to the user
     */
    private static $allowed_pages = null;
    
    /**
     * Get stylesheet(s)
     * 
     * @return array of http file path
     */
    public static function stylesheets() {
        return self::filterSources(array(
            'lib/reset/reset.css',
            'lib/jquery/smoothness/jquery-ui-1.10.2.custom.min.css',
            'lib/font-awesome/css/font-awesome.min.css',
            'css/default.css',
            'skin/styles.css'
        ));
    }
    
    /**
     * Include stylesheets
     */
    public static function includeStylesheets() {
        foreach(self::path(self::stylesheets()) as $path)
            echo '<link type="text/css" rel="stylesheet" href="'.$path.'?v='.Utilities::runningInstanceUID().'" />'."\n";
    }
    
    /**
     * Get script(s)
     * 
     * @return array of http file path
     */
    public static function scripts() {
        $sources = array(
            'lib/jquery/jquery-1.9.1.min.js',
            'lib/jquery/jquery-ui-1.10.2.custom.min.js',
            'js/filesender.js',
            'js/lang.js',
            'js/client.js',
            'js/transfer.js',
            'js/ui.js',
        );
        
        if(Config::get('terasender_enabled')) $sources[] = 'lib/terasender/terasender.js';
        
        $sources[] = 'skin/script.js';
        
        return self::filterSources($sources);
    }
    
    /**
     * Include scripts
     */
    public static function includeScripts() {
        foreach(self::path(self::scripts()) as $path)
            echo '<script type="text/javascript" src="'.$path.'?v='.Utilities::runningInstanceUID().'"></script>'."\n";
    }
    
    /**
     * Get favicon
     * 
     * @return http file path
     */
    public static function favicon() {
        $locations = self::filterSources(array(
            'images/favicon.ico',
            'skin/favicon.ico'
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
            'images/logo.png',
            'skin/logo.png'
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
     * Force HTTPS - redirects HTTP to HTTPS
     */
    public static function forceHTTPS() {
        if(Config::get('force_ssl') && !(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')) {
            if(session_id() != '') {
                // Destroy current session to prevent stealing session, because someone may have sniffed it during our HTTP (not HTTPS) request.
                unset($_SESSION);
                
                if(ini_get('session.use_cookies')) {
                    // Unset the PHPSESSID cookie, so that the user will get a new session ID on their next request.
                    $params = session_get_cookie_params();
                    
                    setcookie(
                        session_name(), '', time() - 42000,
                        $params['path'], $params['domain'],
                        $params['secure'], $params['httponly']
                    );
                }
                
                session_destroy();
            }
            
            // ... Redirect the user to HTTPS.
            $redirect = sprintf('Location: https://%s%s', $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI']);
            header($redirect);
            exit;
        }
    }
    
    /**
     * Get current page
     * 
     * @param string $page if given replaces the current page
     * 
     * @return string
     */
    public function currentPage($page = null) {
        if(!is_null($page)) self::$current_page = $page;
        
        if(!self::$current_page) {
            $page = null;
            if(array_key_exists('s', $_REQUEST)) $page = $_REQUEST['s'];
            
            if(Config::get('maintenance')) $page = 'maintenance';
            
            if(!$page) {
                if(Auth::isAuthenticated() && !Auth::isGuest()) {
                    $landing_page = Config::get('landing_page');
                    $page = ($landing_page && GUIPages::isValidValue($landing_page)) ? $landing_page : 'upload';
                } else {
                    $page = 'home';
                }
            }
            
            if(!GUIPages::isValidValue($page))
                throw new GUIUnknownPageException($page);
            
            self::$current_page = $page;
        }
        
        return self::$current_page;
    }
    
    /**
     * Get the pages the current user has access to
     * 
     * @return array
     */
    public static function allowedPages() {
        if(is_null(self::$allowed_pages)) {
            self::$allowed_pages = array();
            
            if(Auth::isAuthenticated()) {
                if(Auth::isGuest()) {
                    self::$allowed_pages = array('upload');
                } else {
                    self::$allowed_pages = array('upload', 'transfers', 'guests', 'download');
                    
                    if(Auth::isAdmin()) self::$allowed_pages[] = 'admin';
                    
                    if(Config::get('user_page')) self::$allowed_pages[] = 'user';
                }
            }
            
            // Always accessible pages
            foreach(array('home', 'download', 'logout', 'exception') as $p)
                self::$allowed_pages[] = $p;
            
                if(Config::get('maintenance')) self::$allowed_pages = array('maintenance');
        }
        
        return self::$allowed_pages;
    }
    
    /**
     * Check if current user can acces a page
     * 
     * @param string $page current page will be checked if none given
     * 
     * @return bool
     */
    public static function isUserAllowedToAccessPage($page = null) {
        if(is_null($page)) $page = self::currentPage();
        
        if(!GUIPages::isValidValue($page))
            throw new GUIUnknownPageException($page);
        
        return in_array($page, self::allowedPages());
    }
}
