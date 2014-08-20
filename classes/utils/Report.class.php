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
 * Report class
 * 
 */
class Report {

    private $objects;           // Current list of DBObjects
    private $currentReportType; // User wanted report type

    /**
     * Constructor
     * 
     * @param ReportType $type: type of report to be generated
     * @param DBObject $object: object in relation
     * 
     * @throws AuthAuthenticationNotFoundException
     * @throws ReportTypeNotFoundException
     */
    public function __construct($type, DBObject $object = null) {
        if (ReportTypes::isValidName($type)){
            if (Auth::isAuthenticated()) {
                $this->currentReportType = $type;

                if (null != $object) {
                    $this->objects[$this->currentReportType] = AuditLog::all(
                        array(  'where' => 'target_type = :targettype AND target_id = :targetid AND user_id = :userid'), array(
                                'targettype' => $object->getClassName(),
                                'targetid' => $object->id,
                                'userid' => Auth::user()->id
                        )
                    );
                } else {
                    $this->objects[$this->currentReportType] = AuditLog::all(
                        array('where' => 'user_id = :userid'), array('userid' => Auth::user()->id)
                    );
                }

            } else {
                throw new AuthAuthenticationNotFoundException();
            }
        }else{
            throw new ReportTypeNotFoundException($type);
        }
    }


    /**
     * Allows to generate a report
     * 
     * @param $sendmail: if mail has to be sent 
     * @return boolean: true if success, false otherwise
     * @throws NoReportFoundException
     */
    public function generateReport( $sendmail = true) {
        if (sizeof($this->objects) == 0) {
            throw new NoReportFoundException();
        } else {
            $formatedReports = $this->getFormatedReports();
            if ($sendmail){
                return array(
                    'reports' => $formatedReports[$this->currentReportType],
                    'mailsent' => $this->sendReportByMail($formatedReports,$sendmail)
                );
            }else{
                return array(
                    'reports' => $formatedReports[$this->currentReportType],
                    'mailsent' => false
                );
            }
        }
    }
    
    /**
     * Allows to get formated reports
     * 
     * @param array $reports: the row reports
     * @return array:  the formated reports
     * @throws NoReportFoundException
     */
    private function getFormatedReports($reports = null){
        if (is_null($reports)) {
            $reports = $this->objects;
        }

        if (sizeof($reports) == 0) {
            throw new NoReportFoundException();
        } else {
            $formatedReports = array();
            
            foreach ($reports[$this->currentReportType] as $tmpReport) {
                
                $c = Lang::translateEmail('report');

                $targettype = $tmpReport->target_type;
                $target = $targettype::fromId($tmpReport->target_id);

                switch ($this->currentReportType) {
                    case ReportTypes::HTML:
                        
                        $report = Template::process('!report_html', array('target' => $target));
                        if(preg_match('`\{reportContent\}`', $c->html)) {
                            $c->html = str_ireplace("{reportContent}", $report, $c->html);
                        }
                        $formatedReports[] = $c;
                        
                        break;

                    case ReportTypes::PDF:
                        //TODO: get PDF reports
                        // -> Store PDF object in the returned array
                        break;

                    case ReportTypes::STANDARD:
                    default:
                        $report = Template::process('!report_standard', array('target' => $target));
                        if(preg_match('`\{reportContent\}`', $c->plain)) {
                            $c->plain = str_ireplace("{reportContent}", $report, $c->plain);
                        }
                        $formatedReports[] = $c;
                        break;
                }
            }
            
            return array(
                $this->currentReportType => $formatedReports,
            );
            
        }
    }

    
    /**
     * Allows to send a report by mail
     * 
     * @param $reports: reports to be sent
     * @return boolean: true if mail sent, false otherwise
     * @throws NoReportFoundException
     */
    public function sendReportByMail($reports = null){
        if (is_null($reports)) {
            $reports = $this->objects;
        }

        if (sizeof($reports) == 0) {
            throw new NoReportFoundException();
        } else {
            // Getting repliers from config
            if (($noReply = Config::get('noreply')) != null){
                if (($noReplyName = Config::get('noreply_name')) == null){
                    $noReplyName = $noReply;
                }

                foreach ($reports[$this->currentReportType] as $tmpReport) {
                    switch ($this->currentReportType) {
                        case ReportTypes::HTML:
                            $mail = new Mail($tmpReport->subject, $noReply, $noReplyName, true);
                            $message = $tmpReport->html;
                            break;

                        case ReportTypes::PDF:
                            //TODO:
                            // Get HTML translation for the mail content
                            // Generate PDF file
                            // Attach to the mail
                            break;

                        case ReportTypes::STANDARD:
                        default:
                            $mail = new Mail($tmpReport->subject, $noReply, $noReplyName, false);
                            $message = $tmpReport->plain;
                            break;
                    }
                    
                    foreach (Auth::user()->email as $key => $recipient) {
                        $mail->to($recipient);
                    }
                    
                    $mail->write($message);

                    $mailsent = $mail->send();
                }

                return array(
                    'mailsend' => $mailsent
                );
                
            }else{
                return false;
            }
        }
    }
}
