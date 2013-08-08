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

// Check for delete/resent/added actions and report back

$statusMsg = '';
$statusClass = '';

if(isset($_REQUEST['tc']) && isset($_REQUEST['fileauth'])){
    if($_REQUEST['a'] == "add") {
        if(isset($_REQUEST['fileto'])) {
            $listOfEmails = explode(",", $_REQUEST['fileto']);
            if($functions->addRecipientsToTransaction($listOfEmails, $_REQUEST['tc'], $_REQUEST['fileauth'], $_REQUEST['filesubject'], $_REQUEST['filemessage'])) {
                $statusMsg = lang("_EMAIL_SENT");
                $statusClass = 'green';
            } else {
                $statusMsg = lang('_ERROR_SENDING_EMAIL');
                $statusClass = 'red';
            }
        }
    } else if ($_REQUEST['a'] == 'deltrans'){
        $requestConfirmation = $_REQUEST['notifyRecipients'] == 'true' ? true : false;
        if($functions->deleteTransaction($_REQUEST['tc'], $_REQUEST['fileauth'], $requestConfirmation)) {
            $statusMsg = lang('_TRANSACTION_DELETED');
            $statusClass = 'green';
        } else {
            $statusMsg = lang('_ERROR_DELETING_RECIPIENT');
            $statusClass = 'red';
        }
    }
}

if(isset($_REQUEST["a"]) && isset($_REQUEST["groupid"])) {
    $recipient = $_REQUEST["groupid"];
    if ($_REQUEST["a"] == "delrecip") { // TODO: Needs some sort of authentication before deletion
        $requestConfirmation = $_REQUEST['notifyRecipient'] == 'true' ? true : false;
        if ($functions->deleteRecipient($recipient, $requestConfirmation)) {
            $statusMsg = lang("_RECIPIENT_DELETED");
            $statusClass = 'green';
        } else {
            $statusMsg = lang("_PERMISSION_DENIED");
            $statusClass = "red";
        }
    } else if ($_REQUEST["a"] == "resend") {
        if ($sendmail->sendDownloadAvailable($recipient)){
            $statusMsg = lang("_EMAIL_SENT");
            $statusClass = 'green';
        } else {
            $statusMsg = lang("_PERMISSION_DENIED");
            $statusClass = "red";
        }
    }
}

foreach ($errorArray as $message) {
    if($message == "err_emailnotsent") {
        $statusMsg = lang('_ERROR_SENDING_EMAIL');
        $statusClass = 'red';
    }
}
// Get list of user files and display page
$filedata = $functions->getUserTrackingCodes();
$json_o=json_decode($filedata,true);

