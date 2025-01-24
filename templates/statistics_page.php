<?php
include_once "pagemenuitem.php";
$idp = Auth::getTenantAdminIDP();
?>
<div class="fs-statistics">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="fs-statistics__title">
                    <h1>{tr:admin_statistics_section}<?php if ($idp !== false) { echo ' ('.$idp.')'; } ?></h1>
                </div>
            </div>
        </div>
        <hr/>
        <div class="row">
            <div class="col-12">
            </div>
         </div>
        <div class="row">
            <div class="col-4">
<?php
if (AggregateStatistic::enabled() && GUI::isUserAllowedToAccessPage('aggregate_statistics')) {
    echo "<h3>{tr:aggregate_statistics}</h3>";
    pagelink('aggregate_statistics');
    echo "<br/>";
if (Auth::isAdmin()) {
?>
    <h3>IDP:
    <select id="idpselect">
        <option value="all">All</option>
<?php
$sql='SELECT DISTINCT saml_user_identification_idp FROM '.call_user_func('Authentication::getDBTable').' WHERE saml_user_identification_idp IS NOT NULL ORDER BY saml_user_identification_idp';
$statement = DBI::prepare($sql);
$statement->execute(array());
$result = $statement->fetchAll();
foreach($result as $row) {
    echo '        <option value="'.$row['saml_user_identification_idp'].'"'.($idp==$row['saml_user_identification_idp']?' selected':'').'>'.$row['saml_user_identification_idp'].'</option>'."\n";
}
?>
    </select>
    <button id="idpbutton">Go</button>
    </h3>
    <br/>
<?php
}
?>

<h3>{tr:global_statistics}</h3>

<table class="fs-table fs-table--striped global_statistics">
    <tr><th>{tr:user_count_estimate}</th><td><?php echo User::users($idp) ?></td></tr>
    <tr><th>{tr:recipient_count_estimate}</th><td><?php echo Recipient::getRecipients($idp) ?></td></tr>
    <tr><th>{tr:guest_count_estimate}</th><td><?php echo Guest::getGuests($idp) ?></td></tr>
    <tr><th>{tr:user_aup_count_estimate}</th><td><?php echo User::usersSignedAUP($idp) ?></td></tr>
    <tr><th>{tr:user_apikey_count_estimate}</th><td><?php echo User::usersWithAPIKey($idp) ?></td></tr>
    <tr><th>{tr:uploading_transfers}</th><td><?php echo count(Transfer::allUploading($idp)) ?></td></tr>
    <tr><th>{tr:available_transfers}</th><td><?php echo count(Transfer::allAvailable($idp)) ?></td></tr>
<?php
if ($idp===false) {
    $creations = StatLog::getEventCount(LogEventTypes::TRANSFER_AVAILABLE);
    if (!is_null($creations)) {
?>
    <tr><th>{tr:created_transfers}</th><td><?php echo Lang::tr('count_from_date_to_date')->r($creations) ?></td></tr>
<?php
    }
}
?>
    <tr><th>{tr:expired_transfers}</th><td><?php echo count(Transfer::allExpired($idp)) ?></td></tr>
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

<table class="fs-table fs-table--striped storage_usage <?php echo $global_warning ? 'warning' : '' ?>">
<?php
if ($idp===false) {
?>
    <tr data-metric="total"><th>{tr:storage_total}</th><td><?php echo Utilities::formatBytes($total_space) ?></td></tr>
    <tr data-metric="used"><th>{tr:storage_used}</th><td><?php echo Utilities::formatBytes($total_space - $free_space).' ('.sprintf('%.1d', 100 * ($total_space - $free_space) / $total_space).'%)' ?></td></tr>
    <tr data-metric="available"><th>{tr:storage_available}</th><td><?php echo Utilities::formatBytes($free_space).' ('.sprintf('%.1d', 100 * $free_space / $total_space).'%)' ?></td></tr>
<?php
} else {
    $usage = Transfer::getUsage($idp);
?>
    <tr data-metric="used"><th>{tr:storage_used}</th><td><?php echo Utilities::formatBytes($usage['idpused']) ?></td></tr>
<?php
}
?>
</table>
            </div>
            <div class="col-4">
                <div class="row graph">
                    <canvas id="graph_transfers_vouchers" height="200"></canvas>
                </div>
                <div class="row graph">
                    <canvas id="graph_transfers_speeds" height="200"></canvas>
                </div>
            </div>
            <div class="col-4">
                <div class="row graph">
                    <canvas id="graph_data_per_day" height="200"></canvas>
                </div>
                <div class="row graph">
                    <canvas id="graph_encryption_split" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

<?php
if ($idp===false) {
?>
    <div class="container">
        <div class="row">
            <div class="col-12">

<table class="fs-table fs-table--striped storage_usage_blocks">
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
            </div>
        </div>
    </div>
<?php
    }
}
}
?>

