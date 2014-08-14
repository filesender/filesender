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
    public $id = '';
    public $return_path = '';
    private $html = false;
    private $sender = 'unknown@somewhere';
    private $subject = '?';
    private $contents = array('html' => '', 'plain' => '');
    private $rcpt = array('to' => array(), 'cc' => array(), 'bcc' => array());
    private $attachments = array();
    private $ics;

    private $otherheaders = '';


    /**
     * Constructor
     * */
    function __construct($subject = 'No subject', $sender = 'unknown@somewhere', $sendername = '', $html = false, $idprefix = '') {
        mb_internal_encoding('UTF-8');
        $d = explode('@', $sender);
        $this->id = ($idprefix ? $idprefix . '-' : '') . 'y' . md5(uniqid() . rand(1000, 25000)) . '@' . end($d);
        $this->sender = ($sendername != '') ? mb_encode_mimeheader($sendername) . ' <' . $sender . '>' : $sender;
        $this->subject = mb_encode_mimeheader(
                trim(str_replace(array("\n", "\r"), ' ', $subject)), mb_internal_encoding(), 'Q'
        );
        $this->html = (bool) $html;
        $this->ics = "";
    }

    /**
     * Adds to/cc/bcc
     * @param $email email address
     * @param $name optionnal name
     */
    public function to($email, $name = '') {
        $this->rcpt['to'][] = ($name != '') ? mb_encode_mimeheader($name) . ' <' . $email . '>' : $email;
    }

    public function cc($email, $name = '') {
        $this->rcpt['cc'][] = ($name != '') ? mb_encode_mimeheader($name) . ' <' . $email . '>' : $email;
    }

    public function bcc($email, $name = '') {
        $this->rcpt['bcc'][] = ($name != '') ? mb_encode_mimeheader($name) . ' <' . $email . '>' : $email;
    }

    /**
     * Adds other headers
     * @param $h headers as array (assoc or not)
     */
    public function addHeaders($h = array()) {
        if (count(array_filter(array_keys($h), create_function('$k', 'return !is_numeric($k);'))))
            $h = array_map(create_function('$k,$v', 'return $k.": ".$v;'), array_keys($h), array_values($h));
        $this->otherheaders = implode(GlobalConstants::NEW_LINE, $h);
    }

    /**
     * Set mail contents
     * @param $content mail contents
     * @param $asis boolean telling wether to chunksplit given content
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
     * @param $ctn text data
     */
    public function writeHTML($ctn) {
        if (!$ctn || !$this->html)
            return;
        $this->contents['html'] .= $ctn;
    }

    /**
     * Write Plain part
     * @param $ctn text data
     */
    public function writePlain($ctn) {
        if (!$ctn)
            return;
        $this->contents['plain'] .= $ctn;
    }

    /**
     * Attach a file
     * @param $filepath file path (local relative, local absolute or web url if supported)
     * @param $mode attached / inline
     * @param $filename filename (if omitted, the original filename is kept)
     * Returns false if file not found, true or a content-id if inline mode otherwise, it can be used to put images/links in the content
     */
    public function attach($path, $mode = 'attachment', $name = '', $cid = null) {
        if (!@file_exists($path))
            return false;
        if (!$name)
            $name = basename($path);
        if (!in_array($mode, array('attachment', 'inline')))
            $mode = 'attachment';
        $this->attachments[] = array('path' => $path, 'mode' => $mode, 'name' => $name, 'cid' => $cid);
        return true;
    }

    /**
     * Generates rcpt lines
     * @param &$fst reference to a string to hold the first rcpt to be passed to mail()
     * @param $raw set to true if all "to" emails must be kept into mail source code (aka if not using php mail)
     * @return linearized rcpt list
     */
    private function buildRcpt(&$fst, $raw) {
        if (!count($this->rcpt['to']))
            die('Mailer needs at least one rcpt');

        $fst = $this->rcpt['to'][0];
        if (!$raw)
            array_shift($this->rcpt['to']);

        $s = '';
        if (count($this->rcpt['to']))
            $s .= 'To: ' . implode(', ', $this->rcpt['to']) . GlobalConstants::NEW_LINE;
        if (count($this->rcpt['cc']))
            $s .= 'Cc: ' . implode(', ', $this->rcpt['cc']) . GlobalConstants::NEW_LINE;
        if (count($this->rcpt['bcc']))
            $s .= 'Bcc: ' . implode(', ', $this->rcpt['bcc']) . GlobalConstants::NEW_LINE;
        return $s;
    }

    /**
     * Generate code for attachments
     * @param $bnd mime boundary
     * @return code as string
     */
    private function buildAttachments($bnd, $mprelated = null) {
        $s = '';
        $mime = array_reduce(explode("\n", @file_get_contents(dirname(__FILE__) . '/ymail.mime.txt')), create_function('$g,$e', '$e = trim(preg_replace("`^([^#]+)#`", \'$1\', $e)); if($e == "") return $g; list($c, $m) = preg_split("`\s+`", $e, 2); if($g === 0) $g = array(); return array_merge($g, array($c => $m));'), 0);
        foreach ($this->attachments as $a) {
            if (!is_null($mprelated) && !$mprelated && $a['cid'])
                continue;
            if (!is_null($mprelated) && $mprelated && !$a['cid'])
                continue;
            $name = explode('.', basename($a['path']));
            $ext = array_pop($name);
            $name = implode('.', $name) . '.' . $ext;
            $type = array_key_exists($ext, $mime) ? $mime[$ext] : 'application/octet-stream';
            if ($a['name'])
                $name = $a['name'];

            $s .= GlobalConstants::NEW_LINE . '--' . $bnd . GlobalConstants::NEW_LINE;
            $s .= 'Content-Type: ' . $type . '; name="' . $name . '"' . GlobalConstants::NEW_LINE;
            $s .= 'Content-Transfer-Encoding: base64' . GlobalConstants::NEW_LINE;
            $s .= 'Content-Disposition: ' . $a['mode'] . '; filename="' . $name . '"' . GlobalConstants::NEW_LINE;
            if ($a['cid'])
                $s .= 'Content-ID: ' . $a['cid'] . GlobalConstants::NEW_LINE;
            $s .= GlobalConstants::NEW_LINE . chunk_split(base64_encode(@file_get_contents($a['path']))) . GlobalConstants::NEW_LINE;
        }
        return $s;
    }

    /**
     * Generate code for attachments
     * @param $bnd mime boundary
     * @return code as string
     */
    private function buildIcs($bnd, $mprelated = null) {
        $s = '';
        $s .= GlobalConstants::NEW_LINE . '--' . $bnd . GlobalConstants::NEW_LINE;
        $s .= 'Content-Type: text/calendar;name="meeting.ics";method=REQUEST' . GlobalConstants::NEW_LINE;
        $s .= 'Content-Transfer-Encoding: binary' . GlobalConstants::NEW_LINE;
        return $s;
    }

    /**
     * Build all the mail source
     * @param $raw false if returns mail function compatible array, string returned otherwise
     */
    public function build($raw = false) {
        $mh = 'From: ' . $this->sender . GlobalConstants::NEW_LINE;

        $to = '';
        $mh .= $this->buildRcpt($to, $raw);

        if ($this->id)
            $mh .= 'Message-Id: <' . $this->id . '>' . GlobalConstants::NEW_LINE;
        if ($this->return_path)
            $mh .= 'Return-Path: ' . $this->return_path . GlobalConstants::NEW_LINE;

        $mh .= 'X-Mailer: YMail PHP/' . phpversion() . GlobalConstants::NEW_LINE;
        $mh .= 'Reply-To: ' . $this->sender . GlobalConstants::NEW_LINE;
        $mh .= 'MIME-Version: 1.0' . GlobalConstants::NEW_LINE;

        // Boundaries generation
        $bndid = time() . rand(999, 9999) . uniqid();
        $mime_bnd_mixed = 'mbnd--mixed--' . $bndid;
        $mime_bnd_alt = 'mbnd--alt--' . $bndid;
        $mime_bnd_rel = 'mbnd--rel--' . $bndid;
        $mime_bnd_ics = 'mbnd--ics--' . $bndid;

        $related = (bool) count(array_filter($this->attachments, function($a) {
                            return (bool) $a['cid'];
                        }));
        $mixed = (bool) count(array_filter($this->attachments, function($a) {
                            return !(bool) $a['cid'];
                        }));

        // Subject
        $ms = 'Subject: ' . $this->subject . GlobalConstants::NEW_LINE;

        $mc = '';

        if ($mixed || $related || $this->html)
            $mc .= 'This is a multi-part message in MIME format.' . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;

        $plain = $this->contents['plain'];
        $html = $this->contents['html'];

        if (strpos($html, '<!DOCTYPE') === false) {
            $html = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">' . "\n"
                    . '<html>' . "\n"
                    . "\t" . '<head><meta content="text/html; charset=UTF-8" http-equiv="Content-Type" /></head>' . "\n"
                    . "\t" . '<body>'
                    . $html . "\n"
                    . "\t" . '</body>'
                    . "\n" . '</html>';
        }

        $plain = quoted_printable_encode($plain);
        $html = quoted_printable_encode($html);

        if ($mixed && $this->html && $related) {
            $mh .= 'Content-type: multipart/mixed; boundary="' . $mime_bnd_mixed . '"';

            $mc .= '--' . $mime_bnd_mixed . GlobalConstants::NEW_LINE;
            $mc .= 'Content-type: multipart/alternative; boundary="' . $mime_bnd_alt . '"' . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;

            $mc .= '--' . $mime_bnd_alt . GlobalConstants::NEW_LINE;
            $mc .= 'Content-Type:text/plain; charset="utf-8"' . GlobalConstants::NEW_LINE;
            $mc .= 'Content-Transfer-Encoding: quoted-printable' . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;
            $mc .= $plain . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;

            $mc .= '--' . $mime_bnd_alt . GlobalConstants::NEW_LINE;
            $mc .= 'Content-type: multipart/related; boundary="' . $mime_bnd_rel . '"' . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;

            $mc .= '--' . $mime_bnd_rel . GlobalConstants::NEW_LINE;
            $mc .= 'Content-Type:text/html; charset="utf-8"' . GlobalConstants::NEW_LINE;
            $mc .= 'Content-Transfer-Encoding: quoted-printable' . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;
            $mc .= $html . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;

            $mc .= $this->buildAttachments($mime_bnd_rel, true); // related
            $mc .= '--' . $mime_bnd_rel . '--' . GlobalConstants::NEW_LINE;
            $mc .= '--' . $mime_bnd_alt . '--' . GlobalConstants::NEW_LINE;

            $mc .= $this->buildAttachments($mime_bnd_mixed, false); // mixed
            $mc .= '--' . $mime_bnd_mixed . '--' . GlobalConstants::NEW_LINE;
        } elseif ($mixed && $this->html) {
            $mh .= 'Content-type: multipart/mixed; boundary="' . $mime_bnd_mixed . '"';

            $mc .= '--' . $mime_bnd_mixed . GlobalConstants::NEW_LINE;
            $mc .= 'Content-type: multipart/alternative; boundary="' . $mime_bnd_alt . '"' . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;

            $mc .= '--' . $mime_bnd_alt . GlobalConstants::NEW_LINE;
            $mc .= 'Content-Type:text/plain; charset="utf-8"; format=flowed' . GlobalConstants::NEW_LINE;
            $mc .= 'Content-Transfer-Encoding: quoted-printable' . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;
            $mc .= $plain . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;

            $mc .= '--' . $mime_bnd_alt . GlobalConstants::NEW_LINE;
            $mc .= 'Content-Type:text/html; charset="utf-8"' . GlobalConstants::NEW_LINE;
            $mc .= 'Content-Transfer-Encoding: quoted-printable' . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;
            $mc .= $html . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;

            $mc .= '--' . $mime_bnd_alt . '--' . GlobalConstants::NEW_LINE;

            $mc .= $this->buildAttachments($mime_bnd_mixed, false); // mixed
            $mc .= '--' . $mime_bnd_mixed . '--' . GlobalConstants::NEW_LINE;
        } elseif (!$this->html && ($mixed || $related)) {
            $mh .= 'Content-type: multipart/mixed; boundary="' . $mime_bnd_mixed . '"';

            $mc .= '--' . $mime_bnd_mixed . GlobalConstants::NEW_LINE;
            $mc .= 'Content-Type:text/plain; charset="utf-8"' . GlobalConstants::NEW_LINE;
            $mc .= 'Content-Transfer-Encoding: quoted-printable' . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;
            $mc .= $plain . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;

            $mc .= $this->buildAttachments($mime_bnd_mixed, true); // related for some reason
            $mc .= $this->buildAttachments($mime_bnd_mixed, false); // mixed
            $mc .= '--' . $mime_bnd_mixed . '--' . GlobalConstants::NEW_LINE;
        } elseif ($this->html && $related) {
            $mh .= 'Content-type: multipart/alternative; boundary="' . $mime_bnd_alt . '"';

            $mc .= '--' . $mime_bnd_alt . GlobalConstants::NEW_LINE;
            $mc .= 'Content-Type:text/plain; charset="utf-8"' . GlobalConstants::NEW_LINE;
            $mc .= 'Content-Transfer-Encoding: quoted-printable' . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;
            $mc .= $plain . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;

            $mc .= '--' . $mime_bnd_alt . GlobalConstants::NEW_LINE;
            $mc .= 'Content-type: multipart/related; boundary="' . $mime_bnd_rel . '"' . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;

            $mc .= '--' . $mime_bnd_rel . GlobalConstants::NEW_LINE;
            $mc .= 'Content-Type:text/html; charset="utf-8"' . GlobalConstants::NEW_LINE;
            $mc .= 'Content-Transfer-Encoding: quoted-printable' . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;
            $mc .= $html . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;

            $mc .= $this->buildAttachments($mime_bnd_rel, true); // related
            $mc .= '--' . $mime_bnd_rel . '--' . GlobalConstants::NEW_LINE;
            $mc .= '--' . $mime_bnd_alt . '--' . GlobalConstants::NEW_LINE;
        } elseif ($this->html) {
            $mh .= 'Content-type: multipart/alternative; boundary="' . $mime_bnd_alt . '"';

            $mc .= '--' . $mime_bnd_alt . GlobalConstants::NEW_LINE;
            $mc .= 'Content-Type:text/plain; charset="utf-8"; format=flowed' . GlobalConstants::NEW_LINE;
            $mc .= 'Content-Transfer-Encoding: quoted-printable' . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;
            $mc .= $plain . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;

            $mc .= '--' . $mime_bnd_alt . GlobalConstants::NEW_LINE;
            $mc .= 'Content-Type:text/html; charset="utf-8"' . GlobalConstants::NEW_LINE;
            $mc .= 'Content-Transfer-Encoding: quoted-printable' . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;
            $mc .= $html . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;

            $mc .= '--' . $mime_bnd_alt . '--' . GlobalConstants::NEW_LINE;
        } elseif ($this->ics != '') {
            $mh .= 'Content-type: multipart/mixed; boundary="' . $mime_bnd_mixed . '"';

            $mc .= '--' . $mime_bnd_mixed . GlobalConstants::NEW_LINE;
            $mc .= 'Content-type: multipart/alternative; boundary="' . $mime_bnd_alt . '"' . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;

            $mc .= '--' . $mime_bnd_alt . GlobalConstants::NEW_LINE;
            $mc .= 'Content-Type:text/plain; charset="utf-8"' . GlobalConstants::NEW_LINE;
            $mc .= 'Content-Transfer-Encoding: quoted-printable' . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;
            $mc .= $plain . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;

            $mc .= '--' . $mime_bnd_alt . '--' . GlobalConstants::NEW_LINE;

//			$mc .= $this->buildIcs($mime_bnd_mixed, false); // mixed

            $mc .= GlobalConstants::NEW_LINE . '--' . $mime_bnd_mixed . GlobalConstants::NEW_LINE;
            $mc .= 'Content-Type: text/calendar;name="meeting.ics";method=REQUEST' . GlobalConstants::NEW_LINE;
            $mc .= 'Content-Transfer-Encoding: 7bit' . GlobalConstants::NEW_LINE;
            $mc .= GlobalConstants::NEW_LINE . $this->ics . GlobalConstants::NEW_LINE;

            $mc .= GlobalConstants::NEW_LINE . '--' . $mime_bnd_mixed . '--' . GlobalConstants::NEW_LINE;
        } else {
            $mh .= 'Content-Type:text/plain; charset="utf-8"' . GlobalConstants::NEW_LINE;
            $mh .= 'Content-Transfer-Encoding: quoted-printable';

            $mc .= $plain . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE;
        }

        return $raw ? $mh . GlobalConstants::NEW_LINE . 'Subject: ' . $this->subject . GlobalConstants::NEW_LINE . GlobalConstants::NEW_LINE . $mc : array('to' => $to, 'subject' => $this->subject, 'body' => $mc, 'headers' => $mh);
    }

    /**
     * Sends the mail using mail function
     * @param $debug print mail source to standart output instead of sending it if true
     */
    public function send() {
        $source = $this->build();

        $safemode = ini_get('safe_mode');
        $safemode = ($safemode && !preg_match('`^off$`i', $safemode));

        if (!$safemode && $this->return_path) {
            return mail($source['to'], $this->subject, $source['body'], $source['headers'], '-f' . $this->return_path);
        } else {
            return mail($source['to'], $this->subject, $source['body'], $source['headers']);
        }
    }

    /**
     * Spools the mail in file named from $this->id under given directory
     * @param $dir directory to store the file in, . taken if missing
     */
    public function spool($dir = null) {
        if (!$dir)
            $dir = './ymail.spool';
        if (!@is_dir($dir))
            if (!@mkdir($dir))
                return false;
        $file = preg_replace('`^(.*)/?$`', '$1/' . $this->id, $dir);
        if (!($fp = fopen($file, 'w')))
            return false;
        fwrite($fp, $this->build(true));
        fclose($fp);
        return true;
    }

    /**
     * Sends the source for debug
     * @param $mode target for output (stdout/- => to standard output / download => to download file prompt (in web mode))
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

    public function getIcs() {
        return $this->ics;
    }

    public function setIcs($ics) {
        $this->ics = $ics;
    }

}
