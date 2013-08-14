<?php
if ($s == "complete" || $s == "completev") {
    if (isset($_REQUEST['gid']) && ensureSaneOpenSSLKey($_REQUEST['gid']) && $functions->isValidGroupId($_REQUEST['gid'])) {
        // Upload completed, display list of uploaded files.
        echo '<div class="box">';
        $groupId = $_REQUEST['gid'];
        $files = $functions->getMultiFileData($groupId);

        $list = "";
        $totalSize = 0;

        foreach ($files as $file) {
            $totalSize += $file['filesize'];
            $list .= '<li>' . $file['fileoriginalname'] . ' (' . formatBytes($file['filesize']) . ')</li>';
        }

        echo lang("_TRACKING_CODE") . ': ' . $files[0]['filetrackingcode'] . '<br /><br />';

        if (isset($_REQUEST['start']) && isset($_REQUEST['end'])) {
            $start = intval($_REQUEST['start']);
            $end = intval($_REQUEST['end']);

            echo 'Upload start time: ' . date('H:i:s', $start) . '<br />';
            echo 'Upload end time: ' . date('H:i:s', $end) . '<br />';

            $uploadSpeed = $totalSize / ($end - $start); // Bytes per second.

            echo 'Average upload speed: ' . formatBytes($uploadSpeed) . '/s. <br /><br />';
        }

        echo lang("_UPLOADED_LIST");
        echo '<ul>' . $list . '</ul>';

        if ($s != "completev") {
            echo lang("_REFER_TO_MY_FILES");
        }
        echo '</div>';

        echo '<script type="text/javascript">'
            .   'statusMessage("' . lang("_UPLOAD_COMPLETE") . '", "green");'
            . '</script>';
    } else {
        // Group ID wasn't supplied, so nothing to show here.
        if ($isAuth) {
            require_once('../pages/multiupload.php');
        } else {
            require_once('../pages/logon.php');
        }
    }
} else {
    // An error occurred, display error message.
    echo '<script type="text/javascript">'
        .   'statusMessage("' . lang("_ERROR_UPLOAD_FAILED") . '", "red");'
        . '</script>';

    echo '<div class="box" style="text-align: center">';

    if ($s == "uploaderror") {
        echo lang("_ERROR_UPLOADING_FILE");
    } else if ($s == "emailsenterror") {
        echo lang("_ERROR_SENDING_EMAIL");
    } else if ($s == "filesizeincorrect") {
        echo lang("_ERROR_INCORRECT_FILE_SIZE");
    }

    echo '</div>';
}
