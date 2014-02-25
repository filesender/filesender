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

/* ---------------------------------
 * MyFiles Page
 * Display details about a users files
 * Allow a user to re-send a file
 * Allow a user to Forward a file
 * ---------------------------------
 *
 */

// Get list of user files and display page
if (isset($_REQUEST['s'])) {
    $transactions = '';

    if ($_REQUEST['s'] == 'vouchers') {
        $transactions = $functions->getUsedVoucherTransactions();
        $heading = '<div class="heading">' . lang('_VOUCHER_UPLOADS') . '</div>';
    } elseif ($_REQUEST['s'] == 'files') {
        $transactions = $functions->getUserTrackingCodes();
        $heading = '<div id="pageheading">' . lang('_MY_FILES') . '</div>';
    }
} else {
    exit;
}

// Check for delete/resent/added actions and report back.
if (isset($_REQUEST['tc']) && isset($_REQUEST['fileauth'])) {
    checkAddRecipient($statusMsg, $statusClass);
    checkDeleteTransaction($statusMsg, $statusClass);
}

if (isset($_REQUEST['a']) && isset($_REQUEST['groupid'])) {
    checkDeleteRecipient($statusMsg, $statusClass);
    checkResendEmail($statusMsg, $statusClass);
}

foreach ($errorArray as $message) {
    if ($message == 'err_emailnotsent') {
        $statusMsg = lang('_ERROR_SENDING_EMAIL');
        $statusClass = 'red';
    }
}

function checkAddRecipient(&$statusMsg, &$statusClass)
{
    global $functions;

    if ($_REQUEST['a'] == 'add') {
        if (isset($_REQUEST['fileto'])) {
            $listOfEmails = explode(',', $_REQUEST['fileto']);
            if ($functions->addRecipientsToTransaction($listOfEmails, $_REQUEST['tc'], $_REQUEST['fileauth'], $_REQUEST['filesubject'], $_REQUEST['filemessage'])) {
                $statusMsg = lang('_EMAIL_SENT');
                $statusClass = 'green';
            } else {
                $statusMsg = lang('_ERROR_SENDING_EMAIL');
                $statusClass = 'red';
            }
        }
    }
}

function checkDeleteTransaction(&$statusMsg, &$statusClass)
{
    global $functions;

    if ($_REQUEST['a'] == 'deltrans') {
        $requestConfirmation = $_REQUEST['notifyRecipients'] == 'true' ? true : false;

        if ($functions->deleteTransaction($_REQUEST['tc'], $_REQUEST['fileauth'], $requestConfirmation)) {
            $statusMsg = lang('_TRANSACTION_DELETED');
            $statusClass = 'green';
        } else {
            $statusMsg = lang('_ERROR_DELETING_RECIPIENT');
            $statusClass = 'red';
        }
    }
}

function checkDeleteRecipient(&$statusMsg, &$statusClass)
{
    global $functions;

    if ($_REQUEST['a'] == 'delrecip') { // TODO: Needs some sort of authentication before deletion.
        $recipient = $_REQUEST['groupid'];
        $requestConfirmation = $_REQUEST['notifyRecipient'] == 'true' ? true : false;

        if ($functions->deleteRecipient($recipient, $requestConfirmation)) {
            $statusMsg = lang('_RECIPIENT_DELETED');
            $statusClass = 'green';
        } else {
            $statusMsg = lang('_PERMISSION_DENIED');
            $statusClass = 'red';
        }
    }
}

function checkResendEmail(&$statusMsg, &$statusClass)
{
    global $sendmail;

    if ($_REQUEST['a'] == 'resend') {
        $recipient = $_REQUEST['groupid'];

        if ($sendmail->sendDownloadAvailable($recipient)) {
            $statusMsg = lang('_EMAIL_SENT');
            $statusClass = 'green';
        } else {
            $statusMsg = lang('_PERMISSION_DENIED');
            $statusClass = 'red';
        }
    }
}
?>
<script type="text/javascript">
//<![CDATA[
var selectedFile = ""; // file uid selected when deleteting
// set default maximum date for date datepicker
var maximumDate = <?php echo (time()+($config['default_daysvalid']*86400))*1000 ?>;
var minimumDate = <?php echo (time()+86400)*1000 ?>;
var maxEmailRecipients = <?php echo $config['max_email_recipients'] ?>;
var datepickerDateFormat = '<?php echo lang('_DP_dateFormat'); ?>';
var showall = false; // flag for toggleDisplayRow all switch to toggleDisplayRow or hide download summaries
var showAllRecipients = false; // flag for expanding all download summaries for individual recipients
var selectedRecipient = "";

