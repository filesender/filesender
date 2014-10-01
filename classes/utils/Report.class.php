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
 */
class Report {
    /**
     * Related audit log entries
     */
    private $logs = array();
    
    /**
     * Target type
     */
    private $target = null;
    
    /**
     * Constructor
     * 
     * @param DBObject $target target to get report about
     * 
     * @throws AuthAuthenticationNotFoundException
     * @throws ReportFormatNotFoundException
     */
    public function __construct(DBObject $target) {
        if(!Auth::isAuthenticated())
            throw new AuthAuthenticationNotFoundException();
        
        $this->logs = array();
        switch(get_class($target)) {
            case 'Transfer': // Get all log about transfer or its related files and recipients
                if(!$target->isOwner(Auth::user()) && !Auth::user()->isAdmin())
                    throw new ReportOwnershipRequiredException('Transfer = '.$target->id);
                
                $this->logs = AuditLog::fromTransfer($target);
                break;
            
            case 'File': // Get log about file life
            case 'Recipient': // Get log about recipient activity
                if(!$target->transfer->isOwner(Auth::user()) && !Auth::user()->isAdmin())
                    throw new ReportOwnershipRequiredException(get_class($target).' = '.$target->id.', Transfer = '.$target->transfer->id);
                
                $this->logs = AuditLog::fromTarget($target);
                break;
            
            default: // Object type not handled
                throw new ReportUnknownTargetTypeException(get_class($target));
        }
        
        $this->target = $target;
    }
    
    /**
     * Getter
     * 
     * @param string $property property to get
     * 
     * @throws PropertyAccessException
     * 
     * @return property value
     */
    public function __get($property) {
        if(in_array($property, array(
            'logs', 
            'target',
        ))) return $this->$property;
        
        if($property == 'target_type') return get_class($this->target);
        
        throw new PropertyAccessException($this, $property);
    }
    
    /**
     * Sends report by email
     * 
     * @param mixed $recipient User, email address
     */
    public function sendTo($recipient) {
        if(is_object($recipient) && ($recipient instanceof User))
            $recipient = $recipient->email;
        
        if(!is_string($recipient) || !filter_var($recipient, FILTER_VALIDATE_EMAIL))
            throw new BadEmailException($recipient);
        
        $format = Config::get('report_format');
        if(!$format) $format = ReportFormats::HTML;
        
        if(!ReportFormats::isValidName($format))
            throw new ReportUnknownFormatException($format);
        
        if(($format == ReportFormats::HTML) && !Config::get('email_use_html'))
            $format = ReportFormats::PLAIN;
        
        $content = array('plain' => '', 'html' => '');
        $file = null;
        if($format == ReportFormats::PDF) {
            $content['plain'] = Lang::tr('report_pdf_attached');
            $content['html'] = $content['plain'];
            
            //$file = ''; // TMP pdf file ...
        } else {
            $content['plain'] = Template::process('!report_plain', array('report' => $this));
            
            if($format == ReportFormats::HTML)
                $content['html'] = Template::process('!report_html', array('report' => $this));
        }
        
        $mail = new ApplicationMail(Lang::translateEmail('report')->r(
            array(
                'target' => array(
                    'type' => $this->target_type,
                    'id' => $this->target->id
                ),
                'content' => $content,
            ),
            $this->target
        ));
        
        if($file)
            $mail->attach($file, 'attachment', 'report_'.strtolower($this->target_type).'_'.$this->target->id);
        
        $mail->to($recipient);
        
        $mail->send();
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
            if (($noReply = Config::get('email_reply_to')) != null){
                if (($noReplyName = Config::get('email_reply_to_name')) == null){
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
