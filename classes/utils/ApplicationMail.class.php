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

class ApplicationMail extends Mail
{
    private $to = array('email' => null, 'name' => null, 'object' => null);
    
    /**
     * Constructor
     *
     * @param mixed $content Lang instance or subject as string
     */
    public function __construct($content = 'No subject')
    {
        $use_html = Config::get('email_use_html');
        
        // Cast content to string if translation object
        $subject = ($content instanceof Translation) ? $content->subject : $content;
        
        if ($subject instanceof Translation) {
            $subject = $subject->out();
        }
        
        if (is_array($subject)) {

            $c = count($subject);
            // filter blank translations.
            $subject = array_filter($subject, function ($s) {
                return strlen($s);
            });
            // if we filtered everything then add a blank back again
            if( !count($subject) && $c ) {
                $subject[] = '';
            }
            
            // Drop subjects with remaining placeholders
            $subject = array_filter($subject, function ($s) { 
                return !preg_match('`\{([a-z_]+:)?[a-z][a-z0-9_\.\(\)]\}`i', $s);
            });
            
            $subject = $subject['prefix'].' '.array_pop($subject);
        }
        
        // Trigger basic mail build
        parent::__construct(null, $subject, $use_html);
        
        // Write content if a translation object was given
        if ($content instanceof Translation) {
            $this->writePlain($content->plain);
            
            if ($use_html) {
                $this->writeHTML($content->html);
            }
        }
    }
    
    /**
     * Quick translated sending
     *
     * @param string $translation_id
     * @param mixed $to recipient / guest / email
     * @param mixed ... additional translation variables
     */
    public static function quickSend($translation_id, $to /*, ... */)
    {
        // Get additional arguments
        $vars = array_slice(func_get_args(), 2);
        
        // Manage recipient if object given, get language if possible
        $lang = null;
        if (is_object($to)) {
            array_unshift($vars, $to);
            if ($to instanceof User) {
                $lang = $to->lang;
                $to = $to->email;
            }
            if ($to instanceof Recipient) {
                $lang = $to->transfer->lang;
            }
        }
        
        // Translate email and replace variables
        $tr = Lang::translateEmail($translation_id, $lang);
        if ($vars) {
            $tr = call_user_func_array(array($tr, 'replace'), $vars);
        }
        
        $ctx = $vars ? self::getContext($vars) : null;
        
        // Create email and send it right away
        $mail = new self($tr);
        $mail->setDebugTemplate($translation_id);
        $mail->to($to);
        $mail->send($ctx);
    }
    
    /**
     * Get context from set of variables by order of priority
     *
     * @param array $vars
     *
     * @return DBObject
     */
    public static function getContext($vars)
    {
        $dbos = array();
        foreach ($vars as $var) {
            if (is_object($var) && ($var instanceof DBObject)) {
                $dbos[get_class($var)] = $var;
            }
        }
        
        foreach (array('Recipient', 'Guest', 'File', 'Transfer') as $p) {
            if (array_key_exists($p, $dbos)) {
                return $dbos[$p];
            }
        }
        
        return null;
    }
    
    /**
     * Adds to
     *
     * @param mixed $who DBObject or email address
     * @param string $name optional name
     */
    public function to($who, $name = '')
    {
        if (is_object($who)) {
            $this->to['email'] = $who->email;
            $this->to['object'] = $who;
        } else {
            $this->to['email'] = $who;
            $this->to['object'] = null;
        }
        
        $this->to['name'] = $name;
    }
    
