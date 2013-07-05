<script type="text/javascript">
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
</script>
