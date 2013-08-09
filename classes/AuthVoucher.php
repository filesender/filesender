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

// --------------------------------
// Voucher related functions
// --------------------------------
class AuthVoucher
{
    private static $instance = null;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    public static function getInstance()
    {
        // Check for both equality and type.
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    // --------------------------------
    // Check voucher exists and is available, returns true or false.
    // --------------------------------
    public function aVoucher()
    {
        global $config;

        if (isset($_REQUEST['vid'])) {
            $vid = $_REQUEST['vid'];

            if (preg_match($config['voucherRegEx'], $vid) and strLen($vid) == $config['voucherUIDLength']) {
                $statement = $this->db->query("SELECT COUNT(*) FROM files WHERE filevoucheruid = %s", $vid);
                $statement->execute();
                $count = $statement->fetchColumn();

                return $count == 1;
            }
        }

        return false;
    }

    // --------------------------------
    // Get voucher information.
    // TODO: Move this to Functions maybe?
    // --------------------------------
    public function getVoucher()
    {
        global $config;

        if (isset($_REQUEST['vid'])) {
            $vid = $_REQUEST['vid'];

            if (preg_match($config['voucherRegEx'], $vid) and strLen($vid) == $config['voucherUIDLength']) {

                $result = $this->db->query("SELECT * FROM files WHERE filevoucheruid = %s", $vid) or die("Error");
                $returnArray = array();
                $returnArray["SessionID"] = session_id();

                foreach ($result as $row) {
                    array_push($returnArray, $row);
                }

                return $returnArray;
            }
        }
        return "error";
    }
}
