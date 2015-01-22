<?php
    if(!isset($status)) $status = 'available';
    if(!isset($mode)) $mode = 'user';
    if(!isset($transfers) || !is_array($transfers)) $transfers = array();
    $show_guest = isset($show_guest) ? (bool)$show_guest : false;
?>
<table class="transfers list" data-status="<?php echo $status ?>" data-mode="<?php echo $mode ?>">
    <thead>
        <tr>
            <th class="expand" title="{tr:expand_all}">
                <span class="clickable fa fa-plus-circle fa-lg"></span>
            </th>
            
            <?php if($show_guest) { ?>
            <th class="guest">
                {tr:guest}
            </th>
            <?php } ?>
            
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
        <tr class="transfer" id="transfer_<?php echo $transfer->id ?>" data-id="<?php echo $transfer->id ?>" data-errors="<?php echo count($transfer->recipients_with_error) ? '1' : '' ?>">
            <td class="expand">
                <span class="clickable fa fa-plus-circle fa-lg" title="{tr:show_details}"></span>
            </td>
            
            <?php if($show_guest) { ?>
            <td class="guest">
                <?php if($transfer->guest) {
                    $who = explode('@', $transfer->guest->email);
                    echo '<abbr title="'.Utilities::sanitizeOutput($transfer->guest->email).'">'.Utilities::sanitizeOutput($who[0]).'</abbr>';
                } ?>
            </td>
            <?php } ?>
            
            <td class="recipients">
                <?php
                $items = array();
                foreach(array_slice($transfer->recipients, 0, 3) as $recipient) {
                    $who = in_array($recipient->email, Auth::user()->email_addresses) ? Lang::tr('me') : $recipient->email;
                    if(!$who) $who = Lang::tr('anonymous');
                    $who = explode('@', $who);
                    $items[] = '<abbr title="'.($recipient->email ? Utilities::sanitizeOutput($recipient->email) : Lang::tr('anonymous_details')).'">'.Utilities::sanitizeOutput($who[0]).'</abbr>';
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
                    <div>
                        {tr:created} : <?php echo Utilities::formatDate($transfer->created) ?>
                    </div>
                    <div>
                        {tr:expires} : <?php echo Utilities::formatDate($transfer->expires) ?>
                    </div>
                    <div>
                        {tr:size} : <?php echo Utilities::formatBytes($transfer->size) ?>
                    </div>
                    <div>
                        {tr:with_identity} : <?php echo Utilities::sanitizeOutput($transfer->user_email) ?>
                    </div>
                    <div class="options">
                        {tr:options} :
                        <?php if(count($transfer->options)) { ?>
                        <ul class="options">
                            <li>
                            <?php echo implode('</li><li>', array_map(function($o) {
                                return Lang::tr($o);
                            }, $transfer->options)) ?>
                            </li>
                        </ul>
                        <?php } else echo Lang::tr('none') ?>
                    </div>
                    <div class="transfer_id">
                        {tr:transfer_id} : <?php echo $transfer->id ?>
                    </div>
                </div>
                
                <div class="recipients">
                    <h2>{tr:recipients}</h2>
                    
                    <?php foreach($transfer->recipients as $recipient) { ?>
                        <div class="recipient" data-id="<?php echo $recipient->id ?>" data-email="<?php echo Utilities::sanitizeOutput($recipient->email) ?>" data-errors="<?php echo count($recipient->errors) ? '1' : '' ?>">
                            <abbr title="<?php echo $recipient->email ? Utilities::sanitizeOutput($recipient->email) : Lang::tr('anonymous_details') ?>">
                            <?php
                                $who = in_array($recipient->email, Auth::user()->email_addresses) ? Lang::tr('me') : $recipient->email;
                                if(!$who) $who = Lang::tr('anonymous');
                                $who = explode('@', $who);
                                echo Utilities::sanitizeOutput($who[0]);
                            ?>
                            </abbr>
                            
                            <?php
                                if($recipient->errors) echo '<span class="errors">'.implode(', ', array_map(function($type) {
                                    return Lang::tr('recipient_error_'.$type);
                                }, array_unique(array_map(function($error) {
                                    return $error->type;
                                }, $recipient->errors)))).'</span>'
                            ?>
                            
                            : <?php echo count($recipient->downloads) ?> {tr:downloads}
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