$(function () {
    getDatePicker();

    // resend email modal dialog box
    $("#dialog-resend").dialog({ autoOpen: false, height: 180, modal: true,
        buttons: {
            'cancelBTN': function () {
                $(this).dialog("close");
            },
            'confirmBTN': function () {
                $(this).dialog("close");
                window.location.href = "index.php?s=files&a=resend&groupid=" + selectedRecipient;
            }
        }
    });

    $("#dialog-delete-recipient").dialog({ autoOpen: false, height: 200, width: 400, modal: true,
        buttons: {
            'cancelBTN': function () {
                $(this).dialog("close");
            },
            'confirmBTN': function () {
                var deleteRecipForm = $('#deleteRecipForm');
                var checked = $('#informRecipient').is(':checked');
                deleteRecipForm.attr('action', deleteRecipForm.attr('action') + checked);
                deleteRecipForm.submit();
            }
        }
    });

    $("#dialog-delete-transaction").dialog({ autoOpen: false, height: 200, width: 400, modal: true,
        buttons: {
            'cancelBTN': function () {
                $(this).dialog("close");
            },
            'confirmBTN': function () {
                var deleteTransForm = $('#deleteTransForm');
                var checked = $('#informRecipients').is(':checked');
                deleteTransForm.attr('action', deleteTransForm.attr('action') + checked);
                deleteTransForm.submit();
            }
        }
    });

    // default auth error dialogue
    $("#dialog-autherror").dialog({ autoOpen: false, height: 240, width: 350, modal: true, title: "",
        buttons: {
            '<?php echo lang("_OK") ?>': function () {
                location.reload(true);
            }
        }
    });

    // add new recipient modal dialog box
    $("#dialog-addrecipient").dialog({ autoOpen: false, height: 360, width: 650, modal: true,
        buttons: {
            'cancelBTN': function () {
                // clear form
                $("#fileto").val("");
                $("#datepicker").datepicker("setDate", new Date(maximumDate));
                $("#filesubject").val("");
                $("#filemessage").val("");
                $(this).dialog("close");
            },
            'sendBTN': function () {
                // Disable the send button to prevent duplicate sending
                if (validateForm()) {
                    $('#fileto').val(getRecipientsList());
                    $("#form1").submit();
                    $('#btn_addrecipientsend').attr("disabled", true);
                }

            }
        }
    });

    addButtonText();
    autoCompleteEmails();
});

// validate form before sending
function validateForm() {
    // remove previous validation messages
    $("#fileto_msg").hide();
    $("#expiry_msg").hide();
    var validate = true;
    if (!validate_recipients() || !validate_expiry()) validate = false;		// validate emails && date
    return validate;
}

function confirmResend(vid) {
    // confirm deletion of selected file
    selectedRecipient = vid;
    $("#dialog-resend").dialog("open");
}

function confirmDeleteRecipient(gid) {
    $('#deleteRecipForm').attr('action', "index.php?s=files&a=delrecip&groupid=" + gid + "&notifyRecipient=");
    $("#dialog-delete-recipient").dialog("open");
}

function confirmDeleteTransaction(trackingCode, fileauth) {
    $('#deleteTransForm').attr("action", "index.php?s=files&a=deltrans&fileauth=" + fileauth + "&tc=" + trackingCode + "&notifyRecipients=");
    $("#dialog-delete-transaction").dialog("open");
}

function openAddRecipient(fileauth, from, subject, message, trackingCode) {
    // Populate form and open add-recipient modal form.
    $("#form1").attr("action", "index.php?s=files&a=add&fileauth=" + fileauth + "&tc=" + trackingCode);
    $("#trackingCode").val(trackingCode);
    $("#filefrom").html(decodeURIComponent(from));
    $("#filesubject").val(decodeURIComponent(subject));
    $("#filemessage").val(decodeURIComponent(message));
    $("#fileto").val("");
    $("#datepicker").datepicker("setDate", new Date(maximumDate));

    // Clear error messages.
    $("#expiry_msg").hide();
    $("#file_msg").hide();
    $("#fileto_msg").hide();
    $("#maxemails_msg").hide();
    $("#dialog-addrecipient").dialog("open");
}

