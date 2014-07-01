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

// Prevent cross site request forgery and check if any data has been posted.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // All posted data is sent as myJSON.
    if (isset($_POST['myJson'])) {
        $data = json_decode($_POST['myJson'], true);

        if (!isset($data['s-token'])) {
            // No token.
            $_POST = array();
            $_REQUEST = array();

            array_push($errorArray, 'err_token');
            $resultArray['errors'] = $errorArray;
            echo json_encode($resultArray);

            logEntry('XSRF: no session token found', 'E_ERROR');
            exit;
        } elseif ($data['s-token'] != $_SESSION['s-token']) {
            // Token does not match the session variable.
            $_POST = array();
            $_REQUEST = array();

            array_push($errorArray, 'err_token');
            $resultArray['errors'] = $errorArray;
            echo json_encode($resultArray);

            logEntry('XSRF: invalid session token found', 'E_ERROR');
            exit;
        }
    }
}

