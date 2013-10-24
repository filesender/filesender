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
// Format bytes into readable text format
// --------------------------------
function formatBytes($bytes, $precision = 2)
{

    if ($bytes > 0) {
        $units = array(' Bytes', ' kB', ' MB', ' GB', ' TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . '' . $units[$pow];
    }
    return 0;
}

// --------------------------------
// Create Unique ID for vouchers.
// --------------------------------
function getGUID()
{
    return sprintf(
        '%08x-%04x-%04x-%02x%02x-%012x',
        mt_rand(),
        mt_rand(0, 65535),
        bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '0100', 11, 4)),
        bindec(substr_replace(sprintf('%08b', mt_rand(0, 255)), '01', 5, 2)),
        mt_rand(0, 255),
        mt_rand()
    );
}

// --------------------------------
// Create cryptographically secure key for group ID's.
// --------------------------------
function getOpenSSLKey()
{
    global $config;
    return bin2hex(openssl_random_pseudo_bytes($config['openSSLKeyLength']));
}

// --------------------------------
// Replace illegal chars with _ character in supplied file names.
// --------------------------------
function sanitizeFilename($fileName)
{

    if (!empty($fileName)) {
        $fileName = preg_replace("/^\./", "_", $fileName); //return preg_replace("/[^A-Za-z0-9_\-\. ]/", "_", $filename);
        return $fileName;
    } else {
        //trigger_error("invalid empty filename", E_USER_ERROR);
        return "";
    }
}

// --------------------------------
// Error if fileUid doesn't look sane.
// --------------------------------
function ensureSaneFileUid($fileUid)
{
    global $config;
    return preg_match($config['voucherRegEx'], $fileUid) && strLen($fileUid) == $config['voucherUIDLength'];
}

