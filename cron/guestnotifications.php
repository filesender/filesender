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
 * *	Redistributions of source code must retain the above copyright
 * 	notice, this list of conditions and the following disclaimer.
 * *	Redistributions in binary form must reproduce the above copyright
 * 	notice, this list of conditions and the following disclaimer in the
 * 	documentation and/or other materials provided with the distribution.
 * *	Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
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

require_once(dirname(dirname(__FILE__)).'/includes/init.php');

Logger::info('[CRON] Guest notifications to send task started');

$guests = Guest::all(Guest::AVAILABLE);
 $notifs = array();
 
if (count($guests) > 0){
    foreach ($guests as $guest){
        $today = date('Y-m-d');
        if (date('Y-m-d',$guest->last_activity) == $today){
            $notifs[] = $guest;
        }
    }
}

if (count($notifs) > 0){
    echo "\tMails to sent:\n ";
    foreach ($notifs as $guest)  {
         // Check from voucher id which options are setted
            try{
                if ($guest->hasOption(GuestOptions::EMAIL_GUEST_ACCESS_UPLOAD_PAGE)){
                    // Send mail to guest the owner of the voucher
                    $c = Lang::translateEmail('guest_access_upload_page')->replace($guest);
                    $mail = new ApplicationMail($c);
                    $mail->to($guest->user_email);
                    $mail->send();
                    echo "\t\t".$guest->email."\n ";
                }
            }catch (GuestNotFoundException $e){
                Logger::log(LogLevels::INFO, $e);
            }
    }
}else{
    echo "No notifications have to be sent\n";
    Logger::info("No notifications have to be sent");
}
    