function expandRecipients(i, j) {
    var num = 0;
    var elemDisplay;
    if (!showAllRecipients) {
        for (num; num < j; num++) {
            elemDisplay = $('#show_' + i + '_' + num + '_recipients').css('display');
            if (elemDisplay == 'none') {
                toggleDisplayRow(i + "_" + num + "_recipients");
            }
        }
        $('#showicon_' + i + '_recipients').attr({class: 'fa fa-minus-circle fa-lg', alt: ''});
        showAllRecipients = true;
    } else {
        for (num; num < j; num++) {
            elemDisplay = $('#show_' + i + '_' + num + '_recipients').css('display');
            if (elemDisplay == 'table-row') {
                toggleDisplayRow(i + "_" + num + "_recipients");
            }
        }
        $('#showicon_' + i + '_recipients').attr({class: 'fa fa-plus-circle fa-lg', alt: ''});
        showAllRecipients = false;
    }
}

function expandTopMenu(numRows) {
    showall = !showall;

    for (var row = 1; row <= numRows; row++) {
        var elemDisplay = $('#show_' + row).css('display');
        if ((showall && elemDisplay == 'none') || (!showall && elemDisplay == 'table-row')) {
            toggleDisplayRow(row);
        }

        var imageSrc = showall ? 'fa fa-minus-circle fa-lg' : 'fa fa-plus-circle fa-lg';
        
        $('#showicon_top').attr({class: imageSrc, alt: ''});
    }
}

function toggleDisplayRow(i) {
    var show = $('#show_' + i);
    show.toggle();
    var $icon = $("#showicon_" + i);

    if (show.is(':visible')) {
        $icon.attr({class: "fa fa-minus-circle fa-lg", alt: ""});
    } else {
        $icon.attr({class: "fa fa-plus-circle fa-lg", alt: ""});
    }
}
//]]>
</script>

<div id="box">
<?php echo $heading; ?>
<div id="tablediv">
<table id="myfiles" style="table-layout:fixed; width: 100%; padding: 4px; border-spacing: 0; border: 0">
<tr class="headerrow">
    <td class="tblmcw1" onclick="expandTopMenu(<?php echo sizeof($transactions); ?>)" title="<?php echo lang("_SHOW_ALL"); ?>" style="vertical-align: middle; cursor:pointer"> <i id="showicon_top" class="fa fa-plus-circle fa-lg"></i></td>
    <td class="HardBreak" colspan="4" style="width: 35%; vertical-align: middle; text-align: center" id="myfiles_header_to"><strong><?php echo lang("_TO"); ?></strong></td>
    <td class="HardBreak tblmcw3" style="vertical-align: middle; text-align: center" id="myfiles_header_size"><strong><?php echo lang("_TOTAL_SIZE") ?></strong></td>
    <td class="HardBreak tblmcw3" id="myfiles_header_downloaded" style="vertical-align: middle; text-align: center"><strong><?php echo lang("_DOWNLOADS"); ?></strong></td>
    <td class="HardBreak tblmcw3" id="myfiles_header_expiry" style="vertical-align: middle; text-align: center"><strong><?php echo lang("_EXPIRY"); ?></strong></td>
    <td class="HardBreak" id="myfiles_header_filename" style="vertical-align: middle; text-align: center"><strong><?php echo lang("_TRACKING_CODE") ?></strong></td>
    <td class="tblmcw1 HardBreak" style="vertical-align: middle"></td>
</tr>
<?php
$i = 0;

