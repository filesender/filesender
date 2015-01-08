<h2>{tr:admin_statistics_section}</h2>

<h3>{tr:global_statistics}</h3>

<table class="global_statistics">
    <tr><th>{tr:available_transfers}</th><td><?php echo count(Transfer::all(Transfer::AVAILABLE)) ?></td></tr>
    <tr><th>{tr:uploading_transfers}</th><td><?php echo count(Transfer::all(Transfer::UPLOADING)) ?></td></tr>
    
    <?php $creations = StatLog::getEventCount(LogEventTypes::TRANSFER_AVAILABLE); if(!is_null($creations)) { ?>
    <tr><th>{tr:created_transfers}</th><td><?php echo Lang::tr('count_from_date_to_date')->r($creations) ?></td></tr>
    <?php } ?>
</table>

<?php
$storage_usage = Storage::getUsage();
if(!is_null($storage_usage)) {
$total_space = 0;
$free_space = 0;
foreach($storage_usage as $info) {
    $total_space += $info['total_space'];
    $free_space += $info['free_space'];
}

$level = Config::get('storage_usage_warning');
$block_warnings = array();
$global_warning = false;
if($level) {
    if($free_space <= $level * $total_space / 100) $global_warning = true;
    
    foreach($storage_usage as $block => $info) {
        if($info['free_space'] > $level * $info['total_space'] / 100) continue;
        $block_warnings[] = $block;
    }
}
?>
<h3>{tr:storage_usage}</h3>

<table class="storage_usage <?php echo $global_warning ? 'warning' : '' ?>">
    <tr data-metric="total"><th>{tr:storage_total}</th><td><?php echo Utilities::formatBytes($total_space) ?></td></tr>
    <tr data-metric="used"><th>{tr:storage_used}</th><td><?php echo Utilities::formatBytes($total_space - $free_space).' ('.sprintf('%.1d', 100 * ($total_space - $free_space) / $total_space).'%)' ?></td></tr>
    <tr data-metric="available"><th>{tr:storage_available}</th><td><?php echo Utilities::formatBytes($free_space).' ('.sprintf('%.1d', 100 * $free_space / $total_space).'%)' ?></td></tr>
</table>

<table class="list storage_usage_blocks">
    <thead>
        <tr>
            <th>{tr:storage_block}</th>
            <th>{tr:storage_paths}</th>
            <th>{tr:storage_total}</th>
            <th>{tr:storage_used}</th>
            <th>{tr:storage_available}</th>
        </tr>
    </thead>
    
    <tbody>
    <?php foreach($storage_usage as $block => $info) { ?>
        <tr class="<?php echo in_array($block, $block_warnings) ? 'warning' : '' ?>">
            <td><?php echo $block ?></td>
            <td><?php echo array_filter($info['paths']) ? implode(', ', $info['paths']) : Lang::tr('storage_main') ?></td>
            <td><?php echo Utilities::formatBytes($info['total_space']) ?></td>
            <td><?php echo Utilities::formatBytes($info['total_space'] - $info['free_space']).' ('.sprintf('%.1d', 100 * ($info['total_space'] - $info['free_space']) / $info['total_space']).'%)' ?></td>
            <td><?php echo Utilities::formatBytes($info['free_space']).' ('.sprintf('%.1d', 100 * $info['free_space'] / $info['total_space']).'%)' ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<?php } ?>
