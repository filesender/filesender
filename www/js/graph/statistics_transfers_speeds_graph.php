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
                'label' => Lang::tr('upload_page_graph_encryption_in_transit')->out(),
                'data' => array(),
                'borderColor' => 'rgba(255, 152, 48, 0.6)',
                'backgroundColor' => 'rgba(255, 152, 48, 0.6)',
                'fill' => false,
                'spanGaps' => true
            ),
            array(
                'label' => Lang::tr('upload_page_graph_encryption_in_transit_and_rest')->out(),
                'data' => array(),
                'borderColor' => 'rgba(150, 217, 141, 0.6)',
                'backgroundColor' => 'rgba(150, 217, 141, 0.6)',
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
                'text' => Lang::tr('transfers_speedsps')->out()
            ),
            'legend' => array(
                'position' => 'bottom'
            ),
        ),
        'scales' => array(
            'y' => array(
                'display' => true,
                'title' => array (
                    'display' => true,
                    'text' => Lang::tr('upload_page_graph_mb_per_second')->out()
                ),
                'ticks' => array( 'min' => 0 ),
            ),
        )
    )
);

//extract(epoch from ts)

$sql =
    'SELECT '
   .'  Date.date, Date.date as d5, '
   .((!$idp) ?
        '  (SELECT MAX(size/('.timeStampToEpoch(made_available).'-'.timeStampToEpoch(created).'))/1048576 FROM transferssizeview WHERE DATE(created) <= Date.date AND DATE(expires) >= Date.date AND options LIKE \'%\\"encryption\\":false%\') as Unencrypted, '
       .'  (SELECT MAX(size/('.timeStampToEpoch(made_available).'-'.timeStampToEpoch(created).'))/1048576 FROM transferssizeview WHERE DATE(created) <= Date.date AND DATE(expires) >= Date.date AND options LIKE \'%\\"encryption\\":true%\') as Encrypted '
     :
        '  (SELECT MAX(size/('.timeStampToEpoch(made_available).'-'.timeStampToEpoch(created).'))/1048576 FROM transferssizeidpview WHERE saml_user_identification_idp = :idp AND DATE(created) <= Date.date AND DATE(expires) >= Date.date AND options LIKE \'%\\"encryption\\":false%\') as Unencrypted, '
       .'  (SELECT MAX(size/('.timeStampToEpoch(made_available).'-'.timeStampToEpoch(created).'))/1048576 FROM transferssizeidpview WHERE saml_user_identification_idp = :idp AND DATE(created) <= Date.date AND DATE(expires) >= Date.date AND options LIKE \'%\\"encryption\\":true%\') as Encrypted '
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
    $data['data']['datasets'][0]['data'][]=$row['Unencrypted'];
    $data['data']['datasets'][1]['data'][]=$row['Encrypted'];
}

echo json_encode($data);
//echo json_encode($data,JSON_PRETTY_PRINT);
