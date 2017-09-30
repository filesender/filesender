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
if(Config::get('host_quota')) {
    $usage = Transfer::getUsage();
?>
<h3>{tr:host_quota_usage}</h3>

<span class="host_quota" data-total="<?php echo $usage['total'] ?>" data-used="<?php echo $usage['used'] ?>" data-available="<?php echo $usage['available'] ?>">
    <?php echo Lang::tr('quota_usage')->r($usage) ?>
</span>
<?php } ?>

<?php
if(Config::get('show_storage_statistics_in_admin')) {
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
<?php
}
}
?>

<?php



$createdTS = DBLayer::timeStampToEpoch('created');
$createdDD = DBLayer::datediff('NOW()','MIN(created)');

$sql=<<<EOF
SELECT 
    MAX("additional_attributes"),
    CASE
        WHEN "additional_attributes" LIKE '%encryption":false%' THEN '<i class="fa fa-unlock"></i>'
        WHEN "additional_attributes" LIKE '%encryption":true%' THEN '<i class="fa fa-lock"></i>'
        ELSE 'Unknown'
    END AS "Encryption",
    CASE
        WHEN "additional_attributes" LIKE '%iPad%' THEN '<i class="fa fa-apple"></i> iPad'
        WHEN "additional_attributes" LIKE '%iPod%' THEN '<i class="fa fa-apple"></i> iPod'
        WHEN "additional_attributes" LIKE '%iPhone%' THEN '<i class="fa fa-apple"></i> iPhone'
        WHEN "additional_attributes" LIKE '%imac%' THEN '<i class="fa fa-apple"></i> Mac'
        WHEN "additional_attributes" LIKE '%Mac%OS%' THEN '<i class="fa fa-apple"></i> Mac OS X'
        WHEN "additional_attributes" LIKE '%android%' THEN '<i class="fa fa-android"></i> Android'
        WHEN "additional_attributes" LIKE '%Ubuntu%' THEN '<i class="fa fa-linux"></i> Ubuntu Linux'
        WHEN "additional_attributes" LIKE '%linux%' THEN '<i class="fa fa-linux"></i> Linux'
        WHEN "additional_attributes" LIKE '%Nokia%' THEN 'Nokia'
        WHEN "additional_attributes" LIKE '%BlackBerry%' THEN 'BlackBerry'
        WHEN "additional_attributes" LIKE '%win%' OR
             "additional_attributes" LIKE '%Win%' THEN
            CASE
                WHEN "additional_attributes" LIKE '%NT 10.0%' THEN '<i class="fa fa-windows"></i> Windows 10'
                WHEN "additional_attributes" LIKE '%NT 6.3%' THEN '<i class="fa fa-windows"></i> Windows 8.1'
                WHEN "additional_attributes" LIKE '%NT 6.2%' THEN '<i class="fa fa-windows"></i> Windows 8'
                WHEN "additional_attributes" LIKE '%NT 6.1%' THEN '<i class="fa fa-windows"></i> Windows 7'
                WHEN "additional_attributes" LIKE '%NT 6.0%' THEN '<i class="fa fa-windows"></i> Windows Vista'
                WHEN "additional_attributes" LIKE '%NT 5.1%' THEN '<i class="fa fa-windows"></i> Windows XP'
                WHEN "additional_attributes" LIKE '%NT 5.0%' THEN '<i class="fa fa-windows"></i> Windows 2000'
                ELSE '<i class="fa fa-windows"></i> Windows'
            END      
        WHEN "additional_attributes" LIKE '%FreeBSD%' THEN 'FreeBSD'
        WHEN "additional_attributes" LIKE '%OpenBSD%' THEN 'OpenBSD'
        WHEN "additional_attributes" LIKE '%NetBSD%' THEN 'NetBSD'
        WHEN "additional_attributes" LIKE '%OpenSolaris%' THEN 'OpenSolaris'
        WHEN "additional_attributes" LIKE '%SunOS%' THEN 'SunOS'
        WHEN "additional_attributes" LIKE '%OS/2%' THEN 'OS/2'
        WHEN "additional_attributes" LIKE '%BeOS%' THEN 'BeOS'
        ELSE 'Unknown'
    END AS "OS",
    CASE
        WHEN "additional_attributes" LIKE '%edge%'THEN '<i class="fa fa-edge"></i> Edge'
        WHEN "additional_attributes" LIKE '%MSIE%' OR
             "additional_attributes" LIKE '%Trident%' THEN '<i class="fa fa-internet-explorer"></i> Internet Explorer'
        WHEN "additional_attributes" LIKE '%Firefox%' THEN '<i class="fa fa-firefox"></i> Mozilla Firefox'
        WHEN "additional_attributes" LIKE '%Vivaldi%' THEN '<i class="fa fa-vimeo-square"></i> Vivaldi' 
        WHEN "additional_attributes" LIKE '%Opera%' OR
             "additional_attributes" LIKE '%OPR%' THEN '<i class="fa fa-opera"></i> Opera' 
        WHEN "additional_attributes" LIKE '%Chrome%' OR
             "additional_attributes" LIKE '%CriOS%' THEN '<i class="fa fa-chrome"></i> Google Chrome'
        WHEN "additional_attributes" LIKE '%Safari%' THEN '<i class="fa fa-safari"></i> Apple Safari'
        WHEN "additional_attributes" LIKE '%Outlook%' THEN 'Outlook' 
        ELSE 'Unknown'
    END AS "Browser",
    AVG(CASE WHEN time_taken > 0 THEN size/time_taken ELSE 0 END) as Speed,
    AVG(CASE WHEN size>1073741824 THEN size/time_taken ELSE NULL END) as GSpeed,
    0 as GSpeed,
    AVG(size) as avgSize,
    MIN(size) as minSize,
    MAX(size) as maxSize,
    SUM(size) as transfered,
    COUNT(ID) as Count,
    MIN($createdTS) as FirstTransfer,
    COUNT(ID)/$createdDD as CountPerDay
