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

// Use database object superclass
if (substr(dirname(__FILE__), -3 ) == "new")
    require_once FILESENDER_BASE.'/classes/new/DBObject.class.php';
else
    require_once FILESENDER_BASE.'/classes/DBObject.class.php';

    
/**
 * Represents a file transfer recipient. Provides methods to create one from transaction/invitation and to delete it when recipient has become inactive.
 * 
 * @property array $dataMap: the corresponding DB table's structure
 * 
 */ 
class Recipient extends DBObject
{
    /**
     * database map
     */
    protected static $dataMap = array(
        'id' => array(
            'type' => 'uint',
            'size' => 'medium',
            'primary' => true,
            'autoinc' => true,
        ),
        'transferid' => array(
            'type' => 'uint',
            'size' => 'medium',
        ),
        'email_address' => array(
            'type' => 'string',
            'size' => 80,
        ),
        'token' => array(
            'type' => 'string',
            'size' => 60,
        ),
        'options' => array(
            'type' => 'string',
            'size' => 250,
        ),
        'created_date' => array(
            'type' => 'datetime',
        ),
        'last_activity' => array(
            'type' => 'datetime',
        ),
    );
    
    /**
     * Properties
     */
    private $id = null;
    private $transferid = null;
    private $email_address = null;
    private $token = null;
    private $options = null;
    private $created_date = null;
    private $last_activity = null;

}