<?php
if ($idp===false) {
?>
    <div class="container">
        <div class="row">
            <div class="col-12">

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
echo '<h3>Browser Stats</h3>';
echo '<table class="fs-table fs-table--striped browser_stats">';
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
echo '<h3>Per Day</h3>';
$days=($now-$firstTransfer)/86400;
echo '<table class="fs-table fs-table--striped per_day">';
echo '<tr><td>Transfered</td>';
echo '<td>'.Utilities::formatBytes($transfered).'</td><td>('.Utilities::formatBytes($transfered/$days).'/day)</td></tr>';
echo '<tr><td>File Transfers</td>';
echo '<td>'.number_format($transfers).'</td><td>('.number_format($transfers/$days,1).' per day)</td></tr>';
echo '</table>';
?>
            </div>
        </div>
    </div>
<?php
}
?>
    <br><br>
    <div class="container">
        <div class="row">
            <div class="col-4">
                <h3>{tr:top_users}</h3>
                <table class="fs-table fs-table--thin">
                    <tr><th>{tr:admin_users_section}</th><th>{tr:admin_transfers_section}</th><th>{tr:size}</th></tr>
<?php
$sql=
    'SELECT '
   .'  '.call_user_func('Transfer::getDBTable').'.user_email as "User", '
   .'  COUNT(DISTINCT '.call_user_func('Transfer::getDBTable').'.id) AS "Transfers", '
   .'  SUM(IF('.call_user_func('Transfer::getDBTable').'.options LIKE \'%\\"encryption\\":true%\','.call_user_func('File::getDBTable').'.encrypted_size,'.call_user_func('File::getDBTable').'.size)) AS "Size" '
   .'FROM '
   .'  '.call_user_func('Transfer::getDBTable').' JOIN '.call_user_func('File::getDBTable').' ON '.call_user_func('File::getDBTable').'.transfer_id='.call_user_func('Transfer::getDBTable').'.id '
   .(($idp===false) ?
     ''
     :
     'LEFT JOIN '.call_user_func('Authentication::getDBTable').' ON '.call_user_func('Transfer::getDBTable').'.userid='.call_user_func('Authentication::getDBTable').'.id '
   )
   .'WHERE '
   .(($idp===false) ?
     ''
     :
     call_user_func('Authentication::getDBTable').'.saml_user_identification_idp = :idp AND '
   )
   .'    ((DATE('.call_user_func('Transfer::getDBTable').'.created) >= NOW() - '.DBLayer::toIntervalDays(30).') OR '
   .'     (DATE('.call_user_func('Transfer::getDBTable').'.expires) >= NOW() - '.DBLayer::toIntervalDays(30).' AND DATE('.call_user_func('Transfer::getDBTable').'.expires) <= NOW())) '
   .'    AND '.call_user_func('Transfer::getDBTable').'.status = "available" '
   .'GROUP BY '.call_user_func('Transfer::getDBTable').'.user_email '
   .'ORDER BY Transfers DESC '
   .'LIMIT 20';
$placeholders=array();
if ($idp!==false)
    $placeholders[':idp'] = $idp;

//error_log($sql);

