<script type="text/javascript">
    var recipientID = 0;
    var numRecipients = 0;
    var recipientsString = '';

    function getDatePicker() {
        var datePicker = $('#datepicker');
        datePicker.datepicker({ minDate: new Date(minimumDate), maxDate: new Date(maximumDate), altField: '#fileexpirydate', altFormat: 'd-m-yy' });
        datePicker.datepicker('option', 'dateFormat', '<?php echo lang('_DP_dateFormat'); ?>');
        datePicker.datepicker('setDate', new Date(maximumDate));
        $('#ui-datepicker-div').css('display', 'none');

        $.datepicker.setDefaults({
            closeText: '<?php echo lang('_DP_closeText'); ?>',
            prevText: '<?php echo lang('_DP_prevText'); ?>',
            nextText: '<?php echo lang('_DP_nextText'); ?>',
            currentText: '<?php echo lang('_DP_currentText'); ?>',
            monthNames: <?php echo lang('_DP_monthNames'); ?>,
            monthNamesShort: <?php echo lang('_DP_monthNamesShort'); ?>,
            dayNames: <?php echo lang('_DP_dayNames'); ?>,
            dayNamesShort: <?php echo lang('_DP_dayNamesShort'); ?>,
            dayNamesMin: <?php echo lang('_DP_dayNamesMin'); ?>,
            weekHeader: '<?php echo lang('_DP_weekHeader'); ?>',
            dateFormat: '<?php echo lang('_DP_dateFormat'); ?>',
            firstDay: <?php echo lang('_DP_firstDay'); ?>,
            isRTL: <?php echo lang('_DP_isRTL'); ?>,
            showMonthAfterYear: <?php echo lang('_DP_showMonthAfterYear'); ?>,
            yearSuffix: '<?php echo lang('_DP_yearSuffix'); ?>'
        });
    }

    function autoCompleteEmails() {
        var availableTags = [<?php  echo (isset($config["autocomplete"]) && $config["autocomplete"])?  $functions->uniqueEmailsForAutoComplete():  ""; ?>];

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

    function addButtonText(){
        $('#pauseBTN').html('<?php echo lang("_PAUSE") ?>');
        $('#cancelBTN').html('<?php echo lang("_CANCEL") ?>');
        $('#confirmBTN').html('<?php echo lang("_YES") ?>');
        $('#sendBTN').html('<?php echo lang("_SEND") ?>');
        $('#canceluploadBTN').html('<?php echo lang("_CANCEL") ?>');
        $('#okBTN').html('<?php echo lang("_OK") ?>');


        $('.ui-dialog-buttonpane button:contains(cancelBTN)').html('<?php echo lang("_CANCEL") ?>');
        $('.ui-dialog-buttonpane button:contains(confirmBTN)').html('<?php echo lang("_YES") ?>');
        $('.ui-dialog-buttonpane button:contains(sendBTN)').html('<?php echo lang("_SEND") ?>');
        $('.ui-dialog-buttonpane button:contains(pauseBTN)').html('<?php echo lang("_PAUSE") ?>');
        $('.ui-dialog-buttonpane button:contains(canceluploadBTN)').html('<?php echo lang("_CANCEL") ?>');
        $('.ui-dialog-buttonpane button:contains(okBTN)').html('<?php echo lang("_OK") ?>');
    }

    function addEmailRecipientBox(emails) {
        var emailList= emails.split(",");

        for (var i=0; i < emailList.length; i++) {
            var email = emailList[i];
            var firstPartOfEmail = email.split('@')[0];
            var currentContents = $('#recipients_box').html();

            if (currentContents.indexOf(email) == -1) {
                if (numRecipients > maxEmailRecipients) {
                    $("#maxemails_msg").show();
                    return;
                }

                var boxString = '<div id="email_' + recipientID + '" class="email_box" >' +
                    '<span class="emailEntry" title="'+email+'">' + firstPartOfEmail + '</span>' +
                    '<span id="email_delete_'+recipientID+'" title="Click here to delete this recipient" onclick="removeEmailFromBox('+recipientID+')"> x </span>' +
                '</div>';

                $('#recipients_box').append(boxString);
                $('#recipients_box').show();
                recipientID++;
                numRecipients++;
            }
        }
        $('#fileto').val("");
        validate_recipients();
    }

    function getRecipientsList() {
        recipientsString = "";
        $('.emailEntry').each(function() {
            recipientsString += $(this).attr('title') + ',';
        });

        return(recipientsString.slice(0, recipientsString.length-1));
    }

    function removeEmailFromBox(i) {
        $('#email_' + i).remove();
        numRecipients--;
    }
</script>
