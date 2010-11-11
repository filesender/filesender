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

        // Convert (transliterate) the personal message to ISO-8859-1 when text is in UTF-8
        logEntry("DEBUG sendemail: filemessage = " . $mailobject['filemessage'] . " - Detected encoding: " . mb_detect_encoding($mailobject['filemessage']) . ". ");
        if ( mb_detect_encoding($mailobject['filemessage']) == 'UTF-8' )
        {
            $mailobject['filemessage'] = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $mailobject['filemessage']);
        }
        // Convert (transliterate) the fileoriginalname to ISO-8859-1 when text is in UTF-8
        if ( mb_detect_encoding($fileoriginalname) == 'UTF-8' )
        {
            $fileoriginalname = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $fileoriginalname);
        }

        $template = str_replace("{fileoriginalname}", $fileoriginalname, $template);
        $template = str_replace("{filename}", $fileoriginalname, $template);	
        $template = str_replace("{filemessage}", $mailobject["filemessage"], $template);
        $template = str_replace("{htmlfilemessage}", htmlentities($mailobject["filemessage"]), $template);
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
            // Properly encode the message subject when it contains UTF-8 characters
            logEntry("DEBUG sendemail: filesubject = " . $mailobject['filesubject'] . " - Detected encoding: " . mb_detect_encoding($mailobject['filesubject']) . ". ");
            if ( mb_detect_encoding($mailobject['filesubject']) == 'UTF-8' )
            {
                mb_internal_encoding("UTF-8");
                $mailobject['filesubject'] = mb_encode_mimeheader($mailobject['filesubject'], "UTF-8", "Q", $crlf );
            }
            $subject = $config["site_name"].": ".$mailobject['filesubject'];
        } else {
            $tempfilesubject = $config['default_emailsubject'];
            $tempfilesubject = str_replace("{siteName}", $config["site_name"], $tempfilesubject);
            $tempfilesubject = str_replace("{fileoriginalname}", $fileoriginalname, $tempfilesubject);
            $tempfilesubject = str_replace("{filename}", $fileoriginalname, $tempfilesubject);

            $subject =   $tempfilesubject;

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
