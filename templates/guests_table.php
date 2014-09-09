<table class="guests list">
    <thead>
        <tr>
            <td class="from">{tr:from}</td>
            <td class="to">{tr:to}</td>
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
        <tr class="guest" data-id="<?php echo $guest->id ?>">
            <td class="from">
                <abbr title="<?php echo htmlentities($guest->user_email) ?>">
                    <?php echo htmlentities(substr($guest->user_email, 0, strpos($guest->user_email, '@'))) ?>
                </abbr>
            </td>
            
            <td class="to">
                <abbr title="<?php echo htmlentities($guest->email) ?>">
                    <?php echo htmlentities(substr($guest->email, 0, strpos($guest->email, '@'))) ?>
                </abbr>
            </td>
            
            <td class="subject">
                <?php if(strlen($guest->subject) > 15) { ?>
                <span class="short"><?php echo htmlentities(substr($guest->subject, 0, 15)) ?></span>
                <span class="clickable expand">[...]</span>
                <div class="full"><?php echo htmlentities($guest->subject) ?></div>
                <?php } else echo htmlentities($guest->subject) ?>
            </td>
            
            <td class="message">
                <?php if(strlen($guest->message) > 15) { ?>
                <span class="short"><?php echo htmlentities(substr($guest->message, 0, 15)) ?></span>
                <span class="clickable expand">[...]</span>
                <div class="full"><?php echo htmlentities($guest->message) ?></div>
                <?php } else echo htmlentities($guest->message) ?>
            </td>
            
            <td class="created"><?php echo Utilities::formatDate($guest->created) ?></td>
            
            <td class="expires"><?php echo Utilities::formatDate($guest->expires) ?></td>
            
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

<script type="text/javascript" src="{path:res/js/guests_table.js}"></script>