if(sizeof($transactions) > 0)
{
    foreach($transactions as $item) {
        $i += 1; // Counter for file IDs.

        $transactionContents = $functions->getTransactionDetails($item['filetrackingcode'], $item['fileauthuseruid']);
        $contentsTableData = array();
        $recipients = array();
        $totalNumDownloads = 0;
        $totalSize = 0;

        foreach ($transactionContents as $fileRow) {
            $fileName = $fileRow['fileoriginalname'];
            $fileDownloads = $fileRow['filenumdownloads'];
            $totalNumDownloads += $fileDownloads;

            if (isset($contentsTableData[$fileName])) {
                $contentsTableData[$fileName]['filenumdownloads'] += $fileDownloads;
            } else {
                $contentsTableData[$fileName] = array(
                    'filesize' => $fileRow['filesize'],
                    'filenumdownloads' => $fileDownloads
                );

                $totalSize += $fileRow['filesize'];
            }

            $rec = $fileRow['fileto'];

            if (isset($recipients[$rec])) {
                $recipients[$rec]['files'][$fileName] = $fileDownloads;
                $recipients[$rec]['downloads'] += $fileDownloads;
            } else {
                $recipients[$rec] = array(
                    'downloads' => $fileDownloads,
                    'gid' => $fileRow['filegroupid'],
                    'files' => array($fileName => $fileDownloads)
                );
            }
        }

        $recipientEmails = array_keys($recipients);
        $recipientsString = implode(', ', $recipientEmails);
        $numRecipients = sizeof($recipientEmails);
        $fileToString = $recipientEmails[0];

        if ($numRecipients > 1) {
            $fileToString .= ' ... (' . ($numRecipients - 1) . ')';
        }

        $rowClass = ($i % 2 != 0)? 'class="altcolor"' : ''; // Alternating rows.

        if (isset($transactionContents[0])) {
            // Print the top level of the expandable row for the transaction.
            echo '<tr>
                    <td class="dr7" colspan="10">
                  </tr>

                  <tr ' . $rowClass . '>
                    <td  class="dr1 expct" onclick="toggleDisplayRow(' . $i . ')">
                   <i id="showicon_'.$i.'" style="cursor:pointer;" class="fa fa-plus-circle fa-lg" title="'. lang("_SHOW_ALL").'"></i>
                    </td>
                    <td colspan="4" class="dr2 HardBreak" title="' . $recipientsString . '">' . $fileToString . '</td>
                    <td class="dr2 HardBreak" style="text-align: center">' . formatBytes($totalSize) . '</td>
                    <td class="dr2 HardBreak" style="text-align: center">' . $totalNumDownloads . '</td>
                    <td class="dr2" style="text-align: center">
                        ' .date(lang('datedisplayformat'),strtotime($transactionContents[0]['fileexpirydate'])) . '
                    </td>
                    <td class="dr2" style="text-align: center">' . utf8ToHtml($transactionContents[0]['filetrackingcode'], true) . '</td>
                    <td class="dr8">
                      <i style="cursor:pointer; color: #ff0000;" title="'. lang("_DELETE_TRANSACTION") . '" onclick="confirmDeleteTransaction(&quot;'.$transactionContents[0]['filetrackingcode'].'&quot;,&quot;'.$item['fileauthuseruid'].'&quot;)" style="cursor:pointer;" class="fa fa-minus-circle fa-lg" title="'. lang("_SHOW_ALL").'"></i>
                    </td>
                  </tr>
                  <tr class="hidden" style="display:none" id="show_'.$i.'">
                    <td class="dr4"></td>
                    <td colspan="8">';

            // --------------------------------
            // SUMMARY TABLE
            // --------------------------------
            $hasMessage = $transactionContents[0]['filemessage'] != "";
            $hasSubject = $transactionContents[0]['filesubject'] != "";

            echo '<table style="width: 100%; padding: 0; border-collapse: collapse; border: 0;" class="rowdetails">
                    <tr>
                        <td colspan="2" class="dr9 headerrow">' .lang('_DETAILS'). '</td>
                    </tr>
                    <tr class="rowdivider">
                        <td class="dr4 sdheading tblmcw3 "><strong>' .lang("_CREATED"). '</strong></td>
                        <td class="dr6 HardBreak">' .date(lang('datedisplayformat'),strtotime($transactionContents[0]['filecreateddate'])). '</td>
                    </tr>
                    <tr>
                        <td class="dr4 sdheading"><strong>'.lang("_FROM").'</strong></td>
                        <td class="dr6 HardBreak">' .$transactionContents[0]['filefrom'] . '</td>
                    </tr>
                    <tr class="rowdivider">';

            // Print comma-separated list of recipients. Check if this is the last row in table (different borders).
            if ($hasMessage || $hasSubject) {
                echo '<td class="dr4 sdheading tblmcw3"><strong>' . lang('_TO') . '</strong></td>
                      <td class="dr6 HardBreak">' . $recipientsString . '</td>
                    </tr>';
            } else {
                echo '<td class="dr11 sdheading tblmcw3"><strong>' . lang('_TO') . '</strong></td>
                      <td class="dr13">' . $recipientsString . '</td>
                    </tr>';
            }

            // Print the file subject if it exists.
            if ($transactionContents[0]['filesubject'] != '') {
                if ($hasMessage) {
                    echo '<tr>
                            <td class="dr4 sdheading tblmcw3"><strong>' . lang('_SUBJECT') . '</strong></td>
                            <td class="dr6 HardBreak">' . utf8ToHtml($transactionContents[0]['filesubject'], true) . '</td>
                          </tr>';
                } else {
                    echo '<tr>
                            <td class="dr11 sdheading tblmcw3"><strong>' . lang('_SUBJECT') . '</strong></td>
                            <td class="dr13 HardBreak">' . utf8ToHtml($transactionContents[0]['filesubject'], true) . '</td>
                          </tr>';
                }
            }

            // Print the file message if it exists.
            if ($transactionContents[0]['filemessage'] != '') {
                $messageClass = $transactionContents[0]['filesubject'] != '' ? 'rowdivider' : '';

                echo '<tr class="' . $messageClass . '">
                        <td class="dr11 sdheading"><strong>'.lang("_MESSAGE").'</strong></td>
                        <td class="dr13" >
                            <pre class="HardBreak">'.utf8ToHtml($transactionContents[0]['filemessage'],TRUE).'</pre>
                        </td>
                      </tr>';
            }

            echo '</table><br />'; // End of summary table.

            // --------------------------------
            // CONTENTS TABLE (INDIVIDUAL FILES)
            // --------------------------------
            echo '<table style="width: 100%; padding: 0; border-collapse: collapse; border: 0;">
                    <tr>
                      <td colspan="3" class="dr9 headerrow">' . lang("_CONTENTS") . '</td>
                    </tr>
                    <tr>
                      <td class="dr4 HardBreak" style="width: 66%; text-align: left"><strong>' . lang("_FILE_NAME") . '</strong></td>
                      <td class="HardBreak" style="width: 17%; text-align: center"><strong>' . lang("_FILE_SIZE") . '</strong></td>
                      <td class="dr6 HardBreak" style="width: 17%; text-align: center"><strong>' . lang("_DOWNLOADS") . '</strong></td>
                    </tr>';

            $rowCount = 0;

            foreach ($contentsTableData as $fileName => $data) {
                // Print file name, file size, number of downloads.
                $fileName = utf8ToHtml($fileName, true);
                $fileSize = formatBytes($data['filesize']);

                echo ($rowCount % 2 == 0) ? '<tr class="rowdivider">' : '<tr>';

                if ($rowCount == sizeof($contentsTableData) - 1) {
                    // Different border styling for the last row of the table.
                    echo '<td class="dr11">' . $fileName . '</td>
                         <td class="dr12 HardBreak" style="text-align: center">' . $fileSize . '</td>
                         <td class="dr13 HardBreak" style="text-align: center">' . $data['filenumdownloads'] . '</td>';
                } else {
                    echo '<td class="dr4">' . $fileName . '</td>
                         <td class="HardBreak" style="text-align: center">' . $fileSize . '</td>
                         <td class="dr6 HardBreak" style="text-align: center">' . $data['filenumdownloads'] . '</td>';
                }

                echo '</tr>';
                $rowCount++;
            }

            echo '</table><br />'; // End of contents table.

            // --------------------------------
            // RECIPIENTS TABLE
            // --------------------------------
            echo '<table style="width: 100%; border-spacing: 0; border: 0">
                    <tr>
                        <td colspan="5" class="dr9 headerrow">' . lang("_RECIPIENTS") . '</td>
                    </tr>
                    <tr>
                        <td class="dr4 tblmcw1" onclick="expandRecipients(&quot;' . $i . '&quot;,&quot;' . sizeOf($recipientEmails) . '&quot;)"
                            style="cursor:pointer; width:5%;">
                             <i id="showicon_' . $i . '_recipients" style="cursor:pointer;" class="fa fa-plus-circle fa-lg"></i>
                      
                   
                        </td>
                        <td class="HardBreak" style="text-align: left; width:61%;" ><strong>' . lang('_EMAIL') . '</strong></td>
                        <td class="HardBreak tblmcw3" style="text-align: center; width:24%"><strong>' . lang('_DOWNLOADS') . '</strong></td>
                        <td class="tbl1mcw1" style="cursor:pointer; width:5%;">&nbsp;</td>
                        <td class="tbl1mcw1 dr6" style="cursor:pointer; width:5%;">&nbsp;</td>
                    </tr>';

            $rowCount = 0;
            foreach ($recipients as $rec => $rowData) {
                // Display email address, number of downloads, buttons for expanding / re-sending / deleting.
                echo ($rowCount % 2 == 0) ? '<tr class="rowdivider">' : '<tr>';
                $id = $i . '_' . $rowCount . '_recipients';

                echo '<td  class="dr4 expct" style="width: 5%;" onclick="toggleDisplayRow(&quot;' . $id . '&quot;)">
                         <i id="showicon_'. $id . '" style="cursor:pointer;" class="fa fa-plus-circle fa-lg"></i>
                      
                
                      </td>

                      <td class="HardBreak" style="text-align: left;">' . $rec . '</td>
                      <td class="HardBreak" style="text-align: center;">' . $rowData['downloads'] . '</td>

                      <td class="tblmcw1" style="cursor:pointer; width:5%;">
                      <i onclick="confirmResend(&quot;' . $rowData['gid'] . '&quot;)"  style="cursor:pointer;" class="fa fa-mail-forward fa-lg" alt="" title="' . lang("_RE_SEND_EMAIL") . '"></i>
                      </td>

                      <td class="tblmcw1 dr6" style="cursor:pointer; width:5%;">
                      <i style="cursor:pointer; color: #ff0000;" title="'. lang("_DELETE_TRANSACTION") . '" onclick="confirmDeleteRecipient(&quot;' . $rowData['gid'] . '&quot;)" style="cursor:pointer;" class="fa fa-minus-circle fa-lg" title="'. lang("_DELETE_RECIPIENT").'"></i>
                      </td>
                    </tr>';

                // Print sub-table (download statistics for each recipient).
                echo '<tr class="hidden" style="display:none" id="show_'. $id .'">
                        <td class="dr4"></td>
                        <td class="" colspan="3" style="">
                          <table style="width: 100%; border-collapse: collapse; border: 0;padding: 10px;">
                            <tr>
                              <td class="dr9 headerrow" colspan="2">' . lang('_DOWNLOAD_STATISTICS') . '</td>
                            </tr>';

                $row = 0;

                foreach ($rowData['files'] as $name => $downloads) {
                    // Display file name and number of downloads for each file.
                    echo ($row % 2 == 0) ? '<tr class="rowdivider">' : '<tr>';

                    echo '<td class="HardBreak dr4" >' . utf8ToHtml($name, true) . '</td>
                          <td class="HardBreak dr6" style="text-align: center">' . $downloads . '</td>
                        </tr>';

                    $row++;
                }

                echo '<tr>
                        <td class="dr7" colspan="2"></td>
                      </tr>
                    </table>
                  </td>
                  <td class="dr6"></td>
                  </tr>'; // End of recipient row.

                $rowCount++;
            }

            echo '<tr>
                    <td colspan="5" style="text-align: center; border: 1px solid #999;">
                        <a target="_blank" style="cursor:pointer;"
                            onclick="openAddRecipient('."'".$transactionContents[0]['fileauthuseruid']."',
                            '".rawurlencode($transactionContents[0]['filefrom'])."',
                            '".rawurlencode($transactionContents[0]['filesubject'])."',
                            '".rawurlencode($transactionContents[0]['filemessage'])."',
                            '".$item['filetrackingcode']."'". ');">Click here to Add a new Recipient
                        </a>
                    </td>
                </tr>
            </table>
            <br />
            </td>
            <td class="dr6"></td>
        </tr>'; // End of hidden div.
        }
    }
    echo '<tr>
            <td class="dr7" colspan="10">
          </tr>';
}
?>
</table>

