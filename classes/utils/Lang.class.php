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
 * Language managment class (current user language, translations ...)
 */
class Lang
{
    /**
     * Translations (lang_id to translated version)
     */
    private static $translations = null;
    
    /**
     * Loader lock
     */
    private static $loading = false;
    
    /**
     * Availabe languages
     */
    private static $available_languages = null;
    
    /**
     * Current lang code stack
     */
    private static $code_stack = null;
    
    /**
     * Current translated string
     */
    private $translation = '';
    
    
    /**
     * Get available languages
     *
     * @return array
     */
    public static function getAvailableLanguages()
    {
        // Already cached ?
        if (is_null(self::$available_languages)) {
            self::$available_languages = array();
            
            // Include first found locale list
            $sources = array('config/language/locale.php', 'language/locale.php');
            
            $locales = array();
            foreach ($sources as $file) {
                if (file_exists(FILESENDER_BASE.'/'.$file)) {
                    include FILESENDER_BASE.'/'.$file;
                    break;
                }
            }
            
            // Normalize locales
            foreach ($locales as $id => $dfn) {
                $name = $id;
                $path = $dfn;
                
                if (is_array($dfn)) {
                    $path = $dfn['path'];
                    if (array_key_exists('name', $dfn)) {
                        $name = $dfn['name'];
                    }
                }

                $specificid = preg_replace('/[^-]*-/', '', $id);
                
                self::$available_languages[$id] = array(
                    'name' => $name,
                    'path' => $path,
                    'specific-id' => $specificid,
                );
            }
        }
        
        return self::$available_languages;
    }
    
    /**
     * Check if a lang code is available (directly or throught aliasing)
     *
     * @param string $code
     *
     * @return mixed real code or null if not found
     */
    public static function realCode($raw_code)
    {
        $raw_code = str_replace('_', '-', strtolower($raw_code));
        $available = self::getAvailableLanguages();
        
        // Exists as is ?
        if (array_key_exists($raw_code, $available)) {
            return $raw_code;
        }
        
        // Lookup main code
        $parts = explode('-', $raw_code);
        $main = array_shift($parts);
        
        if (array_key_exists($main, $available)) {
            return $main;
        }
        
        return null;
    }
    
