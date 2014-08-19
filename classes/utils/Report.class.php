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

/**
 * Utility functions holder
 * 
 */
class Report {

    private $objects;
    private $currentReportType;

    public function __construct(DBObject $object = null) {
        if (Auth::isAuthenticated()) {
            if (null != $object) {
                $this->objects = AuditLog::all(
                                array('where' => 'target_type = :targettype AND target_id = :targetid AND user_id = :userid'), array(
                            'targettype' => $object->getClassName(),
                            'targetid' => $object->id,
                            'userid' => Auth::user()->id
                                )
                );
            } else {
                $this->objects = AuditLog::all(
                                array('where' => 'user_id = :userid'), array('userid' => Auth::user()->id)
                );
            }
            $this->currentReportType = ReportTypes::STANDARD;
        } else {
            throw new AuthAuthenticationNotFoundException();
        }
    }


    /**
     * Alows to generate a report
     * 
     * @param $type: type of report 
     * @param $sendmail: if mail has to be sent 
     * @return boolean: true if success, false otherwise
     * @throws NoReportFoundException
     */
    public function generateReport($type = ReportTypes::STANDARD, $sendmail = true) {
        if (sizeof($this->objects) == 0) {
            throw new NoReportFoundException();
        } else {
            $this->currentReportType = $type;
            if ($sendmail) {
                // Send mail
                return $this->sendReportByMail();
            }else{
                return true;
            }
        }
    }

    
    /**
     * Alows to send a report by mail
     * 
     * @param $reports: reports to be sent
     * @return boolean: true if mail sent, false otherwise
     * @throws NoReportFoundException
     */
    public function sendReportByMail($reports = null) {
        if (is_null($reports)) {
            $reports = $this->objects;
        }

        if (sizeof($reports) == 0) {
            throw new NoReportFoundException();
        } else {
            // Getting repliers from config
            if (($noReply = Config::get('noreply')) == null) {
                $noReply = "no_reply@renater.fr";
            }
            if (($noReplyName = Config::get('noreply_name')) == null) {
                $noReplyName = "NO_REPLY";
            }

            $message = "";
            $html = false;
            
            $c = Lang::translateEmail('report_html');
            
            foreach ($reports as $report) {

                $targettype = $report->target_type;
                $target = $targettype::fromId($report->target_id);

                switch ($this->currentReportType) {
                    case ReportTypes::HTML:
                        $html = true;
                        $message .= Template::process('!report_html', array('target' => $target));
                        if(preg_match('`\{reportContent\}`', $c->html)) {
                            $c->html = str_ireplace("{reportContent}", $message, $c->html);
                        }
                        $message = $c->html;
                        break;
                    
                    case ReportTypes::PDF:
                        break;
                    
                    case ReportTypes::STANDARD:
                    default:
                        $html = false;
                        $message .= Template::process('!report_standard', array('target' => $target));
                        if(preg_match('`\{reportContent\}`', $c->plain)) {
                            $c->plain = str_ireplace("{reportContent}", $message, $c->plain);
                        }
                        $message = $c->plain;
                        break;
                        
                }
            }
            
            $mail = new Mail($c->subject, $noReply, $noReplyName, $html);

            foreach (Auth::user()->email as $key => $recipient) {
                $mail->to($recipient);
            }

            $mail->write($message);
            
            return $mail->send();
        }
    }

}
