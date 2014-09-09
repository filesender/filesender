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
 * Language managment class (current user language, translations ...)
 */
class Lang {
    /**
     * Translations (lang_id to translated version)
     */
    private static $translations = null;
    
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
     * Does the translation allows replacements
     */
    private $allow_replace = true;
    
    /**
     * Get available languages
     * 
     * @return array
     */
    public static function getAvailableLanguages() {
        if(is_null(self::$available_languages)) {
            self::$available_languages = array();
            
            $sources = array('config/language/locale.php', 'language/locale.php');
            
            $locales = array();
            foreach($sources as $file) {
                if(file_exists(FILESENDER_BASE.'/'.$file)) {
                    include FILESENDER_BASE.'/'.$file;
                    break;
                }
            }
            
            foreach($locales as $id => $dfn) {
                $name = $id;
                $path = $dfn;
                
                if(is_array($dfn)) {
                    $path = $dfn['path'];
                    if(array_key_exists('name', $dfn))
                        $name = $dfn['name'];
                }
                
                self::$available_languages[$id] = array(
                    'name' => $name,
                    'path' => $path
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
    private static function realCode($raw_code) {
        $available = self::getAvailableLanguages();
        
        if(array_key_exists($raw_code, $available))
            return $raw_code;
        
        $parts = explode('-', $raw_code);
        $main = array_shift($parts);
        
        if(array_key_exists($main, $available))
            return $main;
        
        return null;
    }
    
    /**
     * Get current lang code stack
     * 
     * @return array
     */
    private static function getCodeStack() {
        if(is_null(self::$code_stack)) {
            $stack = array();
            
            // Fill stack by order of preference and without duplicates
            
            // URL/session given language
            if(Config::get('lang_url_enabled')) {
                if(array_key_exists('lang', $_GET) && preg_match('`^[a-z]+(_.+)?$`', $_GET['lang'])) {
                    $code = self::realCode($_GET['lang']);
                    if($code) $_SESSION['lang'] = $code;
                }
                
                if(array_key_exists('lang', $_SESSION)) {
                    if(!in_array($_SESSION['lang'], $stack))
                        $stack[] = $_SESSION['lang'];
                }
            }
            
            // User preference stored language
            if(Config::get('lang_userpref_enabled') && Auth::isAuthenticated()) {
                $code = Auth::user()->lang;
                if($code && !in_array($code, $stack))
                    $stack[] = $code;
            }
            
            // Browser language
            if(Config::get('lang_browser_enabled') && array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
                $codes = array();
                foreach(array_map('trim', explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'])) as $part) {
                    $code = $part;
                    $weight = 1;
                    if(strpos($part, ';') !== false) {
                        $part = array_map('trim', explode(';', $part));
                        $code = $part[0];
                        if(is_numeric($part[1])) $weight = (float)$part[1];
                    }
                    $codes[$code] = $weight;
                }
                asort($codes);
                foreach($codes as $code => $weight) {
                    $code = self::realCode($code);
                    if($code && !in_array($code, $stack))
                        $stack[] = $code;
                }
            }
            
            // Config default language
            $code = Config::get('default_language');
            if($code) {
                $code = self::realCode($code);
                if($code && !in_array($code, $stack))
                    $stack[] = $code;
            }
            
            // Absolute default if not already present
            $code = self::realCode('en');
            if($code) {
                if(!in_array($code, $stack)) $stack[] = $code;
            }else $stack[] = key(self::getAvailableLanguages()); // Should not go there ...
            
            // Add to cached stack (most significant first)
            $main = array_shift($stack);
            self::$code_stack = array('main' => $main, 'fallback' => $stack);
        }
        
        return self::$code_stack;
    }
    
    /**
     * Get current lang code
     * 
     * @return string
     */
    public static function getCode() {
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
    public static function cleanId($id) {
        $id = trim($id);
        $id = trim($id, '_');
        $id = strtolower($id);
        return $id;
    }
    
    /**
     * Load dictionary
     * 
     * @param string $code lang code
     */
    private static function loadDictionary($code) {
        $available = self::getAvailableLanguages();
        
        $dictionary = array();
        
        $locations = array(
            'language',
            'config/language'
        );
        
        foreach($locations as $location) {
            $path = FILESENDER_BASE.'/'.$location.'/'.$available[$code]['path'];
            
            if(!is_dir($path)) continue;
            
            if(file_exists($path.'/lang.php')) {
                $lang = array();
                include $path.'/lang.php';
                foreach($lang as $id => $s)
                    $dictionary[self::cleanId($id)] = array('text' => $s);
            }
            
            foreach(scandir($path) as $i) {
                if(!is_file($path.'/'.$i)) continue;
                
                if(preg_match('`^([^.]+)\.(te?xt(\.php)?|html?(\.php)?|php)$`', $i, $m)) {
                    if($m[1] == 'lang') continue;
                    if(!array_key_exists($m[1], $dictionary))
                        $dictionary[$m[1]] = array('text' => null);
                    $dictionary[$m[1]]['file'] = $path.'/'.$i;
                }
            }
        }
        
        return $dictionary;
    }
    
    /**
     * Load dictionaries
     */
    private static function loadDictionaries() {
        if(!is_null(self::$translations)) return;
        
        $stack = self::getCodeStack();
        
        $fallback = array();
        foreach($stack['fallback'] as $code) {
            $dictionary = self::loadDictionary($code);
            
            foreach($dictionary as $id => $d)
                if(!array_key_exists($id, $fallback))
                    $fallback[$id] = $d;
        }
        
        self::$translations = array(
            'main' => self::loadDictionary($stack['main']),
            'fallback' => $fallback
        );
    }
    
    /**
     * Translate a string
     * 
     * @param string $id identifier of lang string
     * 
     * @return Lang
     */
    public static function translate($id) {
        self::loadDictionaries();
        $id = self::cleanId($id);
        
        $src = '';
        if(array_key_exists($id, self::$translations['main'])) {
            $tr = self::$translations['main'][$id];
            $src = 'main';
        }else{
            $stack = self::getCodeStack();
            Logger::warn('No translation found for '.$id.' in '.$stack['main'].' language');
            
            if(array_key_exists($id, self::$translations['fallback'])) {
                Logger::warn('No fallback translation found for '.$id.' in '.implode(', ', $stack['fallback']).' languages');
            
                $tr = self::$translations['fallback'][$id];
                $src = 'fallback';
            }else{
                return new self('{'.$id.'}', false);
            }
        }
        
        if(is_null($tr['text']) && array_key_exists('file', $tr)) {
            ob_start(); // Allows for php inside translations
            include $tr['file'];
            $s = ob_get_clean();
            
            $tr['text'] = $s;
            self::$translations[$src][$id]['text'] = $s; // Update cache
        }
        
        // Config syntax
        $tr['text'] = preg_replace_callback('`\{(cfg|conf|config):([^}]+)\}`', function($m) {
            return Config::get($m[2]);
        }, $tr['text']);
        
        return new self($tr['text']);
    }
    
    /**
     * Translation shortcut
     * 
     * @param string $id identifier of lang string
     * 
     * @return Lang
     */
    public static function tr($id) {
        return self::translate($id);
    }
    
    /**
     * Translate email
     * 
     * @param string $id identifier of email
     * 
     * @return array of Lang
     */
    public static function translateEmail($id) {
        $stack = self::getCodeStack();
        $codes = $stack['fallback'];
        array_unshift($codes, $stack['main']);
        
        $locations = array(
            'config/language',
            'language'
        );
        
        $available = self::getAvailableLanguages();
        
        foreach($codes as $code) {
            foreach($locations as $location) {
                $path = FILESENDER_BASE.'/'.$location.'/'.$available[$code]['path'];
                
                if(!is_dir($path)) continue;
                
                $file = $path.'/'.$id.'.mail';
                
                if(file_exists($file.'.php')) $file .= '.php';
                
                if(!file_exists($file)) continue;
                
                ob_start();
                include $file;
                $translation = trim(ob_get_clean());
                
                // Config syntax
                $translation = preg_replace_callback('`\{(cfg|conf|config):([^}]+)\}`', function($m) {
                    return Config::get($m[2]);
                }, $translation);
                
                $parts = preg_split('`\n\s*\n`', $translation, 2);
                
                // Do we have headings
                $subject = '{cfg:site_name}';
                if(count($parts) > 1) {
                    foreach(explode('\n', $parts[0]) as $line) {
                        if(preg_match('`^\s*subject\s*:\s*(.*)$`i', $line, $m)) {
                            array_shift($parts);
                            $subject = $m[1];
                        }
                    }
                }
                
                // Try to split body
                $misc = array();
                $plain = array();
                $html = array();
                $mode = null;
                foreach(explode("\n", $parts[0]) as $line) {
                    if(trim($line) == '{alternative:plain}') {
                        $mode = 'plain';
                    }else if(trim($line) == '{alternative:html}') {
                        $mode = 'html';
                    }else if(trim($line) == '{alternative}') {
                        if($mode == 'plain') $mode = 'html';
                        else if($mode == 'html') $mode = 'plain';
                        else $mode = 'html';
                    }else if($mode == 'html') {
                        $html[] = $line;
                    }else if($mode == 'plain') {
                        $plain[] = $line;
                    }else $misc[] = $line;
                }
                
                $misc = trim(implode("\n", $misc));
                $plain = trim(implode("\n", $plain));
                $html = trim(implode("\n", $html));
                
                // Handle defaults
                if($misc) {
                    if($html && !$plain) $plain = $misc;
                    if($plain && !$html) $html = $misc;
                    
                    if(!$html && !$plain) {
                        if(preg_match('`(</(a|p|table|td|tr)>|<br\s*/?>)`', $misc)) {
                            $html = $misc;
                        }else{
                            $plain = $misc;
                        }
                    }
                }
                
                // Convert to Lang instances
                return new self(array(
                    'subject' => $subject,
                    'plain' => $plain,
                    'html' => $html
                ));
            }
        }
        
        throw new DetailedException('mail_translation_not_found', 'id = '.$id);
    }
    
    /**
     * Whole dictionary getter
     * 
     * Do not get file-translated strings
     * 
     * @return array
     */
    public static function getTranslations() {
        self::loadDictionaries();
        
        return array_filter(array_map(function($t) {
            return $t['text'];
        }, array_merge(self::$translations['fallback'], self::$translations['main'])), function($t) {
            return !is_null($t);
        });
    }
    
    /**
     * Constructor
     * 
     * @param string $translation
     */
    private function __construct($translation, $allow_replace = true) {
        if(is_string($translation)) {
            $this->translation = $translation;
        } else {
            $this->translation = array();
            foreach((array)$translation as $k => $v) {
                $this->translation[$k] = is_string($v) ? new self($v, $allow_replace) : $v;
            }
        }
        
        $this->allow_replace = $allow_replace;
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
    public function replace() {
        if(!$this->allow_replace) return $this;
        
        $args = func_get_args();
        
        if(!is_string($this->translation)) {
            $t = array();
            foreach($this->translation as $k => $v)
                $t[$k] = call_user_func_array(array($v, 'replace'), $args);
            
            return new self($t);
        }
        
        $placeholders = array();
        while($arg = array_shift($args)) {
            if(is_string($arg)) {
                $placeholders[$arg] = array_shift($args);
            } else if(is_array($arg)) {
                foreach($arg as $k => $v)
                    if(!is_numeric($k))
                        $placeholders[$k] = $v;
            } else if(is_object($arg)) {
                $placeholders[strtolower(get_class($arg))] = $arg;
            }
        }
        
        $translation = $this->translation;
        
        $placeholder_resolver = function($path) use($placeholders) {
            $path = explode(':', $path);
            $cast = (count($path) > 1) ? array_shift($path) : null;
            $path = array_shift($path);
            
            $path = array_filter(array_map('trim', explode('.', $path)));
            $name = array_shift($path);
            
            if(!array_key_exists($name, $placeholders))
                return null;
            
            $value = $placeholders[$name];
            
            while($entry = array_shift($path)) {
                if(is_object($value)) {
                    $value = $value->$entry;
                } else if(is_array($value)) {
                    if(is_numeric($entry) && !is_float($entry))
                        $entry = (int)$entry;
                    $value = array_key_exists($entry, $value) ? $value[$entry] : null;
                }
            }
            
            if($cast) switch($cast) {
                case 'date' : $value = Utilities::formatDate($value); break;
                case 'size' : $value = Utilities::formatBytes($value); break;
            }
            
            return $value;
        };
        
        $translation = preg_replace_callback('`\{if:([^\}]+)\}(.+)(?:\{else\}(.+))?\{endif\}`msiU', function($m) use($placeholder_resolver) {
            $condition = $m[1];
            $ifcontent = $m[2];
            $elsecontent = (count($m) > 3) ? $m[3] : '';
            
            $match = false;
            $leftor = array();
            foreach(array_map('trim', array_filter(explode('|', $condition))) as $orpart) {
                $smatch = true;
                $leftand = array();
                foreach(array_map('trim', array_filter(explode('&', $orpart))) as $andpart) {
                    $op = 'bool';
                    $ov = true;
                    $neg = false;
                    if(preg_match('`^(.+)(==|!=|<|<=|>|>=)(.+)$`', $andpart, $m)) {
                        $andpart = trim($m[1]);
                        $op = $m[2];
                        $ov = trim($m[3]);
                    }
                    
                    $andpart = trim($andpart);
                    if(substr($andpart, 0, 1) == '!') {
                        $neg = true;
                        $andpart = trim(substr($andpart, 1));
                    }
                    
                    $value = $placeholder_resolver($andpart);
                    
                    if(is_null($value)) { // Placeholder not available
                        $leftand[] = ($neg ? '!' : '').$andpart.($op != 'bool' ? $op.$ov : '');
                        $smatch = false;
                        break;
                    }
                    
                    if(is_object($value)) $value = true;
                    if(is_array($value)) $value = count($value);
                    
                    if($ov == 'true') {
                        $ov = true;
                    } else if($ov == 'false') {
                        $ov = false;
                    } else if(is_float($ov)) {
                        $ov = (float)$ov;
                    } else if(is_numeric($ov)) {
                        $ov = (int)$ov;
                    } else if(is_string($ov)) {
                        if(preg_match('`^(["\'])(.+)\1$`', $ov, $m))
                            $ov = $m[2];
                    }
                    
                    switch($op) {
                        case '==' : $smatch &= ($value == $ov); break;
                        case '!=' : $smatch &= ($value != $ov); break;
                        case '<' : $smatch &= ($value < $ov); break;
                        case '<=' : $smatch &= ($value <= $ov); break;
                        case '>' : $smatch &= ($value > $ov); break;
                        case '>=' : $smatch &= ($value >= $ov); break;
                        
                        case 'bool' :
                        default :
                            $smatch &= (bool)$value;
                    }
                }
                
                if(count($leftand)) {
                    $leftor[] = implode('&', $leftand);
                } else if($smatch) {
                    $match = true;
                    break;
                }
            }
            
            if(!$match && count($leftor)) // Conditions remains, set it for next replace
                return '{if:'.implode('|', $leftor).'}'.$ifcontent.($elsecontent ? '{else}'.$elsecontent : '').'{endif}';
            
            return $match ? $ifcontent : $elsecontent;
        }, $translation);
        
        foreach($placeholders as $k => $v) {
            $translation = preg_replace_callback('`\{(([^:\}]+:)?'.$k.'(\.[a-z0-9_]+)*)\}`iU', function($m) use($placeholder_resolver) {
                if(substr($m[0], 0, 4) == '{if:') return $m[0]; // Remaining ifs
                
                return $placeholder_resolver($m[1]);
            }, $translation);
        }
        
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
    public function r($placeholder, $value = null) {
        return $this->replace($placeholder, $value);
    }
    
    /**
     * Getter
     */
    public function __get($entry) {
        $exists = is_array($this->translation) && array_key_exists($entry, $this->translation);
        return $exists ? $this->translation[$entry] : new self('', false);
    }
    
    /**
     * Convert to string
     */
    public function out() {
        if(!is_string($this->translation))
            return '';
        
        // Get rid of unresolved ifs
        return preg_replace('`\{if:([^\}]+)\}(.+)\{endif\}`msiU', '', $this->translation);
    }
    
    /**
     * Convert to string
     */
    public function __toString() {
        return $this->out();
    }
}
