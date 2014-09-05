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

/*
 * TODO: cleans this class
 * 2014/09/05: in progress
 */


class Functions
{
    private static $instance = null;
    private $saveLog;
    private $db;
    private $sendMail;
    private $authSaml;
    private $authVoucher;

    public function __construct()
    {
        $this->saveLog = Log::getInstance();
        $this->db = DB::getInstance();
        $this->sendMail = Mail::getInstance();
        $this->authSaml = AuthSaml::getInstance();
        $this->authVoucher = AuthVoucher::getInstance();
    }

    // --------------------------------
    // Singleton: creates a Functions object (if one hasn't been created already) and returns it.
    // --------------------------------
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    // --------------------------------
    // Returns basic statistics from DB, e.g. 'UP: 600 files (5.25 GB) | DOWN: 220 files (1.03 GB) '.
    // --------------------------------
    public function getStats()
    {
        global $lang;
        
        // Get upload statistics.
        $statement = $this->db->prepare(
            "SELECT COUNT(*), SUM(logfilesize) " .
            "FROM logs " .
            "WHERE logtype = 'Uploaded'"
        );

        $statement = $this->db->execute($statement);
        $result = $statement->fetch(PDO::FETCH_NUM);

        $count = $result[0];
        $sumFileSize = $result[1];
        $statString = lang("_UP").': ' . $count . ' ' . lang("_FILES").' (' . Utilities::formatBytes($sumFileSize) . ') |';

        // Get download statistics.
        $statement = $this->db->prepare(
            "SELECT COUNT(*), SUM(logfilesize) " .
            "FROM logs " .
            "WHERE logtype = 'Download'"
        );

        $statement = $this->db->execute($statement);
        $result = $statement->fetch(PDO::FETCH_NUM);

        $count = $result[0];
        $sumFileSize = $result[1];
        $statString .= lang("_DOWN") . $count . ' ' . lang("_FILES").' (' . Utilities::formatBytes($sumFileSize) . ')';

        return $statString;
    }

    private function getCommaSeparatedEmailParameters()
    {
        $authAttributes = $this->getAuthAttributes();
        $emailList = '';

        for ($i = 0; $i < count($authAttributes['email']); $i++) {
            $emailList .= ':email_' . $i . ', ';
        }

        return rtrim($emailList, ', ');
    }

    // --------------------------------
    // Returns a list of unique emails for auto complete for the current user.
    // --------------------------------
    public function uniqueEmailsForAutoComplete()
    {

        // Limit results by config option.
        $isLimited = Config::exists('atutocomplete_max_shown') && is_numeric(Config::get('atutocomplete_max_shown'));
        $limit = $isLimited ? 'LIMIT ' . Config::get('atutocomplete_max_shown') : '';

        $statement = $this->db->prepare(
            "SELECT DISTINCT fileto " .
            "FROM files " .
            "WHERE fileauthuseruid = :fileauthuseruid " .
            "ORDER BY fileto " .
            $limit
        );

        $authAttributes = $this->getAuthAttributes();
        $statement->bindParam(':fileauthuseruid', $authAttributes['saml_uid_attribute']);

        $statement = $this->db->execute($statement);
        $result = $statement->fetchAll();

        $emailArray = array();

        foreach ($result as $row) {
            // Split multiple emails into single emails.
            $row['fileto'] = str_replace(';', ',', $row['fileto']);
            $emails = explode(',', $row['fileto']);

            foreach ($emails as $email) {
                // Add to return array.
                $emailArray[$email] = "'" . addslashes($email) . "'";
            }
        }

        // Sort array and return as comma separated string.
        asort($emailArray);
        $commaList = implode(', ', $emailArray);

        return $commaList;
    }

    // --------------------------------
    // Returns data for admin.php from the logs table (if user has admin access).
    // --------------------------------
    public function adminLogs($type)
    {
        global $page;
        global $totalPages;

        $statement = $this->db->prepare(
            'SELECT COUNT(logtype) ' .
            'FROM logs ' .
            'WHERE logtype = :logtype'
        );

        $statement->bindParam(':logtype', $type);
        $statement = $this->db->execute($statement);
        $total = $statement->fetch(PDO::FETCH_NUM);
        $total = $total[0];

        $maxItemsPerPage = 20;
        $totalPages[$type] = ceil($total / $maxItemsPerPage);

        // Check that $_REQUEST["page"] is an integer, to prevent SQL injection.
        if (isset($_REQUEST["page"]) && is_numeric($_REQUEST["page"])) {
            $page = intval($_REQUEST["page"]);

            if ($page == 0) {
                $page = 1;
            }

            $start = $maxItemsPerPage * ($page - 1);
            $pagination = "LIMIT " . $maxItemsPerPage . " OFFSET " . $start;
        } else {
            $pagination = "LIMIT " . $maxItemsPerPage . " OFFSET 0";
        }

        // Check that user has admin access before returning data.
        if ($this->authSaml->authIsAdmin()) {
            $statement = $this->db->prepare(
                'SELECT logtype, logfrom, logto, logdate, logfilesize, logfilename, logmessage ' .
                'FROM logs ' .
                'WHERE logtype = :logtype ' .
                'ORDER BY logdate DESC ' .
                $pagination
            );

            $statement->bindParam(':logtype', $type);
            $statement = $this->db->execute($statement);
            return $statement->fetchAll();
        }

        return '';
    }

