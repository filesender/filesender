<div class="exception">
    {tr:encountered_exception}
    
    <div class="details">
        <?php echo $message ?>
    </div>
    
    <div class="report">
        {tr:you_can_report_exception} : <a href="mailto:<?php echo Config::get('support_email') ?>?subject=Exception <?php echo isset($logid) ? $logid : '' ?>">{tr:report_exception}</a>
    </div>
</div>
