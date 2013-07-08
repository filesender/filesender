<?php
// check if a voucher and load into form if it is
$filestatus = 'Available';
$voucherUID = '';
$senderemail = $useremail;
$functions = Functions::getInstance();

// get initial upload uid
$id = getGUID();
// set id for progress bar upload
// $id = md5(microtime() . rand());

// check if this is a voucher
if ($authvoucher->aVoucher()) {
    // clear aup session
    //unset ($_SESSION['aup'], $var);

    // get voucher information
    $voucherData = $authvoucher->getVoucher();
    $voucherUID = $voucherData[0]['filevoucheruid'];
    $senderemail = array($voucherData[0]['fileto']);
    // check if voucher is invalid (this should be an external function
    if ($voucherData[0]['filestatus'] == 'Voucher') {
        $filestatus = 'Voucher';
    } else {
        if ($voucherData[0]['filestatus'] == 'Voucher Cancelled' || $voucherData[0]['filestatus'] == 'Closed') {
            echo '<p>' . lang('_VOUCHER_CANCELLED') . '</p>';
            return;
        }
    }
}

if (isset($_COOKIE['SimpleSAMLAuthToken'])) {
    $token = urlencode($_COOKIE['SimpleSAMLAuthToken']);
} else {
    $token = '';
}

global $config;
?>

<script type="text/javascript">
    var uploadid = '<?php echo $id ?>';
    var maximumDate = <?php echo (time()+($config['default_daysvalid']*86400))*1000 ?>;
    var minimumDate = <?php echo (time()+86400)*1000 ?>;
    var maxFLASHuploadsize = <?php echo $config['max_flash_upload_size']; ?>;
    var maxHTML5UploadSize = <?php echo $config['max_html5_upload_size']; ?>;
    var maxEmailRecipients = <?php echo $config['max_email_recipients']; ?>;
    var datepickerDateFormat = '<?php echo lang('_DP_dateFormat'); ?>';
    var chunksize =  <?php echo $config['upload_chunk_size']; ?>;
    var aup = '<?php echo $config['AuP'] ?>';
    var bytesUploaded = 0;
    var bytesTotal = 0;
    var ext = '<?php echo $config['ban_extension']?>';
    var bannedExtensions = ext.split(",");
    var previousBytesLoaded = 0;
    var intervalTimer = 0;
    var errmsg_disk_space = "<?php echo lang("_DISK_SPACE_ERROR"); ?>";
    var filedata = [];
    var nameLang = '<?php echo lang("_FILE_NAME"); ?>';
    var sizeLang = '<?php echo lang("_SIZE"); ?>';
    var groupid = '<?php echo getOpenSSLKey(); ?>';

    <?php
        if (!$authvoucher->aVoucher()) {
            $userData = $authsaml->sAuth();
            echo "var trackingCode = '" . $functions->getTrackingCode($userData['saml_uid_attribute']) . "';";
        } else {
            echo "var trackingCode = '" . $functions->getTrackingCode() . "';";
        }

        if (isset($_REQUEST['vid'])) {
            echo "\nvar vid = '" . htmlspecialchars($_REQUEST['vid']) . "';\n";
        }
    ?>

    function autoCompleteEmails() {
        var availableTags = [<?php  echo (isset($config["autocomplete"]) && $config["autocomplete"])?  $functions->uniqueemailsforautocomplete():  ""; ?>];

        function split(val) {
            return val.split(/,\s*/);
        }

        function extractLast(term) {
            return split(term).pop();
        }

        $("#fileto")
            // don't navigate away from the field on tab when selecting an item
            .bind("keydown", function (event) {
                if (event.keyCode === $.ui.keyCode.TAB &&
                    $(this).data("uiAutocomplete").menu.active) {
                    event.preventDefault();
                }
            })
            .autocomplete({
                minLength: 0,
                source: function (request, response) {
                    // delegate back to autocomplete, but extract the last term
                    response($.ui.autocomplete.filter(
                        availableTags, extractLast(request.term)));
                },
                focus: function () {
                    // prevent value inserted on focus
                    return false;
                },
                select: function (event, ui) {
                    var terms = split(this.value);
                    // remove the current input
                    terms.pop();
                    // add the selected item
                    terms.push(ui.item.value);
                    // add placeholder to get the comma-and-space at the end
                    terms.push("");
                    this.value = terms;//.join( ", " );
                    return false;
                }
            });
    }

    function displayAuthError(){
        $("#dialog-autherror").dialog({ height: 400, width: 550, modal: true, title: '',
            buttons: {
                '<?php echo lang('_OK') ?>': function () {
                    location.reload();
                }
            }
        });
    }

    function validateExtension(filename) {
        for (var i = 0, len = bannedExtensions.length; i < len; ++i) {
            if (filename.split('.').pop() == bannedExtensions[i]) {
                return false;
            }
        }
        return true;
    }

    function uploadError(name, size) {
        openErrorDialog("<?php echo lang("_ERROR_UPLOADING_FILE") ?> " + name + ":" + size);
    }


    function validate() {
        if (html5) {
            if (validateForm()) {
                constrainNumWebWorkers(); // make sure selected web workers isn't over the config limit
                n = 0;
                openProgressBar(trackingCode);
                startTime = new Date().getTime();// Calling before first upload stops progress bar opening for every download (when attempting to close it)
                startUpload();

            }
        } else {
            getFlexApp("filesenderup").returnMsg("validatebeforeupload");
        }
    }

    function validateFileName(name)
    {

        if (!validateExtension(name)) {
            fileMsg("<?php echo lang("_INVALID_FILE_EXT")." ".lang("_SELECT_ANOTHER_FILE") ?>");
            return false;
        }
        if (/^[^\\\/:;\*\?\"<>|]+(\.[^\\\/:;\*\?\"<>|]+)*$/.test(name)) {
            return true;
        } else {
            fileMsg("<?php echo lang("_INVALID_FILE_NAME") ?>");
            return false;
        }
    }

    function openErrorDialog(msg) {
        // Default error message dialog.
        var dialogDefault = $("#dialog-default");
        dialogDefault.dialog({ height: 200, modal: true, title: 'Error',
            buttons: {
                '<?php echo lang('_OK') ?>': function () {

                    $(this).dialog('close');
                }
            }
        });
        dialogDefault.html(msg);
        dialogDefault.dialog('open');
    }


    function keepMeAlive() {
        $.ajax({
            url: "keepalive.php" + '?x=' + encodeURIComponent(new Date().toString()),
            success: function (data) {
            }
        });
    }

</script>

