<?php
require_once('../../../includes/init.php');

if (!Auth::isAdmin() && !Auth::isTenantAdmin()) {
    //Go Away
    exit(0);
}

$idp = Auth::getTenantAdminIDP();
$pagelimit=Config::get('statistics_table_rows_per_page');

if (!array_key_exists('t', $_GET))
    exit(0);

$start = array_key_exists('start', $_GET) ? $_GET['start'] : 0;

switch ($_GET['t']) {
    case 'top_users':
        echo '<tr><th>'.Lang::translate('admin_users_section').'</th><th>'.Lang::translate('admin_transfers_section').'</th><th>'.Lang::translate('size').'</th><th>'.Lang::translate('downloads').'</th></tr>'."\n";
        $sql=
            'SELECT '
           .'  '.call_user_func('Transfer::getDBTable').'.user_email as "User", '
           .'  COUNT(DISTINCT '.call_user_func('Transfer::getDBTable').'.id) AS "Transfers", '
           .'  SUM(IF('.call_user_func('Transfer::getDBTable').'.options LIKE \'%\\"encryption\\":true%\','.call_user_func('File::getDBTable').'.encrypted_size,'.call_user_func('File::getDBTable').'.size)) AS "Size", '
           .'  SUM('.call_user_func('Transfer::getDBTable').'.download_count) as "Downloads" '
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
           .'LIMIT '.$start.', '.$pagelimit;
        $placeholders=array();
        if ($idp!==false)
            $placeholders[':idp'] = $idp;

        //error_log($sql);

        $statement = DBI::prepare($sql);
        $statement->execute($placeholders);
        $result = $statement->fetchAll();
        $i=$start;
        foreach($result as $row) {
            echo '<tr data-row="'.$i.'"><td>'.$row['User'].'</td><td>'.$row['Transfers'].'</td><td>'.Utilities::formatBytes($row['Size']).'</td><td>'.$row['Downloads'].'</td></tr>'."\n";
            $i++;
        }
        for($i-=$start;$i<$pagelimit;$i++) {
            echo '<tr data-row="'.($i+$start).'" data-row-blank="1"><td>&nbsp;</td><td></td><td></td><td></td></tr>'."\n";
        }
        break;

    case 'transfer_per_user':
        echo '<tr><th>'.Lang::translate('admin_users_section').'</th><th>'.Lang::translate('admin_transfers_section').'</th><th>'.Lang::translate('size').'</th><th>'.Lang::translate('downloads').'</th></tr>'."\n";
        $sql=
            'SELECT '
           .'  '.call_user_func('Transfer::getDBTable').'.user_email as "User", '
           .'  COUNT(DISTINCT '.call_user_func('Transfer::getDBTable').'.id) AS "Transfers", '
           .'  SUM(IF('.call_user_func('Transfer::getDBTable').'.options LIKE \'%\\"encryption\\":true%\','.call_user_func('File::getDBTable').'.encrypted_size,'.call_user_func('File::getDBTable').'.size)) AS "Size", '
           .'  SUM('.call_user_func('Transfer::getDBTable').'.download_count) as "Downloads" '
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
           .'LIMIT '.$start.', '.$pagelimit;
        $placeholders=array();
        if ($idp!==false)
            $placeholders[':idp'] = $idp;

        //error_log($sql);

        $statement = DBI::prepare($sql);
        $statement->execute($placeholders);
        $result = $statement->fetchAll();
        $i=$start;
        foreach($result as $row) {
            echo '<tr data-row="'.$i.'"><td>'.$row['User'].'</td><td>'.$row['Transfers'].'</td><td>'.Utilities::formatBytes($row['Size']).'</td><td>'.$row['Downloads'].'</td></tr>'."\n";
            $i++;
        }
        for($i-=$start;$i<$pagelimit;$i++) {
            echo '<tr data-row="'.($i+$start).'" data-row-blank="1"><td>&nbsp;</td><td></td><td></td><td></td></tr>'."\n";
        }
        break;

    case 'mime_types':
        echo '<tr><th>'.Lang::translate('mime_types').'</th><th></th></tr>'."\n";
        $sql=
            'SELECT '
           .'  mime_type as "Mime Type", count(*) as Total '
           .'FROM '
           .'  filesbywhoview LEFT JOIN '.call_user_func('Authentication::getDBTable').' on filesbywhoview.userid='.call_user_func('Authentication::getDBTable').'.id '
           .'WHERE '
           .(($idp===false) ?
             ''
             :
             call_user_func('Authentication::getDBTable').'.saml_user_identification_idp = :idp AND '
           )
           .'    ((DATE(filesbywhoview.created) >= NOW() - '.DBLayer::toIntervalDays(30).') OR '
           .'     (DATE(filesbywhoview.expires) >= NOW() - '.DBLayer::toIntervalDays(30).' AND DATE(filesbywhoview.expires) <= NOW())) '
           .'GROUP BY mime_type '
           .'ORDER BY Total DESC '
           .'LIMIT '.$start.', '.$pagelimit;
        $placeholders=array();
        if ($idp!==false)
            $placeholders[':idp'] = $idp;

        //error_log($sql);

        $statement = DBI::prepare($sql);
        $statement->execute($placeholders);
        $result = $statement->fetchAll();
        $i=$start;
        foreach($result as $row) {
            echo '<tr data-row="'.$i.'"><td>'.$row['Mime Type'].'</td><td>'.$row['Total'].'</td></tr>'."\n";
            $i++;
        }
        for($i-=$start;$i<$pagelimit;$i++) {
            echo '<tr data-row="'.($i+$start).'"data-row-blank="1"><td>&nbsp;</td><td></td></tr>'."\n";
        }
        break;

    case 'users_with_api_keys':
        echo '<tr><th>'.Lang::translate('admin_users_section').'</th><th>'.Lang::translate('date').'</th></tr>'."\n";
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
           .'ORDER BY Date DESC '
           .'LIMIT '.$start.', '.$pagelimit;
        $placeholders=array();
        if ($idp!==false)
            $placeholders[':idp'] = $idp;

        //error_log($sql);

        $statement = DBI::prepare($sql);
        $statement->execute($placeholders);
        $result = $statement->fetchAll();
        $i=$start;
        foreach($result as $row) {
            echo '<tr data-row="'.$i.'"><td>'.$row['User'].'</td><td>'.$row['Date'].'</td></tr>'."\n";
            $i++;
        }
        for($i-=$start;$i<$pagelimit;$i++) {
            echo '<tr data-row="'.($i+$start).'"data-row-blank="1"><td>&nbsp;</td><td></td></tr>'."\n";
        }
        break;
}