    /**
     * Get current lang code stack
     *
     * @return array
     */
    private static function getCodeStack()
    {
        if (is_null(self::$code_stack)) {
            $stack = array();
            
            $available = self::getAvailableLanguages();
            
            // Fill stack by order of preference and without duplicates
            
            if (count($available) > 1) {
                // Auth exception should not stop processing of lang code
                try {
                    // URL/session given language
                    if (Config::get('lang_url_enabled')) {
                        if (array_key_exists('lang', $_GET) && preg_match('`^[a-z]+(-.+)?$`', $_GET['lang'])) {
                            $code = self::realCode($_GET['lang']);
                            if ($code) {
                                if (!in_array($code, $stack)) {
                                    $stack[] = $code;
                                }
                                
                                if (isset($_SESSION)) {
                                    $_SESSION['lang'] = $code;
                                }
                                if (Config::get('lang_save_url_switch_in_userpref') && Auth::isAuthenticated()) {
                                    Auth::user()->lang = $code;
                                    Auth::user()->save();
                                }
                            }
                        }
                        
                        if (isset($_SESSION) && array_key_exists('lang', $_SESSION)) {
                            if (!in_array($_SESSION['lang'], $stack)) {
                                $stack[] = $_SESSION['lang'];
                            }
                        }
                    }
                    
                    // User preference stored language
                    if (Config::get('lang_userpref_enabled') && Auth::isAuthenticated() && Auth::user()) {
                        $code = Auth::user()->lang;
                        if ($code && !in_array($code, $stack)) {
                            $stack[] = $code;
                        }
                    }
                } catch (Exception $e) {
                }
                
                // Browser language
                if (Config::get('lang_browser_enabled') && array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
                    $codes = array();
                    foreach (array_map('trim', explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'])) as $part) {
                        $code = $part;
                        $weight = 1;
                        if (strpos($part, ';') !== false) {
                            $part = array_map('trim', explode(';', $part));
                            $code = array_shift($part);
                            foreach ($part as $p) {
                                if (preg_match('`^q=([0-9]+\.[0-9]+)$`', $p, $m)) {
                                    $weight = (float)$m[1];
                                }
                            }
                        }
                        $codes[$code] = $weight;
                    }
                    
                    uasort($codes, function ($a, $b) {
                        return ($b > $a) ? 1 : (($b < $a) ? -1 : 0);
                    });
                    
                    foreach ($codes as $code => $weight) {
                        $code = self::realCode($code);
                        if ($code && !in_array($code, $stack)) {
                            $stack[] = $code;
                        }
                    }
                }
            }
            
            // Filter provided values
            $stack = array_filter($stack, function ($code) use ($available) {
                return array_key_exists($code, $available);
            });
            
            // Config default language
            $code = Config::get('default_language');
            if ($code) {
                $code = self::realCode($code);
                if ($code && !in_array($code, $stack)) {
                    $stack[] = $code;
                }
            }
            
            // Absolute default, but avoid adding "en" if already set as default
            if (!in_array('en', $stack)) {
                $stack[] = 'en';
            }
            
            // Add to cached stack (most significant first)
            $main = array_shift($stack);
            self::$code_stack = array('main' => $main, 'fallback' => $stack);
            
            Logger::debug(self::$code_stack);
        }
        
        return self::$code_stack;
    }

    /**
     * This is like the PHP setlocale but it respects the language the 
     * user has selected and tries to work with that selection into 
     * something that the php setlocale() can handle.
     *
     * @param category is the same as for setlocale().
     */
    public static function setlocale_fromUserLang( int $category = LC_ALL )
    {
        if (Auth::user() == null)
            return;
        
        $userlang = Auth::user()->lang;

        // this is the array of lang to try
        $a = array($userlang);

        // append a version that goes from it-it to it_IT so PHP will be happy
        $t = explode('-',$userlang);
        if (count($t) == 2 ) {
            array_push($a, $t[0] . "_" . strtoupper($t[1]));
        }

        // try each language in the array until something sticks
        foreach($a as $lang) {
            if (strlen($lang) ) {
                if (setlocale($category, $lang))
                    break;
            }
        }
    }
    
    /**
     * Get base code, without any user related getters
     *
     * @return string
     */
    public static function getBaseCode()
    {
        // Config default language
        $code = Config::get('default_language');
        if ($code) {
            $code = self::realCode($code);
            if ($code) {
                return $code;
            }
        }
        
        return key(self::getAvailableLanguages()); // Defaults to first available language
    }
    
    /**
     * Get current lang code
     *
     * @return string
     */
    public static function getCode()
    {
        $stack = self::getCodeStack();
        
        return $stack['main'];
    }
    
    /**
     * Clean lang string id
     *
     * @param string $id
     *
     * @return string cleaned id
     */
    public static function cleanId($id)
    {
        $id = trim($id);
        $id = trim($id, '_');
        $id = strtolower($id);
        return $id;
    }
    
    /**
     * Load dictionary
     *
     * @param string $rel_path translations relative path
     */
    private static function loadDictionary($rel_path)
    {
        Logger::debug($rel_path);
        
        $dictionary = array();
        
        // Translations locations
        $locations = array(
            'language',
            'config/language'
        );
        
        // Lookup locations for translation files
        foreach ($locations as $location) {
            $path = FILESENDER_BASE.'/'.$location.'/'.$rel_path;
            Logger::debug($path);
            
            if (!is_dir($path)) {
                continue;
            }
            
            // Main translation file
            if (file_exists($path.'/lang.php')) {
                $lang = array();
                include $path.'/lang.php';
                foreach ($lang as $id => $s) {
                    $dictionary[self::cleanId($id)] = array('text' => $s);
                }
            }
            
            // Extended file name based translations
            foreach (scandir($path) as $i) {
                if (!is_file($path.'/'.$i)) {
                    continue;
                }
                
                if (preg_match('`^([^.]+)\.(te?xt(\.php)?|html?(\.php)?|php)$`', $i, $m)) {
                    if ($m[1] == 'lang') {
                        continue;
                    }
                    if (!array_key_exists($m[1], $dictionary)) {
                        $dictionary[$m[1]] = array('text' => null);
                    }
                    $dictionary[$m[1]]['file'] = $path.'/'.$i;
                }
            }
        }
        
        return $dictionary;
    }
    
    /**
     * Load dictionaries
     */
    private static function loadDictionaries()
    {
        if (!is_null(self::$translations) || self::$loading) {
            return;
        }
        
        self::$loading = true;
        
        // Get lang codes stack
        $stack = self::getCodeStack();
        
        // Get list of available languages
        $available = self::getAvailableLanguages();
        if (!array_key_exists('en', $available)) {
            $available['en'] = array('path' => 'en_AU');
        }
        
        // Build fallback dictionaries
        $fallback = array();
        foreach ($stack['fallback'] as $code) {
            $dictionary = self::loadDictionary($available[$code]['path']);
            
            foreach ($dictionary as $id => $d) {
                if (!array_key_exists($id, $fallback)) {
                    $fallback[$id] = $d;
                }
            }
        }
        
        // Set dictionaries cache
        self::$translations = array(
            'main' => self::loadDictionary($available[$stack['main']]['path']),
            'fallback' => $fallback
        );
        
        self::$loading = false;
    }
    
    /**
     * Translate a string
     *
     * @param string $id identifier of lang string
     *
     * @return Lang
     */
    public static function translate($id)
    {
        $tr = null;
        
        // Load dictionaries if not already done
        self::loadDictionaries();
        
        // Clean given id from unwanted characters
        $id = self::cleanId($id);
        
        // Need a translation while loading dictionaries, probably
        // encoutering an exception, do not translate to avoid loops
        if (self::$loading) {
            return new Translation('{'.$id.'}', false);
        }
        
        // Lookup for translation in the main language, warn and look in the fallbacks if not found
        $src = '';
        if (array_key_exists($id, self::$translations['main'])) {
            $tr = self::$translations['main'][$id];
            $src = 'main';
        } else {
            $stack = self::getCodeStack();
            Logger::warn('No translation found for '.$id.' in '.$stack['main'].' language');

            $fallbackid = 'unknown';
            // fallback is an array of lang codes, so loop through those
            foreach (self::$translations['fallback'] as $fallback_lang) {
                if (array_key_exists($id, $fallback_lang)) {
                    $tr = $fallback_lang[$id];
                    $src = 'fallback';
                    $fallbackid = $id;
                    // we stop on first match
                    continue;
                }       
            }           

            if (empty($src) && !empty(self::$translations['fallback'])) {
                Logger::warn('No fallback translation found for '.$id.' in '.$fallbackid.' languages');
                return new Translation('{'.$id.'}', false); 
            }       
        }

        if( !$tr ) {
            Logger::error("translate() can not find translation for id $id");
            return new Translation($id);
        }
        
        // File based ? Then loads it up and cache contents
        if (is_null($tr['text']) && array_key_exists('file', $tr)) {
            $filepath = $tr['file'];
            if (file_exists($filepath.'.local.php')) {
                $filepath = $filepath.'.local.php';
            }
            
            ob_start(); // Allows for php inside translations
            include $filepath;
            $s = ob_get_clean();
            
            $tr['text'] = $s;
            self::$translations[$src][$id]['text'] = $s; // Update cache
        }
        
        // Config syntax replacments
        $tr['text'] = self::replaceConfigValues($tr['text']);
        
        // Cast to translation object
        return new Translation($tr['text']);
    }
    
    /**
     * Replace config values (not doing that in Translation::replace avoids config extraction by syntax injection)
     *
     * @param string $text
     *
     * @return string
     */
    private static function replaceConfigValues($text)
    {
        return preg_replace_callback('`\{(|size:)(cfg|conf|config):([^}]+)\}`', function ($m) {
            $value = Config::get($m[3]);
            switch (substr($m[1], 0, -1)) {
                case 'size': $value = Utilities::formatBytes($value); break;
            }
            return $value;
        }, $text);
    }
    
    /**
     * Translation shortcut
     *
     * @param string $id identifier of lang string
     *
     * @return Lang
     */
    public static function tr($id)
    {
        return self::translate($id);
    }

    public static function trWithConfigOverride($id)
    {
        $v = Config::get('tr_' . $id);
        if( $v && strlen($v)) {
            return new Translation($v);
        }
        return self::tr($id);
    }
    
    /**
     * Translate email
     *
     * @param string $id identifier of email
     *
     * @return array of Lang
     */
    public static function translateEmail($id, $lang = null)
    {
        // Load (user) lang codes stack
        $stack = self::getCodeStack();
        
        // Merge main and fallbacks into a single stack
        $codes = $stack['fallback'];
        array_unshift($codes, $stack['main']);
        
        // Add given lang if any
        if ($lang) {
            array_unshift($codes, $lang);
        }
        
        // Translations locations
        $locations = array(
            'config/language',
            'language'
        );
        
        // List available languages
        $available = self::getAvailableLanguages();
        
        // Look for translation in code stack ...
        foreach ($codes as $code) {
            if (!array_key_exists($code, $available)) {
                continue;
            }
            
            // ... and for each possible location
            foreach ($locations as $location) {
                // Translations path
                $path = FILESENDER_BASE.'/'.$location.'/'.$available[$code]['path'];
                
                if (!is_dir($path)) {
                    continue;
                }
                
                // Mail translation file
                $file = $path.'/'.$id.'.mail';
                
                if (file_exists($file.'.php')) {
                    $file .= '.php';
                }
                
                // No matching file ? Then go to next location / code
                if (!file_exists($file)) {
                    continue;
                }
                
                // Load file contents and eval
                ob_start();
                include $file;
                $translation = trim(ob_get_clean());
                
                // Separate headers from body
                $parts = preg_split('`\n\s*\n`', $translation, 2);
                
                // Do we have headings
                $subject = array('prefix' => trim((string)Config::get('email_subject_prefix')), '{cfg:site_name}');
                if (count($parts) > 1) {
                    $headers = explode("\n", array_shift($parts));
                    foreach ($headers as $line) {
                        // Get subject
                        if (preg_match('`^\s*subject\s*:\s*(.+)$`i', $line, $m)) {
                            $subject[] = trim($m[1]);
                        }
                    }
                }
                
                // Try to split body based on alternatives tags
                $misc = array();
                $plain = array();
                $html = array();
                $mode = null;
                foreach (explode("\n", array_shift($parts)) as $line) {
                    if (trim($line) == '{alternative:plain}') {
                        $mode = 'plain';
                    } elseif (trim($line) == '{alternative:html}') {
                        $mode = 'html';
                    } elseif (trim($line) == '{alternative}') {
                        if ($mode == 'plain') {
                            $mode = 'html';
                        } elseif ($mode == 'html') {
                            $mode = 'plain';
                        } else {
                            $mode = 'html';
                        }
                    } elseif ($mode == 'html') {
                        $html[] = $line;
                    } elseif ($mode == 'plain') {
                        $plain[] = $line;
                    } else {
                        $misc[] = $line;
                    }
                }
                
                // Trim contents
                $misc = trim(implode("\n", $misc));
                $plain = trim(implode("\n", $plain));
                $html = trim(implode("\n", $html));
                
                // Handle defaults
                if ($misc) {
                    if ($html && !$plain) {
                        $plain = $misc;
                    }
                    if ($plain && !$html) {
                        $html = $misc;
                    }
                    
                    if (!$html && !$plain) {
                        if (preg_match('`(</(a|p|table|td|tr)>|<br\s*/?>)`', $misc)) {
                            $html = $misc;
                        } else {
                            $plain = $misc;
                        }
                    }
                }
                
                // Config syntax replacements
                $subject = self::replaceConfigValues($subject);
                list($plain, $html) = self::replaceConfigValues(array($plain, $html));
                
                // Convert to Translation instance with sub-Translations
                return new Translation(array(
                    'subject' => new Translation($subject, true, true), // Raw outputting, will be encoded
                    'plain' => new Translation($plain, true, true), // Raw outputting
                    'html' => $html
                ));
            }
        }
        
        // No translation founs
        throw new DetailedException('mail_translation_not_found', 'id = '.$id);
    }
    
    /**
     * Whole dictionary getter
     *
     * Do not get file-translated strings
     *
     * @return array
     */
    public static function getTranslations()
    {
        self::loadDictionaries();
        
        return array_filter(array_map(function ($t) {
            return $t['text'];
        }, array_merge(self::$translations['fallback'], self::$translations['main'])), function ($t) {
            return !is_null($t);
        });
    }
}

/**
 * Translated content
 */
class Translation
{
    /**
     * Actual translation holder
     */
    private $translation = '';
    
