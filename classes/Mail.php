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

    // Simple check for ISO-8859-1
    private function detectLatin1($string) {
        return (preg_match("/^[\\x00-\\xFF]*$/u", $string) === 1);
    }
    
    // Simple check for UTF-8
    // Returns true if $string is UTF-8 and false otherwise.
    // From http://w3.org/International/questions/qa-forms-utf-8.html
    // Modified to only fire on the non-ASCII multibyte chars (courtesy
    // of chris AT w3style.co DOT uk)
    private function detectUTF8($string) {
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
    
    // detect the required charset MIME encoding for $string
    // only distinguishes between US-ASCII, ISO-8859-1 and UTF-8
    private function detect_char_encoding($string) {
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
    
    // QP header encoding using iconv_mime_encode
    private function mime_qp_encode_header_value($string,$charsetin,$charsetout,$crlf) {
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

    // converts a UTF8-string into HTML entities
    //  - $utf8:        the UTF8-string to convert
    //  - $encodeTags:  booloean. TRUE will convert "<" to "&lt;"
    //  - return:       returns the converted HTML-string
    // From: http://www.php.net/manual/en/function.htmlentities.php#96648
    private function utf8tohtml($utf8, $encodeTags) {
        $result = '';
        for ($i = 0; $i < strlen($utf8); $i++) {
            $char = $utf8[$i];
            $ascii = ord($char);
            if ($ascii < 128) {
                // one-byte character
                $result .= ($encodeTags) ? htmlentities($char) : $char;
            } else if ($ascii < 192) {
                // non-utf8 character or not a start byte
            } else if ($ascii < 224) {
                // two-byte character
                $result .= htmlentities(substr($utf8, $i, 2), ENT_QUOTES, 'UTF-8');
                $i++;
            } else if ($ascii < 240) {
                // three-byte character
                $ascii1 = ord($utf8[$i+1]);
                $ascii2 = ord($utf8[$i+2]);
                $unicode = (15 & $ascii) * 4096 +
                           (63 & $ascii1) * 64 +
                           (63 & $ascii2);
                $result .= "&#$unicode;";
                $i += 2;
            } else if ($ascii < 248) {
                // four-byte character
                $ascii1 = ord($utf8[$i+1]);
                $ascii2 = ord($utf8[$i+2]);
                $ascii3 = ord($utf8[$i+3]);
                $unicode = (15 & $ascii) * 262144 +
                           (63 & $ascii1) * 4096 +
                           (63 & $ascii2) * 64 +
                           (63 & $ascii3);
                $result .= "&#$unicode;";
                $i += 3;
            }
        }
        // This function doesn't encode all UTF8 sequences to &#$unicode;
        // Enable (uncomment) the following kludge to encode left over UTF-8
        // chars by mb_convert_encoding() the result (needs the mbstring
        // PHP extension).
        // return mb_convert_encoding($result, 'HTML-ENTITIES', "UTF-8");
	return $result;
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
        $template = str_replace("{filesize}", formatBytes($mailobject["filesize"]), $template);
        $template = str_replace("{CRLF}",  $config["crlf"], $template);
        $template = str_replace("{filemessage}", $mailobject["filemessage"], $template);
        // Encode the 'filemessage' with a UTF8-safe version of htmlentities to allow for multibyte UTF-8 characters
        $template = str_replace("{htmlfilemessage}", $this->utf8tohtml($mailobject["filemessage"],TRUE), $template);

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