?>
<script type="text/javascript">
//<![CDATA[
	var selectedFile = ""; // file uid selected when deleteting
	// set default maximum date for date datepicker
	var maximumDate = <?php echo (time()+($config['default_daysvalid']*86400))*1000 ?>;
	var minimumDate = <?php echo (time()+86400)*1000 ?>;
	var maxEmailRecipients = <?php echo $config['max_email_recipients'] ?>;
	var datepickerDateFormat = '<?php echo lang('_DP_dateFormat'); ?>';
	var showall = false; // flag for show all switch to show or hide download summaries
    var showAllRecipients = false; // flag for expanding all download summaries for individual recipients
    var statusMsg = '<?php echo $statusMsg ?>';
    var statusClass = '<?php echo $statusClass ?>';
    var selectedRecipient = "";

	$(function() {

        statusMessage(statusMsg, statusClass);

        getDatePicker();

		// stripe every second row in the tables
        //$(".rowdetails").addClass("rowdivider");

		// delete modal dialog box

		// resend email modal dialog box
		$("#dialog-resend").dialog({ autoOpen: false, height: 180, modal: true,
			buttons: {
				'cancelsendBTN': function() {
				    $( this ).dialog( "close" );
				},
				'sendBTN': function() {
				    $( this ).dialog( "close" );
				    resend();
				}
			}
		});

		// default auth error dialogue
		$("#dialog-autherror").dialog({ autoOpen: false, height: 240,width: 350, modal: true,title: "",
		buttons: {
			'<?php echo lang("_OK") ?>': function() {
				location.reload(true);
				}
			}
		});

		$('.ui-dialog-buttonpane button:contains(cancelBTN)').attr("id","btn_cancel");
		$('#btn_cancel').html('<?php echo lang("_NO") ?>');
		$('.ui-dialog-buttonpane button:contains(deleteBTN)').attr("id","btn_delete");
		$('#btn_delete').html('<?php echo lang("_YES") ?>');
		$('.ui-dialog-buttonpane button:contains(cancelsendBTN)').attr("id","btn_cancelsend");
		$('#btn_cancelsend').html('<?php echo lang("_NO") ?>');
		$('.ui-dialog-buttonpane button:contains(sendBTN)').attr("id","btn_send");
		$('#btn_send').html('<?php echo lang("_YES") ?>');
		// add new recipient modal dialog box
		$("#dialog-addrecipient").dialog({ autoOpen: false, height: 410,width:650, modal: true,
			buttons: {
				'addrecipientcancelBTN': function() {
					// clear form
					$("#fileto").val("");
					$("#datepicker").datepicker("setDate", new Date(maximumDate));
					$("#filesubject").val("");
					$("#filemessage").val("");
					$( this ).dialog( "close" );
				},
				'addrecipientsendBTN': function() {
					// Disable the send button to prevent duplicate sending
                    if (validateForm()) {
                        $("#form1").submit();
                        $('#btn_addrecipientsend').attr("disabled", true);
                    }

				}
			}
		});

		$('.ui-dialog-buttonpane button:contains(addrecipientcancelBTN)').attr("id","btn_addrecipientcancel");
		$('#btn_addrecipientcancel').html('<?php echo lang("_CANCEL") ?>')
		$('.ui-dialog-buttonpane button:contains(addrecipientsendBTN)').attr("id","btn_addrecipientsend");
		$('#btn_addrecipientsend').html('<?php echo lang("_SEND") ?>')

    // autocomplete
	var availableTags = [<?php  echo (isset($config["autocomplete"]) && $config["autocomplete"])?  $functions->uniqueemailsforautocomplete():  ""; ?>];

		function split( val ) {
            return val.split( /,\s*/ );
        }

        function extractLast( term ) {
            return split( term ).pop();
        }

		$( "#fileto" )
            // don't navigate away from the field on tab when selecting an item
            .bind( "keydown", function( event ) {
                if ( event.keyCode === $.ui.keyCode.TAB &&
                        $( this ).data( "uiAutocomplete" ).menu.active ) {
                    event.preventDefault();
                }
            })
            .autocomplete({
                minLength: 0,
                source: function( request, response ) {
                    // delegate back to autocomplete, but extract the last term
                    response( $.ui.autocomplete.filter(
                        availableTags, extractLast( request.term ) ) );
                },
                focus: function() {
                    // prevent value inserted on focus
                    return false;
                },
                select: function( event, ui ) {
                    var terms = split( this.value );
                    // remove the current input
                    terms.pop();
                    // add the selected item
                    terms.push( ui.item.value );
                    // add placeholder to get the comma-and-space at the end
                    terms.push( "" );
                    this.value = terms;//.join( ", " );
                    return false;
                }
            });
            // End autocomplete

        // End document ready
	});

	// validate form before sending
	function validateForm()
	{
		// remove previous validation messages
		$("#fileto_msg").hide();
		$("#expiry_msg").hide();
		var validate = true;
		if(!validate_fileto() || !validate_expiry()) validate = false;		// validate emails && date
		return validate;
	}

	function resend(uid)
	{
		window.location.href="index.php?s=files&a=resend&groupid=" + selectedRecipient;
	}

	function confirmResend(vid)
    {
        // confirm deletion of selected file
        selectedRecipient = vid;
        $("#dialog-resend" ).dialog( "open" );
    }

	function confirmDeleteRecipient(gid)
    {
        $("#dialog-delete-recipient").dialog({ autoOpen: false, height: 200, width: 400, modal: true,
            buttons: {
                'Cancel': function() {
                    $( this ).dialog( "close" );
                },
                'Confirm': function() {
                    var checked = $('#informRecipient').is(':checked');
                    window.location.href="index.php?s=files&a=delrecip&groupid=" + gid + "&notifyRecipient=" + checked;
                }
            }
        });
        $("#dialog-delete-recipient").dialog("open");
    }

    function confirmDeleteTransaction(trackingCode, fileauth)
    {
        $("#dialog-delete-transaction").dialog({ autoOpen: false, height: 200, width: 400, modal: true,
            buttons: {
                'Cancel': function() {
                    $(this).dialog("close");
                },
                'Confirm': function() {
                    var checked = $('#informRecipients').is(':checked');
                    window.location.href="index.php?s=files&a=deltrans&fileauth="+fileauth + "&tc=" + trackingCode + "&notifyRecipients=" + checked;
                }
            }
        });
        $("#dialog-delete-transaction").dialog("open");
    }

	function openAddRecipient(fileauth,filename,filesize,from, subject, message, trackingCode)
	{// populate form and open add-recipient modal form
		$("#form1").attr("action", "index.php?s=files&a=add&fileauth=" + fileauth  + "&tc=" + trackingCode);
        $("#trackingCode").val(trackingCode);
		$("#filefrom").html(decodeURIComponent(from));
		$("#filename").html(decodeURIComponent(filename));
		$("#filesubject").val(decodeURIComponent(subject));
		$("#filemessage").val(decodeURIComponent(message));
		$("#filesize").html(readablizebytes(filesize));
		$("#fileto").val("");
		$("#datepicker").datepicker("setDate", new Date(maximumDate));
		// clear error messages
		$("#expiry_msg").hide();
		$("#file_msg").hide();
		$("#fileto_msg").hide();
		$("#maxemails_msg").hide();
		$("#dialog-addrecipient" ).dialog( "open" );
	}

    function expandRecipients(i, j)
    {
        var num = 0;
        var elemDisplay;
        if(!showAllRecipients) {
            for (num; num < j; num++) {
                elemDisplay = $('#show_'+i+'_'+num+'_recipients').css('display');
                if (elemDisplay  == 'none') {
                    show(i+"_"+num+"_recipients");
                }
            }
            $('#showicon_'+i+'_recipients').attr({src: 'images/openboth2.png', alt: ''});
            showAllRecipients = true;
        } else {
            for (num; num < j; num++) {
                elemDisplay = $('#show_'+i+'_'+num+'_recipients').css('display');
                if (elemDisplay  == 'table-row') {
                    show(i+"_"+num+"_recipients");
                }
            }
            $('#showicon_'+i+'_recipients').attr({src: 'images/openboth.png', alt: ''});
            showAllRecipients = false;
        }
    }

    function expandTopMenu(numRows)
    {
        var row = 0;
        var elemDisplay;
        if (!showall) {
            for (row; row<numRows; row++) {
                elemDisplay = $('#show_'+row).css('display');
                if (elemDisplay == 'none') {
                    show(row);
                }
            }
            $('#showicon_top').attr({src: 'images/openboth2.png', alt: ''});
            showall = true;
        } else {
            for (row; row<numRows; row++) {
                elemDisplay = $('#show_'+row).css('display');
                if (elemDisplay == 'table-row') {
                    show(row);
                }
            }
            $('#showicon_top').attr({src: 'images/openboth.png', alt: ''});
            showall = false;
        }
    }

	function show(i)
	{
        var show = $('#show_'+i);
        show.toggle();
        var $icon = $("#showicon_"+i);

        if (show.is(':visible')) {
            $icon.attr({src: "images/openboth2.png", alt: ""});
        } else {
            $icon.attr({src: "images/openboth.png", alt: ""});
        }
	}