    // --------------------------------
    // Returns data for admin.php from the files table (if user has admin access).
    // --------------------------------
    public function adminFiles($type)
    {
        global $page;
        global $totalPages;

        $statement = $this->db->prepare(
            'SELECT COUNT(fileid) ' .
            'FROM files ' .
            'WHERE filestatus = :logtype'
        );

        $statement->bindParam(':logtype', $type);
        $statement = $this->db->execute($statement);
        $total = $statement->fetch(PDO::FETCH_NUM);
        $total = $total[0];

        $maxItemsPerPage = 10;
        $totalPages[$type] = ceil($total / $maxItemsPerPage);

        // Check that $_REQUEST["page"] is an integer, to prevent SQL injection.
        if (isset($_REQUEST["page"]) && is_numeric($_REQUEST["page"])) {
            $page = intval($_REQUEST["page"]);

            if ($page == 0) {
                $page = 1;
            }

            $start = $maxItemsPerPage * ($page - 1);
            $pagination = "LIMIT " . $maxItemsPerPage . " OFFSET " . $start;
        } else {
            $pagination = "LIMIT " . $maxItemsPerPage . " OFFSET 0";
        }

        // Check that user has admin access before returning data.
        if ($this->authSaml->authIsAdmin()) {
            $statement = $this->db->prepare(
                'SELECT ' . $this->getReturnFields() . ' ' .
                'FROM files ' .
                'WHERE filestatus = :filestatus ' .
                'ORDER BY fileactivitydate DESC ' .
                $pagination
            );

            $statement->bindParam(':filestatus', $type);

            $statement = $this->db->execute($statement);
            return $statement->fetchAll();
        }

        return '';
    }

    // --------------------------------
    // Checks if a file name is valid (i.e. is not empty and does not contain illegal characters).
    // --------------------------------
    private function isValidFileName($data)
        {
        return isset($data['fileoriginalname']) && $data['fileoriginalname'] !== ''
        && preg_match('=^[^\\\\/:;\*\?\"<>|]+(\.[^\\\\/:;\*\?\"<>|]+)*$=', $data["fileoriginalname"]) !== 0;
    }

    // --------------------------------
    // Checks if a file extension is valid (i.e. is not banned in config.php).
    // --------------------------------
    private function isValidFileExtension($data)
    {
        $ban_extension = explode(',', Config::get('ban_extension'));

        if (!isset($data['fileoriginalname'])) {
            return false;
        }

        foreach ($ban_extension as $extension) {
            if ($extension == pathinfo($data['fileoriginalname'], PATHINFO_EXTENSION)) {
                return false;
            }
        }

        return true;
    }

    // --------------------------------
    // Validation for the 'fileto' field.
    // --------------------------------
    private function validateRecipientEmail($data, $errorArray)
    {

        if (!isset($data["fileto"])) {
            array_push($errorArray, "err_filetomissing");
            return $errorArray;
        }

        $emailTo = str_replace(",", ";", $data["fileto"]);
        $emailArray = preg_split("/;/", $emailTo);

        if (count($emailArray) > Config::get('max_email_recipients')) {
            array_push($errorArray, "err_toomanyemail");
        }

        foreach ($emailArray as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                array_push($errorArray, "err_invalidemail");
            }
        }

