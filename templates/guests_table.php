<?php
    if(!isset($status)) $status = 'available';
    if(!isset($mode)) $mode = 'user';
    if(!isset($transfers) || !is_array($transfers)) $transfers = array();
?>

<div class="table-responsive">
<table class="guests list table table-hover " data-status="<?php echo $status ?>" data-mode="<?php echo $mode ?>">
    <thead class="thead-light">
        <tr>
            <th class="to">{tr:guest}</td>
            <th class="subject">{tr:subject}</td>
            <th class="message d-none d-lg-table-cell">{tr:message}</td>
            <th class="created d-none d-lg-table-cell">{tr:created}</td>
            <th class="expires">{tr:expires}</td>
            <th class="actions">{tr:actions}</th>
        </tr>
    </thead>
    
    <tbody>
    
    </tbody>
        <?php foreach($guests as $guest) { ?>
            <tr class="guest objectholder"
                data-id="<?php echo $guest->id ?>" 
                data-expiry-extension="<?php echo $guest->expiry_date_extension ?>"
                data-errors="<?php echo count($guest->errors) ? '1' : '' ?>">
            <td class="to">
                <a href="mailto:<?php echo Template::sanitizeOutputEmail($guest->email) ?>"><?php echo Template::sanitizeOutputEmail($guest->email) ?></a>
                
                <?php if($guest->errors) echo '<br /><span class="errors">'.implode(', ', array_map(function($type) {
                    return Lang::tr('recipient_error_'.$type);
                }, array_unique(array_map(function($error) {
                    return $error->type;
                }, $guest->errors)))).'</span>' ?>
            </td>
            
            <td class="subject">
                <?php if(strlen($guest->subject) > 15) { ?>
                <span class="short"><?php echo Template::sanitizeOutput(mb_substr($guest->subject, 0, 15)) ?></span>
                <span class="clickable expand">[...]</span>
                <div class="full"><?php echo Template::sanitizeOutput($guest->subject) ?></div>
                <?php } else echo Template::sanitizeOutput($guest->subject) ?>
            </td>
            
            <td class="message  d-none d-lg-table-cell">
                <?php if(strlen($guest->message) > 15) { ?>
                <span class="short"><?php echo Template::sanitizeOutput(mb_substr($guest->message, 0, 15)) ?></span>
                <span class="clickable expand">[...]</span>
                <div class="full"><?php echo Template::sanitizeOutput($guest->message) ?></div>
                <?php } else echo Template::sanitizeOutput($guest->message) ?>
            </td>
            
            <td class="created d-none d-lg-table-cell"><?php echo Utilities::formatDate($guest->created) ?></td>
            
            <td class="expires" data-rel="expires">
                <?php echo $guest->getOption(GuestOptions::DOES_NOT_EXPIRE) ? Lang::tr('never') : Utilities::formatDate($guest->expires) ?>
            </td>
            
            <td class="actions">
                <div class="actionsblock">

                    <span class="delete clickable  fa fa-lg fa-trash-o" title="{tr:delete}"></span>
                    <?php if( $mode == 'user' ) { ?>
                        <span class="remind clickable fa fa-lg fa-repeat" title="{tr:send_reminder}" ></span>
                        <span class="forward clickable fa fa-lg fa-mail-forward" title="{tr:forward}"></span>
                    <?php } ?>
                </div>
            </td>
        </tr>
        <?php } ?>
        
        <?php if(!count($guests)) { ?>
        <tr>
            <td colspan="7">{tr:no_guests}</td>
        </tr>
        <?php } ?>
    </tbody>
</table>
</div>

<script type="text/javascript" src="{path:js/guests_table.js}"></script>
