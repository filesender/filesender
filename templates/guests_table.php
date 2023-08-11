<?php
    if(!isset($status)) $status = 'available';
    if(!isset($mode)) $mode = 'user';
    if(!isset($transfers) || !is_array($transfers)) $transfers = array();
?>

<table class="fs-table fs-table--responsive fs-table--selectable fs-table--striped fs-table--text-middle guests list" data-status="<?php echo $status ?>" data-mode="<?php echo $mode ?>">
    <thead>
    <tr>
        <th class="created">
            {tr:invitation_was_sent_on}
        </th>
        <th class="to">
            {tr:recipients}
        </th>
        <th class="expires">
            {tr:expiration}
        </th>
        <th class="guest_transfers">
            {tr:guest_transfers}
        </th>
        <th class="actions">
            {tr:actions}
        </th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($guests as $guest) { ?>
        <tr class="guest objectholder fs-table__row fs-table__row--clickable"
            data-id="<?php echo $guest->id ?>"
            data-expiry-extension="<?php echo $guest->expiry_date_extension ?>"
            data-errors="<?php echo count($guest->errors) ? '1' : '' ?>">

            <td class="created d-none d-lg-table-cell" data-label="Invitation was sent on">
                <?php echo Utilities::formatDate($guest->created) ?>
            </td>

            <td class="to" data-label="Recipients">
                <a href="mailto:<?php echo Template::sanitizeOutputEmail($guest->email) ?>"><?php echo Template::sanitizeOutputEmail($guest->email) ?></a>

                <?php if($guest->errors) echo '<br /><span class="errors">'.implode(', ', array_map(function($type) {
                        return Lang::tr('recipient_error_'.$type);
                    }, array_unique(array_map(function($error) {
                        return $error->type;
                    }, $guest->errors)))).'</span>' ?>
            </td>

            <td class="expires" data-rel="expires" data-label="Expiration">
                <?php echo $guest->getOption(GuestOptions::DOES_NOT_EXPIRE) ? Lang::tr('never') : Utilities::formatDate($guest->expires) ?>
            </td>

            <td class="guest_transfers" data-label="Guest transfers">
                <ul class="fs-list fs-list--inline">
                    <li>
                        0
                    </li>
                    <li>
                        <i class="fa fa-exclamation-circle"></i>
                    </li>
                </ul>
            </td>

<!--            <td class="subject">-->
<!--                --><?php //if(strlen($guest->subject) > 15) { ?>
<!--                    <span class="short">--><?php //echo Template::sanitizeOutput(mb_substr($guest->subject, 0, 15)) ?><!--</span>-->
<!--                    <span class="clickable expand">[...]</span>-->
<!--                    <div class="full">--><?php //echo Template::sanitizeOutput($guest->subject) ?><!--</div>-->
<!--                --><?php //} else echo Template::sanitizeOutput($guest->subject) ?>
<!--            </td>-->
<!---->
<!--            <td class="message  d-none d-lg-table-cell">-->
<!--                --><?php //if(strlen($guest->message) > 15) { ?>
<!--                    <span class="short">--><?php //echo Template::sanitizeOutput(mb_substr($guest->message, 0, 15)) ?><!--</span>-->
<!--                    <span class="clickable expand">[...]</span>-->
<!--                    <div class="full">--><?php //echo Template::sanitizeOutput($guest->message) ?><!--</div>-->
<!--                --><?php //} else echo Template::sanitizeOutput($guest->message) ?>
<!--            </td>-->

            <td class="actions fs-table__actions" data-label="Actions">
                <div class="actionsblock">
                    <?php if( $mode == 'user' ) { ?>
                        <button type="button" class="fs-button fs-button--circle fs-button--no-text remind" title="Send a reminder">
                            <i class="fa fa-mail-forward"></i>
                        </button>
                        <button type="button" class="fs-button fs-button--circle fs-button--no-text forward" title="Resend invitation">
                            <i class="fa fa-repeat"></i>
                        </button>
                    <?php } ?>

                    <button type="button" class="fs-button fs-button--circle fs-button--no-text fs-button--danger delete" title="Delete invitation">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    <?php } ?>

    <?php if(!count($guests)) { ?>
        <tr>
            <td colspan="7" data-label="Results">{tr:no_guests}</td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<!--<div class="fs-paginator fs-paginator--right">-->
<!--    <a class="fs-link fs-link--circle" href="">-->
<!--        <i class="fa fa-angle-double-left"></i>-->
<!--    </a>-->
<!--    <a class="fs-link fs-link--circle" href="">-->
<!--        <i class="fa fa-angle-left"></i>-->
<!--    </a>-->
<!--    <a class="fs-link fs-link--circle" href="">-->
<!--        <i class="fa fa-angle-right"></i>-->
<!--    </a>-->
<!--</div>-->

<script type="text/javascript" src="{path:js/guests_table.js}"></script>
