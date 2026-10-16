<?php

function config_VisString( $k, $specialv, $specialret ) {
    $v = Config::get($k);
    if($v == $specialv) {
        return $specialret;
    }
    return $v;
}
?>

<div class="fs-base-page">
    <div class="container">
        <div class="row">
            <div class="col">
                <?php if (Auth::isAuthenticated()) { ?>
                    <a id="fs-back-link" class="fs-link fs-link--primary fs-link--no-hover fs-back-link fs-base-page__back">
                        <i class="fi fi-chevron-left"></i>
                        <span>{tr:back_to_settings}</span>
                    </a>
                <?php } ?>

                <div class="fs-base-page__header">
                    <h1>{tr:privacy_title}</h1>
                </div>

                <div class="fs-base-page__content">
                    {tr:privacy_text}
                    <br/>
                    <strong>{tr:privacy_page_days_old_table_text}</strong>
                    <table class="fs-table fs-table--responsive fs-table--thin" columns="2">
                        <thead>
                        <tr>
                            <th><?php echo Lang::tr('value')?></th>
                            <th><?php echo Lang::tr('description')?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td data-label="{tr:value}"><?php echo Config::get('max_transfer_days_valid') ?></td>
                            <td data-label="{tr:description}"><?php echo Lang::tr('privacy_page_max_transfer_days_valid')?></td>
                        </tr>
                        <tr>
                            <td data-label="{tr:value}"><?php echo Config::get('clientlogs_lifetime') ?></td>
                            <td data-label="{tr:description}"><?php echo Lang::tr('privacy_page_clientlogs_lifetime')?> </td>
                        </tr>
                        <tr>
                            <td data-label="{tr:value}"><?php echo Config::get('translatable_emails_lifetime') ?></td>
                            <td data-label="{tr:description}"><?php echo Lang::tr('privacy_page_translatable_emails_lifetime')?> </td>
                        </tr>
                        <tr>
                            <td data-label="{tr:value}"><?php echo config_VisString('guests_expired_lifetime',-1,Lang::tr('never')) ?></td>
                            <td data-label="{tr:description}"><?php echo Lang::tr('privacy_page_guests_expired_lifetime')?> </td>
                        </tr>
                        <tr>
                            <td data-label="{tr:value}"><?php echo Config::get('auditlog_lifetime') ?></td>
                            <td data-label="{tr:description}"><?php echo Lang::tr('privacy_page_auditlog_lifetime')?> </td>
                        </tr>
                        <tr>
                            <td data-label="{tr:value}"><?php echo Config::get('trackingevents_lifetime') ?></td>
                            <td data-label="{tr:description}"><?php echo Lang::tr('privacy_page_trackingevents_lifetime')?> </td>
                        </tr>
                        <tr>
                            <td data-label="{tr:value}"></td>
                            <td data-label="{tr:description}"><?php echo Lang::tr('privacy_page_frequent_recipients_text')?> </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


