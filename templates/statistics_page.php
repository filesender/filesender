<?php
include_once "pagemenuitem.php";

$idp = Auth::getTenantAdminIDP();

function html_selectidp( $idp, $row ) {
    if( $idp == $row['saml_user_identification_idp'] ) {
        return ' selected';
    }
    return '';
}

?>
<div class="fs-statistics">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="fs-statistics__title">
                    <h1>{tr:admin_statistics_section}<?php if ($idp) { echo ' ('.$idp.')'; } ?></h1>
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
}
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
    echo '        <option value="'.$row['saml_user_identification_idp'].'"'.html_selectidp( $idp, $row ).'>'.$row['saml_user_identification_idp'].'</option>'."\n";
}
?>
    </select>
    <button id="idpbutton">Go</button>
    </h3>
<?php
}
?>

<h3>{tr:global_statistics}</h3>

<table class="fs-table fs-table--striped global_statistics">
    <tr><th>{tr:user_count_estimate}</th><td><?php echo number_format(User::users($idp)) ?></td></tr>
    <tr><th>{tr:recipient_count_estimate}</th><td><?php echo number_format(Recipient::getRecipientCount($idp)) ?></td></tr>
    <tr><th>{tr:guest_count_estimate}</th><td><?php echo number_format(Guest::getGuestCount($idp)) ?></td></tr>
    <tr><th>{tr:user_aup_count_estimate}</th><td><?php echo number_format(User::usersSignedAUP($idp)) ?></td></tr>
    <tr><th>{tr:user_apikey_count_estimate}</th><td><?php echo number_format(User::usersWithAPIKey($idp)) ?></td></tr>
    <tr><th>{tr:uploading_transfers}</th><td><?php echo number_format(count(Transfer::allUploading($idp))) ?></td></tr>
    <tr><th>{tr:available_transfers}</th><td><?php echo number_format(count(Transfer::allAvailable($idp))) ?></td></tr>
<?php
if (!$idp) {
    $creations = StatLog::getEventCount(LogEventTypes::TRANSFER_AVAILABLE);
    if (!is_null($creations)) {
        $creations['count']=number_format($creations['count']);
?>
    <tr><th>{tr:created_transfers}</th><td><?php echo Lang::tr('count_from_date_to_date')->r($creations) ?></td></tr>
<?php
    }
}
?>
    <tr><th>{tr:expired_transfers}</th><td><?php echo number_format(count(Transfer::allExpired($idp))) ?></td></tr>
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
if (!$idp) {
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

<h3>Per Day</h3>
<table class="fs-table fs-table--striped per_day">
<?php
$sql='SELECT FLOOR(AVG(size)) as s, FLOOR(AVG(count)) as c FROM (select DATE(created) as day,SUM(filesize) as size, COUNT(id) as count FROM transfersfilesview GROUP BY DATE(created)) t';
$placeholders=array();
if ($idp) {
    $sql='SELECT FLOOR(AVG(size)) as s, FLOOR(AVG(count)) as c FROM (select DATE(transfersfilesview.created) as day, SUM(transfersfilesview.filesize) as size, COUNT(transfersfilesview.id) as count FROM transfersfilesview LEFT JOIN '.call_user_func('Authentication::getDBTable').' ON transfersfilesview.userid='.call_user_func('Authentication::getDBTable').'.id WHERE '.call_user_func('Authentication::getDBTable').'.saml_user_identification_idp = :idp GROUP BY DATE(transfersfilesview.created)) t';
$placeholders=array();
    $placeholders[':idp'] = $idp;
}
$statement = DBI::prepare($sql);
$statement->execute($placeholders);
$row = array_pop($statement->fetchAll());

echo '<tr><td>Transfered</td>';
echo '<td>'.Utilities::formatBytes($row['s']).'/day</td></tr>';
echo '<tr><td>File Transfers</td>';
echo '<td>'.number_format($row['c']).'/day</td></tr>';
?>
</table>
            </div>
            <div class="col-4">
                <div class="row graph">
                    <div id="graph_transfers_vouchers" height="200"></div>
                </div>
                <div class="row graph">
                    <div id="graph_transfers_speeds" height="200"></div>
                </div>
            </div>
            <div class="col-4">
                <div class="row graph">
                    <div id="graph_data_per_day" height="200"></div>
                </div>
                <div class="row graph">
                    <div id="graph_encryption_split" height="200"></div>
                </div>
            </div>
        </div>
    </div>

<?php
    if (!$idp) {
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
if (!$idp) {
?>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <table id="browser_stats" class="fs-table fs-table--striped browser_stats"></table>
            </div>
        </div>
    </div>
<?php
}
?>
    <div class="container">
        <div class="row">
            <div class="col-6">
                <h3>{tr:top_users}</h3>
                <table id="top_users" class="fs-table fs-table--thin"></table>
            </div>
            <div class="col-6">
                <h3>{tr:top_users_include_expired}</h3>
                <table id="transfer_per_user" class="fs-table fs-table--thin"></table>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <h3>{tr:mime_types}</h3>
                <table id="mime_types" class="fs-table fs-table--thin"></table>
            </div>
            <div class="col-6">
                <h3>{tr:users_with_api_keys}</h3>
                <table id="users_with_api_keys" class="fs-table fs-table--thin"></table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{path:lib/chart.js/chart.min.js}"></script>
<script type="text/javascript" src="{path:js/admin_statistics.js}"></script>
