<?php
header('Content-Type: application/json');
require_once('../../../includes/init.php');

if (!Auth::isAdmin() && !Auth::isTenantAdmin()) {
    //Go Away
    echo json_encode(array());
    exit(0);
}
$idp = Auth::getTenantAdminIDP();

$data = array(
    'type' => 'line',
    'data' => array(
        'labels' => array(),
        'datasets' => array(
            array(
                'label' => Lang::tr('admin_transfers_section')->out(),
                'data' => array(),
                'borderColor' => 'rgba(115, 191, 105, 0.6)',
                'backgroundColor' => 'rgba(115, 191, 105, 0.6)',
                'fill' => false,
                'spanGaps' => true
            ),
            array(
                'label' => Lang::tr('admin_guests_section')->out(),
                'data' => array(),
                'borderColor' => 'rgba(242, 204, 12, 0.6)',
                'backgroundColor' => 'rgba(242, 204, 12, 0.6)',
                'fill' => false,
                'spanGaps' => true
            )
        )
    ),
    'options' => array (
        'responsive' => true,
        'maintainAspectRatio' => false,
        'plugins' => array(
            'title' => array(
                'display' => true,
                'text' => Lang::tr('transfers_and_guests_per_day')->out()
            ),
            'legend' => array(
                'position' => 'bottom'
            ),
        ),
        'scales' => array(
            'y' => array(
                'display' => true,
                'title' => array (
                    'display' => false,
                    'text' => ''
                ),
                'ticks' => array( 'min' => 0 ),
            ),
        )
    )
);

$sql =
    'SELECT '
   .'  Date.date, '
   .((!$idp) ?
        '  (SELECT COUNT(id) FROM transfersfilesview WHERE date_created <= Date.date AND date_expires >= Date.date) as transfers, '
       .'  (SELECT COUNT(id) FROM guestsidpview      WHERE date_created <= Date.date AND date_expires >= Date.date) as guests '
     :
        '  (SELECT COUNT(id) FROM transfersfilesview WHERE idpid = :idp AND date_created <= Date.date AND date_expires >= Date.date) as transfers, '
       .'  (SELECT COUNT(id) FROM guestsidpview      WHERE idpid = :idp AND date_created <= Date.date AND date_expires >= Date.date) as guests '
    )
   .'FROM '
   .'  (SELECT (SELECT Date(NOW() - '.DBLayer::toIntervalDays(30).')) + '.DBLayer::toIntervalDays("a+b").' date '
   .'  FROM (SELECT 0 a UNION SELECT 1 a UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 '
   .'  UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 ) d, '
   .'  (SELECT 0 b UNION SELECT 10 UNION SELECT 20 UNION SELECT 30 UNION SELECT 40) m '
   .'  WHERE (SELECT Date(NOW() - '.DBLayer::toIntervalDays(30).')) + '.DBLayer::toIntervalDays("a+b").' <= (select date(now())) '
   .'  ORDER BY a + b) as Date '
   .'ORDER BY date';

$placeholders = array();
if ($idp)
    $placeholders[':idp'] = $idp;

//error_log($sql);

$statement = DBI::prepare($sql);
$statement->execute($placeholders);
$result = $statement->fetchAll();
foreach($result as $row) {
    $label = $row['date'];
    $label = preg_replace('/ 00:00:00/','',$label);
    $data['data']['labels'][]=$label;
    $data['data']['datasets'][0]['data'][]=$row['transfers'];
    $data['data']['datasets'][1]['data'][]=$row['guests'];
}

echo json_encode($data);
//echo json_encode($data,JSON_PRETTY_PRINT);
