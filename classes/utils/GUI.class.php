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
 * GUI utilities
 */
class GUI
{
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
    public static function stylesheets()
    {
        return self::filterSources(array(
            'lib/jquery-ui/jquery-ui.min.css',
            'lib/font-awesome/css/font-awesome.min.css',
            'lib/bootstrap/dist/css/bootstrap.min.css',
            'lib/flag-icons/css/flag-icons.min.css',
            'css/default.css',
            'css/new-ui/styles.css',  // Adding the new-ui styles
            'css/themes/default.css', // Adding the new-ui theme styles
            'skin/styles.css',
            'css/' . Config::get('site_css')
        ));
    }

    /**
     * Include stylesheets
     */
    public static function includeStylesheets()
    {
        foreach (self::path(self::stylesheets()) as $path) {
            echo '<link type="text/css" rel="stylesheet" href="'.$path.'" />'."\n";
        }
    }

    public static function browser_is_ie11()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            return preg_match("@MSIE@i", $_SERVER['HTTP_USER_AGENT'] )
              ||   preg_match("@Trident/@i", $_SERVER['HTTP_USER_AGENT'] );
        }
    }

    public static function browser_is_edge()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            return preg_match("@ Edge/@i", $_SERVER['HTTP_USER_AGENT'] );
        }
    }


    /**
     * Get script(s)
     *
     * @return array of http file path
     */
    public static function scripts()
    {
        $sources = array();

        if( Browser::instance()->allowStreamSaver ) {
            array_push( $sources,
                        'lib/streamsaver/StreamSaver.js',
                        'js/crypter/streamsaver_sink.js',
                        'js/crypter/archive_sink.js',
                        'js/crc32handler.js',
                        'js/zip64handler.js'
            );
        }
        if( Browser::instance()->allowFileSystemWritableFileStream ) {
            array_push( $sources,
                        'js/crypter/filesystemwritablefilestream_sink.js'
            );
        }

        array_push( $sources,
                    'lib/jquery/jquery.min.js',
                    'lib/jquery-ui/jquery-ui.min.js',
                    'lib/promise-polyfill/polyfill.min.js',
                    'lib/web-streams-polyfill/dist/ponyfill.js',
                    'lib/webcrypto-shim/webcrypto-shim.min.js',
                    'lib/bootstrap/dist/js/bootstrap.bundle.min.js',
                    'lib/bootbox/dist/bootbox.all.min.js',
                    'js/filesender.js',
                    'js/lang.js',
                    'js/client.js',
                    'js/transfer.js',
                    'js/logger.js',
                    'js/ui.js',
                    'js/FileSaver.js',
                    'js/crypter/crypto_common.js',
                    'js/crypter/crypto_blob_reader.js',
                    'js/crypter/crypto_app.js',
                    'js/pbkdf2dialog.js',
                    'js/notification.js',
                    'lib/xregexp/xregexp-all.js',
                    'js/theme.js',
        );


        if (Config::get('terasender_enabled')) {
            $sources[] = 'js/terasender/terasender.js';
        }


        $sources[] = 'skin/script.js';

        return self::filterSources($sources);
    }

    /**
     * Include scripts
     */
    public static function includeScripts()
    {
        foreach (self::path(self::scripts()) as $path) {
            echo '<script type="text/javascript" src="'.$path.'"></script>'."\n";
        }
    }

    /**
     * Get favicon
     *
     * @return http file path
     */
    public static function favicon()
    {
        $locations = self::filterSources(array(
            'images/favicon.ico',
            'images/favicon.gif',
            'images/favicon.png',
            'skin/favicon.ico',
            'skin/favicon.gif',
            'skin/favicon.png'
        ));

        return array_pop($locations);
    }

    /**
     * Include favicon
     */
    public static function includeFavicon()
    {
        $location = self::favicon();
        if (!$location) {
            return;
        }

        echo '<link type="'.Mime::getFromFile($location).'" rel="icon" href="'.self::path($location).'" />'."\n";
    }

    /**
     * Get logo
     *
     * @return http file path
     */
    public static function logo()
    {
        $locations = self::filterSources(array(
            'images/logo.png',
            'skin/logo.png',
            Config::get('site_logo')
        ));

        return array_pop($locations);
    }

    /**
     * Include logo
     */
    public static function includeLogo()
    {
        $location = self::logo();
        if (!$location) {
            return;
        }

        echo '<img id="logo" src="'.self::path($location).'" alt="'.Config::get('site_name').'" />'."\n";
    }

    /**
     * Compute base path
     *
     * @param mixed $location location to append
     *
     * @return string
     */
    public static function path($location = null)
    {
        if (is_null(self::$path)) {
            self::$path = preg_replace('`^(https?://)?([^/]+)/`', '/', Config::get('site_url'));
        }

        if (is_array($location)) {
            return array_map(function ($l) {
                return GUI::path($l);
            }, $location);
        }

        return self::$path.$location;
    }

    /**
     * Filter sources based on existance
     *
     * @param array $sources
     *
     * @return array
     */
    private static function filterSources($sources)
    {
        return array_filter($sources, function ($source) {
            return is_file(FILESENDER_BASE.'/www/'.$source);
        });
    }

    /**
     * Force HTTPS - redirects HTTP to HTTPS
     */
    public static function forceHTTPS()
    {
        if (Config::get('force_ssl') && !Utilities::httpsInUse()) {
            if (session_id() != '') {
                // Destroy current session to prevent stealing session, because someone may have sniffed it during our HTTP (not HTTPS) request.
                unset($_SESSION);

                if (ini_get('session.use_cookies')) {
                    // Unset the PHPSESSID cookie, so that the user will get a new session ID on their next request.
                    $params = session_get_cookie_params();

                    setcookie(
                        session_name(),
                        '',
                        time() - 42000,
                        $params['path'],
                        Config::get('cookie_domain'),
                        $params['secure'],
                        $params['httponly']
                    );
                }

                session_destroy();
            }

            // ... Redirect the user to HTTPS.
            $host = array_key_exists('HTTP_HOST', $_SERVER) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
            $redirect = sprintf('Location: https://%s%s', $host, $_SERVER['REQUEST_URI']);
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
    public static function currentPage($page = null)
    {
        if (!is_null($page)) {
            self::$current_page = $page;
        }

        // Already cached ?
        if (!self::$current_page) {
            // Get from request
            $page = null;
            if (array_key_exists('s', $_REQUEST)) {
                $t = $_REQUEST['s'];
                if( !in_array($t, self::allowedPages())) {
                    $page = 'home';
                } else {
                    $page = $t;
                }
            }

            // Maintenance override
            if (Config::get('maintenance')) {
                $page = 'maintenance';
            }

            // Landing page if no value found
            if (!$page) {
                if (Auth::isAuthenticated() && !Auth::isGuest()) {
                    $landing_page = Config::get('landing_page');
                    $page = ($landing_page && GUIPages::isValidValue($landing_page)) ? $landing_page : 'upload';
                } else {
                    $page = 'home';
                }
            }

            // Fail if unknown
            if (!GUIPages::isValidValue($page)) {
                throw new GUIUnknownPageException($page);
            }

            self::$current_page = $page;
        }

        return self::$current_page;
    }

    /**
     * Get the pages the current user has access to
     *
     * @return array
     */
    public static function allowedPages()
    {
        // Already cached ?
        if (is_null(self::$allowed_pages)) {

            self::$allowed_pages = Config::get('allow_pages_core');

            // Authenticated users have access to lots ...
            if (Auth::isAuthenticated(false)) {
                if (Auth::isGuest()) {
                    self::$allowed_pages = array_merge( self::$allowed_pages,
                                                        Config::get('allow_pages_add_for_guest'));
                } else {
                    self::$allowed_pages = array_merge( self::$allowed_pages,
                                                        Config::get('allow_pages_add_for_user'));

                    // ... and admin to even more !
                    if (Auth::isAdmin()) {
                        self::$allowed_pages = array_merge( self::$allowed_pages,
                                                            Config::get('allow_pages_add_for_admin'));
                    }

                    if (Auth::canViewAggregateStatistics()) {
                        self::$allowed_pages[] = GUIPages::AGGREGATE_STATISTICS;
                    }

                    if (Auth::canViewStatistics()) {
                        self::$allowed_pages[] = GUIPages::STATISTICS;
                    }

                    // Is user page disabled?
                    if (!Config::get('user_page')) {
                        self::$allowed_pages = array_diff( self::$allowed_pages,
                                                           array(GUIPages::USER));
                    }
                }
            }


            //
            // the above doesn't matter if we are not allowing use right now.
            //
            if (Config::get('maintenance')) {
                self::$allowed_pages = array(GUIPages::MAINTENANCE);
            }
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
    public static function isUserAllowedToAccessPage($page = null)
    {
        if (is_null($page)) {
            $page = self::currentPage();
        }

        // Fail if unknown
        if (!GUIPages::isValidValue($page)) {
            throw new GUIUnknownPageException($page);
        }

        // no guests page if disabled
        if(!Config::get('guest_support_enabled') && $page == 'guests') {
            throw new GUIUnknownPageException($page);
        }


        return in_array($page, self::allowedPages());
    }

    /**
     * Make a login button with css class $addedClass. If present, the $target
     * should be the result of a call to Utilities::http_build_query()
     * which may wish to use the 's' parameter to set the page to use after login
     * as well as any other state that page might want.
     */
    public static function getLoginButton($target = null,$addedClass=null)
    {
        if(!$addedClass) {
            $addedClass = '';
        }

        $embed = Config::get('auth_sp_embed');

        if(!$embed) {
            $embed = '<a class="fs-button '.$addedClass.'" id="btn_logon" href="'.AuthSP::logonURL($target).'"><i class="fa fa-sign-in"></i><span>'.Lang::tr('logon').'</span></a>';
        }

        return $embed;
    }
}
