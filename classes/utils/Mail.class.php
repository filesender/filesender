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

class Mail {
    private $msg_id = null;
    private $return_path = null;
    private $html = false;
    private $subject = '?';
    private $contents = array('html' => '', 'plain' => '');
    private $rcpt = array('To' => array(), 'Cc' => array(), 'Bcc' => array());
    private $attachments = array();
    
    private $headers = array();
    
    /**
     * Constructor
     * 
     * @param string $to (optionnal)
     * @param string $subject (optionnal)
     * @param bool $html (optionnal)
     */
    public function __construct($to = null, $subject = '', $html = false) {
        mb_internal_encoding('UTF-8');
        
        if($to) $this->to($to);
        $this->subject = $subject;
        $this->html = (bool)$html;
    }
    
    /**
     * Setter
     * 
     * @param string $property property to get
     * @param mixed $value value to set property to
     * 
     * @throws BadEmailException
     * @throws PropertyAccessException
     */
    public function __set($property, $value) {
        if($property == 'subject') {
            $this->subject = mb_encode_mimeheader(trim(str_replace(array("\n", "\r"), ' ', $value)), mb_internal_encoding(), 'Q');
        }else if($property == 'return_path') {
            if(!filter_var($value, FILTER_VALIDATE_EMAIL)) throw new BadEmailException($value);
            $this->return_path = (string)$value;
        }else if($property == 'html') {
            $this->html = (bool)$value;
        }else if($property == 'msg_id') {
            $this->msg_id = (string)$value;
        }else throw new PropertyAccessException($this, $property);
    }
    
    /**
     * Adds recipient
     * 
     * @param string $mode to/cc/bcc
     * @param $email email address
     * @param $name optionnal name
     */
    public function addRcpt($mode, $email, $name = '') {
        $this->rcpt[ucfirst($mode)][] = $name ? mb_encode_mimeheader($name).' <'.$email.'>' : $email;
    }
    
    /**
     * Adds to
     * 
     * @param string $email email address
     * @param string $name optionnal name
     */
    public function to($email, $name = '') {
        $this->addRcpt('To', $email, $name);
    }
    
    /**
     * Adds cc
     * 
     * @param string $email email address
     * @param string $name optionnal name
     */
    public function cc($email, $name = '') {
        $this->addRcpt('Cc', $email, $name);
    }
    
    /**
     * Adds bcc
     * 
     * @param string $email email address
     * @param string $name optionnal name
     */
    public function bcc($email, $name = '') {
        $this->addRcpt('Bcc', $email, $name);
    }
    
    /**
     * Adds header(s)
     * 
     * @param mixed $header name or array of name=>value
     * @param string $value (optionnal)
     */
    public function addHeader($header, $value = null) {
        if(!is_array($header)) $header = array($header => $value);
        
        foreach($header as $name => $value)
            $this->headers[$name] = $value;
    }
    
    /**
     * Set mail contents
     * 
     * @param string $content mail contents
     * @param bool $asis wether to chunksplit given content
     */
    public function write($contents, $asis = false) {
        if ($this->html) {
            $ctns = preg_replace('`<a[^>]+href=["\']?(mailto:)?([^"\']+)["\']?[^>]*>([^<]+)</a>`i', '$2', $contents);
            if ($asis) {
                $this->contents['html'] .= $contents;
            } else {
                $words = explode(' ', $contents);
                $ctn = array_shift($words);
                foreach ($words as $w) {
                    if (strlen($ctn . ' ' . $w) > 76) {
                        $this->contents['html'] .= $ctn . "\n";
                        $ctn = $w;
                    } else
                        $ctn .= ' ' . $w;
                }
                $this->contents['html'] .= $ctn;
            }
            $contents = strip_tags(preg_replace(array('`(?<!\n)<br\s*/?>`i', '`<br\s*/?>(?!\n)`i'), "\n", $ctns));
        }

        if ($asis) {
            $this->contents['plain'] .= $contents;
        } else {
            $words = explode(' ', $contents);
            $ctn = array_shift($words);
            foreach ($words as $w) {
                if (strlen($ctn . ' ' . $w) > 76) {
                    $this->contents['plain'] .= $ctn . "\n";
                    $ctn = $w;
                } else
                    $ctn .= ' ' . $w;
            }
            $this->contents['plain'] .= $ctn;
        }
    }
    
