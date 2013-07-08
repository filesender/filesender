<script type="text/javascript">

    // Validate FILE (with embedded calls to check filename and file-extension)
    function validate_file(id) {
        fileMsg('');

        if (!fileData[id]) {
            // display message if a user enters all form details and selects upload without selecting a file
            // in theory this error should not appear as a browse button should not be visible without a file first being selected
            fileMsg('<?php echo lang('_SELECT_FILE') ?>');
            return false;
        } else {
            var file = fileData[id];

            if (!validateFileName(file.name)) {
                return false;
            } else if (file.size < 1) {
                fileMsg('<?php echo lang('_INVALID_FILESIZE_ZERO') ?>');
                return false;
            } else if (file.size > maxHTML5UploadSize) {
                fileMsg('<?php echo lang('_INVALID_TOO_LARGE_1') ?> ' + readablizebytes(maxHTML5UploadSize) + '. <?php echo lang('_SELECT_ANOTHER_FILE') ?> ');
                return false;
            }
        }
        return true;
    }

    // HTML5 form Validation
    function validateForm() {
        // remove messages from any previous attempt
        hideMessages();
        return (validate_fileto() && validate_file(n) && validate_expiry() && validate_aup());
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
</script>
