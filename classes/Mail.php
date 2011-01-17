<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2011, AARNet, HEAnet, SURFnet, UNINETT
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
 * *	Neither the name of AARNet, HEAnet, SURFnet and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
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
    // Send mail
    // 
    public function sendemail($mailobject,$template){

        $authsaml = AuthSaml::getInstance();
        $authvoucher = AuthVoucher::getInstance();

        $CFG = config::getInstance();
        $config = $CFG->loadConfig();

        $fileoriginalname = sanitizeFilename($mailobject['fileoriginalname']);
        $crlf = $config["crlf"];

        $template = str_replace("{siteName}", $config["site_name"], $template);
        $template = str_replace("{fileto}", $mailobject["fileto"], $template);
        $template = str_replace("{serverURL}", $config["site_url"], $template);
        $template = str_replace("{filevoucheruid}", $mailobject["filevoucheruid"], $template);
        $template = str_replace("{fileexpirydate}", date("d-M-Y",strtotime($mailobject["fileexpirydate"])), $template);
        $template = str_replace("{filefrom}", $mailobject["filefrom"], $template);
        $template = str_replace("{fileoriginalname}", $fileoriginalname, $template);
        $template = str_replace("{filename}", $fileoriginalname, $template);	
        $template = str_replace("{filesize}", formatBytes($mailobject["filesize"]), $template);
        $template = str_replace("{CRLF}", $crlf, $template);

        // Replace 'newlines' (various formats) in filemessage with $crlf and count the number of lines
        $mailobject["filemessage"] = preg_replace("/\r\n|\n|\r/", $crlf , $mailobject["filemessage"], -1, $nlcount);

        // Encode the 'filemessage' with a UTF8-safe version of htmlentities to allow for multibyte UTF-8 characters
        // Also insert <br /> linebreak tags to preserve intended formatting in the HTML body part
        $mailobject["htmlfilemessage"] = nl2br(utf8tohtml($mailobject["filemessage"],TRUE));

        // Add extra newlines when filemessage contains more than a few words
        // (to get a better layout in the non HTML body part)
        if ( $nlcount > 0 ) {
             $mailobject["filemessage"] = $crlf . $crlf . $mailobject["filemessage"];
        }

        $template = str_replace("{filemessage}", $mailobject["filemessage"], $template);
        $template = str_replace("{htmlfilemessage}", $mailobject["htmlfilemessage"], $template);


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

        $headers .= "Cc: <" . $mailobject['filefrom'] . ">".$crlf;
        $to = "<".$mailobject['fileto'] . ">";


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
        $subj_encoding = detect_char_encoding($subject) ;
        if ($subj_encoding != 'US-ASCII') {
            $subject = mime_qp_encode_header_value($subject,'UTF-8',$subj_encoding,$crlf) ;
        }

        // Check and set the needed encoding for the body and convert if necessary
        $body_encoding = detect_char_encoding($template) ;
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
        $body = wordwrap($crlf ."--simple_mime_boundary".$crlf ."Content-type:text/plain; charset=iso-8859-1".$crlf.$crlf .$message,70);

        if (mail($to, $subject, $body, $headers)) {
            return true;
        } else {
            return false;
        }
    }

}
?>
