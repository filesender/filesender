<?php

function config_VisString( $k, $specialv, $specialret ) {
    $v = Config::get($k);
    if($v == $specialv) {
        return $specialret;
    }
    return $v;
}
?>
<div class="box">
   
    <div id="dialog-privacy" title="Privacy">
        {tr:privacy_text}
        <h2>{tr:privacy_page_days_old_table_text}</h2>
        <table columns="2">
            <tr><th><?php echo Lang::tr('value')?></th>
                <th><?php echo Lang::tr('description')?></th>
            </tr>
            <tr><td><?php echo Config::get('max_transfer_days_valid') ?></td>
                <td><?php echo Lang::tr('privacy_page_max_transfer_days_valid')?></td>
            </tr>
            <tr><td><?php echo Config::get('clientlogs_lifetime') ?></td>
                <td><?php echo Lang::tr('privacy_page_clientlogs_lifetime')?> </td>
            </tr>
            <tr><td><?php echo Config::get('translatable_emails_lifetime') ?></td>
                <td><?php echo Lang::tr('privacy_page_translatable_emails_lifetime')?> </td>
            </tr>
            <tr><td><?php echo config_VisString('guests_expired_lifetime',-1,Lang::tr('never')) ?></td>
                <td><?php echo Lang::tr('privacy_page_guests_expired_lifetime')?> </td>
            </tr>
            <tr><td><?php echo Config::get('auditlog_lifetime') ?></td>
                <td><?php echo Lang::tr('privacy_page_auditlog_lifetime')?> </td>
            </tr>
            <tr><td><?php echo Config::get('trackingevents_lifetime') ?></td>
                <td><?php echo Lang::tr('privacy_page_trackingevents_lifetime')?> </td>
            </tr>
            <tr><td></td>
                <td><?php echo Lang::tr('privacy_page_frequent_recipients_text')?> </td>
            </tr>
            

        </table>
    </div>
    
</div>
