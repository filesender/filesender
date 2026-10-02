<?php
include_once "pagemenuitem.php";

$idp = Auth::getTenantAdminIDP();

function html_selectidp( $idp, $row ) {
    if( $idp == $row['id'] ) {
        return ' selected';
    }
    return '';
}

$idpname = '';
if ($idp) {
    $sql='SELECT name, entityid FROM idpsview WHERE id=:idp';
    $placeholders=array();
    $placeholders[':idp'] = $idp;
    $statement = DBI::prepare($sql);
    $statement->execute($placeholders);
    $result = $statement->fetchAll();
    $row = array_pop($result);
    $row['name'] ??= $row['entityid'];
    $idpname = $row['name'];
}

$storage_usage = null;
if(Config::get('show_storage_statistics_in_admin')) {
    $storage_usage = Storage::getUsage();
}

?>
<div class="fs-statistics">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="fs-statistics__header">
                    <h1>{tr:admin_statistics_section}</h1>
                    <?php if ($idpname) { ?>
                        <p class="fs-statistics__idp"><?php echo Template::Q($idpname) ?></p>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-xl-5">
                <div class="row">
                    <?php if (Auth::isAdmin()) { ?>
                        <div class="col-12">
                            <div class="fs-statistics__section">
                                <div class="fs-statistics__filter">
                                    <div class="fs-select">
                                        <label for="idpselect">{tr:statistics_idp}</label>
                                        <select id="idpselect">
                                            <option value="0">{tr:statistics_all_idps}</option>
                                            <?php
                                            $sql='SELECT id, name, entityid FROM idpsview ORDER BY name';
                                            $statement = DBI::prepare($sql);
                                            $statement->execute(array());
                                            $result = $statement->fetchAll();
                                            foreach($result as $row) {
                                                $row['name'] ??= $row['entityid'];
                                                echo '<option value="'.$row['id'].'"'.html_selectidp( $idp, $row ).'>'.Template::Q($row['name']).'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <button type="button" id="idpbutton" class="fs-button">
                                        <i class="fi fi-search"></i>
                                        <span>{tr:statistics_filter}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="col-12 col-md-6 col-xl-12">
                        <div class="fs-statistics__section">
                            <h4>{tr:global_statistics}</h4>
                            <table class="fs-table fs-table--striped fs-statistics__summary global_statistics">
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
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-12">
                        <?php if (AggregateStatistic::enabled() && GUI::isUserAllowedToAccessPage('aggregate_statistics')) { ?>
                            <div class="fs-statistics__section">
                                <h4>{tr:aggregate_statistics}</h4>
                                <?php pagelink('aggregate_statistics') ?>
                            </div>
                        <?php } ?>

                        <?php
                        if(Config::get('host_quota')) {
                            $usage = Transfer::getUsage();
                        ?>
                            <div class="fs-statistics__section">
                                <h4>{tr:host_quota_usage}</h4>
                                <span class="host_quota" data-total="<?php echo $usage['total'] ?>" data-used="<?php echo $usage['used'] ?>" data-available="<?php echo $usage['available'] ?>">
                                    <?php echo Lang::tr('quota_usage')->r($usage) ?>
                                </span>
                            </div>
                        <?php } ?>

                        <?php
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
                            <div class="fs-statistics__section">
                                <h4>{tr:storage_usage}</h4>
                                <table class="fs-table fs-table--striped fs-statistics__summary storage_usage <?php echo $global_warning ? 'warning' : '' ?>">
                                    <?php if (!$idp) { ?>
                                        <tr data-metric="total"><th>{tr:storage_total}</th><td><?php echo Utilities::formatBytes($total_space) ?></td></tr>
                                        <tr data-metric="used"><th>{tr:storage_used}</th><td><?php echo Utilities::formatBytes($total_space - $free_space).' ('.sprintf('%.1d', 100 * ($total_space - $free_space) / $total_space).'%)' ?></td></tr>
                                        <tr data-metric="available"><th>{tr:storage_available}</th><td><?php echo Utilities::formatBytes($free_space).' ('.sprintf('%.1d', 100 * $free_space / $total_space).'%)' ?></td></tr>
                                    <?php } else { $usage = Transfer::getUsage($idp); ?>
                                        <tr data-metric="used"><th>{tr:storage_used}</th><td><?php echo Utilities::formatBytes($usage['idpused']) ?></td></tr>
                                    <?php } ?>
                                </table>
                            </div>
                        <?php } ?>

                        <div class="fs-statistics__section">
                            <h4>{tr:statistics_per_day}</h4>
                            <table class="fs-table fs-table--striped fs-statistics__summary per_day">
                                <?php
                                $sql='SELECT FLOOR(AVG(size)) as s, FLOOR(AVG(count)) as c FROM (select date_created as day, SUM(filesize) as size, COUNT(id) as count FROM transfersfilesview GROUP BY date_created) t';
                                $placeholders=array();
                                if ($idp) {
                                    $sql='SELECT FLOOR(AVG(size)) as s, FLOOR(AVG(count)) as c FROM (select date_created as day, SUM(filesize) as size, COUNT(id) as count FROM transfersfilesview WHERE idpid = :idp GROUP BY date_created) t';
                                    $placeholders[':idp'] = $idp;
                                }
                                $statement = DBI::prepare($sql);
                                $statement->execute($placeholders);
                                $result = $statement->fetchAll();
                                $row = array_pop($result);
                                ?>
                                <tr><th>{tr:statistics_transferred}</th><td><?php echo Lang::tr('statistics_value_per_day')->r(array('value' => Utilities::formatBytes($row['s']))) ?></td></tr>
                                <tr><th>{tr:statistics_file_transfers}</th><td><?php echo Lang::tr('statistics_value_per_day')->r(array('value' => number_format($row['c']))) ?></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-7">
                <div class="row">
                    <div class="col-12 col-md-6 col-xl-12">
                        <div class="fs-statistics__graph">
                            <div id="graph_transfers_vouchers"></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-12">
                        <div class="fs-statistics__graph">
                            <div id="graph_data_per_day"></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-12">
                        <div class="fs-statistics__graph">
                            <div id="graph_transfers_speeds"></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-xl-12">
                        <div class="fs-statistics__graph">
                            <div id="graph_encryption_split"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!is_null($storage_usage) && !$idp) { ?>
            <div class="row">
                <div class="col">
                    <div class="fs-statistics__section">
                        <h4>{tr:storage_block}</h4>
                        <table class="fs-table fs-table--striped fs-table--responsive storage_usage_blocks">
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
                                        <td data-label="{tr:storage_block}"><?php echo $block ?></td>
                                        <td data-label="{tr:storage_paths}"><?php echo array_filter($info['paths']) ? implode(', ', $info['paths']) : Lang::tr('storage_main') ?></td>
                                        <td data-label="{tr:storage_total}"><?php echo Utilities::formatBytes($info['total_space']) ?></td>
                                        <td data-label="{tr:storage_used}"><?php echo Utilities::formatBytes($info['total_space'] - $info['free_space']).' ('.sprintf('%.1d', 100 * ($info['total_space'] - $info['free_space']) / $info['total_space']).'%)' ?></td>
                                        <td data-label="{tr:storage_available}"><?php echo Utilities::formatBytes($info['free_space']).' ('.sprintf('%.1d', 100 * $info['free_space'] / $info['total_space']).'%)' ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php } ?>
        <?php if (!$idp) { ?>
            <div class="row">
                <div class="col">
                    <div class="fs-statistics__section">
                        <h4>{tr:statistics_browser_stats}</h4>
                        <table id="browser_stats" class="fs-table fs-table--striped browser_stats"></table>
                    </div>
                </div>
            </div>
        <?php } ?>

        <div class="row">
            <div class="col">
                <div class="fs-statistics__section">
                    <h4>{tr:top_users}</h4>
                    <table id="top_users" class="fs-table fs-table--striped"></table>
                </div>
                <div class="fs-statistics__section">
                    <h4>{tr:top_users_include_expired}</h4>
                    <table id="transfer_per_user" class="fs-table fs-table--striped"></table>
                </div>
                <div class="fs-statistics__section">
                    <h4>{tr:mime_types}</h4>
                    <table id="mime_types" class="fs-table fs-table--striped"></table>
                </div>
                <div class="fs-statistics__section">
                    <h4>{tr:users_with_api_keys}</h4>
                    <table id="users_with_api_keys" class="fs-table fs-table--striped"></table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{path:lib/chart.js/chart.min.js}"></script>
<script type="text/javascript" src="{path:js/admin_statistics.js}"></script>
