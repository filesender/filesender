<?php

/*
 *  Filsender www.filesender.org
 *      
 *  Copyright (c) 2009-2010, Aarnet, HEAnet, UNINETT
 * 	All rights reserved.
 *
 * 	Redistribution and use in source and binary forms, with or without
 *	modification, are permitted provided that the following conditions are met:
 *	* 	Redistributions of source code must retain the above copyright
 *   		notice, this list of conditions and the following disclaimer.
 *   	* 	Redistributions in binary form must reproduce the above copyright
 *   		notice, this list of conditions and the following disclaimer in the
 *   		documentation and/or other materials provided with the distribution.
 *   	* 	Neither the name of Aarnet, HEAnet and UNINETT nor the
 *   		names of its contributors may be used to endorse or promote products
 *   		derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY Aarnet, HEAnet and UNINETT ''AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL Aarnet, HEAnet or UNINETT BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */


//  --------------------------------
// email class
// ---------------------------------
class Mail {

    private static $instance = NULL;

    public static function getInstance() {
        // Check for both equality and type		
        if(self::$instance === NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    } 

    //---------------------------------------
    // Functions to handle international characters
    // 
    private function detectLatin1($string) {
        // Simple check for ISO-8859-1
        return (preg_match("/^[\\x00-\\xFF]*$/u", $string) === 1);
    }
    
    private function detectUTF8($string) {
        // Simple check for UTF-8
        // Returns true if $string is UTF-8 and false otherwise.
        // From http://w3.org/International/questions/qa-forms-utf-8.html
        // Modified to only fire on the non-ASCII multibyte chars (courtesy
        // of chris AT w3style.co DOT uk)
        return preg_match('%(?:
        [\xC2-\xDF][\x80-\xBF]        # non-overlong 2-byte
        |\xE0[\xA0-\xBF][\x80-\xBF]               # excluding overlongs
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}      # straight 3-byte
        |\xED[\x80-\x9F][\x80-\xBF]               # excluding surrogates
        |\xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
        |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
        |\xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
        )+%xs', $string);
    }
    
    private function detect_char_encoding($string) {
    // detect the required charset MIME encoding for $string
    // only distinguishes between US-ASCII, ISO-8859-1 and UTF-8
        if ($this->detectUTF8($string)) {
            if ( $this->detectLatin1($string)) {
                return "ISO-8859-1";
            } else {
                return "UTF-8";
            }
        } else { 
            return "US-ASCII";
        }
    }
    
    private function mime_qp_encode_header_value($string,$charsetin,$charsetout,$crlf) {
        // QP header encoding using iconv_mime_encode
        $prefs = array(
            'scheme' => 'Q',
            'input-charset' => $charsetin,
            'output-charset' => $charsetout,
            'line-length' => 76,
            'line-break-chars' => $crlf,
        ); 
        // iconv_mime_encode requires a header name so strip it including
        // the ": "
        return preg_replace('/^HEADER: /', '', iconv_mime_encode("HEADER", $string, $prefs)) ;
    }

