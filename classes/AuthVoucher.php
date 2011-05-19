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

//
// voucher related functions
//
// aVoucher() - check if a voucher exists and returns true/false
// validVoucher() - check if a voucher exists and is available and returns found/notfound/invalid/none (for flex application)
// getVoucher() - returns voucher as json array


class AuthVoucher {

    private static $instance = NULL;

    public static function getInstance() {
        // Check for both equality and type		
        if(self::$instance === NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    } 


    //---------------------------------------
    // Check voucher exists and is available
    // return TRUE if voucher exists and is available for use
    public function aVoucher() {

        $db = DBAL::getInstance();
        
		global $config;

        if (isset($_REQUEST['vid'])) {
            $vid = $_REQUEST['vid'];

            if (preg_match($config['voucherRegEx'], $vid) and strLen($vid) == $config['voucherUIDLength']) {

                //$search =  pg_query("SELECT * FROM files WHERE filevoucheruid='".$vid."' AND filestatus!='Closed'") or die("Error");
                $search =  $db->query("SELECT * FROM files WHERE filevoucheruid='%s'", $vid) or die("Error");
                $total_records = sizeof($search);
                if($total_records == 1){
                    return TRUE;
                }
                return FALSE;
            } 
            else {
                // invalid vid format to match regex from config
                return FALSE;
            }
            return FALSE;
        }
    }	

    //---------------------------------------
    // Check voucher exists and is available
    // return TRUE if voucher exists and is available for use	
    public function validVoucher() {

        $db = DBAL::getInstance();
        global $config;

        if (isset($_REQUEST['vid'])) {
            $vid = $_REQUEST['vid'];


            if (preg_match($config['voucherRegEx'], $vid) and strLen($vid) == $config['voucherUIDLength']) {

                $search =  $db->query("SELECT * FROM files WHERE filevoucheruid='%s'", $vid) or die("Error");
                $total_records = sizeof($search);
                if($total_records == 1){
                    return "found";
                }
                return "notfound";
            } 
            else {
                // invalid vid format to match regex from config
                return "invalid";
            }
        } 
        return "none";
    }	

    //---------------------------------------
    // Get Voucher information
    // TODO: Move this to Functions maybe??
    public function getVoucher() {

        $db = DBAL::getInstance();
       	global $config;

        if (isset($_REQUEST['vid'])) {
            $vid = $_REQUEST['vid'];

            if (preg_match($config['voucherRegEx'], $vid) and strLen($vid) == $config['voucherUIDLength']) {

                $search =  $db->query("SELECT * FROM files WHERE filevoucheruid='%s'", $vid) or die("Error");
                $returnArray = array();
                $returnArray["SessionID"] = session_id();
                foreach($search as $row)
                {
                    array_push($returnArray, $row);
                }
				//return json_encode($returnArray);
				return $returnArray;
            } 
            else {
                // invalid vid format to match regex from config
                return "error";
            }
        }
    }	
}
?>