<?php
if($i == 0) {
    // No transactions were found for user.
    echo lang("_NO_FILES");
}
?>
</div>
</div>

<div style="display: none;" id="dialog-delete-recipient" title="<?php echo lang('_DELETE_RECIPIENT'); ?>">
    <p><?php echo lang('_CONFIRM_DELETE_RECIPIENT'); ?></p>
    <form id="deleteRecipForm" name="deleteRecipForm" method="post" action="#">
        <label for="informRecipient"><?php echo lang('_EMAIL_RECIPIENTS_DELETION'); ?></label>
        <input type="checkbox" name="informRecipient" id="informRecipient" style="float:left; width:20px;" />
    </form>
</div>

<div style="display: none;" id="dialog-delete-transaction" title="<?php echo lang('_DELETE_TRANSACTION'); ?>">
    <p><?php echo lang('_CONFIRM_DELETE_TRANSACTION');?></p>
    <form id="deleteTransForm" name="deleteTransForm" method="post" action="#">
        <label for="informRecipients"><?php echo lang('_EMAIL_RECIPIENTS_DELETION'); ?></label>
        <input type="checkbox" name="informRecipients" id="informRecipients" style="float:left; width:20px;" />
    </form>
</div>

<div id="dialog-resend" title="<?php echo  lang('_RE_SEND_EMAIL'); ?>">
    <p><?php echo lang('_CONFIRM_RESEND_EMAIL');?></p>
