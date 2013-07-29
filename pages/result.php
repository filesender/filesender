<?php
if ($s == "complete" || $s == "completev") {
    if (isset($_REQUEST['gid']) && ensureSaneOpenSSLKey($_REQUEST['gid']) && $functions->isValidGroupId($_REQUEST['gid'])) {
        // Upload completed, display list of uploaded files.
        echo '<div class="box">';
        $groupId = $_REQUEST['gid'];
        $files = $functions->getMultiFileData($groupId);

        echo lang("_TRACKING_CODE") . ': <a href="?gid=' . $groupId . '">' . $files[0]['filetrackingcode'] . '</a><br /><br />';
        echo lang("_UPLOADED_LIST");
        echo '<ul>';

        foreach ($files as $file) {
            echo '<li>' . $file['fileoriginalname'] . ' (' . formatBytes($file['filesize']) . ')</li>';
        }

        echo '</ul>';

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
