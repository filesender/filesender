<?php
header('Content-Type: application/json');
require_once('../../../includes/init.php');

if (!Auth::isAdmin() && !Auth::isTenantAdmin()) {
    //Go Away
    echo json_encode(array());
    exit(0);
}
$idp = Auth::getTenantAdminIDP();

$sql = '';
$placeholders = array();
if (!$idp) {
    $sql =
        'SELECT '
       .'  SUM(case WHEN options LIKE \'%\\"encryption\\":false%\' THEN 1 ELSE 0 END) as "Unencrypted", '
       .'  SUM(case WHEN options LIKE \'%\\"encryption\\":true%\' THEN 1 ELSE 0 END) as "Encrypted" '
       .'FROM transferssizeview '
       .'WHERE (DATE(created) >= NOW() - '.DBLayer::toIntervalDays(30).') OR '
       .'      (DATE(expires) >= NOW() - '.DBLayer::toIntervalDays(30).' AND DATE(expires) <= NOW())';
} else {
    $sql =
        'SELECT '
       .'  SUM(case WHEN transferssizeview.options LIKE \'%\\"encryption\\":false%\' THEN 1 ELSE 0 END) as "Unencrypted", '
       .'  SUM(case WHEN transferssizeview.options LIKE \'%\\"encryption\\":true%\' THEN 1 ELSE 0 END) as "Encrypted" '
       .'FROM transferssizeview LEFT JOIN '.call_user_func('Authentication::getDBTable').' ON transferssizeview.userid='.call_user_func('Authentication::getDBTable').'.id '
       .'WHERE ((DATE(transferssizeview.created) >= NOW() - '.DBLayer::toIntervalDays(30).') OR '
       .'       (DATE(transferssizeview.expires) >= NOW() - '.DBLayer::toIntervalDays(30).' AND DATE(transferssizeview.expires) <= NOW())) '
       .'  AND '.call_user_func('Authentication::getDBTable').'.saml_user_identification_idp = :idp';
    $placeholders[':idp'] = $idp;
}

//error_log($sql);

$max=0;
$statement = DBI::prepare($sql);
$statement->execute($placeholders);
$row = $statement->fetch();

$data = array(
    'type' => 'doughnut',
    'data' => array(
        'labels' => array(
            Lang::tr('upload_page_graph_encryption_in_transit')->out(),
            Lang::tr('upload_page_graph_encryption_in_transit_and_rest')->out()
        ),
        'datasets' => array(
           array(
                'data' => array(
                    $row['Unencrypted'],
                    $row['Encrypted']
                ),
                'backgroundColor' => array(
                    'rgba(255, 152, 48,0.9)',
                    'rgba(150, 217, 141,0.9)'
                )
            )
        ),
    ),
    'options' => array (
        'responsive' => true,
        'maintainAspectRatio' => false,
        'plugins' => array(
            'title' => array(
                'display' => true,
                'text' => Lang::tr('encryption_split')->out()
            ),
            'legend' => array(
                'position' => 'bottom'
            )
        )
    )
);

echo json_encode($data);
//echo json_encode($data,JSON_PRETTY_PRINT);