        return $errorArray;
    }

    // --------------------------------
    // Validation for the 'filefrom' field.
    // --------------------------------
    private function validateSenderEmail($data, $errorArray)
    {
        $authSaml = AuthSaml::getInstance();

        if (!isset($data['filefrom'])) {
            array_push($errorArray, 'err_filefrommissing');
            return $errorArray;
        }

        if (!filter_var($data['filefrom'], FILTER_VALIDATE_EMAIL)) {
            array_push($errorArray, 'err_invalidemail');
        }

        if (isset($_SESSION['voucher'])) {
            // Check that the voucher to address matches the sender email.
            $voucherData = $this->getVoucherData($_SESSION['voucher']);

            if ($data['filefrom'] != $voucherData['fileto']) {
                array_push($errorArray, 'err_invalidemail');
            }
        } elseif ($authSaml->isAuth()) {
            // Check that the authenticated email matches the sender email.
            $authAttributes = $authSaml->sAuth();

            if (!in_array($data['filefrom'], $authAttributes['email'])) {
                array_push($errorArray, 'err_invalidemail');
            }
        }

        return $errorArray;
    }

    // --------------------------------
    // Updates the table record for a given file.
    // --------------------------------
    public function updateFile($dataItem)
    {
        $statement = $this->db->prepare(
            'UPDATE files SET
            fileexpirydate = :fileexpirydate,
            fileto = :fileto,
            filesubject = :filesubject,
            fileactivitydate = :fileactivitydate,
            filemessage = :filemessage,
            filefrom = :filefrom,
            filesize = :filesize,
            fileoriginalname = :fileoriginalname,
            filestatus = :filestatus,
            fileip4address = :fileip4address,
            fileip6address = :fileip6address,
            filesendersname = :filesendersname,
            filereceiversname = :filereceiversname,
            filevouchertype = :filevouchertype,
            fileuid = :fileuid,
            fileauthuseruid = :fileauthuseruid,
            fileauthuseremail = :fileauthuseremail,
            filecreateddate = :filecreateddate,
            filegroupid = :filegroupid,
            filetrackingcode = :filetrackingcode,
            filedownloadconfirmations = :filedownloadconfirmations,
            fileenabledownloadreceipts = :fileenabledownloadreceipts,
            filedailysummary = :filedailysummary,
            filenumdownloads = :filenumdownloads
            WHERE filevoucheruid = :filevoucheruid'
        );

        $statement->bindParam(':fileexpirydate', $dataItem['fileexpirydate']);
        $statement->bindParam(':fileto', $dataItem['fileto']);
        $statement->bindParam(':filesubject', $dataItem['filesubject']);
        $statement->bindParam(':fileactivitydate', $dataItem['fileactivitydate']);
        $statement->bindParam(':filevoucheruid', $dataItem['filevoucheruid']);
        $statement->bindParam(':filemessage', $dataItem['filemessage']);
        $statement->bindParam(':filefrom', $dataItem['filefrom']);
        $statement->bindParam(':filesize', $dataItem['filesize']);
        $statement->bindParam(':fileoriginalname', $dataItem['fileoriginalname']);
        $statement->bindParam(':filestatus', $dataItem['filestatus']);
        $statement->bindParam(':fileip4address', $dataItem['fileip4address']);
        $statement->bindParam(':fileip6address', $dataItem['fileip6address']);
        $statement->bindParam(':filesendersname', $dataItem['filesendersname']);
        $statement->bindParam(':filereceiversname', $dataItem['filereceiversname']);
        $statement->bindParam(':filevouchertype', $dataItem['filevouchertype']);
        $statement->bindParam(':fileuid', $dataItem['fileuid']);
        $statement->bindParam(':fileauthuseruid', $dataItem['fileauthuseruid']);
        $statement->bindParam(':fileauthuseremail', $dataItem['fileauthuseremail']);
        $statement->bindParam(':filecreateddate', $dataItem['filecreateddate']);
        $statement->bindParam(':filegroupid', $dataItem['filegroupid']);
        $statement->bindParam(':filetrackingcode', $dataItem['filetrackingcode']);
        $statement->bindParam(':filedownloadconfirmations', $dataItem['filedownloadconfirmations']);
        $statement->bindParam(':fileenabledownloadreceipts', $dataItem['fileenabledownloadreceipts']);
        $statement->bindParam(':filedailysummary', $dataItem['filedailysummary']);
        $statement->bindParam(':filenumdownloads', $dataItem['filenumdownloads']);

        $this->db->execute($statement);
    }


    // --------------------------------
    // Returns file size or 0 if file not found.
    // --------------------------------
    public function getFileSize($fileName)
    {
        if ($fileName != '' && file_exists($fileName)) {
            if (PHP_OS == "Darwin") {
                $size = trim(shell_exec("stat -f %z " . escapeshellarg($fileName)));
            } elseif (!(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')) {
                $size = trim(shell_exec("stat -c%s " . escapeshellarg($fileName)));
            } else {
                $fsObj = new COM("Scripting.FileSystemObject");
                $f = $fsObj->GetFile($fileName);
                $size = $f->Size;
            }

            return $size;
        }

        return 0;
    }

    // --------------------------------
    // Returns an array containing fields for total and available drive space on the server.
    // TODO: manage with storage handler
    // --------------------------------
    public function driveSpace()
    {

        // Use absolute locations.
        $result["site_filestore_total"] = disk_total_space(Config::get('site_filestore'));
        $result["site_temp_filestore_total"] = disk_total_space(Config::get('site_temp_filestore'));
        $result["site_filestore_free"] = disk_free_space(Config::get('site_filestore'));
        $result["site_temp_filestore_free"] = disk_free_space(Config::get('site_temp_filestore'));

        return $result;

    }

    // --------------------------------
    // Checks if active menu item, returns 'active' or empty string.
    // --------------------------------
    public function active($value, $menuName)
    {
        return $value == $menuName ? 'active' : '';
    }

    public function advancedSettingsEnabled()
    {
        return (Config::get('terasender_enabled') && Config::get('terasender_advanced'))
            || Config::get('upload_complete_email_display') == 'hidden'
            || Config::get('inform_download_email_display') == 'hidden'
            || Config::get('email_me_daily_statistics_display') == 'hidden'
            || Config::get('download_confirmation_enabled_display') == 'hidden'
            || Config::get('add_me_to_recipients_display') == 'hidden'
            || Config::get('email_me_copies_display') == 'hidden';
    }
}

