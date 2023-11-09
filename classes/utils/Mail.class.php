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

class Mail
{
    /**
     * Message-Id header value
     */
    private $msg_id = null;


    /**
     * Setting this will force sending emails to off for group testing.
     * Do not call this setting unless you are bulk testing FileSender.
     */
    private static $TESTING_MODE_SO_DO_NOT_SEND_EMAIL = false;
    public static function TESTING_SET_DO_NOT_SEND_EMAIL()
    {
        self::$TESTING_MODE_SO_DO_NOT_SEND_EMAIL = true;
    }


    /**
     * Return path value
     */
    private $return_path = null;
    
    /**
     * HTML body
     */
    private $html = false;
    
    /**
     * Subject header
     */
    private $subject = '?';
    
    /**
     * text body parts
     */
    private $contents = array('html' => '', 'plain' => '');
    
    /**
     * Recipients
     */
    private $rcpt = array('To' => array(), 'Cc' => array(), 'Bcc' => array());
    
    /**
     * Attached files
     */
    private $attachments = array();
    
    /**
     * Additional headers
     */
    private $headers = array();
    
    /**
     * New line style
     */
    private $nl = "\r\n";
    
    /**
     * Constructor
     *
     * @param string $to (optionnal)
     * @param string $subject (optionnal)
     * @param bool $html (optionnal)
     */
    public function __construct($to = null, $subject = '', $html = false)
    {
        mb_internal_encoding('UTF-8');
        
        if ($to) {
            $this->to($to);
        }
        $this->__set('subject', $subject);
        $this->html = (bool)$html;
        
        $nl = Config::get('email_newline');
        if ($nl) {
            $this->nl = $nl;
        }
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
    public function __set($property, $value)
    {
        if ($property == 'subject') {
            $this->subject = mb_encode_mimeheader(trim(str_replace(array("\n", "\r"), ' ', $value)), mb_internal_encoding(), 'Q', $this->nl);
        } elseif ($property == 'return_path') {
            if (!Utilities::validateEmail($value)) {
                throw new BadEmailException($value);
            }
            $this->return_path = (string)$value;
        } elseif ($property == 'html') {
            $this->html = (bool)$value;
        } elseif ($property == 'msg_id') {
            $this->msg_id = (string)$value;
        } else {
            throw new PropertyAccessException($this, $property);
        }
    }
    
    /**
     * Adds recipient
     *
     * @param string $mode to/cc/bcc
     * @param $email email address
     * @param $name optionnal name
     */
    public function addRcpt($mode, $email, $name = '')
    {
        $this->rcpt[ucfirst($mode)][] = $name ? mb_encode_mimeheader($name).' <'.$email.'>' : $email;
    }
    
    /**
     * Adds to
     *
     * @param string $email email address
     * @param string $name optionnal name
     */
    public function to($email, $name = '')
    {
        $this->addRcpt('To', $email, $name);
    }
    
    /**
     * Adds cc
     *
     * @param string $email email address
     * @param string $name optionnal name
     */
    public function cc($email, $name = '')
    {
        $this->addRcpt('Cc', $email, $name);
    }
    
    /**
     * Adds bcc
     *
     * @param string $email email address
     * @param string $name optionnal name
     */
    public function bcc($email, $name = '')
    {
        $this->addRcpt('Bcc', $email, $name);
    }
    
    /**
     * Adds header(s)
     *
     * @param mixed $header name or array of name=>value
     * @param string $value (optionnal)
     */
    public function addHeader($header, $value = null)
    {
        if (!is_array($header)) {
            $header = array($header => $value);
        }
        
        foreach ($header as $name => $value) {
            $this->headers[$name] = $value;
        }
    }
    
    /**
     * Set mail contents
     *
     * @param string $content mail contents
     * @param bool $asis wether to chunksplit given content
     */
    public function write($contents, $asis = false)
    {
        // Process body if HTML
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
                    } else {
                        $ctn .= ' ' . $w;
                    }
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
                } else {
                    $ctn .= ' ' . $w;
                }
            }
            $this->contents['plain'] .= $ctn;
        }
    }
    
    /**
     * Write HTML part
     *
     * @param string $ctn text data
     */
    public function writeHTML($ctn)
    {
        if (!$ctn || !$this->html) {
            return;
        }
        
        $this->contents['html'] .= $ctn;
    }
    
    /**
     * Write Plain part
     *
     * @param string $ctn text data
     */
    public function writePlain($ctn)
    {
        if (!$ctn) {
            return;
        }
        
        $this->contents['plain'] .= $ctn;
    }
    
    /**
     * Attach a file
     *
     * @param MailAttachment $attachment
     * @param bool $related related to content
     */
    public function attach(MailAttachment $attachment)
    {
        $this->attachments[] = $attachment;
    }
    
    /**
     * Get new line style from config
     *
     * @return string
     */
    private function newLine()
    {
    }
    
    /**
     * Generate code for attachments
     *
     * @param string $bnd mime boundary
     *
     * @return string
     */
    private function buildAttachments($bnd, $related = false)
    {
        $s = '';
        foreach ($this->attachments as $attachment) {
            if ((bool)$attachment->cid != $related) {
                continue;
            }
            
            $s .= $this->nl . '--' . $bnd . $this->nl;
            $s .= $attachment->build();
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
    public function build($raw = false)
    {
        // Additional headers
        $headers = $this->headers;
        
        // Generate Message-Id if none
        if (!$this->msg_id) {
            $this->msg_id = 'filesender-'.uniqid();
        }
        
        // Extract "main" recipient
        $to = $raw ? null : array_shift($this->rcpt['To']);
        
        // Add recipients for each reception mode
        foreach ($this->rcpt as $mode => $rcpt) {
            if (count($rcpt)) {
                $headers[$mode] = implode(', ', $rcpt);
            }
        }
        
        // Add Message-Id
        $headers['Message-Id'] = $this->msg_id;
        
        // Add Return-Path if any
        if ($this->return_path) {
            $headers['Return-Path'] = $this->return_path;
        }
        
        // Mailer identification and basic headers
        $headers['X-Mailer'] = 'YMail PHP/' . phpversion();
        $headers['MIME-Version'] = '1.0';
        
        // Boundaries generation
        $bndid = time() . rand(999, 9999) . uniqid();
        $mime_bnd_mixed = 'mbnd--mixed--' . $bndid;
        $mime_bnd_alt = 'mbnd--alt--' . $bndid;
        $mime_bnd_rel = 'mbnd--rel--' . $bndid;
        
        // Get number of each attachment types
        $related = count(array_filter($this->attachments, function ($a) {
            return $a->cid;
        }));
        
        $mixed = count(array_filter($this->attachments, function ($a) {
            return !$a->cid;
        }));
        
        // Add Subject header if raw mail (given to PHP's mail function separately otherwise)
        if ($raw) {
            $headers['Subject'] = $this->subject;
        }
        
        $content = '';
        
        // Required by rfc822
        if ($mixed || $related || $this->html) {
            $content .= 'This is a multi-part message in MIME format.' . $this->nl.$this->nl;
        }
        
        $plain = $this->contents['plain'];
        $html = $this->contents['html'];
        
        // Only keep HTML body content
        if (preg_match('`<body[^>]*>`', $html)) { // Strip existing body
            $html = preg_replace('`^.*<body[^>]*>(.+)</body>.*$`ims', '$1', $html);
        }
        
        // Get HTML mail styles
        $styles = array('www/css/mail.css', 'www/skin/mail.css');
        $css = '';
        foreach ($styles as $file) {
            if (file_exists(FILESENDER_BASE.'/'.$file)) {
                $css .= "\n\n".file_get_contents(FILESENDER_BASE.'/'.$file);
            }
        }
        $css = trim($css);
        
        // Build HTML
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
        
        // Encode contents
        $plain = quoted_printable_encode($plain);
        $html = quoted_printable_encode($html);
        
        if (!preg_match('`\r`', $this->nl)) {
            $plain = str_replace("\r", '', $plain);
            $html = str_replace("\r", '', $html);
        }
        
        if ($mixed && $this->html && $related) {
            // Mail with attachments, embedded attachments and HTML part
            
            $headers['Content-type'] = 'multipart/mixed; boundary="' . $mime_bnd_mixed . '"';
            
            $content .= '--' . $mime_bnd_mixed . $this->nl;
            $content .= 'Content-type: multipart/alternative; boundary="' . $mime_bnd_alt . '"' . $this->nl.$this->nl;
            
            $content .= '--' . $mime_bnd_alt . $this->nl;
            $content .= 'Content-Type:text/plain; charset="utf-8"' . $this->nl;
            $content .= 'Content-Transfer-Encoding: quoted-printable' . $this->nl.$this->nl;
            $content .= $plain . $this->nl.$this->nl;
            
            $content .= '--' . $mime_bnd_alt . $this->nl;
            $content .= 'Content-type: multipart/related; boundary="' . $mime_bnd_rel . '"' . $this->nl.$this->nl;
            
            $content .= '--' . $mime_bnd_rel . $this->nl;
            $content .= 'Content-Type:text/html; charset="utf-8"' . $this->nl;
            $content .= 'Content-Transfer-Encoding: quoted-printable' . $this->nl.$this->nl;
            $content .= $html . $this->nl.$this->nl;
            
            $content .= $this->buildAttachments($mime_bnd_rel, true); // related
            $content .= '--' . $mime_bnd_rel . '--' . $this->nl;
            $content .= '--' . $mime_bnd_alt . '--' . $this->nl;
            
            $content .= $this->buildAttachments($mime_bnd_mixed, false); // mixed
            $content .= '--' . $mime_bnd_mixed . '--' . $this->nl;
        } elseif ($mixed && $this->html) {
            // Mail with attachments and HTML part
            
            $headers['Content-type'] = 'multipart/mixed; boundary="' . $mime_bnd_mixed . '"';
            
            $content .= '--' . $mime_bnd_mixed . $this->nl;
            $content .= 'Content-type: multipart/alternative; boundary="' . $mime_bnd_alt . '"' . $this->nl.$this->nl;
            
            $content .= '--' . $mime_bnd_alt . $this->nl;
            $content .= 'Content-Type:text/plain; charset="utf-8"; format=flowed' . $this->nl;
            $content .= 'Content-Transfer-Encoding: quoted-printable' . $this->nl.$this->nl;
            $content .= $plain . $this->nl.$this->nl;
            
            $content .= '--' . $mime_bnd_alt . $this->nl;
            $content .= 'Content-Type:text/html; charset="utf-8"' . $this->nl;
            $content .= 'Content-Transfer-Encoding: quoted-printable' . $this->nl.$this->nl;
            $content .= $html . $this->nl.$this->nl;
            
            $content .= '--' . $mime_bnd_alt . '--' . $this->nl;
            
            $content .= $this->buildAttachments($mime_bnd_mixed, false); // mixed
            $content .= '--' . $mime_bnd_mixed . '--' . $this->nl;
        } elseif (!$this->html && ($mixed || $related)) {
            // Plain mail with attachments of any type
            
            $headers['Content-type'] = 'multipart/mixed; boundary="' . $mime_bnd_mixed . '"';
            
            $content .= '--' . $mime_bnd_mixed . $this->nl;
            $content .= 'Content-Type:text/plain; charset="utf-8"' . $this->nl;
            $content .= 'Content-Transfer-Encoding: quoted-printable' . $this->nl.$this->nl;
            $content .= $plain . $this->nl.$this->nl;
            
            $content .= $this->buildAttachments($mime_bnd_mixed, true); // related for some reason
            $content .= $this->buildAttachments($mime_bnd_mixed, false); // mixed
            $content .= '--' . $mime_bnd_mixed . '--' . $this->nl;
        } elseif ($this->html && $related) {
            // HTML mail with embedded attachments
            
            $headers['Content-type'] = 'multipart/alternative; boundary="' . $mime_bnd_alt . '"';
            
            $content .= '--' . $mime_bnd_alt . $this->nl;
            $content .= 'Content-Type:text/plain; charset="utf-8"' . $this->nl;
            $content .= 'Content-Transfer-Encoding: quoted-printable' . $this->nl.$this->nl;
            $content .= $plain . $this->nl.$this->nl;
            
            $content .= '--' . $mime_bnd_alt . $this->nl;
            $content .= 'Content-type: multipart/related; boundary="' . $mime_bnd_rel . '"' . $this->nl.$this->nl;
            
            $content .= '--' . $mime_bnd_rel . $this->nl;
            $content .= 'Content-Type:text/html; charset="utf-8"' . $this->nl;
            $content .= 'Content-Transfer-Encoding: quoted-printable' . $this->nl.$this->nl;
            $content .= $html . $this->nl.$this->nl;
            
            $content .= $this->buildAttachments($mime_bnd_rel, true); // related
            $content .= '--' . $mime_bnd_rel . '--' . $this->nl;
            $content .= '--' . $mime_bnd_alt . '--' . $this->nl;
        } elseif ($this->html) {
            // HTML mail
            
            $headers['Content-type'] = 'multipart/alternative; boundary="' . $mime_bnd_alt . '"';
            
            $content .= '--' . $mime_bnd_alt . $this->nl;
            $content .= 'Content-Type:text/plain; charset="utf-8"; format=flowed' . $this->nl;
            $content .= 'Content-Transfer-Encoding: quoted-printable' . $this->nl.$this->nl;
            $content .= $plain . $this->nl.$this->nl;
            
            $content .= '--' . $mime_bnd_alt . $this->nl;
            $content .= 'Content-Type:text/html; charset="utf-8"' . $this->nl;
            $content .= 'Content-Transfer-Encoding: quoted-printable' . $this->nl.$this->nl;
            $content .= $html . $this->nl.$this->nl;
            
            $content .= '--' . $mime_bnd_alt . '--' . $this->nl;
        } else {
            // Plain mail
            
            $headers['Content-Type'] = 'text/plain; charset="utf-8"' . $this->nl;
            $headers['Content-Transfer-Encoding'] = 'quoted-printable';
            
            $content .= $plain . $this->nl.$this->nl;
        }
        
        // Build headers
        $headers = implode($this->nl, array_map(function ($name, $value) {
            return $name.': '.$value;
        }, array_keys($headers), array_values($headers)));
        
        // Return raw if needed
        if ($raw) {
            return $headers . $this->nl.$this->nl . $content;
        }
        
        return array('to' => $to, 'subject' => $this->subject, 'body' => $content, 'headers' => $headers);
    }
    
    /**
     * Sends the mail using mail function
     *
     * @return bool success
     */
    public function send()
    {
        $source = $this->build();

        if (self::$TESTING_MODE_SO_DO_NOT_SEND_EMAIL) {
            // Logger::warn('testing mode so not really sending mail');
            return true;
        }
        Logger::warn('Sending mail');

        $add_minus_r_to_mail = Utilities::isTrue(Config::get('email_send_with_minus_r_option'));
    
        if (Config::get('debug_mail')) {
            $this->sendDebugMail($source);
        } else {

           if( $add_minus_r_to_mail && $this->return_path ) {
               return mail($source['to'], $this->subject, $source['body'], $source['headers'], '-r' . $this->return_path);
           } else {
               return mail($source['to'], $this->subject, $source['body'], $source['headers']);
           }
        }
    }

    private $_debug_template = null;
    public function setDebugTemplate($translation_id)
    {
        //file_put_contents('/tmp/debugmails','PRE'.$translation_id."\n", FILE_APPEND);

        $this->_debug_template = $translation_id;
    }

    public function sendDebugMail($source)
    {
        //file_put_contents('/tmp/debugmails', print_r($this->rcpt, true), FILE_APPEND);

        Logger::error($source);
        $target_dir = '../testmails'.DIRECTORY_SEPARATOR.$source['to'].DIRECTORY_SEPARATOR;
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // get highest mail number
        $dir = new DirectoryIterator($target_dir);
        $highest_number_found = 0;
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                if (preg_match('/^(\d+)\.mail$/', $fileinfo->getFilename(), $matches)) {
                    if ((int)$matches[1] > $highest_number_found) {
                        $highest_number_found = (int)$matches[1];
                    }
                }
            }
        }

        $other_recipients = '';
        foreach (array('To', 'Cc', 'Bcc') as $recipient_type) {
            foreach ($this->rcpt[$recipient_type] as $recipient) {
                $other_recipients .= strtoupper($recipient_type).": ".$recipient."\n";
            }
        }

        $template_id = '';
        if (property_exists($this, '_debug_template') && $this->_debug_template != null) {
            $template_id = "TEMPLATE: ".$this->_debug_template. "\n";
            //file_put_contents('/tmp/debugmails',$template_id."\n", FILE_APPEND);
        }


        //* Write the log
        file_put_contents($target_dir.($highest_number_found+1).'.mail', "SUBJECT: ".$this->subject."\n".$template_id.$other_recipients.$this->contents['html']);
    }
    
    /**
     * Spools the mail in file named from $this->id under given directory
     *
     * @param string $dir directory to store the file in, . taken if omitted
     *
     * @return bool success
     */
    public function spool($dir = null)
    {
        if (!$dir) {
            $dir = './ymail.spool';
        }
        if (!@is_dir($dir)) {
            if (!@mkdir($dir)) {
                return false;
            }
        }
        $file = preg_replace('`^(.*)/?$`', '$1/' . $this->id, $dir);
        if (!($fp = fopen($file, 'w'))) {
            throw new CoreCannotWriteFileException($file);
        }
        fwrite($fp, $this->build(true));
        fclose($fp);
        return true;
    }
    
    /**
     * Sends the source for debug
     *
     * @param string $mode target for output (stdout/- => to standard output / download => to download file prompt (in web mode))
     */
    public function debug($mode = '-')
    {
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

/**
 * Handle mail attachment
 */
class MailAttachment
{
    /**
     * Attachment path
     */
    private $path = null;
    
    /**
     * Attachment name
     */
    private $name = null;
    
    /**
     * Attachment contents
     */
    private $content = null;
    
    /**
     * Attachment mime type
     */
    private $mime_type = null;
    
    /**
     * Attachment disposition
     */
    private $disposition = 'attachment';
    
    /**
     * Attachment transfer encoding
     */
    private $transfer_encoding = 'base64';
    
    /**
     * Attachment cid
     */
    private $cid = null;
    
    /**
     * New line style
     */
    private $nl = "\r\n";
    
    /**
     * Create new empty attachment
     *
     * @param string $name
     * @param string $cid if inline displayed
     */
    public function __construct($name = null)
    {
        $this->name = $name;
        
        $nl = Config::get('email_newline');
        if ($nl) {
            $this->nl = $nl;
        }
    }
    
    /**
     * Build attachment for sending
     *
     * @return string
     */
    public function build()
    {
        // Fail if missing data
        if (is_null($this->content) && is_null($this->path)) {
            throw new MailAttachmentNoContentException($this->path);
        }
        
        // Extract name from path
        if (!$this->name && $this->path) {
            $this->name = basename($this->path);
        }
        
        // Extract mime type from path
        if (!$this->mime_type && $this->name) {
            $this->mime_type = Mime::getFromFile($this->name);
        }
        
        // Set Content-Type part header
        $source = 'Content-Type: '.$this->mime_type.($this->name ? '; name="'.$this->name.'"' : '').$this->nl;
        
        // Set Content-Transfer-Encoding part header
        if ($this->transfer_encoding) {
            $source .= 'Content-Transfer-Encoding: '.$this->transfer_encoding.$this->nl;
        }
        
        // Set Content-Disposition part header
        $source .= 'Content-Disposition: '.$this->disposition.($this->name ? '; filename="'.$this->name.'"' : '').$this->nl;

        // Set Content-ID part header (for embedded attachments)
        if ($this->cid) {
            $source .= 'Content-ID: '.$this->cid.$this->nl;
        }
        
        // Get file data
        $content = $this->content ? $this->content : file_get_contents($this->path);
        
        // Encode file data if needed
        switch ($this->transfer_encoding) {
            case 'base64': $content = chunk_split(base64_encode($content)); break;
        }
        
        $source .= $this->nl.$content.$this->nl;
        
        return $source;
    }
    
    /**
     * Getter
     *
     * @param string $property property to get
     *
     * @return property value
     */
    public function __get($property)
    {
        if (in_array($property, array(
            'path', 'name', 'content', 'mime_type', 'disposition', 'transfer_encoding', 'cid'
        ))) {
            return $this->$property;
        }
        
        if ($property == 'source') {
            return $this->build();
        }
        
        return null;
    }
    
    /**
     * Setter
     *
     * @param string $property property to get
     * @param mixed $value value to set property to
     */
    public function __set($property, $value)
    {
        if ($property == 'path') {
            if (!file_exists($value)) {
                throw new CoreCannotReadFileException($value);
            }
            
            $this->path = $value;
            $this->name = basename($value);
            $this->content = null;
        } elseif ($property == 'content') {
            $this->path = null;
            $this->content = $value;
        } elseif ($property == 'mime_type') {
            $this->mime_type = $value;
        } elseif ($property == 'name') {
            $this->name = $value;
        } elseif ($property == 'disposition') {
            if (!in_array($value, array('inline', 'attachment'))) {
                throw new MailAttachmentBadDispositionException($value);
            }
            
            $this->disposition = $value;
        } elseif ($property == 'transfer_encoding') {
            if ($value && !in_array($value, array('raw', 'base64'))) {
                throw new MailAttachmentBadTransferEncodingException($value);
            }
            
            $this->transfer_encoding = $value;
        } elseif ($property == 'cid') {
            $this->cid = $value;
        }
    }
}