    /**
     * Write HTML part
     * 
     * @param string $ctn text data
     */
    public function writeHTML($ctn) {
        if (!$ctn || !$this->html)
            return;
        $this->contents['html'] .= $ctn;
    }
    
    /**
     * Write Plain part
     * 
     * @param string $ctn text data
     */
    public function writePlain($ctn) {
        if (!$ctn)
            return;
        $this->contents['plain'] .= $ctn;
    }
    
    /**
     * Attach a file
     * 
     * @param string $filepath file path (local relative, local absolute or web url if supported)
     * @param string $mode attached / inline
     * @param string $filename filename (if omitted, the original filename is kept)
     * 
     * @return bool false if file not found, true or a content-id if inline mode otherwise, it can be used to put images/links in the content
     */
    public function attach($path, $mode = 'attachment', $name = '', $cid = null, $mime = null) {
        if(!@file_exists($path))
            return false;
        
        if(!$name)
            $name = basename($path);
        
        if(!in_array($mode, array('attachment', 'inline')))
            $mode = 'attachment';
        
        $this->attachments[] = array('path' => $path, 'mode' => $mode, 'name' => $name, 'cid' => $cid, 'mime' => $mime);
        
        return true;
    }
    
    /**
     * Generate code for attachments
     * 
     * @param string $bnd mime boundary
     * 
     * @return string
     */
    private function buildAttachments($bnd, $mprelated = null) {
        $nl = GlobalConstants::NEW_LINE;
        
        $s = '';
        foreach ($this->attachments as $a) {
            if (!is_null($mprelated) && !$mprelated && $a['cid'])
                continue;
            
            if (!is_null($mprelated) && $mprelated && !$a['cid'])
                continue;
            
            $name = $a['name'] ? $a['name'] : basename($a['path']);
            
            $type = $a['mime'] ? $a['mime'] : Mime::getFromFile($name);
            
            $s .= $nl . '--' . $bnd . $nl;
            $s .= 'Content-Type: ' . $type . '; name="' . $name . '"' . $nl;
            $s .= 'Content-Transfer-Encoding: base64' . $nl;
            $s .= 'Content-Disposition: ' . $a['mode'] . '; filename="' . $name . '"' . $nl;
            if ($a['cid'])
                $s .= 'Content-ID: ' . $a['cid'] . $nl;
            
            $s .= $nl . chunk_split(base64_encode(@file_get_contents($a['path']))) . $nl;
        }
        return $s;
    }
    
