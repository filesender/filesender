<?php

/*
 * FileSender www.filesender.org
 *
 * Copyright (c) 2009-2014, AARNet, Belnet, HEAnet, SURFnet, UNINETT
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

include '../includes/init_cli.php';

Logger::setProcess(ProcessTypes::CLI);


if (!file_exists(FILESENDER_BASE . "/config/config.php")) {
    $errorMsg .= '<li>Configuration file is missing.</li>';
    Logger::error('Configuration file is missing.');
} else {

    // The following list must be updated any time a config setting is added/removed.
    $requiredConfigFields = array(
        array('admin', array('string', 'array')),
        array('admin_email', 'string'),
        array('email_reply_to', 'string'),
        
        array('db_type', 'string'),
        array('db_host', 'string'),
        array('db_database', 'string'),
        array('db_username', 'string'),
        array('db_password', 'string'),
        array('db_password', 'string'),
    );

    $errors = array();

    foreach ($requiredConfigFields as $datas) {
        $field = $datas[0];
        $type = $datas[1];

        $conf = Config::get($field);
        if ($conf === null) {
            $errors['missing_conf'][] = $field;
        } else {
            if (is_array($type)) {
                $err = array();
                foreach ($type as $tmp){
                    $ret[] = checkConf($field, $tmp);
                }
                if (array_search(false,$ret,true)){
                    $errors = array_merge($errors,$ret);
                }
                
            } else{
                $ret = checkConf($field,$type);
                if (is_array($ret)){
                    $errors = array_merge($errors,$ret);
                }
            }
        }
    }
    echo '@'.date('Y-m-d H:i:s')." - ";
    if (count($errors) > 0){
        echo "Configuration [KO] - See logs";
        Logger::error($errors);
    } else{
        echo 'Configuration [OK]';
        Logger::info('Configuration [OK]');
    }
    echo PHP_EOL;
}

function checkConf($field,$type){
    $errors = false; 
    
    switch ($type){
        case 'string':
            if (!is_string($field)){
                $errors['bad_type'][] = array($field,$type);
            }
            break;
        case 'array':
            if (!is_array($field)){
                $errors['bad_type'][] = array($field,$type);
            }
            break;
        case 'int':
            if (!is_numeric($field)){
                $errors['bad_type'][] = array($field,$type);
            }
            break;
        default:
            break;
        }
        
        return $errors ;
}