function ensureSaneOpenSSLKey($key)
{
    global $config;
    return ctype_alnum($key) && strlen($key) == $config['openSSLKeyLength'] * 2;
}

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
    // Returns a 3-letter tracking code which is unique for the given user. 'AAA' is returned if no previous
    // tracking codes are found for the user, or if no user is specified.
    // --------------------------------
    public function getTrackingCode($authUser = null)
    {
        $statement = $this->db->prepare('SELECT MAX(filetrackingcode) FROM files WHERE fileauthuseruid = :fileauthuseruid');
        $statement->bindParam(':fileauthuseruid', $authUser);

        $statement = $this->db->execute($statement);
        $result = $statement->fetchColumn();
        $trackingCode = $result;

        if ($authUser == null || empty($trackingCode)) {
            return 'AAA';
        } else {
            // Return the next unused tracking code (turns 'AAA' into 'AAB', 'AAZ' into 'ABA', etc).
            return ++$trackingCode;
        }
    }

    // --------------------------------
    // Returns basic statistics from DB, e.g. 'UP: 600 files (5.25 GB) | DOWN: 220 files (1.03 GB) '.
    // --------------------------------
    public function getStats()
    {
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
        $statString = 'UP: ' . $count . ' files (' . formatBytes($sumFileSize) . ') |';

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
        $statString .= ' DOWN: ' . $count . ' files (' . formatBytes($sumFileSize) . ')';

        return $statString;
    }

    // --------------------------------
    // Returns all vouchers created by current user (based on saml_uid_attribute).
    // --------------------------------
    public function getVouchers()
    {
        $authAttributes = $this->getAuthAttributes();

        $statement = $this->db->prepare(
            "SELECT " . $this->getReturnFields() .
            "FROM files " .
            "WHERE (fileauthuseruid = :fileauthuseruid) " .
            "AND filestatus = 'Voucher' " .
            "ORDER BY fileactivitydate DESC"
        );

        $statement->bindParam(':fileauthuseruid', $authAttributes['saml_uid_attribute']);
        $statement = $this->db->execute($statement);

        return json_encode($statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getUsedVoucherTransactions() {
        $authAttributes = $this->getAuthAttributes();
        $emailList = '';

        for ($i = 0; $i < count($authAttributes['email']); $i++) {
            $emailList .= ':filefrom_' . $i . ', ';
        }

        $emailList = $this->getCommaSeparatedEmailParameters();

        $statement = $this->db->prepare(
            "SELECT DISTINCT(filetrackingcode), fileauthuseruid " .
            "FROM files " .
            "WHERE (fileauthuseruid = :fileauthuseruid) " .
            "AND filestatus = 'Available' " .
            "AND filefrom NOT IN ($emailList) " .
            "ORDER BY filetrackingcode DESC"
        );

        $statement->bindParam(':fileauthuseruid', $authAttributes['saml_uid_attribute']);

        for ($i = 0; $i < count($authAttributes['email']); $i++) {
            $statement->bindParam(':email_' . $i, $authAttributes['email'][$i]);
        }

        $statement = $this->db->execute($statement);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getCommaSeparatedEmailParameters() {
        $authAttributes = $this->getAuthAttributes();
        $emailList = '';

        for ($i = 0; $i < count($authAttributes['email']); $i++) {
            $emailList .= ':email_' . $i . ', ';
        }

        return rtrim($emailList, ', ');
    }

    // --------------------------------
    // Gets the user SAML uid attribute if user is authenticated.
    // --------------------------------
    private function getAuthAttributes()
    {
        if ($this->authSaml->isAuth()) {
            return $this->authSaml->sAuth();
        }

        $authAttributes['saml_uid_attribute'] = '';
        return $authAttributes;
    }

    // --------------------------------
    // Gets the DB files table return fields, fileUID excluded to stop unauthorised users accessing it.
    // --------------------------------
    private function getReturnFields()
    {
        return ' fileid, fileexpirydate, fileto, filesubject, fileactivitydate, filemessage, filefrom, filesize, '
        . 'fileoriginalname, filestatus, fileip4address, fileip6address, filesendersname, filereceiversname, '
        . 'filevouchertype, fileauthuseruid, fileauthuseremail, filecreateddate, fileauthurl, fileuid, filevoucheruid, '
        . 'filegroupid, filetrackingcode, filedownloadconfirmations, fileenabledownloadreceipts, filedailysummary, filenumdownloads ';
    }

    // --------------------------------
    // Gets all active tracking codes belonging to the authenticated user.
    // --------------------------------
    public function getUserTrackingCodes()
    {
        $emailList = $this->getCommaSeparatedEmailParameters();

        $statement = $this->db->prepare(
            "SELECT DISTINCT(filetrackingcode), fileauthuseruid " .
            "FROM files " .
            "WHERE fileauthuseruid = :fileauthuseruid " .
            "AND filestatus = 'Available' " .
            "AND filefrom IN ($emailList) " .
            "ORDER BY filetrackingcode DESC"
        );

        $authAttributes = $this->getAuthAttributes();

        $statement->bindParam(':fileauthuseruid', $authAttributes['saml_uid_attribute']);

        for ($i = 0; $i < count($authAttributes['email']); $i++) {
            $statement->bindParam(':email_' . $i, $authAttributes['email'][$i]);
        }

        $statement = $this->db->execute($statement);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    // --------------------------------
    // Returns a list of unique emails for auto complete for the current user.
    // --------------------------------
    public function uniqueEmailsForAutoComplete()
    {
        global $config;

        // Limit results by config option.
        $isLimited = isset($config['autocompleteHistoryMax']) && is_numeric($config['autocompleteHistoryMax']);
        $limit = $isLimited ? 'LIMIT ' . $config['autocompleteHistoryMax'] : '';

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
    // Check if a file upload already has a pending database entry.
    // --------------------------------
    public function checkPending($dataItem)
    {
        $statement = $this->db->prepare(
            "SELECT * " .
            "FROM files " .
            "WHERE fileoriginalname = :fileoriginalname " .
            "AND filesize = :filesize " .
            "AND fileuid = :fileuid " .
            "AND filestatus = 'Pending'"
        );

        $statement->bindParam(':fileoriginalname', $dataItem['fileoriginalname']);
        $statement->bindParam(':filesize', $dataItem['filesize']);
        $statement->bindParam(':fileuid', $dataItem['fileuid']);

        $statement = $this->db->execute($statement);
        $result = $statement->fetchAll();

        return $result ? $result[0] : '';
    }


    // --------------------------------
    // Returns file information based on filevoucheruid.
    // --------------------------------
    public function getFile($dataItem)
    {
        $statement = $this->db->prepare(
            'SELECT * ' .
            'FROM files ' .
            'WHERE filevoucheruid = :filevoucheruid'
        );

        $statement->bindParam(':filevoucheruid', $dataItem['filevoucheruid']);

        $statement = $this->db->execute($statement);
        return json_encode($statement->fetchAll());
    }

    // --------------------------------
    // Adds a list of email recipients to the transaction identified by $trackingCode and $authUserUid.
    // --------------------------------
    function addRecipientsToTransaction($emailList, $trackingCode, $authUserUid, $subject, $message)
    {
        $transactionDetails = $this->getTransactionDetails($trackingCode, $authUserUid); // Transaction file list.
        $existingDetails = $this->getTransactionRecipients($trackingCode, $authUserUid); // Existing recipients list.
        $groupIDs = array();

        foreach ($emailList as $email) {
            global $config;

            // Skip any recipients that already have access to the transaction.
            if ($this->isDuplicateRecipient($email, $existingDetails)) {
                continue;
            }

            // Add the fields that are common for all of the files in the transaction.
            $fileData = $transactionDetails[0];
            $fileData['filegroupid'] = getOpenSSLKey(); // Assign a new group ID to the email address.
            $fileData['fileto'] = $email;
            $fileData['fileauthuseruid'] = $authUserUid;
            $fileData['filetrackingcode'] = $trackingCode;
            $fileData['filesubject'] = $subject;
            $fileData['filemessage'] = $message;
            $fileData['filenumdownloads'] = 0;

            // Set created date to now, not the time the transaction was created.
            $fileData['filecreateddate'] = date($config['db_dateformat'], time());

            // Add each individual file to the new recipient.
            foreach ($transactionDetails as $transaction) {
                $fileData['filesize'] = $transaction['filesize'];
                $fileData['fileoriginalname'] = $transaction['fileoriginalname'];
                $fileData['filestatus'] = $transaction['filestatus'];
                $fileData['fileuid'] = $transaction['fileuid'];
                $fileData['filevoucheruid'] = getGUID();

                $this->insertFile($fileData, 'Added');
            }

            $groupIDs[] = $fileData['filegroupid']; // Needed for emails.
        }

        return ($this->sendMail->sendDownloadAvailable($groupIDs));
    }

    // --------------------------------
    // Gets information about a specific transaction.
    // --------------------------------
    function getTransactionDetails($trackingCode, $authUserUid)
    {
        $statement = $this->db->prepare(
            "SELECT * " .
            "FROM files " .
            "WHERE filetrackingcode = :filetrackingcode " .
            "AND fileauthuseruid = :fileauthuseruid " .
            "AND filestatus = 'Available' " .
            "ORDER BY fileoriginalname ASC"
        );

        $statement->bindParam(':filetrackingcode', $trackingCode);
        $statement->bindParam(':fileauthuseruid', $authUserUid);
        $statement = $this->db->execute($statement);
        $result = $statement->fetchAll();
        return $result;

        /*$returnArray = array();
        $previousRow = null;

        foreach ($result as $row) {
            // Only return 'Available' rows.
            if ($row['filestatus'] != 'Available') {
                continue;
            }

            // List each file only once and include downloads.
            if ($previousRow == null || $previousRow['fileoriginalname'] != $row['fileoriginalname']) {
                $row["downloads"] = $this->countDownloads($row["filevoucheruid"]);
                array_push($returnArray, $row);
            }

            $previousRow = $row;
        }

        return $returnArray*/;
    }

    // --------------------------------
    // Returns the number of times a single file has been downloaded, based on filevoucheruid.
    // --------------------------------
    public function countDownloads($vid)
    {
        $statement = $this->db->prepare(
            "SELECT COUNT(*) " .
            "FROM logs " .
            "WHERE logvoucheruid = :logvoucheruid " .
            "AND logtype = 'Download'"
        );

        $statement->bindParam(':logvoucheruid', $vid);

        $statement = $this->db->execute($statement);
        $total = $statement->fetch(PDO::FETCH_NUM);

        return $total[0];
    }

    // --------------------------------
    // Returns a list of recipients for a specific transaction.
    // --------------------------------
    function getTransactionRecipients($trackingCode, $authUserUid)
    {
        $statement = $this->db->prepare(
            'SELECT * ' .
            'FROM files ' .
            'WHERE filetrackingcode = :filetrackingcode ' .
            'AND fileauthuseruid = :fileauthuseruid ' .
            'ORDER BY fileto ASC'
        );

        $statement->bindParam(':filetrackingcode', $trackingCode);
        $statement->bindParam(':fileauthuseruid', $authUserUid);

        $statement = $this->db->execute($statement);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        $returnArray = array();
        $previousRow = null;

        foreach ($result as $row) {
            // Only return 'Available' rows.
            if ($row['filestatus'] != 'Available') {
                continue;
            }

            // Remove duplicate file names.
            if ($previousRow == null || $previousRow['fileto'] != $row['fileto']) {
                array_push($returnArray, $row);
            }

            $previousRow = $row;
        }

        return $returnArray;
    }

    // --------------------------------
    // Checks if a recipient email address already exists in array $recipientsList.
    // --------------------------------
    private function isDuplicateRecipient($email, $recipientsList)
    {
        foreach ($recipientsList as $recipient) {
            if (isset($recipient['fileto']) && $recipient['fileto'] == $email) {
                return true;
            }
        }

        return false;
    }

    // --------------------------------
    // Adds a table record for uploaded file/voucher and sends email(s).
    // --------------------------------
    public function insertFile($dataItem, $logType = 'Uploaded')
    {
        // prepare PDO insert statement
        $statement = $this->db->prepare(
            'INSERT INTO files ( ' .
            'fileexpirydate, fileto, filesubject, fileactivitydate, filevoucheruid, filemessage, filefrom, ' .
            'filesize, fileoriginalname, filestatus, fileip4address,fileip6address, filesendersname, ' .
            'filereceiversname, filevouchertype, fileuid, fileauthuseruid, fileauthuseremail, filecreateddate, ' .
            'filegroupid, filetrackingcode, filedownloadconfirmations, fileenabledownloadreceipts, filedailysummary, filenumdownloads ' .
            ') VALUES ( ' .
            ':fileexpirydate, :fileto, :filesubject, :fileactivitydate, :filevoucheruid, :filemessage, :filefrom, ' .
            ':filesize, :fileoriginalname, :filestatus, :fileip4address, :fileip6address, :filesendersname, ' .
            ':filereceiversname, :filevouchertype, :fileuid, :fileauthuseruid, :fileauthuseremail, :filecreateddate, ' .
            ':filegroupid, :filetrackingcode, :filedownloadconfirmations, :fileenabledownloadreceipts, :filedailysummary, :filenumdownloads ' .
            ')'
        );

        $statement->bindParam(':fileexpirydate', $dataItem['fileexpirydate']);
        $statement->bindParam(':fileto', $dataItem['fileto']);
        $statement->bindParam(':filefrom', $dataItem['filefrom']);
        $statement->bindParam(':filesubject', $dataItem['filesubject']);
        $statement->bindParam(':fileactivitydate', $dataItem['fileactivitydate']);
        $statement->bindParam(':filevoucheruid', $dataItem['filevoucheruid']);
        $statement->bindParam(':filemessage', $dataItem['filemessage']);
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

        if ($dataItem['filestatus'] == 'Voucher') {
            $this->saveLog->saveLog($dataItem, 'Voucher Sent', '');
            return $this->sendMail->sendVoucherIssued($dataItem['filevoucheruid']);
        } elseif ($dataItem['filestatus'] == 'Available') {
            $this->saveLog->saveLog($dataItem, $logType, '');
        }

        return true;
    }

    // --------------------------------
    // Returns the number of times a given recipient has downloaded each file from a given transaction.
    // --------------------------------
    function getTransactionDownloadsForRecipient($recipientEmail, $trackingCode, $authUserUid)
    {
        $statement = $this->db->prepare(
            'SELECT fileoriginalname, filevoucheruid, filestatus ' .
            'FROM files ' .
            'WHERE fileto = :fileto ' .
            'AND filetrackingcode = :filetrackingcode ' .
            'AND fileauthuseruid = :fileauthuseruid ' .
            'ORDER BY fileoriginalname ASC'
        );

        $statement->bindParam(':fileto', $recipientEmail);
        $statement->bindParam(':filetrackingcode', $trackingCode);
        $statement->bindParam(':fileauthuseruid', $authUserUid);

        $statement = $this->db->execute($statement);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        $returnArray = array();

        foreach ($result as $row) {
            if ($row['filestatus'] == 'Available') {
                $returnArray[] = array(
                    'fileoriginalname' => $row['fileoriginalname'],
                    'downloads' => $this->countDownloads($row['filevoucheruid'])
                );
            }
        }

        return $returnArray;
    }

    // --------------------------------
    // Check if a group ID is valid and does not already exist.
    // --------------------------------
    function isValidGroupId($groupId)
    {
        if (ensureSaneOpenSSLKey($groupId)) {
            $files = $this->getMultiFileData($groupId);

            return !empty($files);
        }

        return false;
    }


    // --------------------------------
    // Gets information about a transaction based on group ID.
    // --------------------------------
    function getMultiFileData($groupId)
    {
        $statement = $this->db->prepare(
            'SELECT * ' .
            'FROM files ' .
            'WHERE filegroupid = :filegroupid ' .
            'ORDER BY fileoriginalname ASC'
        );

        $statement->bindParam(':filegroupid', $groupId);

        $statement = $this->db->execute($statement);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        $returnArray = array();

        foreach ($result as $row) {
            if ($row['filestatus'] == 'Available') {
                $row["downloads"] = $this->countDownloads($row["filevoucheruid"]);
                array_push($returnArray, $row);
            }
        }

        return $returnArray;
    }

    // --------------------------------
    // Returns total number of times each file in a transaction has been downloaded (total, not per-user).
    // --------------------------------
    function getFileDownloadTotals($trackingCode, $authUserUid)
    {
        $statement = $this->db->prepare(
            "SELECT COUNT(*), logfilename " .
            "FROM logs " .
            "WHERE logfiletrackingcode = :logfiletrackingcode " .
            "AND logtype= 'Download' " .
            "AND logauthuseruid = :logauthuserid " .
            "GROUP BY logfilename " .
            "ORDER BY logfilename ASC"
        );

        $statement->bindParam(':logfiletrackingcode', $trackingCode);
        $statement->bindParam(':logauthuserid', $authUserUid);

        $statement = $this->db->execute($statement);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    // --------------------------------
    // Removes a recipient from a transaction and sends email(s).
    // --------------------------------
    function deleteRecipient($groupId, $notifyRecipient)
    {
        $files = $this->getMultiFileData($groupId);

        $statement = $this->db->prepare(
            "UPDATE files " .
            "SET filestatus = 'Deleted' " .
            "WHERE filegroupid = :filegroupid"
        );

        $statement->bindParam(':filegroupid', $groupId);

        $statement = $this->db->execute($statement);
        $result = $statement->rowCount();

        if ($result != 0) {
            $this->sendMail->sendRecipientDeleted($files, $notifyRecipient);
            $this->saveLog->saveLog($files[0], 'Removed', '');
            return true;
        }

        return false;
    }

    // --------------------------------
    // Deletes all files in a transaction.
    // --------------------------------
    function deleteTransaction($trackingCode, $authUserUid, $notifyRecipients)
    {
        $recipients = $this->getTransactionRecipients($trackingCode, $authUserUid);

        $statement = $this->db->prepare(
            "UPDATE files " .
            "SET filestatus = 'Deleted' " .
            "WHERE filetrackingcode = :filetrackingcode " .
            "AND fileauthuseruid = :fileauthuseruid"
        );

        $statement->bindParam(':filetrackingcode', $trackingCode);
        $statement->bindParam(':fileauthuseruid', $authUserUid);

        $statement = $this->db->execute($statement);
        $result = $statement->rowCount();

        if ($result != 0) {
            $this->sendMail->sendTransactionDeleted($recipients, $notifyRecipients);
            return true;
        }

        return false;
    }

    public function incrementDownloadCount($voucherUid)
    {
        if (ensureSaneFileUid($voucherUid)) {
            $statement = $this->db->prepare(
                "UPDATE files " .
                "SET filenumdownloads = filenumdownloads + 1 " .
                "WHERE filevoucheruid = :filevoucheruid"
            );

            $statement->bindParam(':filevoucheruid', $voucherUid);
            $this->db->execute($statement);
        }
    }

    // --------------------------------
    // Adds a table record for a created voucher and sends email(s).
    // --------------------------------
    public function insertVoucher($to, $from, $expiry, $message, $subject)
    {
        if ($this->authSaml->isAuth()) {

            global $config;
            $dbCheck = DB_Input_Checks::getInstance();
            $authAttributes = $this->authSaml->sAuth();

            $statement = $this->db->prepare(
                'INSERT INTO files (' .
                'fileexpirydate, fileto, filesubject, fileactivitydate, filevoucheruid, filemessage, ' .
                'filefrom, filesize, fileoriginalname, filestatus, fileip4address, fileip6address, ' .
                'filesendersname, filereceiversname, filevouchertype, fileuid, fileauthuseruid, ' .
                'fileauthuseremail, filecreateddate ' .
                ') VALUES ( ' .
                ':fileexpirydate, :fileto, :filesubject, :fileactivitydate, :filevoucheruid, :filemessage, ' .
                ':filefrom, :filesize, :fileoriginalname, :filestatus, :fileip4address, :fileip6address, ' .
                ':filesendersname, :filereceiversname, :filevouchertype, :fileuid, :fileauthuseruid, ' .
                ':fileauthuseremail, :filecreateddate ' .
                ')'
            );

            $blank = '';
            $zero = 0;

            // Set subject, overriding the default if one is passed as argument.
            $fileSubject = ($subject != '') ? $subject : lang('_EMAIL_SUBJECT_VOUCHER');
            $statement->bindParam(':filesubject', $fileSubject);

            // Set expiry, activity and created dates.
            $fileExpiryDate = date($config['db_dateformat'], strtotime($expiry));
            $statement->bindParam(':fileexpirydate', $fileExpiryDate);
            $fileActivityDate = date($config['db_dateformat'], time());
            $statement->bindParam(':fileactivitydate', $fileActivityDate);
            $statement->bindParam(':filecreateddate', $fileActivityDate);

            $fileVoucherUid = getGUID();
            $statement->bindParam(':filevoucheruid', $fileVoucherUid);
            $statement->bindParam(':fileto', $to);
            $statement->bindParam(':filefrom', $from);
            $statement->bindParam(':filemessage', $message);
            $statement->bindParam(':filesize', $zero);
            $statement->bindParam(':fileoriginalname', $blank);

            $fileStatus = 'Voucher';
            $statement->bindParam(':filestatus', $fileStatus);

            $fileIp4Address = $dbCheck->checkIP($_SERVER['REMOTE_ADDR']);
            $statement->bindParam(':fileip4address', $fileIp4Address);
            $fileIp6Address = $dbCheck->checkIPv6($_SERVER['REMOTE_ADDR']);
            $statement->bindParam(':fileip6address', $fileIp6Address);

            $statement->bindParam(':filesendersname', $blank);
            $statement->bindParam(':filereceiversname', $blank);
            $statement->bindParam(':filevouchertype', $blank);

            $fileUid = getGUID();
            $statement->bindParam(':fileuid', $fileUid);
            $statement->bindParam(':fileauthuseruid', $authAttributes["saml_uid_attribute"]);
            $statement->bindParam(':fileauthuseremail', $from);

            $this->db->execute($statement);

            // Get voucher data to log and email.
            $dataItem = $this->getVoucherData($fileVoucherUid);
            $this->saveLog->saveLog($dataItem, "Voucher Sent", "");

            return $this->sendMail->sendVoucherIssued($fileVoucherUid);
        }

        return false; // Not authenticated.
    }

    // --------------------------------
    // Returns information about a voucher, based on filevoucheruid.
    // --------------------------------
    public function getVoucherData($vid)
    {
        if (!ensureSaneFileUid($vid)) {
            trigger_error("Invalid voucher UID", E_USER_ERROR);
        }

        $statement = $this->db->prepare(
            'SELECT * ' .
            'FROM files ' .
            'WHERE filevoucheruid = :filevoucheruid'
        );

        $statement->bindParam(':filevoucheruid', $vid);

        $statement = $this->db->execute($statement);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $result[0];
    }

    // --------------------------------
    // Validates fields of the $data array to ensure it is a valid file.
    // --------------------------------
    public function validateFileData($data)
    {
        global $config;
        global $resultArray;

        $errorArray = array();

        if (!isset($data["filesize"])) {
            array_push($errorArray, "err_missingfilesize");
        } elseif (disk_free_space($config['site_filestore']) - $data["filesize"] < 1) {
            array_push($errorArray, "err_nodiskspace");
        }

        if (!isset($data["fileexpirydate"])) {
            array_push($errorArray, "err_expmissing");
        }

        if (!isset($data["fileto"])) {
            array_push($errorArray, "err_tomissing");
        }

        if (!$this->isValidFileName($data)) {
            array_push($errorArray, "err_invalidfilename");
        }

        if (!$this->isValidFileExtension($data)) {
            array_push($errorArray, 'err_invalidextension');
        }

        $errorArray = $this->validateRecipientEmail($data, $errorArray);
        $errorArray = $this->validateSenderEmail($data, $errorArray);

        // If any errors were found, return them to client as JSON.
        if (count($errorArray) > 0) {
            $resultArray["errors"] = $errorArray;
            echo json_encode($resultArray);
            exit;
        }

        // No errors; ensure valid fields for database insert.
        $dbCheck = DB_Input_Checks::getInstance();

        $data["fileexpirydate"] = $this->ensureValidFileExpiryDate($data["fileexpirydate"]);
        $data["filesubject"] = (isset($data["filesubject"])) ? $data["filesubject"] : "";
        $data["fileactivitydate"] = date($config['db_dateformat'], time());
        $data["filevoucheruid"] = (isset($data["filevoucheruid"])) ? $data["filevoucheruid"] : getGUID();
        $data["filemessage"] = (isset($data["filemessage"])) ? $data["filemessage"] : "";
        $data["fileoriginalname"] = sanitizeFilename($data['fileoriginalname']);
        $data["filestatus"] = "Pending";

        $data["fileip4address"] = $dbCheck->checkIP($_SERVER['REMOTE_ADDR']);
        $data["fileip6address"] = $dbCheck->checkIPv6($_SERVER['REMOTE_ADDR']);
        $data["filesendersname"] = isset($data['filesendersname']) ? $data['filesendersname'] : null;
        $data["filereceiversname"] = isset($data['filereceiversname']) ? $data['filereceiversname'] : null;
        $data["filevouchertype"] = isset($data['filevouchertype']) ? $data['filevouchertype'] : null;
        $data["filecreateddate"] = date($config['db_dateformat'], time());
        $data['fileuid'] = $data['fileuid'] == '' ? '' : getGUID();
        $data['filenumdownloads'] = isset($data['filenumdownloads']) ? $data['filenumdownloads'] : 0;

        return $data;
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
        global $config;
        $ban_extension = explode(',', $config['ban_extension']);

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
        global $config;

        if (!isset($data["fileto"])) {
            array_push($errorArray, "err_filetomissing");
            return $errorArray;
        }

        $emailTo = str_replace(",", ";", $data["fileto"]);
        $emailArray = preg_split("/;/", $emailTo);

        if (count($emailArray) > $config['max_email_recipients']) {
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
    // Check that the file expiry date is valid, resetting it to a default value if not.
    // --------------------------------
    public function ensureValidFileExpiryDate($data)
    {
        global $config;
        $latestValidDate = strtotime('+' . $config['default_daysvalid'] . ' day');

        if ((strtotime($data) >= $latestValidDate || strtotime($data) <= strtotime('now'))) {
            // Expiry date is invalid, reset it to max date from server config.
            $data = date($config['db_dateformat'], $latestValidDate);
        }

        return date($config['db_dateformat'], strtotime($data));
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
    // Removes a voucher and sends email(s).
    // --------------------------------
    public function deleteVoucher($fileId)
    {
        if ($this->authSaml->isAuth()) {
            $statement = $this->db->prepare(
                "UPDATE files " .
                "SET filestatus = 'Voucher Cancelled' " .
                "WHERE fileid = :fileid"
            );

            $statement->bindParam(':fileid', $fileId);
            $this->db->execute($statement);

            $fileArray = $this->getVoucher($fileId);

            if (count($fileArray) > 0) {
                $this->saveLog->saveLog($fileArray[0], 'Voucher Cancelled', '');
                return $this->sendMail->sendVoucherCancelled($fileArray[0]['filevoucheruid']);
            }
        }

        return false;
    }

    // --------------------------------
    // Returns information about a voucher, based on filevoucheruid.
    // --------------------------------
    public function getVoucher($vid)
    {
        $statement = $this->db->prepare(
            'SELECT * ' .
            'FROM files ' .
            'WHERE fileid = :fileid'
        );

        $statement->bindParam(':fileid', $vid);
        $statement = $this->db->execute($statement);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    // --------------------------------
    // Closes a voucher (cancelling it), returning true if successful.
    // --------------------------------
    public function closeVoucher($fileId)
    {
        if ($this->authSaml->isAuth() || $this->authVoucher->aVoucher()) {
            $statement = $this->db->prepare(
                "UPDATE files " .
                "SET filestatus = 'Closed' " .
                "WHERE fileid = :fileid"
            );

            $statement->bindParam(':fileid', $fileId);
            $this->db->execute($statement);

            $fileArray = $this->getVoucher($fileId);

            if (count($fileArray) > 0) {
                $this->saveLog->saveLog($fileArray[0], 'Voucher Cancelled', '');
                return true;
            }
        }

        return false;
    }

    // --------------------------------
    // Closes a complete voucher based on filevoucheruid.
    // --------------------------------
    public function closeCompleteVoucher($fileVoucherUid)
    {
        if ($this->authSaml->isAuth() || $this->authVoucher->aVoucher()) {
            $statement = $this->db->prepare(
                "UPDATE files " .
                "SET filestatus = 'Closed' " .
                "WHERE filevoucheruid = :filevoucheruid"
            );

            $statement->bindParam(':filevoucheruid', $fileVoucherUid);
            $this->db->execute($statement);

            logEntry('Voucher Closed: ' . $fileVoucherUid);
        }
    }

    // --------------------------------
    // Deletes a file.
    // --------------------------------
    public function deleteFile($fileId)
    {
        global $config;

        if ($this->authSaml->isAuth()) {
            $statement = $this->db->prepare(
                "UPDATE files " .
                "SET filestatus = 'Deleted' " .
                "WHERE fileid = :fileid"
            );

            $statement->bindParam(':fileid', $fileId);
            $this->db->execute($statement);

            $fileArray = $this->getVoucher($fileId);

            if (count($fileArray) > 0) {
                $this->sendMail->sendEmail($fileArray[0], $config['defaultfilecancelled']);
                $this->saveLog->saveLog($fileArray[0], 'File Cancelled', '');
                return true;
            }
        }

        return false;
    }

    // --------------------------------
    // Deletes a transaction and returns result.
    // --------------------------------
    public function cancelUpload($authUserUid, $trackingCode)
    {
        $statement = $this->db->prepare(
            'DELETE FROM files ' .
            'WHERE fileauthuseruid  = :fileauthuseruid ' .
            'AND filetrackingcode = :filetrackingcode'
        );

        $statement->bindParam(':fileauthuseruid', $authUserUid);
        $statement->bindParam(':filetrackingcode', $trackingCode);

        $statement = $this->db->execute($statement);
        return $statement->rowCount();
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
    // --------------------------------
    public function driveSpace()
    {
        global $config;

        // Use absolute locations.
        $result["site_filestore_total"] = disk_total_space($config['site_filestore']);
        $result["site_temp_filestore_total"] = disk_total_space($config['site_temp_filestore']);
        $result["site_filestore_free"] = disk_free_space($config['site_filestore']);
        $result["site_temp_filestore_free"] = disk_free_space($config['site_temp_filestore']);

        return $result;

    }

    // --------------------------------
    // Checks if active menu item, returns 'active' or empty string.
    // --------------------------------
    public function active($value, $menuName)
    {
        return $value == $menuName ? 'active' : '';
    }

    // --------------------------------
    // Converts from UNIX to DOS style timestamp.
    // Defaults to current time if $timestamp parameter is missing or 0.
    // --------------------------------
    function unixToDosTime($timestamp = 0)
    {
        $timeBit = ($timestamp == 0) ? getdate() : getdate($timestamp);

        if ($timeBit['year'] < 1980) {
            return (1 << 21 | 1 << 16);
        }

        $timeBit['year'] -= 1980;

        return ($timeBit['year'] << 25 | $timeBit['mon'] << 21 |
            $timeBit['mday'] << 16 | $timeBit['hours'] << 11 |
            $timeBit['minutes'] << 5 | $timeBit['seconds'] >> 1);
    }
}