FROM StatLogs
WHERE event='file_uploaded'
GROUP BY "Encryption","OS","Browser"
ORDER BY COUNT(ID) DESC, maxSize DESC
EOF;
$statement = DBI::prepare($sql);
$statement->execute(array());
$result = $statement->fetchAll();
$transfered=0;
$transfers=0;
$now=time();
$firstTransfer=$now;
echo '<br><br>';
echo '<h3>Browser Stats</h3>';
echo '<table class="list storage_usage_blocks">';
echo '<thead><tr><th>Browser</th><th>OS</th><th>Encrypted</th><th>Average Speed</th><th>Average Speed of &gt;1GB</th><th>Min Size</th><th>Average Size</th><th>Max Size</th><th>Transfered</th><th>File Transfers</th><th>Average Transfers per Day</th></tr></thead>';
foreach($result as $row) {
    echo '<tr>';
    if ($row['Browser'] != 'Unknown' && $row['OS'] != 'Unknown') {
	echo '<td>'.$row['Browser'].'</td>';
	echo '<td>'.$row['OS'].'</td>';
    } else {
	echo '<td colspan="2">';
	echo $row['Browser'].'<br>'.$row['OS'].'<br>';
	echo $row['additional_attributes'];
	echo '</td>';
    }
    echo '<td>'.$row['Encryption'].'</td>';
    echo '<td>'.($row['Speed']>0?(Utilities::formatBytes($row['Speed']).'/s'):'&nbsp;').'</td>';
    echo '<td>'.($row['GSpeed']>0?(Utilities::formatBytes($row['GSpeed']).'/s'):'&nbsp;').'</td>';
    echo '<td>'.($row['minSize']>0?Utilities::formatBytes($row['minSize']):'&nbsp;').'</td>';
    echo '<td>'.($row['avgSize']>0?Utilities::formatBytes($row['avgSize']):'&nbsp;').'</td>';
    echo '<td>'.($row['maxSize']>0?Utilities::formatBytes($row['maxSize']):'&nbsp;').'</td>';
    echo '<td>'.Utilities::formatBytes($row['transfered']).'</td>';
    echo '<td>'.$row['Count'].'</td>';
    echo '<td>'.round($row['CountPerDay']).'</td>';
    echo '</tr>';
    
    //echo '<tr><td colspan="11">'.nl2br(str_replace(' ','&nbsp;',json_encode($a,JSON_PRETTY_PRINT))).'</td></tr>';
    $transfered+=$row['transfered'];
    $transfers+=$row['Count'];
    $firstTransfer=min($firstTransfer,$row['FirstTransfer']);
}
echo '</table>';
echo '<br><br>';
$days=($now-$firstTransfer)/86400;
echo '<table>';
echo '<tr><td>Transfered</td>';
echo '<td>'.Utilities::formatBytes($transfered).'</td><td>('.Utilities::formatBytes($transfered/$days).'/day)</td></tr>';
echo '<tr><td>File Transfers</td>';
echo '<td>'.number_format($transfers).'</td><td>('.number_format($transfers/$days,1).' per day)</td></tr>';
echo '</table>';
?>


<script type="text/javascript" src="{path:js/admin_statistics.js}"></script>
