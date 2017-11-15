<?php
    if(!isset($status)) $status = 'available';
    if(!isset($mode)) $mode = 'user';
    if(!isset($transfers) || !is_array($transfers)) $transfers = array();
    if(!isset($limit)) $limit = 100000;
    if(!isset($offset)) $offset = 0;
    if(!isset($pagerprefix)) $pagerprefix = '';
    $show_guest = isset($show_guest) ? (bool)$show_guest : false;
    $extend = (bool)Config::get('allow_transfer_expiry_date_extension');
    $audit = (bool)Config::get('auditlog_lifetime') ? '1' : '';
    $haveNext = 0;
    $havePrev = 0;

    if( count($transfers) > $limit ) {
        $haveNext = 1;
        $transfers = array_slice($transfers,0,$limit);
    }
    if( $offset > 0 ) {
        $havePrev = 1;
    }

    $showPager = $havePrev || $haveNext;
    
    if( $havePrev || $haveNext ) {
        echo '<table class="paginator" border="1"><tr>';
        $base = '?s=' . $_GET['s'];
        $cgioffset = $pagerprefix . 'offset';
        $cgilimit  = $pagerprefix . 'limit';
        $nextPage  = $offset+$limit;
        $nextLink  = "$base&$cgioffset=$nextPage&$cgilimit=$limit";
        
        if( $havePrev ) {
           $prevPage = max(0,$offset-$limit);
           echo "<td class='pageprev0'><a href='$base&$cgioffset=0&$cgilimit=$limit'><span class='fa-stack fa-lg'><i class='fa fa-square fa-stack-2x'></i><i class='fa fa-angle-double-left fa-stack-1x fa-inverse'></i></span></a></td>";
           echo "<td class='pageprev'><a href='$base&$cgioffset=$prevPage&$cgilimit=$limit'><span class='fa-stack fa-lg'><i class='fa fa-square fa-stack-2x'></i><i class='fa fa-angle-left fa-stack-1x fa-inverse'></i></span></a></td>";
        } else {
           echo "<td class='pageprev0'>&nbsp;&nbsp;</td><td class='pageprev'>&nbsp;</td>";
        }

        if( $haveNext ) {
           echo "<td class='pagenext'><a href='$nextLink'><span class='fa-stack fa-lg'><i class='fa fa-square fa-stack-2x'></i><i class='fa fa-angle-right fa-stack-1x fa-inverse'></i></span></a></td>";
        } else {
           echo "<td class='pagenext'>&nbsp;</td>";
        }
        echo "<td class='pageheader'>$header</td>";
        echo '</tr></tbody></table>';
    } else {
        if(isSet($header) && strlen($header))
            echo "<h2>$header</h2>";
    }
?>

