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
if (!defined('FILESENDER_BASE'))
    die('Missing environment');

class ApplicationMail extends Mail {
    private $to = array('email' => null, 'name' => null, 'object' => null);
    
    /**
     * Constructor
     * 
     * @param mixed $content Lang instance or subject as string
     */
    public function __construct($content = 'No subject') {
        $use_html = Config::get('email_use_html');
        
        $subject = ($content instanceof Translation) ? (string)$content->subject : $content;
        
        parent::__construct(null, $subject, $use_html);
        
        if($content instanceof Translation) {
            $this->writePlain($content->plain);
            
            if($use_html)
                $this->writeHTML($content->html);
        }
    }
    
    /**
     * Adds to
     * 
     * @param mixed $who DBObject or email address
     * @param string $name optionnal name
     */
    public function to($who, $name = '') {
        if(is_object($who)) {
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
     * @return bool success
     */
    public function send() {
        parent::to($this->to['email'], $this->to['name']);
        
        $sender = '';
        if($this->to['object']) {
            try { // Transfer related objects
                $sender = $this->to['object']->transfer->user->email;
            } catch(PropertyAccessException $e) {
                try { // Directly owned objects
                    $sender = $this->to['object']->user->email;
                } catch(PropertyAccessException $e) {}
            }
        }
        
        $context = $this->to['object'] ? strtolower(get_class($this->to['object'])).'-'.$this->to['object']->id : 'no_context';
        
        $from = Config::get('email_from');
        if($from) {
            if($from != 'sender' && !filter_var($from, FILTER_VALIDATE_EMAIL))
                throw new ConfigBadParameterException('email_from');
            
            if($from == 'sender') $from = $sender;
            
            if($from) {
                if(!filter_var($from, FILTER_VALIDATE_EMAIL))
                    throw new BadEmailException($from);
                
                $from_name = Config::get('email_from_name');
                if($from_name) $from = '"'.mb_encode_mimeheader($from_name).'" <'.$from.'>';
                $this->addHeader('From', $from);
            }
        }
        
        $reply_to = Config::get('email_reply_to');
        if($reply_to) {
            if($reply_to != 'sender' && !filter_var($reply_to, FILTER_VALIDATE_EMAIL))
                throw new ConfigBadParameterException('email_reply_to');
            
            if($reply_to == 'sender') $reply_to = $sender;
            
            if($reply_to) {
                if(!filter_var($reply_to, FILTER_VALIDATE_EMAIL))
                    throw new BadEmailException($reply_to);
                
                $reply_to_name = Config::get('email_reply_to_name');
                if($reply_to_name) $reply_to = '"'.mb_encode_mimeheader($reply_to_name).'" <'.$reply_to.'>';
                $this->addHeader('Reply-To', $reply_to);
            }
        }
        
        $return_path = Config::get('email_return_path');
        if($return_path) {
            if($return_path != 'sender' && !filter_var(str_replace('<verp>', 'verp', $return_path), FILTER_VALIDATE_EMAIL))
                throw new ConfigBadParameterException('email_return_path');
            
            if($return_path == 'sender') $return_path = $sender;
            
            if($return_path) {
                if(preg_match('`^(.+)<verp>(.+)$`i', $return_path, $match))
                    $return_path = $match[1].$context.$match[2];
                
                if(!filter_var($return_path, FILTER_VALIDATE_EMAIL))
                    throw new BadEmailException($return_path);
                
                $this->return_path = $return_path;
            }
        }
        
        if($context) {
            $this->msg_id = '<'.$context.'-'.uniqid().'@filesender>';
            $this->addHeader('X-Filesender-Context', $context);
        }
        
        return parent::send();
    }
}
