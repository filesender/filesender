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

function os_name_to_html( $v ) {
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

function browser_name_to_html( $v ) {
    if( $v == 'Edge' )              return '<i class="fa fa-edge"></i> Edge';
    if( $v == 'Internet Explorer' ) return '<i class="fa fa-internet-explorer"></i> Internet Explorer';
    if( $v == 'Mozilla Firefox' )   return '<i class="fa fa-firefox"></i> Mozilla Firefox';
    if( $v == 'Opera' )             return '<i class="fa fa-opera"></i> Opera';
    if( $v == 'Google Chrome' )     return '<i class="fa fa-chrome"></i> Google Chrome';
    if( $v == 'Apple Safari' )      return '<i class="fa fa-safari"></i> Apple Safari';
    if( $v == 'Outlook'     )       return '<i class="fa "></i> Outlook';
    return $v;
}

function is_encrypted_to_html( $v ) {
    if( $v == '1' )
        return '<i class="fa fa-lock"></i>';
    return '<i class="fa fa-unlock"></i>';
}

switch ($_GET['t']) {
    case 'top_users':
        echo '<tr><th>'.Lang::translate('admin_users_section').'</th><th>'.Lang::translate('admin_transfers_section').'</th><th>'.Lang::translate('size').'</th><th>'.Lang::translate('downloads').'</th></tr>'."\n";
        $sql=
            'SELECT '
           .'  t.user_email as "User", '
           .'  COUNT(DISTINCT t.id) AS "Transfers", '
           .'  SUM(IF(t.options LIKE \'%\\"encryption\\":true%\',f.encrypted_size,f.size)) AS "Size", '
           .'  SUM(t.download_count) as "Downloads" '
           .'FROM '
           .'  '.call_user_func('Transfer::getDBTable').' t JOIN '.call_user_func('File::getDBTable').' f ON f.transfer_id=t.id '
           .((!$idp) ?
             ''
             :
             'LEFT JOIN '.call_user_func('User::getDBTable').' u ON t.userid=u.id LEFT JOIN '.call_user_func('Authentication::getDBTable').' a ON u.authid=a.id '
           )
           .'WHERE '
           .((!$idp) ?
             ''
             :
             'a.saml_user_identification_idp = :idp AND '
           )
           .'    ((DATE(t.created) >= NOW() - '.DBLayer::toIntervalDays(30).') OR '
           .'     (DATE(t.expires) >= NOW() - '.DBLayer::toIntervalDays(30).' AND DATE(t.expires) <= NOW())) '
           .'    AND t.status = "available" '
           .'GROUP BY t.user_email '
           .'ORDER BY Transfers DESC '
           .'LIMIT '.$start.', '.$pagelimit;
        $placeholders=array();
        if ($idp)
            $placeholders[':idp'] = $idp;

        //error_log($sql);

        $statement = DBI::prepare($sql);
        $statement->execute($placeholders);
        $result = $statement->fetchAll();
        $i=$start;
        foreach($result as $row) {
            echo '<tr data-row="'.$i.'"><td>'.$row['User'].'</td><td>'.number_format($row['Transfers']).'</td><td>'.Utilities::formatBytes($row['Size']).'</td><td>'.number_format($row['Downloads']).'</td></tr>'."\n";
            $i++;
        }
        for($i-=$start;$i<$pagelimit;$i++) {
            echo '<tr class="blank_row" data-row="'.($i+$start).'" data-row-blank="1"><td>&nbsp;</td><td></td><td></td><td></td></tr>'."\n";
        }
        break;

    case 'transfer_per_user':
        echo '<tr><th>'.Lang::translate('admin_users_section').'</th><th>'.Lang::translate('admin_transfers_section').'</th><th>'.Lang::translate('size').'</th><th>'.Lang::translate('downloads').'</th></tr>'."\n";
        $sql=
            'SELECT '
           .'  t.user_email as "User", '
           .'  COUNT(DISTINCT t.id) AS "Transfers", '
           .'  SUM(IF(t.options LIKE \'%\\"encryption\\":true%\',f.encrypted_size,f.size)) AS "Size", '
           .'  SUM(t.download_count) as "Downloads" '
           .'FROM '
           .'  '.call_user_func('Transfer::getDBTable').' t JOIN '.call_user_func('File::getDBTable').' f ON f.transfer_id=t.id '
           .((!$idp) ?
             ''
             :
             'LEFT JOIN '.call_user_func('User::getDBTable').' u ON t.userid=u.id LEFT JOIN '.call_user_func('Authentication::getDBTable').' a ON u.authid=a.id '
           )
           .'WHERE '
           .((!$idp) ?
             ''
             :
             'a.saml_user_identification_idp = :idp AND '
           )
           .'    ((DATE(t.created) >= NOW() - '.DBLayer::toIntervalDays(30).') OR '
           .'     (DATE(t.expires) >= NOW() - '.DBLayer::toIntervalDays(30).' AND DATE(t.expires) <= NOW())) '
           .'GROUP BY t.user_email '
           .'ORDER BY Transfers DESC '
           .'LIMIT '.$start.', '.$pagelimit;
        $placeholders=array();
        if ($idp)
            $placeholders[':idp'] = $idp;

        //error_log($sql);

        $statement = DBI::prepare($sql);
        $statement->execute($placeholders);
        $result = $statement->fetchAll();
        $i=$start;
        foreach($result as $row) {
            echo '<tr data-row="'.$i.'"><td>'.$row['User'].'</td><td>'.number_format($row['Transfers']).'</td><td>'.Utilities::formatBytes($row['Size']).'</td><td>'.number_format($row['Downloads']).'</td></tr>'."\n";
            $i++;
        }
        for($i-=$start;$i<$pagelimit;$i++) {
            echo '<tr class="blank_row" data-row="'.($i+$start).'" data-row-blank="1"><td>&nbsp;</td><td></td><td></td><td></td></tr>'."\n";
        }
        break;

    case 'mime_types':
        echo '<tr><th>'.Lang::translate('mime_types').'</th><th></th></tr>'."\n";
        $sql=
            'SELECT '
           .'  mime_type as "Mime Type", count(*) as Total '
           .'FROM '
           .'  filesbywhoview LEFT JOIN '.call_user_func('Authentication::getDBTable').' a on filesbywhoview.userid=a.id '
           .'WHERE '
           .((!$idp) ?
             ''
             :
             'a.saml_user_identification_idp = :idp AND '
           )
           .'    ((DATE(filesbywhoview.created) >= NOW() - '.DBLayer::toIntervalDays(30).') OR '
           .'     (DATE(filesbywhoview.expires) >= NOW() - '.DBLayer::toIntervalDays(30).' AND DATE(filesbywhoview.expires) <= NOW())) '
           .'GROUP BY mime_type '
           .'ORDER BY Total DESC '
           .'LIMIT '.$start.', '.$pagelimit;
        $placeholders=array();
        if ($idp)
            $placeholders[':idp'] = $idp;

        //error_log($sql);

        $statement = DBI::prepare($sql);
        $statement->execute($placeholders);
        $result = $statement->fetchAll();
        $i=$start;
        foreach($result as $row) {
            echo '<tr data-row="'.$i.'"><td>'.$row['Mime Type'].'</td><td>'.number_format($row['Total']).'</td></tr>'."\n";
            $i++;
        }
        for($i-=$start;$i<$pagelimit;$i++) {
            echo '<tr class="blank_row" data-row="'.($i+$start).'"data-row-blank="1"><td>&nbsp;</td><td></td></tr>'."\n";
        }
        break;

    case 'users_with_api_keys':
        echo '<tr><th>'.Lang::translate('admin_users_section').'</th><th>'.Lang::translate('date').'</th></tr>'."\n";
        $sql=
            'SELECT '
           .'  a.saml_user_identification_uid as "User", '
           .'  DATE(u.auth_secret_created) as "Date" '
           .'FROM '
           .'  '.call_user_func('Authentication::getDBTable').' a LEFT JOIN '.call_user_func('User::getDBTable').' u on a.id=u.authid '
           .'WHERE '
           .'  u.auth_secret IS NOT NULL '
           .((!$idp) ?
             ''
             :
             'AND a.saml_user_identification_idp = :idp '
           )
           .'ORDER BY Date DESC '
           .'LIMIT '.$start.', '.$pagelimit;
        $placeholders=array();
        if ($idp)
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
            echo '<tr class="blank_row" data-row="'.($i+$start).'"data-row-blank="1"><td>&nbsp;</td><td></td></tr>'."\n";
        }
        break;

    case 'browser_stats':
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
LIMIT $start, $pagelimit
EOF;

        $statement = DBI::prepare($sql);
        $statement->execute(array());
        $result = $statement->fetchAll();

        echo '<thead class="thead-light"><tr><th>Browser</th><th>OS</th><th>Encrypted</th><th>Average Speed</th><th>Average Speed of &gt;1GB</th><th>Min Size</th><th>Average Size</th><th>Max Size</th><th>Transfered</th><th>File Transfers</th><th>Average Transfers per Day</th></tr></thead>';
        $i=$start;
        foreach($result as $row) {
            echo '<tr data-row="'.$i.'">';
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
            echo '<td>'.number_format($row['count']).'</td>';
            echo '<td>'.number_format(round($row['countperday'])).'</td>';
            echo '</tr>';
            $i++;

            //echo '<tr><td colspan="11">'.nl2br(str_replace(' ','&nbsp;',json_encode($a,JSON_PRETTY_PRINT))).'</td></tr>';
        }
        for($i-=$start;$i<$pagelimit;$i++) {
            echo '<tr class="blank_row" data-row="'.($i+$start).'"data-row-blank="1"><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>'."\n";
        }
        break;
}
