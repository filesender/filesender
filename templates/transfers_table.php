<table class="transfers">
    <tr>
        <th class="expand" title="{tr:expand_all}">
            <span class="clickable fa fa-plus-circle fa-lg"></span>
        </th>
        
        <th class="recipients">
            {tr:recipients}
        </th>
        
        <th class="size">
            {tr:size}
        </th>
        
        <th class="files">
            {tr:files}
        </th>
        
        <th class="downloads">
            {tr:downloads}
        </th>
        
        <th class="expires">
            {tr:expires}
        </th>
        
        <th class="actions">
            {tr:actions}
        </th>
    </tr>
    
    <?php foreach($transfers as $transfer) { ?>
    <tr class="transfer" data-id="<?php echo $transfer->id ?>">
        <td class="expand" title="{tr:show_details}">
            <span class="clickable fa fa-plus-circle fa-lg"></span>
        </td>
        
        <td class="recipients">
            <?php
            echo implode('<br />', array_map(function($recipient) {
                return htmlentities($recipient->email);
            }, array_slice($transfer->recipients, 0, 3)));
            
            if(count($transfer->recipients) > 3)
                echo '<br />(<span class="clickable expand">'.Lang::tr('n_more', array('n' => count($transfer->recipients) - 3)).'</span>)';
            ?>
        </td>
        
        <td class="size">
            <?php echo Utilities::formatBytes($transfer->size) ?>
        </td>
        
        <td class="files">
            <?php
            echo implode('<br />', array_map(function($file) {
                return htmlentities($file->name);
            }, array_slice($transfer->files, 0, 3)));
            
            if(count($transfer->files) > 3)
                echo '<br />(<span class="clickable expand">'.Lang::tr('n_more', array('n' => count($transfer->recipients) - 3)).'</span>)';
            ?>
        </td>
        
        <td class="downloads">
            TODO (<span class="clickable expand">{tr:see_all}</span>)
        </td>
        
        <td class="expires">
            <?php echo Utilities::formatDate($transfer->expires) ?>
        </td>
        
        <td class="actions"></td>
    </tr>
    
    <tr class="transfer_details" data-id="<?php echo $transfer->id ?>">
        <td colspan="7">
            <div class="general">
                {tr:created} : <?php echo Utilities::formatDate($transfer->created) ?><br />
                {tr:expires} : <?php echo Utilities::formatDate($transfer->expires) ?><br />
                {tr:size} : <?php echo Utilities::formatBytes($transfer->size) ?><br />
                {tr:with_identity} : <?php echo htmlentities($transfer->user_email) ?><br />
                {tr:options} : <?php echo implode(', ', array_map(function($o) {
                    return Lang::tr($o);
                }, $transfer->options)) ?>
            </div>
            
            <div class="recipients">
                <h2>{tr:recipients}</h2>
                
                <?php foreach($transfer->recipients as $recipient) { ?>
                    <div class="recipient" data-id="<?php echo $recipient->id ?>">
                        <?php echo htmlentities($recipient->email) ?> : <!--count($transfer->auditLogs('download', $recipient)--> 0 {tr:downloads}
                    </div>
                <?php } ?>
            </div>
            
            <div class="files">
                <h2>{tr:files}</h2>
                
                <?php foreach($transfer->files as $file) { ?>
                    <div class="file" data-id="<?php echo $file->id ?>">
                        <?php echo htmlentities($file->name) ?> (<?php echo Utilities::formatBytes($file->size) ?>) : <!--count($transfer->auditLogs('download', $file)--> 0 {tr:downloads}
                    </div>
                <?php } ?>
            </div>
            
            <div class="auditlog">
                <h2>{tr:auditlog}</h2>
                TODO
            </div>
        </td>
    </tr>
    <?php } ?>
</table>

<script type="text/javascript" src="{path:res/js/transfers_table.js}"></script>
