<?php
    if(!isset($status)) $status = 'available';
    if(!isset($mode)) $mode = 'user';
    if(!isset($transfers) || !is_array($transfers)) $transfers = array();
?>
<table class="transfers list" data-status="<?php echo $status ?>" data-mode="<?php echo $mode ?>">
    <thead>
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
            
            <?php if($status == 'available') { ?>
            <th class="expires">
                {tr:expires}
            </th>
            
            <th class="actions">
                {tr:actions}
            </th>
            <?php } ?>
        </tr>
    </thead>
    
    <tbody>
        <?php foreach($transfers as $transfer) { ?>
        <tr class="transfer" data-id="<?php echo $transfer->id ?>">
            <td class="expand">
                <span class="clickable fa fa-plus-circle fa-lg" title="{tr:show_details}"></span>
            </td>
            
            <td class="recipients">
                <?php
                $items = array();
                foreach(array_slice($transfer->recipients, 0, 3) as $recipient) {
                    $email = $recipient->email;
                    if(strlen($email) > 28) $email = substr($email, 0, 25).'...';
                    $items[] = '<span title="'.Utilities::sanitizeOutput($recipient->email).'">'.Utilities::sanitizeOutput($email).'</span>';
                }
                
                if(count($transfer->recipients) > 3)
                    $items[] = '(<span class="clickable expand">'.Lang::tr('n_more')->r(array('n' => count($transfer->recipients) - 3)).'</span>)';
                
                echo implode('<br />', $items);
                ?>
            </td>
            
            <td class="size">
                <?php echo Utilities::formatBytes($transfer->size) ?>
            </td>
            
            <td class="files">
                <?php
                $items = array();
                foreach(array_slice($transfer->files, 0, 3) as $file) {
                    $name = $file->name;
                    if(strlen($name) > 28) $name = substr($name, 0, 25).'...';
                    $items[] = '<span title="'.Utilities::sanitizeOutput($file->name).'">'.Utilities::sanitizeOutput($name).'</span>';
                }
                
                if(count($transfer->files) > 3)
                    $items[] = '(<span class="clickable expand">'.Lang::tr('n_more')->r(array('n' => count($transfer->files) - 3)).'</span>)';
                
                echo implode('<br />', $items);
                ?>
            </td>
            
            <td class="downloads">
                <?php $dc = count($transfer->downloads); echo $dc; if($dc) { ?> (<span class="clickable expand">{tr:see_all}</span>)<?php } ?>
            </td>
            
            <?php if($status == 'available') { ?>
            <td class="expires">
                <?php echo Utilities::formatDate($transfer->expires) ?>
            </td>
            
            <td class="actions"></td>
            <?php } ?>
        </tr>
        
        <tr class="transfer_details" data-id="<?php echo $transfer->id ?>">
            <td colspan="<?php echo ($status == 'available') ? 7 : 5 ?>">
                <div class="actions"></div>
                
                <div class="collapse">
                    <span class="clickable fa fa-minus-circle fa-lg" title="{tr:hide_details}"></span>
                </div>
                
                <div class="general">
                    {tr:created} : <?php echo Utilities::formatDate($transfer->created) ?><br />
                    {tr:expires} : <?php echo Utilities::formatDate($transfer->expires) ?><br />
                    {tr:size} : <?php echo Utilities::formatBytes($transfer->size) ?><br />
                    {tr:with_identity} : <?php echo Utilities::sanitizeOutput($transfer->user_email) ?><br />
                    {tr:options} : <?php echo implode(', ', array_map(function($o) {
                        return Lang::tr($o);
                    }, $transfer->options)) ?>
                </div>
                
                <div class="recipients">
                    <h2>{tr:recipients}</h2>
                    
                    <?php foreach($transfer->recipients as $recipient) { ?>
                        <div class="recipient" data-id="<?php echo $recipient->id ?>">
                            <?php echo Utilities::sanitizeOutput($recipient->email) ?> : <?php echo count($recipient->downloads) ?> {tr:downloads}
                        </div>
                    <?php } ?>
                </div>
                
                <div class="files">
                    <h2>{tr:files}</h2>
                    
                    <?php foreach($transfer->files as $file) { ?>
                        <div class="file" data-id="<?php echo $file->id ?>">
                            <?php echo Utilities::sanitizeOutput($file->name) ?> (<?php echo Utilities::formatBytes($file->size) ?>) : <?php echo count($file->downloads) ?> {tr:downloads}
                        </div>
                    <?php } ?>
                </div>
            </td>
        </tr>
        <?php } ?>
        
        <?php if(!count($transfers)) { ?>
        <tr>
            <td colspan="<?php echo ($status == 'available') ? 7 : 5 ?>">{tr:no_transfers}</td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<script type="text/javascript" src="{path:js/transfers_table.js}"></script>
