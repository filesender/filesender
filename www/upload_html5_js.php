<script type="text/javascript">

    var emailCache = '';

    // Validate FILE (with embedded calls to check filename and file-extension)
    function validate_file(id)
    {
        var isValid = true;
        var file = fileData[id];

        if (!validateFileName(file.name)) {
            isValid =  false;
        } else if (file.size < 1) {
            fileMsg('<?php echo lang('_INVALID_FILESIZE_ZERO') ?>');
            isValid =  false;
        } else if (file.size > maxHTML5UploadSize) {
            fileMsg('<?php echo lang('_INVALID_TOO_LARGE_1') ?> ' + readablizebytes(maxHTML5UploadSize) + '. <?php echo lang('_SELECT_ANOTHER_FILE') ?> ');
            isValid =  false;
        }

        if (isValid) {
            $('#dragfilestouploadcss').removeClass('errorglow');
            $("#file_msg").hide();
            return true;
        } else {
            $('#dragfilestouploadcss').addClass('errorglow');
            return false;
        }
    }

    function validateNumFiles() {
        if (n >= maxUploads) {
            fileMsg('<?php echo lang("_MAX_FILES_REACHED") ?>' + maxUploads);
            $('#dragfilestouploadcss').addClass('errorglow');
            return false;
        } else {
            $("#file_msg").hide();
            $('#dragfilestouploadcss').removeClass('errorglow');
            return true;
        }
    }


    function validate_files() {

        if (n == -1) {
            // display message if a user enters all form details and selects upload without selecting a file
            // in theory this error should not appear as a browse button should not be visible without a file first being selected
            fileMsg('<?php echo lang('_SELECT_FILE') ?>');
            $('#dragfilestouploadcss').addClass('errorglow');
            return false;
        }

        for(var i = 0; i <= n; i++) {
            if (!fileData[i].name) continue;
            if(!validate_file(i)) return false;
        }

        if ($('#filestoupload').html().indexOf('ghost_file') != -1) {
            fileMsg('<?php echo lang('_REMOVE_GHOST_FILES') ?>');
            $('#dragfilestouploadcss').addClass('errorglow');
            return false;
        }

        return validateNumFiles();
    }

    // HTML5 form Validation
    function validateForm() {
        // remove messages from any previous attempt
        hideMessages();
        var isValid = true;
        if (!validate_recipients()){
            isValid = false;
        }
        if (!validate_files()){
            isValid = false;
        }
        if (!validate_expiry()) {
            isValid = false;
        }
        <?php if ($config['AuP']) { ?>
        if (!validate_aup()) {
            isValid = false;
        }
        <?php } ?>

        if (!isValid) {
            statusMessage(lang('_THERE_ARE_VALIDATION_ERRORS_ON_THIS_PAGE'), 'red');
        } else {
            statusMessage(lang('_YOUR_UPLOAD_HAS_STARTED'), 'green');
        }
//        $('#filesubject').html(htmlentities($('#filesubject')));
//        $('#filemessage').html(htmlentities($('#filemessage')));
        return isValid;
    }

    function constrainNumWebWorkers() {
        <?php
        $limit = 'undefined';
        if (isset($config['webWorkersLimit'])) {
            $limit = $config['webWorkersLimit'];
        }
        ?>

        var maxLimitWebWorkers = <?php echo $limit; ?>;
        var workerCount = $('#workerCount');
        if (maxLimitWebWorkers != 'undefined' && parseInt(workerCount.val()) > maxLimitWebWorkers) {
            workerCount.val(maxLimitWebWorkers);
        }
    }

    function checkFilesSelected() {
        return document.getElementById('fileToUpload').files[0] != null || document.getElementById('file_0') != null;
    }

    function disableToField(){
        emailCache = $('#fileto').val();
        $('#fileto').val($('#filefrom').val());
        $('#fileto').attr('disabled', 'disabled');
    }

    function reenableToField(){
        $('#fileto').val(emailCache);
        $('#fileto').removeAttr('disabled');
    }

    // Functions which change the onclick event on the button based on the previous click event.
    function setButtonToClear()
    {
        var clearAll = $('#clearallbtn');
        clearAll.find('.ui-button-text').html('<?php echo lang("_CLEAR_ALL"); ?>');
        clearAll.attr('onclick', 'clearFileBox()');
    }

    function setButtonToUndo()
    {
        var clearAll = $('#clearallbtn');
        clearAll.button('enable');
        clearAll.find('.ui-button-text').html('<?php echo lang("_UNDO_CLEAR"); ?>');
        clearAll.attr('onclick', 'undoClearFileBox()');
    }

    function pauseUpload()
    {
        terasender.pauseUpload();
        $('.progress_bar').css('background-color', '#FF8800');
        $('#progress_string').html('<?php echo lang("_PAUSING"); ?>');
        vid = fileData[n].filevoucheruid;
        pausedUpload = true;
        $('#pauseBTN').button('disable');

    }

    function resumeUpload() {
        $('#progress_string').html(percentComplete + '%');
        startUpload();
        $('.progress_bar').css('background-color', '#5c5');
        resumeTime = new Date().getTime();
        timeSpentPaused += resumeTime - pauseTime;
    }

    function uploadPaused()
    {
        $('#progress_string').html('<?php echo lang("_UPLOAD_PAUSED"); ?>');
        pauseTime = new Date().getTime();
        $('#pauseBTN').button('enable');
    }

</script>