    //---------------------------------------
    // Send mail
    // 
    public function sendemail($mailobject,$template){

        $authsaml = AuthSaml::getInstance();
        $authvoucher = AuthVoucher::getInstance();

        $CFG = config::getInstance();
        $config = $CFG->loadConfig();

        $fileoriginalname = sanitizeFilename($mailobject['fileoriginalname']);
        $template = str_replace("{siteName}", $config["site_name"], $template);
        $template = str_replace("{fileto}", $mailobject["fileto"], $template);
        $template = str_replace("{serverURL}", $config["site_url"], $template);
        $template = str_replace("{filevoucheruid}", $mailobject["filevoucheruid"], $template);
        $template = str_replace("{fileexpirydate}", date("d-M-Y",strtotime($mailobject["fileexpirydate"])), $template);
        $template = str_replace("{filefrom}", $mailobject["filefrom"], $template);
        $template = str_replace("{fileoriginalname}", $fileoriginalname, $template);
        $template = str_replace("{filename}", $fileoriginalname, $template);	
        $template = str_replace("{filemessage}", $mailobject["filemessage"], $template);
        // use mb_convert_encoding() in addition to htmlentities() to allow for multibyte UTF-8 characters
        $template = str_replace("{htmlfilemessage}", htmlentities(mb_convert_encoding($mailobject["filemessage"],'HTML-ENTITIES', "UTF-8"),null,null,false), $template);
        $template = str_replace("{filesize}", formatBytes($mailobject["filesize"]), $template);
        $template = str_replace("{CRLF}",  $config["crlf"], $template);

        $crlf = $config["crlf"];

        $headers = "MIME-Version: 1.0".$crlf;
        $headers .= "Content-Type: multipart/alternative; boundary=simple_mime_boundary".$crlf;
        $headers .= "From: <".$mailobject['filefrom'].">".$crlf;

        // if voucher is being used then bcc fileauthuseremail a copy so voucher creator knows a file was sent as they are responsible for the use of the voucher
        if($authvoucher->aVoucher()) {
            if(isset($mailobject['fileauthuseremail'])){
                $headers .= "Bcc: <".$mailobject['fileauthuseremail'].">".$crlf;
            }
        }

        $headers .= "Reply-To: <".$mailobject['filefrom'].">".$crlf;

        $returnpath = "-r <".$mailobject['filefrom'].">".$crlf;

        $to = "<".$mailobject['fileto'].">".","."<".$mailobject['filefrom'].">";


        if(isset($mailobject['filesubject']) && $mailobject['filesubject'] != ""){
            $subject = $config["site_name"].": ".$mailobject['filesubject'];
        } else {
            $tempfilesubject = $config['default_emailsubject'];
            $tempfilesubject = str_replace("{siteName}", $config["site_name"], $tempfilesubject);
            $tempfilesubject = str_replace("{fileoriginalname}", $fileoriginalname, $tempfilesubject);
            $tempfilesubject = str_replace("{filename}", $fileoriginalname, $tempfilesubject);

            $subject =   $tempfilesubject;

        }
        // Check needed encoding for $subject
        // Assumes input string is UTF-8 encoded
        $subj_encoding = $this->detect_char_encoding($subject) ;
        if ($subj_encoding != 'US-ASCII') {
            $subject = $this->mime_qp_encode_header_value($subject,'UTF-8',$subj_encoding,$crlf) ;
        }

        // Check and set the needed encoding for the body and convert if necessary
        $body_encoding = $this->detect_char_encoding($template) ;
        $template = str_replace("{charset}", $body_encoding , $template);
        if ( $body_encoding == 'ISO-8859-1' ) {
            $template = iconv("UTF-8", "ISO-8859-1", $template);
        }

        $body = wordwrap($template,70);

        if (mail($to, $subject, $body, $headers, $returnpath)) {
            return true;
        } else {
            return false;
        }
    }

    //---------------------------------------
    // Send admin mail messages
    // 	
    public function sendemailAdmin($message){

        // send admin notifications via email

        $CFG = config::getInstance();
        $config = $CFG->loadConfig();

        $crlf = $config["crlf"];

        $headers = "MIME-Version: 1.0".$crlf;
        $headers .= "Content-Type: multipart/alternative; boundary=simple_mime_boundary".$crlf;
        $headers .= "From: noreply@".$_SERVER['HTTP_HOST'].$crlf;

        //$headers .= "Reply-To: ".$mailobject['filefrom'].$crlf;
        //$returnpath = "-r".$mailobject['filefrom'].$crlf;

        $to = $config['adminEmail'];

        $subject =   $config['site_name']." - Admin Message";
        $body = wordwrap($crlf ."--simple_mime_boundary".$crlf ."Content-type:text/plain; charset=iso-8859-1".$crlf .$message,70);

        if (mail($to, $subject, $body, $headers)) {
            return true;
        } else {
            return false;
        }
    }

}
?>
