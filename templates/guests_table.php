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
        <?php foreach($guests as $gv) { ?>
        <tr class="guest" data-id="<?php echo $gv->id ?>">
            <td class="from">
                <abbr title="<?php echo htmlentities($gv->user_email) ?>">
                    <?php echo htmlentities(substr($gv->user_email, 0, strpos($gv->user_email, '@'))) ?>
                </abbr>
            </td>
            
            <td class="to">
                <abbr title="<?php echo htmlentities($gv->email) ?>">
                    <?php echo htmlentities(substr($gv->email, 0, strpos($gv->email, '@'))) ?>
                </abbr>
            </td>
            
            <td class="subject">
                <?php if(strlen($gv->subject) > 15) { ?>
                <span class="short"><?php echo htmlentities(substr($gv->subject, 0, 15)) ?></span>
                <span class="clickable expand">[...]</span>
                <div class="full"><?php echo htmlentities($gv->subject) ?></div>
                <?php } else echo htmlentities($gv->subject) ?>
            </td>
            
            <td class="message">
                <?php if(strlen($gv->message) > 15) { ?>
                <span class="short"><?php echo htmlentities(substr($gv->message, 0, 15)) ?></span>
                <span class="clickable expand">[...]</span>
                <div class="full"><?php echo htmlentities($gv->message) ?></div>
                <?php } else echo htmlentities($gv->message) ?>
            </td>
            
            <td class="created"><?php echo Utilities::formatDate($gv->created) ?></td>
            
            <td class="expires"><?php echo Utilities::formatDate($gv->expires) ?></td>
            
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
