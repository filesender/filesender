<div class="exception">
    {tr:encountered_exception}
    
    <div class="message">
        <?php echo Lang::tr(Utilities::sanitizeOutput($exception->getMessage())) ?>
    </div>
    
    <?php if(method_exists($exception, 'getDetails')) { ?>
    <pre class="details"><?php echo Utilities::sanitizeOutput(print_r($exception->getDetails(), true)) ?></pre>
    <?php } ?>
    
    <div class="report">
    <?php if(Config::get('support_email')) { ?>
        {tr:you_can_report_exception_by_email} : <a href="mailto:<?php echo Config::get('support_email') ?>?subject=Exception <?php echo method_exists($exception, 'getUid') ? Utilities::sanitizeOutput($exception->getUid()) : '' ?>">{tr:report_exception}</a>
    <?php } else if(method_exists($exception, 'getUid')) { ?>
        {tr:you_can_report_exception} : "<?php echo Utilities::sanitizeOutput($exception->getUid()) ?>"
    <?php } ?>
    </div>
</div>