$statement = DBI::prepare($sql);
$statement->execute($placeholders);
$result = $statement->fetchAll();
foreach($result as $row) {
    echo '<tr><td>'.$row['User'].'</td><td>'.$row['Transfers'].'</td><td>'.Utilities::formatBytes($row['Size']).'</td></tr>';
}
?>
                </table>
            </div>
            <div class="col-4">
                <h3>{tr:top_users_include_expired}</h3>
                <table class="fs-table fs-table--thin">
                    <tr><th>{tr:admin_users_section}</th><th>{tr:admin_transfers_section}</th><th>{tr:size}</th></tr>
<?php
$sql=
    'SELECT '
   .'  '.call_user_func('Transfer::getDBTable').'.user_email as "User", '
   .'  COUNT(DISTINCT '.call_user_func('Transfer::getDBTable').'.id) AS "Transfers", '
   .'  SUM(IF('.call_user_func('Transfer::getDBTable').'.options LIKE \'%\\"encryption\\":true%\','.call_user_func('File::getDBTable').'.encrypted_size,'.call_user_func('File::getDBTable').'.size)) AS "Size" '
   .'FROM '
   .'  '.call_user_func('Transfer::getDBTable').' JOIN '.call_user_func('File::getDBTable').' ON '.call_user_func('File::getDBTable').'.transfer_id='.call_user_func('Transfer::getDBTable').'.id '
   .(($idp===false) ?
     ''
     :
     'LEFT JOIN '.call_user_func('Authentication::getDBTable').' ON '.call_user_func('Transfer::getDBTable').'.userid='.call_user_func('Authentication::getDBTable').'.id '
   )
   .'WHERE '
   .(($idp===false) ?
     ''
     :
     call_user_func('Authentication::getDBTable').'.saml_user_identification_idp = :idp AND '
   )
   .'    ((DATE('.call_user_func('Transfer::getDBTable').'.created) >= NOW() - '.DBLayer::toIntervalDays(30).') OR '
   .'     (DATE('.call_user_func('Transfer::getDBTable').'.expires) >= NOW() - '.DBLayer::toIntervalDays(30).' AND DATE('.call_user_func('Transfer::getDBTable').'.expires) <= NOW())) '
   .'GROUP BY '.call_user_func('Transfer::getDBTable').'.user_email '
   .'ORDER BY Transfers DESC '
   .'LIMIT 20';
$placeholders=array();
if ($idp!==false)
    $placeholders[':idp'] = $idp;

//error_log($sql);

$statement = DBI::prepare($sql);
$statement->execute($placeholders);
$result = $statement->fetchAll();
foreach($result as $row) {
    echo '<tr><td>'.$row['User'].'</td><td>'.$row['Transfers'].'</td><td>'.Utilities::formatBytes($row['Size']).'</td></tr>';
}
?>
                </table>
            </div>
            <div class="col-4">
                <h3>{tr:users_with_api_keys}</h3>
                <table class="fs-table fs-table--thin">
                    <tr><th>{tr:admin_users_section}</th><th>{tr:date}</th></tr>
<?php
$sql=
    'SELECT '
   .'  '.call_user_func('Authentication::getDBTable').'.saml_user_identification_uid as "User", '
   .'  DATE('.call_user_func('User::getDBTable').'.auth_secret_created) as "Date" '
   .'FROM '
   .'  '.call_user_func('Authentication::getDBTable').' LEFT JOIN '.call_user_func('User::getDBTable').' on '.call_user_func('Authentication::getDBTable').'.id='.call_user_func('User::getDBTable').'.authid '
   .'WHERE '
   .'  '.call_user_func('User::getDBTable').'.auth_secret IS NOT NULL '
   .(($idp===false) ?
     ''
     :
     'AND '.call_user_func('Authentication::getDBTable').'.saml_user_identification_idp = :idp '
   )
   .'ORDER BY Date DESC';
$placeholders=array();
if ($idp!==false)
    $placeholders[':idp'] = $idp;

//error_log($sql);

$statement = DBI::prepare($sql);
$statement->execute($placeholders);
$result = $statement->fetchAll();
foreach($result as $row) {
    echo '<tr><td>'.$row['User'].'</td><td>'.$row['Date'].'</td></tr>';
}
?>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{path:lib/chart.js/chart.min.js}"></script>
<script type="text/javascript" src="{path:js/admin_statistics.js}"></script>