    /**
     * Build all the mail source
     * 
     * @param bool $raw if false returns mail function compatible array, string returned otherwise
     * 
     * @return mixed
     */
    public function build($raw = false) {
        $nl = GlobalConstants::NEW_LINE;
        
        $headers = $this->headers;
        
        if(!$this->msg_id) $this->msg_id = 'filesender-'.uniqid();
        
        $to = $raw ? null : array_shift($this->rcpt['To']);
        
        foreach($this->rcpt as $mode => $rcpt)
            if(count($rcpt))
                $headers[$mode] = implode(', ', $rcpt);
        
        $headers['Message-Id'] = $this->msg_id;
        
        if ($this->return_path)
            $headers['Return-Path'] = $this->return_path;
        
        $headers['X-Mailer'] = 'YMail PHP/' . phpversion();
        $headers['MIME-Version'] = '1.0';
        
        // Boundaries generation
        $bndid = time() . rand(999, 9999) . uniqid();
        $mime_bnd_mixed = 'mbnd--mixed--' . $bndid;
        $mime_bnd_alt = 'mbnd--alt--' . $bndid;
        $mime_bnd_rel = 'mbnd--rel--' . $bndid;
        
        $related = (bool) count(array_filter($this->attachments, function($a) {
            return (bool) $a['cid'];
        }));
        
        $mixed = (bool) count(array_filter($this->attachments, function($a) {
            return !(bool) $a['cid'];
        }));
        
        if($raw) $headers['Subject'] = $this->subject;
        
        $content = '';
        
        if ($mixed || $related || $this->html)
            $content .= 'This is a multi-part message in MIME format.' . $nl.$nl;
        
        $plain = $this->contents['plain'];
        $html = $this->contents['html'];
        
        if (preg_match('`<body[^>]*>`', $html)) // Strip existing body
            $html = preg_replace('`^.*<body[^>]*>(.+)</body>.*$`ims', '$1', $html);
        
        $styles = array('www/css/mail.css', 'www/skin/mail.css');
        $css = '';
        foreach($styles as $file)
            if(file_exists(FILESENDER_BASE.'/'.$file))
                $css .= "\n\n".file_get_contents(FILESENDER_BASE.'/'.$file);
        $css = trim($css);
        
        $html = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">' . "\n"
                . '<html>' . "\n"
                . "\t" . '<head>' . "\n"
                . "\t\t" . '<meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />' . "\n"
                .($css ? "\t\t" . '<style type="text/css">'.$css.'</style>' : '')
                . "\t" . '</head>' . "\n"
                . "\t" . '<body>'
                . $html . "\n"
                . "\t" . '</body>'
                . "\n" . '</html>';
        
        $plain = quoted_printable_encode($plain);
        $html = quoted_printable_encode($html);
        
        if ($mixed && $this->html && $related) {
            $headers['Content-type'] = 'multipart/mixed; boundary="' . $mime_bnd_mixed . '"';
            
            $content .= '--' . $mime_bnd_mixed . $nl;
            $content .= 'Content-type: multipart/alternative; boundary="' . $mime_bnd_alt . '"' . $nl.$nl;
            
            $content .= '--' . $mime_bnd_alt . $nl;
            $content .= 'Content-Type:text/plain; charset="utf-8"' . $nl;
            $content .= 'Content-Transfer-Encoding: quoted-printable' . $nl.$nl;
            $content .= $plain . $nl.$nl;
            
            $content .= '--' . $mime_bnd_alt . $nl;
            $content .= 'Content-type: multipart/related; boundary="' . $mime_bnd_rel . '"' . $nl.$nl;
            
            $content .= '--' . $mime_bnd_rel . $nl;
            $content .= 'Content-Type:text/html; charset="utf-8"' . $nl;
            $content .= 'Content-Transfer-Encoding: quoted-printable' . $nl.$nl;
            $content .= $html . $nl.$nl;
            
            $content .= $this->buildAttachments($mime_bnd_rel, true); // related
            $content .= '--' . $mime_bnd_rel . '--' . $nl;
            $content .= '--' . $mime_bnd_alt . '--' . $nl;
            
            $content .= $this->buildAttachments($mime_bnd_mixed, false); // mixed
            $content .= '--' . $mime_bnd_mixed . '--' . $nl;
        } elseif ($mixed && $this->html) {
            $headers['Content-type'] = 'multipart/mixed; boundary="' . $mime_bnd_mixed . '"';
            
            $content .= '--' . $mime_bnd_mixed . $nl;
            $content .= 'Content-type: multipart/alternative; boundary="' . $mime_bnd_alt . '"' . $nl.$nl;
            
            $content .= '--' . $mime_bnd_alt . $nl;
            $content .= 'Content-Type:text/plain; charset="utf-8"; format=flowed' . $nl;
            $content .= 'Content-Transfer-Encoding: quoted-printable' . $nl.$nl;
            $content .= $plain . $nl.$nl;
            
            $content .= '--' . $mime_bnd_alt . $nl;
            $content .= 'Content-Type:text/html; charset="utf-8"' . $nl;
            $content .= 'Content-Transfer-Encoding: quoted-printable' . $nl.$nl;
            $content .= $html . $nl.$nl;
            
            $content .= '--' . $mime_bnd_alt . '--' . $nl;
            
            $content .= $this->buildAttachments($mime_bnd_mixed, false); // mixed
            $content .= '--' . $mime_bnd_mixed . '--' . $nl;
        } elseif (!$this->html && ($mixed || $related)) {
            $headers['Content-type'] = 'multipart/mixed; boundary="' . $mime_bnd_mixed . '"';
            
            $content .= '--' . $mime_bnd_mixed . $nl;
            $content .= 'Content-Type:text/plain; charset="utf-8"' . $nl;
            $content .= 'Content-Transfer-Encoding: quoted-printable' . $nl.$nl;
            $content .= $plain . $nl.$nl;
            
            $content .= $this->buildAttachments($mime_bnd_mixed, true); // related for some reason
            $content .= $this->buildAttachments($mime_bnd_mixed, false); // mixed
            $content .= '--' . $mime_bnd_mixed . '--' . $nl;
        } elseif ($this->html && $related) {
            $headers['Content-type'] = 'multipart/alternative; boundary="' . $mime_bnd_alt . '"';
            
            $content .= '--' . $mime_bnd_alt . $nl;
            $content .= 'Content-Type:text/plain; charset="utf-8"' . $nl;
            $content .= 'Content-Transfer-Encoding: quoted-printable' . $nl.$nl;
            $content .= $plain . $nl.$nl;
            
            $content .= '--' . $mime_bnd_alt . $nl;
            $content .= 'Content-type: multipart/related; boundary="' . $mime_bnd_rel . '"' . $nl.$nl;
            
            $content .= '--' . $mime_bnd_rel . $nl;
            $content .= 'Content-Type:text/html; charset="utf-8"' . $nl;
            $content .= 'Content-Transfer-Encoding: quoted-printable' . $nl.$nl;
            $content .= $html . $nl.$nl;
            
            $content .= $this->buildAttachments($mime_bnd_rel, true); // related
            $content .= '--' . $mime_bnd_rel . '--' . $nl;
            $content .= '--' . $mime_bnd_alt . '--' . $nl;
        } elseif ($this->html) {
            $headers['Content-type'] = 'multipart/alternative; boundary="' . $mime_bnd_alt . '"';
            
            $content .= '--' . $mime_bnd_alt . $nl;
            $content .= 'Content-Type:text/plain; charset="utf-8"; format=flowed' . $nl;
            $content .= 'Content-Transfer-Encoding: quoted-printable' . $nl.$nl;
            $content .= $plain . $nl.$nl;
            
            $content .= '--' . $mime_bnd_alt . $nl;
            $content .= 'Content-Type:text/html; charset="utf-8"' . $nl;
            $content .= 'Content-Transfer-Encoding: quoted-printable' . $nl.$nl;
            $content .= $html . $nl.$nl;
            
            $content .= '--' . $mime_bnd_alt . '--' . $nl;
        } else {
            $headers['Content-Type'] = 'text/plain; charset="utf-8"' . $nl;
            $headers['Content-Transfer-Encoding'] = 'quoted-printable';
            
            $content .= $plain . $nl.$nl;
        }
        
        $headers = implode($nl, array_map(function($name, $value) {
            return $name.': '.$value;
        }, array_keys($headers), array_values($headers)));
        
        if($raw) return $headers . $nl.$nl . $content;
        
        return array('to' => $to, 'subject' => $this->subject, 'body' => $content, 'headers' => $headers);
    }
    
