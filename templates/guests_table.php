<?php
    if(!isset($status)) $status = 'available';
    if(!isset($mode)) $mode = 'user';
    if(!isset($transfers) || !is_array($transfers)) $transfers = array();
?>
<table class="guests list" data-status="<?php echo $status ?>" data-mode="<?php echo $mode ?>">
    <thead>
        <tr>
            <td class="to">{tr:guest}</td>
            <td class="subject">{tr:subject}</td>
            <td class="message">{tr:message}</td>
            <td class="created">{tr:created}</td>
            <td class="expires">{tr:expires}</td>
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
            
            <td class="message">
                <?php if(strlen($guest->message) > 15) { ?>
                <span class="short"><?php echo Template::sanitizeOutput(mb_substr($guest->message, 0, 15)) ?></span>
                <span class="clickable expand">[...]</span>
                <div class="full"><?php echo Template::sanitizeOutput($guest->message) ?></div>
                <?php } else echo Template::sanitizeOutput($guest->message) ?>
            </td>
            
            <td class="created"><?php echo Utilities::formatDate($guest->created) ?></td>
            
            <td class="expires" data-rel="expires">
                <?php echo $guest->getOption(GuestOptions::DOES_NOT_EXPIRE) ? Lang::tr('never') : Utilities::formatDate($guest->expires) ?>
            </td>
            
            <td class="actions"></td>
        </tr>
        <?php } ?>
        
        <?php if(!count($guests)) { ?>
        <tr>
            <td colspan="7">{tr:no_guests}</td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<script type="text/javascript" src="{path:js/guests_table.js}"></script>