//]]>
</script>

<div id="box">
    <?php echo '<div id="pageheading">'.lang("_MY_FILES").'</div>'; ?>
    <div id="tablediv">
        <table id="myfiles" style="table-layout:fixed; width: 100%; padding: 4px; border-spacing: 0; border: 0">
            <tr class="headerrow">
                <td class="tblmcw1" onclick="expandTopMenu(<?php echo sizeof($json_o); ?>)" title="<?php echo lang("_SHOW_ALL"); ?>" style="cursor:pointer">
                    <img class="expct" id="showicon_top" src="images/openboth.png" alt=""/>
                </td>

                <td class="HardBreak" colspan="4" style="text-align: center" id="myfiles_header_to"><strong><?php echo lang("_TO"); ?></strong></td>
                <td class="HardBreak tblmcw3" style="text-align: center" id="myfiles_header_size"><strong><?php echo lang("_TOTAL_SIZE") ?></strong></td>
                <td class="HardBreak tblmcw3" id="myfiles_header_downloaded" style="text-align: center"
                    title="# <?php echo lang("_DOWNLOADS"); ?>"><strong><?php echo lang("_DOWNLOADS"); ?></strong>
                </td>
                <td class="HardBreak tblmcw3" id="myfiles_header_expiry" style="text-align: center"><strong><?php echo lang("_EXPIRY"); ?></strong></td>
                <td class="HardBreak" id="myfiles_header_filename" style="text-align: center"><strong><?php echo lang("_TRACKING_CODE") ?></strong></td>
                <td class="tblmcw1 HardBreak"></td>
            </tr>
            <?php
            $i = 0;
            if(sizeof($json_o) > 0)
            {
                foreach($json_o as $item) {
                    $i += 1; // counter for file id's
                    // alternating rows
                    $rowClass = ($i % 2 != 0)? "class='altcolor'":"";


                    $itemContents = $functions->getTransactionDetails($item['filetrackingcode'], $item['fileauthuseruid']);

                    // skips closed entries
                    if (empty($itemContents)) {
                        continue;
                    }
                    $onClick = "'" . $itemContents[0]['filevoucheruid'] . "'";
                    $recipientsArray = $functions->getMultiRecipientDetails($item['filetrackingcode'], $item['fileauthuseruid']);
                    $numExtraRecipients = sizeof($recipientsArray)-1;
                    $fileToString = $numExtraRecipients == 0 ? $itemContents[0]['fileto']  : $itemContents[0]['fileto'] . ' ... (' . $numExtraRecipients . ')';
                    $recipientsString = '';

                    for ($temp = 0; $temp < sizeOf($recipientsArray); $temp++){
                        $recipientsString .= $recipientsArray[$temp]['fileto'];
                        if ($temp != sizeOf($recipientsArray)-1) $recipientsString .= ', ';
                    }

                    $maxDownloaded = 0;
                    $totalDownloaded = 0;
                    $totalSize = 0;

                    $downloadTotals = $functions->getFileDownloadTotals($item['filetrackingcode'], $item['fileauthuseruid']);

                    for ($download = 0; $download < sizeof($downloadTotals); $download++) {
                        $totalDownloaded += $downloadTotals[$download]['count'];
                    }


                    for($temp = 0; $temp < sizeOf($itemContents); $temp++){
                        if($itemContents[$temp]['downloads'] > $maxDownloaded){
                            $maxDownloaded = $itemContents[$temp]['downloads'];
                        }
                        $totalSize += $itemContents[$temp]['filesize'];
                    }

                    if (isset($itemContents[0]) && $itemContents[0]['filestatus'] == 'Available') {
                        echo '<tr><td class="dr7" colspan="10"></tr>
                        <tr  '.$rowClass.'>
                            <td  class="dr1 expct" onclick="show('.$i.')">
                                <img class="expct" id="showicon_'.$i.'"  style="cursor:pointer"
                                    title="'. lang("_SHOW_ALL").'" src="images/openboth.png" alt=""/>
                            </td>

                            <td colspan="4" class="dr2 HardBreak" title="'. $recipientsString . '">' . $fileToString . '</td>
                            <td class="dr2 HardBreak" style="text-align: center">' .formatBytes($totalSize). '</td>
                            <td class="dr2 HardBreak" style="text-align: center">' . $totalDownloaded . '</td>
                            <td class="dr2" style="text-align: center">
                                ' .date(lang('datedisplayformat'),strtotime($itemContents[0]['fileexpirydate'])) . '
                            </td>
                            <td class="dr2" style="text-align: center">' . utf8tohtml($itemContents[0]['filetrackingcode'],TRUE) . '</td>
                            <td class="dr8">
                                <img src="images/shape_square_delete.png" alt="" title="'. lang("_DELETE_RECIPIENT") . '"
                                    style="cursor:pointer;"  onclick="confirmDeleteTransaction(&quot;'.$itemContents[0]['filetrackingcode'].'&quot;,&quot;'.$item['fileauthuseruid'].'&quot;)"
                                />
                            </td>
                        </tr>
                        <tr class="hidden" style="display:none" id="show_'.$i.'">
                            <td class="dr4"></td>
                            <td colspan="8">';

                            /* SUMMARY TABLE */

                            $hasMessage = $itemContents[0]['filemessage'] != "";
                            $hasSubject = $itemContents[0]['filesubject'] != "";

                            echo '<table style="width: 100%; padding: 0; border-collapse: collapse; border: 0;" class="rowdetails">
                                <tr>
                                    <td colspan="2" class="dr9 headerrow">' .lang('_DETAILS'). '</td>
                                </tr>
                                <tr class="rowdivider">
                                    <td class="dr4 sdheading tblmcw3 "><strong>' .lang("_CREATED"). '</strong></td>
                                    <td class="dr6 HardBreak">' .date(lang('datedisplayformat'),strtotime($itemContents[0]['filecreateddate'])). '</td>
                                </tr>
                                <tr>
                                    <td class="dr4 sdheading"><strong>'.lang("_FROM").'</strong></td>
                                    <td class="dr6 HardBreak">' .$itemContents[0]['filefrom'] . '</td>
                                </tr>
                                <tr class="rowdivider">';
                                    if ($hasMessage || $hasSubject) {
                                        echo '<td class="dr4 sdheading tblmcw3"><strong>'.lang("_TO").'</strong></td>
                                        <td class="dr6 HardBreak">'.$recipientsString.'</td></tr>';
                                    } else {
                                        echo '<td class="dr11 sdheading tblmcw3"><strong>'.lang("_TO").'</strong></td>
                                        <td class="dr13">' . $recipientsString . '</td></tr>';
                                    }


                                if ($itemContents[0]['filesubject'] != "") {
                                    if ($hasMessage) {
                                        echo '<tr>
                                            <td class="dr4 sdheading tblmcw3"><strong>'.lang("_SUBJECT").'</strong></td>
                                            <td class="dr6 HardBreak">'.utf8tohtml($itemContents[0]['filesubject'],TRUE). '</td>
                                        </tr>';
                                    } else {
                                        echo '<tr>
                                            <td class="dr11 sdheading tblmcw3"><strong>'.lang("_SUBJECT").'</strong></td>
                                            <td class="dr13 HardBreak">'.utf8tohtml($itemContents[0]['filesubject'],TRUE). '</td>
                                        </tr>';
                                    }
                                }

                                if ($itemContents[0]['filemessage'] != "") {
                                    $itemContents[0]['filesubject'] != "" ? $messageClass = "rowdivider" : $messageClass = "";
                                    echo '<tr class="' . $messageClass . '">
                                        <td class="dr11 sdheading"><strong>'.lang("_MESSAGE").'</strong></td>
                                        <td class="dr13" >
                                            <pre class="HardBreak">'.utf8tohtml($itemContents[0]['filemessage'],TRUE).'</pre>
                                        </td>
                                    </tr>';
                                }

                            echo '</table>
                            <br />';

                            /* INDIVIDUAL FILES TABLE */

                            echo '<table style="width: 100%; padding: 0; border-collapse: collapse; border: 0;">
                                <tr>
                                    <td colspan="3" class="dr9 headerrow">' . lang("_CONTENTS") . '</td>
                                </tr>
                                <tr>
                                    <td class="dr4 HardBreak" style="width: 66%; text-align: left"><strong>' . lang("_FILE_NAME") . '</strong></td>
                                    <td class="HardBreak" style="width: 17%; text-align: center"><strong>' . lang("_FILE_SIZE") . '</strong></td>
                                    <td class="dr6 HardBreak" style="width: 17%; text-align: center"><strong>' . lang("_DOWNLOADS") . '</strong></td>
                                </tr>';

                                for($file = 0; $file < sizeof($itemContents); $file++) {
                                    $row = $file % 2 == 0 ? '<tr class="rowdivider">' : '<tr>';

                                    if (!isset($downloadTotals[$file]) || $downloadTotals[$file]['logfilename'] != $itemContents[$file]['fileoriginalname']) {
                                        array_splice($downloadTotals, $file, 0, array(array('count' => 0, 'logfilename' => $itemContents[$file]['fileoriginalname'])));
                                    }

                                    $numDownloaded = $downloadTotals[$file]['count'];

                                    if ($file == sizeof($itemContents)-1){
                                        echo $row .
                                            '<td class="dr11">' . utf8tohtml($itemContents[$file]['fileoriginalname'],true) . '</td>
                                            <td class="dr12 HardBreak" style="text-align: center">' . formatBytes($itemContents[$file]['filesize']) . '</td>
                                            <td class="dr13 HardBreak" style="text-align: center">' . $downloadTotals[$file]['count'] . '</td>
                                        </tr>';
                                    } else {
                                        echo $row .
                                            '<td class="dr4">' . utf8tohtml($itemContents[$file]['fileoriginalname'], true) . '</td>
                                            <td class="HardBreak" style="text-align: center">' . formatBytes($itemContents[$file]['filesize']) . '</td>
                                            <td class="dr6 HardBreak" style="text-align: center">' . $downloadTotals[$file]['count'] . '</td>
                                        </tr>';
                                    }
                                }
                            echo '</table>
                            <br />';

                            /* RECIPIENTS TABLE */

                            echo '<table style="width: 100%; border-spacing: 0; border: 0">
                                <tr>
                                    <td colspan="5" class="dr9 headerrow">' . lang("_RECIPIENTS") . '</td>
                                </tr>
                                <tr>
                                    <td class="dr4 tblmcw1"
                                        onclick="expandRecipients(&quot;'.$i.'&quot;,&quot;'.sizeOf($recipientsArray).'&quot;)"
                                        style="cursor:pointer; width:5%;">
                                            <img class="expct" id="showicon_'.$i.'_recipients" src="images/openboth.png" alt="" draggable="false" />
                                    </td>
                                    <td class="HardBreak" style="text-align: left; width:61%;" ><strong>Email</strong></td>
                                    <td class="HardBreak tblmcw3" style="text-align: center; width:24%"><strong>'. lang("_DOWNLOADS").'</strong></td>
                                    <td class="tbl1mcw1" style="cursor:pointer; width:5%;">&nbsp;</td>
                                    <td class="tbl1mcw1 dr6" style="cursor:pointer; width:5%;">&nbsp;</td>
                                </tr>';

                            /* Individual recipients information */

                            for ($temp = 0; $temp < sizeOf($recipientsArray); $temp++) {
                                $row = $temp % 2 != 0 ? '<tr class="altcolor">' : '<tr>';

                                $files = $functions->getTransactionDownloadsForRecipient($recipientsArray[$temp]['fileto'], $item['filetrackingcode'], $item['fileauthuseruid']);

                                $totalDownloadedIndividual = 0;
                                $fileNames = "";
                                for($file = 0; $file < sizeOf($files); $file++){
                                    $totalDownloadedIndividual += $files[$file]['downloads'];
                                    $fileNames .= $files[$file]['fileoriginalname'] . "<br />";
                                }

                                echo $row .
                                    '<td  class="dr4 expct" style="width: 5%;" onclick="show(&quot;'.$i.'_'.$temp.'_recipients&quot;)">
                                        <img class="expct" id="showicon_'.$i.'_'.$temp.'_recipients"
                                            style="cursor:pointer" src="images/openboth.png"  alt=""/>
                                    </td>
                                    <td class="HardBreak" style="text-align: left;">' . $recipientsArray[$temp]['fileto'] . '</td>
                                    <td class="HardBreak" style="text-align: center;">' . $totalDownloadedIndividual . '</td>
                                    <td class="tblmcw1" style="cursor:pointer; width:5%;">
                                        <img src="images/email_go.png" alt="" title="'.lang("_RE_SEND_EMAIL").'"
                                            style="cursor:pointer;"  onclick="confirmResend(&quot;'.$recipientsArray[$temp]['filegroupid'].'&quot;)" />
                                    </td>
                                    <td class="tblmcw1 dr6" style="cursor:pointer; width:5%;">
                                        <img src="images/shape_square_delete.png" alt="" title="'.lang("_DELETE_RECIPIENT").'"
                                            style="cursor:pointer;"  onclick="confirmDeleteRecipient(&quot;'.$recipientsArray[$temp]['filegroupid'].'&quot;)" />
                                    </td>
                                </tr>
                                <tr class="hidden" style="display:none" id="show_'.$i.'_'.$temp.'_recipients">
                                    <td class="dr4"></td>
                                    <td class="" colspan="3" style="">
                                        <table style="width: 100%; border-collapse: collapse; border: 0;padding: 10px;">
                                            <tr>
                                                <td class="dr9 headerrow" colspan="2">Download Statistics</td>
                                            </tr>'; // Needs a Lang[] for contents;

                                            for($file = 0; $file < sizeof($files); $file++) {
                                                $row = $file % 2 == 0 ? '<tr class="rowdivider">' : '<tr>';
                                                echo $row . '
                                                    <td class="HardBreak dr4" >' . utf8tohtml($files[$file]['fileoriginalname'], true) . '</td>
                                                    <td class="HardBreak dr6" style="text-align: center">' . $files[$file]['downloads'] . '</td>
                                                </tr>';
                                            }
                                            echo '<tr>
                                                <td class="dr7" colspan="2"></td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td class="dr6"></td>
                                </tr>';
                            }

                                echo '<tr>
                                    <td colspan="5" style="text-align: center; border: 1px solid #999;">
                                        <a target="_blank" style="cursor:pointer;"
                                            onclick="openAddRecipient('."'".$itemContents[0]['fileauthuseruid']."',
                                            '". utf8tohtml(addslashes($fileNames), true) ."',
                                            '".$itemContents[0]['filesize'] ."','".rawurlencode($itemContents[0]['filefrom'])."',
                                            '".rawurlencode($itemContents[0]['filesubject'])."',
                                            '".rawurlencode($itemContents[0]['filemessage'])."',
                                            '".$item['filetrackingcode']."'". ');">Click here to Add a new Recipient
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <br />
                            </td>
                            <td class="dr6"></td>
                        </tr>'; // End of hidden div
                        }
                }
                echo '<tr>
                    <td class="dr7" colspan="10">
                </tr>';
            }
            ?>
        </table>
        <?php
        if($i==0) {
            echo lang("_NO_FILES");
        }
        ?>
    </div>
