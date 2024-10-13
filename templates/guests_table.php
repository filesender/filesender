<?php
    if(!isset($status)) $status = 'available';
    if(!isset($mode)) $mode = 'user';
    if(!isset($transfers) || !is_array($transfers)) $transfers = array();
    if(!isset($guests)) $guests = array();
?>

<table class="fs-table fs-table--responsive fs-table--selectable fs-table--striped fs-table--text-middle guests list" data-status="<?php echo $status ?>" data-mode="<?php echo $mode ?>">
    <thead>
    <tr>
        <th class="created">
            {tr:date}
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

            <td class="created d-none d-lg-table-cell" data-label="{tr:invitation_was_sent_on}">
                <?php echo Utilities::formatDate($guest->created) ?>
            </td>

            <td class="to" data-label="{tr:recipients}">
                <a href="mailto:<?php echo Template::sanitizeOutputEmail($guest->email) ?>"><?php echo Template::sanitizeOutputEmail($guest->email) ?></a>

                <?php if($guest->errors) echo '<br /><span class="errors">'.implode(', ', array_map(function($type) {
                        return Lang::tr('recipient_error_'.$type);
                    }, array_unique(array_map(function($error) {
                        return $error->type;
                    }, $guest->errors)))).'</span>' ?>
            </td>

            <td class="expires" data-rel="expires" data-label="{tr:expiration}">
                <?php echo $guest->getOption(GuestOptions::DOES_NOT_EXPIRE) ? Lang::tr('never') : Utilities::formatDate($guest->expires) ?>
            </td>

            <td class="guest_transfers" data-label="{tr:guest_transfers}">
                <?php
                $guestTransfers = Transfer::fromGuest($guest);
                if (!$guestTransfers || !count($guestTransfers)) {
                ?>
                    <ul class="fs-list fs-list--inline">
                        <li>
                            0
                        </li>
                        <li>
                            <i class="fa fa-exclamation-circle"></i>
                        </li>
                    </ul>
                <?php } else {
                    $dc = count($guestTransfers);
                    echo $dc;
                } ?>
            </td>

            <td class="actions fs-table__actions" data-label="{tr:actions}">
                <div class="actionsblock">
                    <?php if($mode == 'user' && $guest->status == 'available') { ?>
                        <button type="button" class="fs-button remind" title="{tr:send_a_reminder}">
                            <i class="fi fi-reminder"></i>
                            <span>{tr:send_reminder}</span>
                        </button>
                        <button type="button" class="fs-button forward" title="{tr:resend_invitation}">
                            <i class="fi fi-resend"></i>
                            <span>{tr:resend_invitation}</span>
                        </button>
                    <?php } ?>
                    <?php if ($guest->status == 'available') { ?>
                        <button type="button" class="fs-button delete" title="{tr:delete_invitation}">
                            <i class="fi fi-trash"></i>
                            <span>{tr:delete_invitation}</span>
                        </button>
                    <?php } ?>
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

<script type="text/javascript" src="{path:js/guests_table.js}"></script>
