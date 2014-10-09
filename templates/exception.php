<div class="exception">
    {tr:encountered_exception}
    
    <div class="message">
        <?php echo Lang::tr(Utilities::sanitizeOutput($exception->getMessage())) ?>
    </div>
    
    <?php if(method_exists($exception, 'getDetails')) { ?>
    <pre class="details"><?php echo Utilities::sanitizeOutput(print_r($exception->getDetails(), true)) ?></pre>
    <?php } ?>
    
    <div class="report">
        {tr:you_can_report_exception} : <a href="mailto:<?php echo Config::get('support_email') ?>?subject=Exception <?php echo method_exists($exception, 'getUid') ? Utilities::sanitizeOutput($exception->getUid()) : '' ?>">{tr:report_exception}</a>
    </div>
</div>
