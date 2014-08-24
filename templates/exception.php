<div class="exception">
    {tr:encountered_exception}
    
    <div class="details">
        <?php echo htmlentities($message) ?>
    </div>
    
    <div class="report">
        {tr:you_can_report_exception} : <a href="mailto:<?php echo Config::get('support_email') ?>?subject=Exception <?php echo isset($logid) ? htmlentities($logid) : '' ?>">{tr:report_exception}</a>
    </div>
</div>