</div>

<div style="display: none;" id="dialog-delete-recipient" title="<?php echo lang("_DELETE_RECIPIENT"); ?>">
    <p><?php echo lang("_CONFIRM_DELETE_RECIPIENT");?></p>
    <form id="deleteRecipForm" name="deleteRecipForm" method="post" action="#">
        <label for="informRecipient">Email recipient with confirmation of deletion</label>
        <input type="checkbox" name="informRecipient" id="informRecipient" style="float:left; width:20px;" />
    </form>
</div>

<div style="display: none;" id="dialog-delete-transaction" title="<?php echo lang("_DELETE_TRANSACTION"); ?>">
    <p><?php echo lang("_CONFIRM_DELETE_TRANSACTION");?></p>
    <form id="deleteTransForm" name="deleteTransForm" method="post" action="#">
        <label for="informRecipients">Email recipients with confirmation of deletion</label>
        <input type="checkbox" name="informRecipients" id="informRecipients" style="float:left; width:20px;" />
    </form>
</div>

<div id="dialog-resend" title="<?php echo  lang("_RE_SEND_EMAIL"); ?>">
    <p><?php echo lang("_CONFIRM_RESEND_EMAIL");?></p>
</div>

<div id="dialog-addrecipient" style="display:none" title="<?php echo  lang("_NEW_RECIPIENT"); ?>">
    <form id="form1" name="form1" enctype="multipart/form-data" method="post" action="#">
        <input type="hidden" name="a" value="add" />
        <input id="trackingCode" type="hidden" name="tc" value="" />
        <table  style="width: 600px; border: 0">
            <tr>
                <td class="formfieldheading mandatory tblmcw3" id="files_to"><?php echo  lang("_TO"); ?>:</td>
                <td style="text-align: center">
                    <input name="fileto" title="<?php echo  lang("_EMAIL_SEPARATOR_MSG"); ?>" type="text" id="fileto" size="60" onblur="validate_fileto()" />
                    <div id="fileto_msg" style="display: none" class="validation_msg"><?php echo lang("_INVALID_MISSING_EMAIL"); ?></div>
                    <div id="maxemails_msg" style="display: none" class="validation_msg"><?php echo lang("_MAXEMAILS"); ?> <?php echo $config['max_email_recipients'] ?>.</div>
                </td>
            </tr>
            <tr>
                <td class="formfieldheading mandatory" id="files_from"><?php echo lang("_FROM"); ?>:</td>
                <td>
                    <div id="filefrom"></div>
                </td>
            </tr>
            <tr>
                <td class="formfieldheading" id="files_subject"><?php echo lang("_SUBJECT"); ?>: (<?php echo lang("_OPTIONAL"); ?>)</td>
                <td><input name="filesubject" type="text" id="filesubject" size="60" /></td>
            </tr>
            <tr>
                <td class="formfieldheading" id="files_message"><?php echo lang("_MESSAGE"); ?>: (<?php echo lang("_OPTIONAL"); ?>)</td>
                <td><textarea name="filemessage" cols="57" rows="4" id="filemessage"></textarea></td>
            </tr>
            <tr>
                <td class="formfieldheading mandatory" id="files_expiry"><?php echo lang("_EXPIRY_DATE"); ?>:
                    <input type="hidden" id="fileexpirydate"
                           name="fileexpirydate"
                           value="<?php echo date(lang('datedisplayformat'),strtotime("+".$config['default_daysvalid']." day"));?>"
                    />
                </td>
                <td>
                    <input id="datepicker" name="datepicker"
                           onchange="validate_expiry()" title="<?php echo lang('_DP_dateFormat'); ?>"
                    />
                    <div id="expiry_msg" class="validation_msg" style="display: none"><?php echo lang("_INVALID_EXPIRY_DATE"); ?></div>
                </td>
            </tr>
            <tr>
                <td class="formfieldheading mandatory" id="files_to_be_resent"><?php echo lang("_FILE_TO_BE_RESENT"); ?>:</td>
                <td><div id="filename"></div></td>
            </tr>
            <tr>
                <td class="formfieldheading mandatory" id="files_size"><?php echo lang("_SIZE"); ?>:</td>
                <td>
                    <div id="filesize"></div>
                </td>
            </tr>
            <tr>
                <td class="formfieldheading mandatory"></td>
                <td>
                    <div id="file_msg" class="validation_msg" style="display: none"><?php echo lang("_INVALID_FILE"); ?></div>
                    <div id="extension_msg" class="validation_msg" style="display: none"><?php echo lang("_INVALID_FILE_EXT"); ?></div>
                </td>
            </tr>
        </table>
        <input name="filevoucheruid" type="hidden" id="filevoucheruid" /><br />
	    <input type="hidden" name="s-token" id="s-token" value="<?php echo (isset($_SESSION["s-token"])) ?  $_SESSION["s-token"] : "";?>" />
  	</form>
</div>

<div id="dialog-autherror" title="<?php echo lang("_MESSAGE"); ?>" style="display:none"><?php echo lang("_AUTH_ERROR"); ?></div>