</div>

<div id="dialog-addrecipient" style="display:none" title="<?php echo lang('_NEW_RECIPIENT'); ?>">
    <form id="form1" name="form1" enctype="multipart/form-data" method="post" action="#">
        <input type="hidden" name="a" value="add" />
        <input id="trackingCode" type="hidden" name="tc" value="" />
        <table  style="width: 600px; border: 0">
            <tr>
                <td class="formfieldheading mandatory tblmcw3" id="files_to"><?php echo lang('_TO'); ?>:</td>
                <td style="text-align: center">
                    <div id="recipients_box" style="display: none"></div>
                    <input name="fileto" title="<?php echo  lang('_EMAIL_SEPARATOR_MSG'); ?>" type="text" id="fileto" size="60" onblur="addEmailRecipientBox($('#fileto').val());" />
                    <div id="fileto_msg" style="display: none" class="validation_msg"><?php echo lang('_INVALID_MISSING_EMAIL'); ?></div>
                    <div id="maxemails_msg" style="display: none" class="validation_msg"><?php echo lang('_MAXEMAILS'); ?> <?php echo $config['max_email_recipients'] ?>.</div>

                </td>
            </tr>
            <tr>
                <td class="formfieldheading mandatory" id="files_from"><?php echo lang('_FROM'); ?>:</td>
                <td>
                    <div id="filefrom"></div>
                </td>
            </tr>
            <tr>
                <td class="formfieldheading" id="files_subject"><?php echo lang('_SUBJECT'); ?>: (<?php echo lang("_OPTIONAL"); ?>)</td>
                <td><input name="filesubject" type="text" id="filesubject" size="60" /></td>
            </tr>
            <tr>
                <td class="formfieldheading" id="files_message"><?php echo lang('_MESSAGE'); ?>: (<?php echo lang("_OPTIONAL"); ?>)</td>
                <td><textarea name="filemessage" cols="57" rows="4" id="filemessage"></textarea></td>
            </tr>
            <tr>
                <td class="formfieldheading mandatory" id="files_expiry"><?php echo lang('_EXPIRY_DATE'); ?>:
                    <input type="hidden" id="fileexpirydate"
                           name="fileexpirydate"
                           value="<?php echo date(lang('datedisplayformat'), strtotime("+".$config['default_daysvalid']." day"));?>"
                        />
                </td>
                <td>
                    <input id="datepicker" name="datepicker"
                           onchange="validate_expiry()" title="<?php echo lang('_DP_dateFormat'); ?>"
                        />
                    <div id="expiry_msg" class="validation_msg" style="display: none"><?php echo lang('_INVALID_EXPIRY_DATE'); ?></div>
                </td>
            </tr>
            <tr>
                <td class="formfieldheading mandatory"></td>
                <td>
                    <div id="file_msg" class="validation_msg" style="display: none"><?php echo lang('_INVALID_FILE'); ?></div>
                    <div id="extension_msg" class="validation_msg" style="display: none"><?php echo lang('_INVALID_FILE_EXT'); ?></div>
                </td>
            </tr>
        </table>
        <input name="filevoucheruid" type="hidden" id="filevoucheruid" /><br />
        <input type="hidden" name="s-token" id="s-token" value="<?php echo (isset($_SESSION["s-token"])) ?  $_SESSION["s-token"] : "";?>" />
    </form>
</div>

<div id="dialog-autherror" title="<?php echo lang("_MESSAGE"); ?>" style="display:none"><?php echo lang('_AUTH_ERROR'); ?></div>