    /**
     * Sends the mail using mail function
     * 
     * @return bool success
     */
    public function send() {
        $source = $this->build();
        
        $safemode = ini_get('safe_mode');
        $safemode = ($safemode && !preg_match('`^off$`i', $safemode));
        
        if (!$safemode && $this->return_path) {
            return mail($source['to'], $this->subject, $source['body'], $source['headers'], '-r' . $this->return_path);
        } else {
            Logger::warn('Safe mode is on, cannot set the return_path for sent email');
            return mail($source['to'], $this->subject, $source['body'], $source['headers']);
        }
    }
    
    /**
     * Spools the mail in file named from $this->id under given directory
     * 
     * @param string $dir directory to store the file in, . taken if omitted
     * 
     * @return bool success
     */
    public function spool($dir = null) {
        if (!$dir)
            $dir = './ymail.spool';
        if (!@is_dir($dir))
            if (!@mkdir($dir))
                return false;
        $file = preg_replace('`^(.*)/?$`', '$1/' . $this->id, $dir);
        if (!($fp = fopen($file, 'w')))
            throw new CoreCannotWriteFileException($file);
        fwrite($fp, $this->build(true));
        fclose($fp);
        return true;
    }
    
    /**
     * Sends the source for debug
     * 
     * @param string $mode target for output (stdout/- => to standard output / download => to download file prompt (in web mode))
     */
    public function debug($mode = '-') {
        $source = $this->build(true);
        if ($mode == 'download') {
            header('Content-type: application/force-download');
            header('Content-Disposition: attachment; filename=mail_' . $this->id . '.txt');
            echo $source;
        } elseif ($mode == 'raw') {
            echo $source;
        } elseif ($mode == '-' || $mode == 'stdout') {
            print_r(nl2br(htmlspecialchars($source)));
        }
    }
}