<table class="transfers list" data-status="<?php echo $status ?>" data-mode="<?php echo $mode ?>" data-audit="<?php echo $audit ?>">
    <thead>
        <tr>
            <th class="expand" title="{tr:expand_all}">
                <span class="clickable fa fa-plus-circle fa-lg"></span>
            </th>
            
            <th class="transfer_id">
                {tr:transfer_id}
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
            
            <th class="expires">
                {tr:expires}
            </th>
            
            <th class="actions">
                {tr:actions}
            </th>
        </tr>
    </thead>
    
    <tbody>
        <?php foreach($transfers as $transfer) { ?>
        <tr class="transfer" id="transfer_<?php echo $transfer->id ?>"
            data-id="<?php echo $transfer->id ?>"
            data-recipients-enabled="<?php echo $transfer->getOption(TransferOptions::GET_A_LINK) ? '' : '1' ?>"
            data-errors="<?php echo count($transfer->recipients_with_error) ? '1' : '' ?>"
            data-expiry-extension="<?php echo $transfer->expiry_date_extension ?>"
        >
            <td class="expand">
                <span class="clickable fa fa-plus-circle fa-lg" title="{tr:show_details}"></span>
            </td>
            
            <td class="transfer_id">
                <?php echo $transfer->id ?>
            </td>
            
            <?php if($show_guest) { ?>
            <td class="guest">
                <?php if($transfer->guest) echo '<abbr title="'.Template::sanitizeOutput($transfer->guest->identity).'">'.Template::sanitizeOutput($transfer->guest->name).'</abbr>' ?>
            </td>
            <?php } ?>
            
            <td class="recipients">
                <?php
                $items = array();
                foreach(array_slice($transfer->recipients, 0, 3) as $recipient) {
                    if(in_array($recipient->email, Auth::user()->email_addresses)) {
                        $items[] = '<abbr title="'.Template::sanitizeOutputEmail($recipient->email).'">'.Lang::tr('me').'</abbr>';
                    } else if($recipient->email) {
                        $items[] = '<a href="mailto:'.Template::sanitizeOutputEmail($recipient->email).'">'.Template::sanitizeOutput($recipient->identity).'</a>';
                    } else {
                        $items[] = '<abbr title="'.Lang::tr('anonymous_details').'">'.Lang::tr('anonymous').'</abbr>';
                    }
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
                    $items[] = '<span title="'.Template::sanitizeOutput($file->name).'">'.Template::sanitizeOutput($name).'</span>';
                }
                
                if(count($transfer->files) > 3)
                    $items[] = '(<span class="clickable expand">'.Lang::tr('n_more')->r(array('n' => count($transfer->files) - 3)).'</span>)';
                
                echo implode('<br />', $items);
                ?>
            </td>
            
            <td class="downloads">
                <?php $dc = count($transfer->downloads); echo $dc; if($dc) { ?> (<span class="clickable expand">{tr:see_all}</span>)<?php } ?>
            </td>
           
            <td class="expires" data-rel="expires">
                <?php echo Utilities::formatDate($transfer->expires) ?>
            </td>
            
            <td class="actions">
                <span data-action="delete" class="fa fa-lg fa-trash-o" title="{tr:delete}"></span>
                <?php if($extend) { ?><span data-action="extend" class="fa fa-lg fa-calendar-plus-o"></span><?php } ?>
                <span data-action="add_recipient" class="fa fa-lg fa-envelope-o" title="{tr:add_recipient}"></span>
                <span data-action="remind" class="fa fa-lg fa-repeat" title="{tr:send_reminder}"></span>
                <?php if($audit) { ?><span data-action="auditlog" class="fa fa-lg fa-history" title="{tr:open_auditlog}"></span><?php } ?>
            </td>
        </tr>
        
        <tr class="transfer_details" data-id="<?php echo $transfer->id ?>">
            <td colspan="8">
                <div class="actions">
                    <span data-action="delete" class="fa fa-lg fa-trash-o" title="{tr:delete}"></span>
                    <?php if($extend) { ?><span data-action="extend" class="fa fa-lg fa-calendar-plus-o"></span><?php } ?>
                    <span data-action="add_recipient" class="fa fa-lg fa-envelope-o" title="{tr:add_recipient}"></span>
                    <span data-action="remind" class="fa fa-lg fa-repeat" title="{tr:send_reminder}"></span>
                    <?php if($audit) { ?><span data-action="auditlog" class="fa fa-lg fa-history" title="{tr:open_auditlog}"></span><?php } ?>
                </div>
                
                <div class="collapse">
                    <span class="clickable fa fa-minus-circle fa-lg" title="{tr:hide_details}"></span>
                </div>
                
                <div class="general">
                    <div class="transfer_id">
                        {tr:transfer_id} : <?php echo $transfer->id ?>
                    </div>
                    <div>
                        {tr:created} : <?php echo Utilities::formatDate($transfer->created) ?>
                    </div>
                    <div>
                        {tr:expires} : <span data-rel="expires"><?php echo Utilities::formatDate($transfer->expires) ?></span>
                    </div>
                    <div>
                        {tr:size} : <?php echo Utilities::formatBytes($transfer->size) ?>
                    </div>
                    <div>
                        {tr:with_identity} : <?php echo Template::sanitizeOutputEmail($transfer->user_email) ?>
                    </div>
                    <?php if($show_guest) { ?>
                    <div>
                        {tr:guest} : <?php if($transfer->guest) echo Template::sanitizeOutputEmail($transfer->guest->email) ?>
                    </div>
                    <?php } ?>
                    <div class="options">
                        {tr:options} :
                        <?php if(count($transfer->options)) { ?>
                        <ul class="options">
                            <li>
                            <?php echo implode('</li><li>', array_map(function($o) {
                                return Lang::tr($o);
                            }, array_keys(array_filter($transfer->options)))) ?>
                            </li>
                        </ul>
                        <?php } else echo Lang::tr('none') ?>
                    </div>
                    
                    <?php if($transfer->getOption(TransferOptions::GET_A_LINK)) { ?>
                    <div class="download_link">
                        {tr:download_link} : <input readonly="readonly" type="text" value="<?php echo $transfer->first_recipient->download_link ?>" />
                    </div>
                    <?php } ?>
                </div>
                
                <?php if($audit) { ?>
                <div class="auditlog">
                    <h2>{tr:auditlog}</h2>
                    <a href="#">
                        <span class="fa fa-lg fa-history"></span>
                        {tr:open_auditlog}
                    </a>
                </div>
                <?php } ?>
                
                <?php if(!$transfer->getOption(TransferOptions::GET_A_LINK)) { ?>
                <div class="recipients">
                    <h2>{tr:recipients}</h2>
                    
                    <?php foreach($transfer->recipients as $recipient) { ?>
                    <div class="recipient" data-id="<?php echo $recipient->id ?>" data-email="<?php echo Template::sanitizeOutputEmail($recipient->email) ?>" data-errors="<?php echo count($recipient->errors) ? '1' : '' ?>">
                        <?php
                        if(in_array($recipient->email, Auth::user()->email_addresses)) {
                            echo '<abbr title="'.Template::sanitizeOutputEmail($recipient->email).'">'.Lang::tr('me').'</abbr>';
                        } else {
                            echo '<a href="mailto:'.Template::sanitizeOutputEmail($recipient->email).'">'.Template::sanitizeOutput($recipient->identity).'</a>';
                        }
                        
                        if ($recipient->errors) echo '<span class="errors">' . implode(', ', array_map(function($type) {
                            return Lang::tr('recipient_error_' . $type);
                        }, array_unique(array_map(function($error) {
                            return $error->type;
                        }, $recipient->errors)))) . ' <span data-action="details" class="fa fa-lg fa-info-circle" title="{tr:details}"></span></span>';
                        
                        echo ' : '.count($recipient->downloads).' '.Lang::tr('downloads');
                        ?>
                        
                        <span data-action="delete" class="fa fa-lg fa-trash-o" title="{tr:delete}"></span>
                        
                        <span data-action="auditlog" class="fa fa-lg fa-history" title="{tr:open_recipient_auditlog}"></span>
                        
                        <span data-action="remind" class="fa fa-lg fa-repeat" title="{tr:send_reminder}"></span>
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>
                
                <div class="files">
                    <h2>{tr:files}</h2>
                    
                    <?php foreach($transfer->files as $file) { ?>
                        <div class="file" data-id="<?php echo $file->id ?>">
                            <?php echo Template::sanitizeOutput($file->name) ?> (<?php echo Utilities::formatBytes($file->size) ?>) : <?php echo count($file->downloads) ?> {tr:downloads}
                            
                            <?php if(!$transfer->is_expired) { ?>
                               
                                <?php if(isset($transfer->options['encryption']) && $transfer->options['encryption'] === true) { ?>
                                <span class="fa fa-lg fa-download transfer-file transfer-download" title="{tr:download}" data-id="<?php echo $file->id ?>" 
                                        data-encrypted="<?php echo isset($transfer->options['encryption'])?$transfer->options['encryption']:'false'; ?>" 
                                        data-mime="<?php echo Template::sanitizeOutput($file->mime_type); ?>" 
                                        data-name="<?php echo Template::sanitizeOutput($file->name); ?>"></span>
                                        
                                <?php } else {?>
                            <a class="fa fa-lg fa-download" title="{tr:download}" href="download.php?files_ids=<?php echo $file->id ?>"></a>
                                <?php } ?>
                            <?php } ?>
                            
                            <span data-action="delete" class="fa fa-lg fa-trash-o" title="{tr:delete}"></span>
                            
                            <?php if($audit) { ?>
                            <span data-action="auditlog" class="fa fa-lg fa-history" title="{tr:open_file_auditlog}"></span>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="fieldcontainer" id="encryption_description_not_supported">
                    {tr:file_encryption_disabled}
                </div>
            </td>
        </tr>
        <?php } ?>
        
        <?php if(!count($transfers)) { ?>
        <tr>
            <td colspan="7">{tr:no_transfers}</td>
        </tr>
        <?php } ?>

        <tr class="pager_bottom_nav">
            <td colspan="8" class="nextColumn">
                <?php if($haveNext) { ?>
                    <?php echo "<a href='$nextLink'>"; ?>
                    {tr:pager_more}
                    </a>
                <?php } else { ?>
                    {tr:pager_has_no_more}
                <?php } ?>
            </td>
        </tr>

    </tbody>
</table>



<script type="text/javascript" src="{path:js/transfers_table.js}"></script>