    /**
     * Sends the mail
     *
     * @param DBObject $context
     *
     * @return bool success
     */
    public function send($context = null)
    {
        // Add registered recipient
        parent::to($this->to['email'], $this->to['name']);
        
        // Get sender from recipient data
        $sender = null;
        if (!$this->to['object'] && $context && ($context instanceof DBObject)) {
            $this->to['object'] = $context;
        }
        
        if ($this->to['object']) {
            if (in_array(get_class($this->to['object']), array('Recipient', 'Guest', 'File', 'Transfer'))) {
                $sender = $this->to['object']->owner;
            }
        }
        
        if (!$sender) {
            $sender = (object)array('email' => $this->to['email']);
        } // Own action
        
        // Context identifier
        $context = $this->to['object'] ? strtolower(get_class($this->to['object'])).'-'.$this->to['object']->id : 'no_context';
        
        // Build from field depending on config
        $from = Config::get('email_from');
        if (is_array($from)) {
            if (!($sender instanceof User)) {
                $from = array_filter($from, function ($f) {
                    return $f !== 'sender';
                });
            }
            
            $from = array_shift($from);
        }
        
        if ($from) {
            if ($from != 'sender' && !Utilities::validateEmail($from)) {
                throw new ConfigBadParameterException('email_from');
            }
            
            if ($from == 'sender') {
                $from = $sender->email;
            }
            
            // Got one, validate and set header
            if ($from) {
                if (!Utilities::validateEmail($from)) {
                    throw new BadEmailException($from);
                }
                
                $from_name = Config::get('email_from_name');
                if (is_array($from_name)) {
                    $from_name = ($sender instanceof User) ? array_shift($from_name) : array_pop($from_name);
                }
                
                if ($from_name) {
                    $attributes = array();
                    if ($sender instanceof User) {
                        $attributes = (array)$sender->additional_attributes;
                    }
                    
                    $attributes['email'] = $sender->email;
                    
                    list($loc, $dom) = explode('@', $sender->email);
                    
                    if (!array_key_exists('name', $attributes)) { // Default name
                        $attributes['name'] = $loc;
                    }
                    
                    foreach ($attributes as $k => $v) {
                        $from_name = str_replace('{'.$k.'}', $v, $from_name);
                    }
                    
                    $from = '"'.mb_encode_mimeheader($from_name).'" <'.$from.'>';
                }
                
                $this->addHeader('From', $from);
            }
        }
        
        // Build reply-to field depending on config
        $reply_to = Config::get('email_reply_to');
        if ($reply_to) {
            if ($reply_to != 'sender' && !Utilities::validateEmail($reply_to)) {
                throw new ConfigBadParameterException('email_reply_to');
            }
            
            if ($reply_to == 'sender') {
                $reply_to = $sender->email;
            }
            
            // Got one, validate and set header
            if ($reply_to) {
                if (!Utilities::validateEmail($reply_to)) {
                    throw new BadEmailException($reply_to);
                }
                
                $reply_to_name = Config::get('email_reply_to_name');
                if ($reply_to_name) {
                    $reply_to = '"'.mb_encode_mimeheader($reply_to_name).'" <'.$reply_to.'>';
                }
                $this->addHeader('Reply-To', $reply_to);
            }
        }
        
        // Build return path field depending on config
        $return_path = Config::get('email_return_path');
        if ($return_path) {
            if ($return_path != 'sender' && !Utilities::validateEmail(str_replace('<verp>', 'verp', $return_path))) {
                throw new ConfigBadParameterException('email_return_path');
            }
            
            if ($return_path == 'sender') {
                $return_path = $sender->email;
            }
            
            // Got one, validate and set property to be passed to PHP's mail internal
            if ($return_path) {
                // If verp pattern insert context so that return path alone tells which object the bounce is related to
                if (preg_match('`^(.+)<verp>(.+)$`i', $return_path, $match)) {
                    $return_path = $match[1].$context.$match[2];
                }
                
                if (!Utilities::validateEmail($return_path)) {
                    throw new BadEmailException($return_path);
                }
                
                $this->return_path = $return_path;
            }
        }
        
        // Set context in headers so that it is returned along with bounces
        $add_headers = Config::get('email_headers');
        if ($context) {
            $this->msg_id = '<'.$context.'-'.uniqid().'@filesender>';
            $this->addHeader('X-Filesender-Context', $context);
            if (is_array($add_headers)) {
                foreach ($add_headers as $key => $value) {
                    $this->addHeader($key, $value);
                }
            }
        }
        
        // Send email
        return parent::send();
    }
}
