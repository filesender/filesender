<h2>{tr:admin_statistics_section}</h2>

<h3>{tr:global_statistics}</h3>

<table>
    <tr><th>{tr:available_transfers}</th><td><?php echo count(Transfer::all(Transfer::AVAILABLE)) ?></td></tr>
    <tr><th>{tr:uploading_transfers}</th><td><?php echo count(Transfer::all(Transfer::UPLOADING)) ?></td></tr>
    
    <?php $creations = StatLog::getEventCount(LogEventTypes::TRANSFER_AVAILABLE); if(!is_null($creations)) { ?>
    <tr><th>{tr:created_transfers}</th><td><?php echo Lang::tr('count_from_date_to_date')->r($creations) ?></td></tr>
    <?php } ?>
</table>

<?php $storage_usage = Storage::getUsage(); if(!is_null($storage_usage)) { ?>
<h3>{tr:storage_usage}</h3>
<table class="list">
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
        <tr>
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
