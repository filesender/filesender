<?php
      include_once "pagemenuitem.php"
?>

<div class="core">
<h2>{tr:admin_statistics_section}</h2>

<?php
if (AggregateStatistic::enabled()) {
    echo "<h3>{tr:aggregate_statistics}</h3>";
    pagelink('aggregate_statistics');
    echo "<br/>";
}
?>
    
<h3>{tr:global_statistics}</h3>

<table class="table global_statistics">
    <tr><th>{tr:user_count_estimate}</th><td><?php echo User::countEstimate() ?></td></tr>
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

<table class="table storage_usage <?php echo $global_warning ? 'warning' : '' ?>">
    <tr data-metric="total"><th>{tr:storage_total}</th><td><?php echo Utilities::formatBytes($total_space) ?></td></tr>
    <tr data-metric="used"><th>{tr:storage_used}</th><td><?php echo Utilities::formatBytes($total_space - $free_space).' ('.sprintf('%.1d', 100 * ($total_space - $free_space) / $total_space).'%)' ?></td></tr>
    <tr data-metric="available"><th>{tr:storage_available}</th><td><?php echo Utilities::formatBytes($free_space).' ('.sprintf('%.1d', 100 * $free_space / $total_space).'%)' ?></td></tr>
</table>

<table class="table storage_usage_blocks">
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

function os_name_to_html( $v )
{
    if( $v == 'iPad'   )  return '<i class="fa fa-apple"></i> iPad';
    if( $v == 'iPod'   )  return '<i class="fa fa-apple"></i> iPod';
    if( $v == 'iPhone' )  return '<i class="fa fa-apple"></i> iPhone';
    if( $v == 'Mac' )     return '<i class="fa fa-apple"></i> Mac';
    if( $v == 'OSX' )     return '<i class="fa fa-apple"></i> Mac OSX';
    if( $v == 'Android' ) return '<i class="fa fa-android"></i> Android';
    if( $v == 'Linux' )   return '<i class="fa fa-linux"></i> Linux';
    if( $v == 'Windows 10' )      return '<i class="fa fa-windows"></i> Windows 10';
    if( $v == 'Windows 8.1' )     return '<i class="fa fa-windows"></i> Windows 8.1';
    if( $v == 'Windows 8.0' )     return '<i class="fa fa-windows"></i> Windows 8.0';
    if( $v == 'Windows 7.0' )     return '<i class="fa fa-windows"></i> Windows 7.0';
    if( $v == 'Windows (Other)' ) return '<i class="fa fa-windows"></i> Windows Other';
    return $v;
}

function browser_name_to_html( $v )
{
    if( $v == 'Edge' )              return '<i class="fa fa-edge"></i> Edge';
    if( $v == 'Internet Explorer' ) return '<i class="fa fa-internet-explorer"></i> Internet Explorer';
    if( $v == 'Mozilla Firefox' )   return '<i class="fa fa-firefox"></i> Mozilla Firefox';
    if( $v == 'Opera' )             return '<i class="fa fa-opera"></i> Opera';
    if( $v == 'Google Chrome' )     return '<i class="fa fa-chrome"></i> Google Chrome';
    if( $v == 'Apple Safari' )      return '<i class="fa fa-safari"></i> Apple Safari';
    if( $v == 'Outlook'     )       return '<i class="fa "></i> Outlook';
    return $v;
}

function is_encrypted_to_html( $v )
{
    if( $v == '1' )
        return '<i class="fa fa-lock"></i>';
    return '<i class="fa fa-unlock"></i>';
}
 

$createdTS = DBLayer::timeStampToEpoch('created');
$createdDD = DBLayer::datediff('NOW()','MIN(created)');

$sql=<<<EOF
SELECT 
    MAX(additional_attributes) as "additional_attributes",
    AVG(CASE WHEN time_taken > 0 THEN size/time_taken ELSE 0 END) as speed,
    AVG(CASE WHEN time_taken > 0 AND size>1073741824 THEN size/time_taken ELSE NULL END) as gspeed,
    AVG(size) as avgsize,
    MIN(size) as minsize,
    MAX(size) as maxsize,
    SUM(size) as transfered,
    COUNT(ID) as count,
    MIN($createdTS) as firsttransfer,
    (CASE WHEN $createdDD > 0 THEN COUNT(ID)/$createdDD ELSE NULL END) as countperday,
    os_name, browser_name, is_encrypted
FROM statlogsview
WHERE event='file_uploaded'
GROUP BY is_encrypted,os_name,browser_name
ORDER BY COUNT(ID) DESC, maxsize DESC
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
echo '<table class="table storage_usage_blocks">';
echo '<thead class="thead-light"><tr><th>Browser</th><th>OS</th><th>Encrypted</th><th>Average Speed</th><th>Average Speed of &gt;1GB</th><th>Min Size</th><th>Average Size</th><th>Max Size</th><th>Transfered</th><th>File Transfers</th><th>Average Transfers per Day</th></tr></thead>';
foreach($result as $row) {
    echo '<tr>';
    if (empty($row['browser_name'])) {
        echo '<td>';
        if ((empty($row['browser'])))  {
            echo 'Unknown';
        } else {
            echo $row['browser'];
        }
        echo '</td>';
        echo '<td>';
        if (empty($row['os']))  {
            echo 'Unknown';
        } else {
            echo $row['os'];
        }
        echo '</td>';
        echo '<td>';
        if ($row['additional_attributes'] === '{"encryption":true}')  {
            echo is_encrypted_to_html(1);
        } 
        elseif ($row['additional_attributes'] === '{"encryption":false}') {
            echo is_encrypted_to_html(0);
        } else {
            echo $row['additional_attributes'];
        }
        echo '</td>';
    } else {
        echo '<td>'.browser_name_to_html($row['browser_name']).'</td>';
        echo '<td>'.os_name_to_html($row['os_name']).'</td>';
        echo '<td>'.is_encrypted_to_html($row['is_encrypted']).'</td>';
    }
    echo '<td>'.($row['speed']>0?(Utilities::formatBytes($row['speed']).'/s'):'&nbsp;').'</td>';
    echo '<td>'.($row['gspeed']>0?(Utilities::formatBytes($row['gspeed']).'/s'):'&nbsp;').'</td>';
    echo '<td>'.($row['minsize']>0?Utilities::formatBytes($row['minsize']):'&nbsp;').'</td>';
    echo '<td>'.($row['avgsize']>0?Utilities::formatBytes($row['avgsize']):'&nbsp;').'</td>';
    echo '<td>'.($row['maxsize']>0?Utilities::formatBytes($row['maxsize']):'&nbsp;').'</td>';
    echo '<td>'.Utilities::formatBytes($row['transfered']).'</td>';
    echo '<td>'.$row['count'].'</td>';
    echo '<td>'.round($row['countperday']).'</td>';
    echo '</tr>';
    
    //echo '<tr><td colspan="11">'.nl2br(str_replace(' ','&nbsp;',json_encode($a,JSON_PRETTY_PRINT))).'</td></tr>';
    $transfered+=$row['transfered'];
    $transfers+=$row['count'];
    $firstTransfer=min($firstTransfer,$row['firsttransfer']);    
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
</div>