    /**
     * Raw mode
     */
    private $raw = false;

    /**
     * Does the translation allows replacements
     */
    private $allow_replace = true;
    
    /**
     * Constructor
     *
     * @param string $translation
     */
    public function __construct($translation, $allow_replace = true, $raw = false)
    {
        // Set text if single translation, set sub-Translations otherwise
        if (is_string($translation)) {
            $this->translation = $translation;
        } else {
            $this->translation = array();
            foreach ((array)$translation as $k => $v) {
                $this->translation[$k] = is_string($v) ? new self($v, $allow_replace, $raw) : $v;
            }
        }
        
        $this->allow_replace = $allow_replace;
        $this->raw = $raw;
    }
    
    /**
     * Placeholder replacement
     *
     * @param mixed $placeholder placeholder id as string or array of placholders and values
     * @param mixed $value value if 1st param is a placeholder id
     *
     * OR
     *
     * @param mixed $* arrays of placeholders and values or data objects
     *
     * @return Lang
     */
    public function replace()
    {
        // Do not replace anything unless allowed
        if (!$this->allow_replace) {
            return $this;
        }
        
        $args = func_get_args();
        
        // Forward call to any sub-Translations
        if (!is_string($this->translation)) {
            $t = array();
            foreach ($this->translation as $k => $v) {
                $t[$k] = call_user_func_array(array($v, 'replace'), $args);
            }
            
            return new self($t);
        }
        
        // Transform function arguments into placeholders values array
        $placeholders = array();
        while ($arg = array_shift($args)) {
            if (is_string($arg)) {
                $placeholders[$arg] = array_shift($args);
            } elseif (is_array($arg)) {
                foreach ($arg as $k => $v) {
                    if (!is_numeric($k)) {
                        $placeholders[$k] = $v;
                    }
                }
            } elseif (is_object($arg)) {
                $placeholders[strtolower(get_class($arg))] = $arg;
            }
        }
        
        // base translation
        $translation = $this->translation;
        
        // Placeholder value getter
        $placeholder_resolver = function ($path, $raw = false) use ($placeholders) {
            // Need value casting ?
            $path = explode(':', $path);
            $cast = (count($path) > 1) ? array_shift($path) : null;
            $path = array_shift($path);
            
            // Path parts
            $path = array_filter(array_map('trim', explode('.', $path)));
            $name = array_shift($path);
            
            // Return empty if placeholder does not exist
            if (!array_key_exists($name, $placeholders)) {
                return null;
            }
            
            $value = $placeholders[$name];
            
            // Follow path if any
            while (!is_null($entry = array_shift($path))) {
                if (is_object($value)) {
                    try {
                       $value = $value->$entry;
                    } catch( DetailedException $e ) {
                         Logger::error( 'AAAA Translation substitution failed. ' . $e->getDetails());
                         $value = '';
                    }
                } elseif (is_array($value)) {
                    if (is_numeric($entry) && !is_float($entry)) {
                        $entry = (int)$entry;
                    }
                    
                    if (preg_match('`^(first|nth|last)\(([0-9]*\))`', $entry, $m)) {
                        $keys = array_keys($value);
                        switch ($m[1]) {
                            case 'first': $entry = reset($keys); break;
                            case 'last': $entry = end($keys); break;
                            case 'nth':
                                $i = $m[2] ? (int)$m[2] : 0;
                                $entry = array_slice($keys, $i, 1);
                                $entry = array_shift($entry);
                                break;
                        }
                    }
                    
                    $value = (!is_null($entry) && array_key_exists($entry, $value)) ? $value[$entry] : null;
                }
            }
            
            // Cast if needed
            if ($cast) {
                switch ($cast) {
                case 'date': $value = Utilities::formatDate($value); break;
                case 'datetime': $value = Utilities::formatDate($value, true); break;
                case 'time': $value = Utilities::formatTime($value); break;
                case 'size': $value = Utilities::formatBytes($value); break;
            }
            }
            
            // Convert non-scalar to scalar unless raw required
            if (!$raw) {
                if (is_array($value)) {
                    $value = count($value);
                }
                if (is_object($value)) {
                    $value = true;
                }
            }
            
            return $value;
        };
        
        // Replace each loops
        $translation = preg_replace_callback('`\{each:([^\}]+)\}(.+)\{endeach\}`msiU', function ($m) use ($placeholders, $placeholder_resolver) {
            // Source variable
            $src = $m[1];
            
            // Inner body
            $content = new Translation($m[2]);
            
            // Whole each statment
            $raw = $m[0];
            
            // Inner loop variable name
            $itemname = 'item';
            if (preg_match('`^(.+)\s+as\s+([a-z0-9_]+)$`i', $src, $m)) {
                $itemname = $m[2];
                $src = $m[1];
            }
            
            // Resolve source variable and get raw value
            $src = $placeholder_resolver($src, true);
            
            // Placeholder not (yet) defined, do not replace
            if (is_null($src)) {
                return $raw;
            }
            
            // Source variable is notan array, cannot replace
            if (!is_array($src)) {
                return '';
            }
            
            // Loop and replace inner variables
            $out = array();
            foreach ($src as $item) {
                $out[] = $content->replace(array_merge($placeholders, array($itemname => $item)))->out();
            }
            
            // Return serialized content
            return implode('', $out);
        }, $translation);
        
        // Replace if statments
        $translation = preg_replace_callback('`\{if:([^\}]+)\}(.+)(?:\{else\}(.+))?\{endif\}`msiU', function ($m) use ($placeholder_resolver) {
            // Get test
            $condition = $m[1];
            
            // Get "if true" content
            $ifcontent = $m[2];
            
            // Get "if false" content
            $elsecontent = (count($m) > 3) ? $m[3] : '';
            
            // Evaluate test (and before or fashion)
            $match = false;
            $leftor = array();
            foreach (array_map('trim', array_filter(explode('|', $condition))) as $orpart) {
                $smatch = true;
                $leftand = array();
                foreach (array_map('trim', array_filter(explode('&', $orpart))) as $andpart) {
                    $op = 'bool';
                    $ov = true;
                    $neg = false;
                    
                    // Is there a comparison operator
                    if (preg_match('`^(.+)(==|!=|<|<=|>|>=)(.+)$`', $andpart, $m)) {
                        $andpart = trim($m[1]);
                        $op = $m[2];
                        $ov = trim($m[3]);
                    }
                    
                    // Is there any negation ?
                    $andpart = trim($andpart);
                    if (substr($andpart, 0, 1) == '!') {
                        $neg = true;
                        $andpart = trim(substr($andpart, 1));
                    }
                    
                    // Resolve compared value
                    $value = $placeholder_resolver($andpart);
                    
                    // Placeholder not (yet) available, cannot choose, leave part as is
                    if (is_null($value)) {
                        $leftand[] = ($neg ? '!' : '').$andpart.($op != 'bool' ? $op.$ov : '');
                        $smatch = false;
                        break;
                    }
                    
                    // Cast value to scalar
                    if (is_object($value)) {
                        $value = true;
                    }
                    if (is_array($value)) {
                        $value = count($value);
                    }
                    
                    // Cast value to compare to
                    if ($ov == 'true') {
                        $ov = true;
                    } elseif ($ov == 'false') {
                        $ov = false;
                    } elseif (is_float($ov)) {
                        $ov = (float)$ov;
                    } elseif (is_numeric($ov)) {
                        $ov = (int)$ov;
                    } elseif (is_string($ov)) {
                        if (preg_match('`^(["\'])(.*)\1$`', $ov, $m)) {
                            $ov = $m[2];
                        }
                    }
                    
                    // Run the test
                    switch ($op) {
                        case '==': $smatch &= ($value == $ov); break;
                        case '!=': $smatch &= ($value != $ov); break;
                        case '<': $smatch &= ($value < $ov); break;
                        case '<=': $smatch &= ($value <= $ov); break;
                        case '>': $smatch &= ($value > $ov); break;
                        case '>=': $smatch &= ($value >= $ov); break;
                        
                        case 'bool':
                        default:
                            $smatch &= (bool)$value;
                    }
                }
                
                // Any test that we couldn't run ? Then leave what's not resolved for later, reduce value otherwise
                if (count($leftand)) {
                    $leftor[] = implode('&', $leftand);
                } elseif ($smatch) {
                    $match = true;
                    break;
                }
            }
            
            // Part of the test remains, set it for next replace
            if (!$match && count($leftor)) {
                return '{if:'.implode('|', $leftor).'}'.$ifcontent.($elsecontent ? '{else}'.$elsecontent : '').'{endif}';
            }
            
            // Return fitting content
            return $match ? $ifcontent : $elsecontent;
        }, $translation);
        
        // Basic placeholder replacement
        $raw = $this->raw;
        foreach ($placeholders as $k => $v) {
            $translation = preg_replace_callback('`\{(([^:\}]+:)?'.$k.'(\.[a-z0-9_\(\)]+)*)\}`iU', function ($m) use ($placeholder_resolver, $raw) {
                if (substr($m[0], 0, 4) == '{if:') {
                    return $m[0];
                } // Remaining ifs
                
                $v = $placeholder_resolver($m[1]);
                
                if (!$raw && substr($m[0], 0, 5) != '{raw:') { // Ensure sanity unless specified
                    $v = Utilities::sanitizeOutput($v);
                }
                
                if (!$raw) { // Format html linebreaks
                    $v = preg_replace('`\n\s*\n`', "\n\n", $v);
                    $v = str_replace("\n", "<br />\n", $v);
                }
                
                return $v;
            }, $translation);
        }
        
        // Return new translation object for further replacements
        return new self($translation);
    }
    
    /**
     * Placeholder replacement shortcut
     *
     * @param mixed $placeholder placeholder id as string or array of placholders and values
     * @param mixed $value value if 1st param is a placeholder id
     *
     * @return Lang
     */
    public function r($placeholder, $value = null)
    {
        return $this->replace($placeholder, $value);
    }
    
    /**
     * Getter
     */
    public function __get($entry)
    {
        $exists = is_array($this->translation) && array_key_exists($entry, $this->translation);
        return $exists ? $this->translation[$entry] : new self('', false);
    }
    
    /**
     * Convert to string
     */
    public function out()
    {
        if (!is_string($this->translation)) {
            return array_map(function ($tr) {
                return $tr->out();
            }, $this->translation);
        }
        
        $out = $this->translation;
        
        // Get rid of unresolved if statments
        $out = preg_replace('`\{if:([^\}]+)\}(.+)\{endif\}`msiU', '', $out);
        
        // Get rid of unresolved each loops
        $out = preg_replace('`\{each:([^\}]+)\}(.+)\{endeach\}`msiU', '', $out);
        
        return $out;
    }
    
    /**
     * Convert to string
     */
    public function __toString()
    {
        return $this->out();
    }
}
