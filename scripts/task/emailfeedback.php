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

require_once(dirname(__FILE__).'/../../includes/init.php');

Logger::setProcess(ProcessTypes::FEEDBACK);

Logger::info('Email feedback handler started');

// TODO daemon mode ?

$remove_after_processing = false;
$move_after_processing = false;
$inputs = array();

foreach(array_slice($argv, 1) as $arg) {
    if($arg == '--remove_after_processing') {
        $remove_after_processing = true;
    
    } else if(preg_match('/^--move_after_processing(=(.*))?$/', $arg, $m)) {
        if($m[1]) {
            $move_after_processing = preg_replace('`/+$`', '', $m[2]);
        } else $move_after_processing = 'done';
    } else {
        $inputs[] = $arg;
    }
}

if(!count($inputs)) $inputs[] = '-';

while($inputs) {
    $input = array_shift($inputs);
    
    if(is_dir($input)) {
        if(substr($input, -1) != '/') $input .= '/';
        foreach(scandir($input) as $i) {
            if(!is_file($input.$i)) continue;
            $inputs[] = $input.$i;
        }
        continue;
    }
    
    try {
        if($input != '-' && !is_file($input))
            throw new Exception('not a valid feedback input');
        
        $message = null;
        if($input == '-') {
            $message = file_get_contents('php://stdin');
        } else if(is_file($input)) {
            $message = file_get_contents($input);
        } else throw new Exception('not a valid feedback input');
        
        // Parse and process
        $email = FeedbackMail::parse($message);
        $headers = $email->headers;
        $parts = $email->parts;
        
        // Get related object
        $target_type = null;
        $target_id = null;
        $feedback_type = null;
        $details = array();
        
        $return_path = Config::get('email_return_path');
        if($return_path && preg_match('`^(.+)<verp>(.+)$`i', $return_path, $match)) {
            $headers_to = (string)$headers->to;
            // remove surrounding <angle brackets> if they are in the to address
            $headers_to = preg_replace( '`^<(.+)>$`', '${1}', $headers_to );
            $addr = explode('@', $headers_to);
            $variable = substr($addr[0], strlen($match[1]));
            
            if(preg_match('/^(recipient|guest)-([0-9]+)$/', $variable, $m)) {
                $target_type = $m[1];
                $target_id = $m[2];
                
                Logger::info('Got rfc3464 report for '.$target_type.'#'.$target_id.' (verp)');
            }
        }
        
        // Try to get context from original message if multipart/report
        if(
            $headers->content_type == 'multipart/report' &&
            $headers->content_type_report_type == 'delivery-status' &&
            count($parts) >= 3
        ) {
            if(preg_match('`^text/`', $parts[0]->headers->content_type)) {
                $details[] = $parts[0]->content;
            }
            
            if($parts[1]->headers->content_type == 'message/delivery-status') {
                if(!$feedback_type) {
                    $data = FeedbackMailHeaders::parse($parts[1]->content);
                    
                    if(
                        preg_match('`failed`', (string)$data->action) ||
                        preg_match('`^[45]\.?[0-9]\.?[0-9]$`', (string)$data->status)
                    ) {
                        $feedback_type = 'bounce';
                        Logger::info('Got bounce from rfc3464 report');
                    }
                }
                
                $details[] = $parts[1]->content;
            }
                
            if(!$target_type && $parts[2]->headers->content_type == 'message/rfc822') {
                $attached_email = FeedbackMail::parse($parts[2]->content);
                
                if(preg_match('/^(recipient|guest)-([0-9]+)$/', (string)$attached_email->headers->x_filesender_context, $m)) {
                    $target_type = $m[1];
                    $target_id = $m[2];
                    Logger::info('Got rfc3464 report for '.$target_type.'#'.$target_id.' (matched in custom header)');
                } else if(preg_match('/^<(recipient|guest)-([0-9]+)-[0-9a-f]+@filesender>$/', (string)$attached_email->headers->message_id, $m)) {
                    $target_type = $m[1];
                    $target_id = $m[2];
                    Logger::info('Got rfc3464 report for '.$target_type.'#'.$target_id.' (matched in message id)');
                }
                
            }
        }
        
        // Here we should have target_type, target_id and feedback_type
        if(!$target_type)
            throw new Exception('is missing target_type or anything allowing to discover it');
        
        if(!$target_id)
            throw new Exception('is missing target_id or anything allowing to discover it');
        
        $details = implode("\n\n", $details);
        
        $target = null;
        try {
            if($target_type == 'recipient') $target = Recipient::fromId((int)$m[2]);
            else if($target_type == 'guest') $target = Guest::fromId((int)$m[2]);
        } catch(Exception $e) {
            throw new Exception('refers to an unknown '.$target_type.' #'.$target_id);
        }
        
        // Handle feedback type
        if($feedback_type == 'bounce') {
            $bounce = TrackingEvent::create(
                TrackingEventTypes::BOUNCE,
                $target,
                $email->headers->date ? strtotime($email->headers->date) : null,
                $details
            );
            $bounce->save();
            
            $report = Config::get('report_bounces');
            if(!$report) continue;
            
            if($report == 'asap_then_daily' && $target_type = 'guest') $report = 'asap';
            
            $range = (int)Config::get('report_bounces_asap_then_daily_range');
            if(!$range) $range = 15 * 60;
            
            if(
                $report == 'asap' ||
                ($report == 'asap_then_daily' && $bounce->created < $target->transfer->created + $range)
            )
                $bounce->report();
            
        } else { // Unknown feedback type
            $relay_to = Config::get('relay_unknown_feedbacks'); // Do we need to relay it to somebody ?
            
            $args = array('target' => $target, 'target_type' => $target_type, 'target_id' => $target_id);
            if($relay_to) {
                // Get recipient(s)
                switch($relay_to) {
                    case 'sender':
                        $mail = new ApplicationMail(Lang::translateEmail('recipient_feedback')->r($args));
                        $mail->setDebugTemplate('recipient_feedback');
                        $mail->to($target->owner->email);
                        break;
                        
                    case 'admin':
                        $mail = new SystemMail(Lang::translateEmail('email_feedback')->r($args));
                        $mail->setDebugTemplate('email_feedback');
                        break;
                        
                    case 'support':
                        $support = Config::get('support_email');
                        if(strlen($support)) {
                            if(!Utilities::validateEmail($support)) throw new BadEmailException($support);
                            
                            $mail = new ApplicationMail(Lang::translateEmail('email_feedback')->r($args));
                            $mail->setDebugTemplate('email_feedback');
                            $mail->to($support);
                        } else throw new ConfigBadParameterException('support_email');
                        
                    default:
                        if(Utilities::validateEmail($relay_to)) {
                            $mail = new ApplicationMail(Lang::translateEmail('email_feedback')->r($args));
                            $mail->setDebugTemplate('email_feedback');
                            $mail->to($relay_to);
                        } else throw new ConfigBadParameterException('relay_unknown_feedbacks');
                }
                
                // Attach report
                $attachment = new MailAttachment('feedback_'.$target_type.'_'.$target_id.'.eml');
                $attachment->transfer_encoding = 'raw';
                $attachment->disposition = 'inline';
                $attachment->content = $message;
                $mail->attach($attachment);
                
                // Send
                $mail->send();
            }
        }
        
        if($input != '-') {
            if($remove_after_processing) {
                unlink($input);
                
            } else if($move_after_processing) {
                $target = $move_after_processing;
                if(substr($target, 0, 1) != '/') $target = dirname($input).'/'.$target;
                
                if(!is_dir($target) && !mkdir($target, 0777, true))
                    throw new Exception('target directory "'.$target.'" does not exist and cannot be created');
                
                copy($input, $target.'/'.basename($input));
                unlink($input);
            }
        }
        
    } catch(Exception $e) {
        Logger::error($input.' processing failed : '.$e->getMessage());
        continue;
    }
}
