<?php

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2017, AARNet, Belnet, HEAnet, SURF, UNINETT
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

/**
 * A wrapper for getopt() based command line argument handling.
 *
 * A use case might be;
 *
 *   $args = new Args(
 *     array(
 *      'h' => 'help',
 *      's:' => 'scale:',
 *      ));

 *   $args->getopts();
 *   $args->maybeDisplayHelpAndExit(
 *     'This program does something'."\n\n" .
 *     'Usage '.basename(__FILE__).' -s|--scale=<1.0...0.01> '."\n" .
 *     "\t".'-s|--scale Amount of data to create 1.0 is full dataset 0.01 is 1% of data'."\n" .
 *     "\t\n"
 *   );
 *
 *   $args->MergeShortToLong();
 *   $scaleFactor = $args->getArg('scale', false, 1.00 );
 *   $scaleFactor = $args->clamp( $scaleFactor, 0.01, 1.00 );
 */
class Args
{
    private $args;
    private $opts = null;

    /**
     * @param args the array() with short => long options
     */
    public function __construct($args)
    {
        $this->args = $args;
    }

    /**
     * Call getopt()
     */
    public function getopts()
    {
        $this->opts = getopt(implode('', array_keys($this->args)), $this->args);
        return $this->opts;
    }

    /**
     * Handle -h --help and zero args if args are needed cases.
     */
    public function maybeDisplayHelpAndExit($msg, $zeroArgsAllowed = true)
    {
        global $argv;
        
        // Print help if no args or help wanted
        if (array_key_exists('h', $this->opts) || array_key_exists('help', $this->opts)) {
            echo $msg;
            exit(1);
        }
        if (!$zeroArgsAllowed && !count(array_slice($argv, 1))) {
            echo $msg;
            exit(1);
        }
    }
    
    /**
     * Merge short options into long ones
     * works on global $args
     */
    public function MergeShortToLong()
    {
        foreach ($this->args as $short => $long) {
            $short = str_replace(':', '', $short);
            $long = str_replace(':', '', $long);
            
            if (!array_key_exists($short, $this->opts)) {
                continue;
            }
            
            if (array_key_exists($long, $this->opts)) {
                $this->opts[$long] = array_merge(
                    (array)$this->opts[$long],
                                                 (array)$this->opts[$short]
                );
            } else {
                $this->opts[$long] = $this->opts[$short];
            }
            
            unset($this->opts[$short]);
        }
    }

    /**
     * Get the single arg value $name. If the arg is not present and fatal is true
     * then display a error message and exit().
     *
     * If !fatal then return $def if nothing was passed by the user.
     */
    public function getArg($name, $fatal = false, $def = null)
    {
        if (!array_key_exists($name, $this->opts) || (!is_bool($this->opts[$name] && !$this->opts[$name]))) {
            if ($fatal) {
                throw new Exception('No '.$name.' provided');
            }
            
            return $def;
        }
        
        $value = array_map(function ($v) {
            return is_bool($v) ? true : $v;
        }, (array)$this->opts[$name]);
        
        if (!count(array_filter($value))) {
            if ($fatal) {
                throw new Exception('No '.$name.' provided');
            }
            
            return $def;
        }
        
        $value = array_shift($value);
        return $value;
    }
    
    /**
     * Get an option with possible multiple values. If nothing was given
     * by the user and fatal is set then display an error message and exit();
     */
    public function getArgument($name, $multiple = false, $fatal = false)
    {
        if (!array_key_exists($name, $this->opts) || (!is_bool($this->opts[$name] && !$this->opts[$name]))) {
            if ($fatal) {
                throw new Exception('No '.$name.' provided');
            }
            
            return $multiple ? array() : null;
        }
        
        $value = array_map(function ($v) {
            return is_bool($v) ? true : $v;
        }, (array)$this->opts[$name]);
        
        if (!count(array_filter($value))) {
            if ($fatal) {
                throw new Exception('No '.$name.' provided');
            }
            
            return $multiple ? array() : null;
        }
        
        if (!$multiple) {
            $value = array_shift($value);
        }
        
        return $value;
    }

    /**
     * return $current clamped to be within the range [$min,$max].
     */
    public function clamp($current, $min, $max)
    {
        return max($min, min($max, $current));
    }


    /**
     * A simple method to get --verbose and --dry-run type single arguments
     * without needing to do full arg passing
     *
     * @param name Full binary arg, for example '--verbose'
     * @return false by default, true of arg is set
     */
    public static function getBoolArg( $name )
    {
        global $argv;
        return in_array($name,$argv);
    }
    
    
}
