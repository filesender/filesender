<?php

function config_VisString( $k, $specialv, $specialret ) {
    $v = Config::get($k);
    if($v == $specialv) {
        return $specialret;
    }
    return $v;
}
?>

<div id="dialog-privacy" title="Privacy" class="fs-base-page">
    <div class="container">
        <div class="row">
            <div class="col">
                <?php
                    if (Auth::isAuthenticated()) {
                ?>
                    <div class="row">
                        <div class="col">
                            <a id='fs-back-link' class='fs-link fs-link--primary fs-link--no-hover fs-back-link'>
                                <i class='fi fi-chevron-left'></i>
                                <span>{tr:back_to_settings}</span>
                            </a>
                        </div>
                    </div>
                <?php
                    }
                ?>

                <div class="row">
                    <div class="col">
                        <div class="fs-base-page__header mt-5">
                            <h1>{tr:privacy_title}</h1>
                        </div>
                    </div>
                </div>

                <div class="fs-base-page__content">
                    {tr:privacy_text}
                    <br/>
                    <strong>{tr:privacy_page_days_old_table_text}</strong>
                    <table class="fs-table fs-table--thin" columns="2">
                        <tbody>
                        <tr>
                            <th><?php echo Lang::tr('value')?></th>
                            <th><?php echo Lang::tr('description')?></th>
                        </tr>
                        <tr>
                            <td><?php echo Config::get('max_transfer_days_valid') ?></td>
                            <td><?php echo Lang::tr('privacy_page_max_transfer_days_valid')?></td>
                        </tr>
                        <tr>
                            <td><?php echo Config::get('clientlogs_lifetime') ?></td>
                            <td><?php echo Lang::tr('privacy_page_clientlogs_lifetime')?> </td>
                        </tr>
                        <tr>
                            <td><?php echo Config::get('translatable_emails_lifetime') ?></td>
                            <td><?php echo Lang::tr('privacy_page_translatable_emails_lifetime')?> </td>
                        </tr>
                        <tr>
                            <td><?php echo config_VisString('guests_expired_lifetime',-1,Lang::tr('never')) ?></td>
                            <td><?php echo Lang::tr('privacy_page_guests_expired_lifetime')?> </td>
                        </tr>
                        <tr>
                            <td><?php echo Config::get('auditlog_lifetime') ?></td>
                            <td><?php echo Lang::tr('privacy_page_auditlog_lifetime')?> </td>
                        </tr>
                        <tr>
                            <td><?php echo Config::get('trackingevents_lifetime') ?></td>
                            <td><?php echo Lang::tr('privacy_page_trackingevents_lifetime')?> </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><?php echo Lang::tr('privacy_page_frequent_recipients_text')?> </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


