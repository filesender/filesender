<?php
require_once('../../../includes/init.php');

if (!Auth::isAdmin() && !Auth::isTenantAdmin()) {
    //Go Away
    exit(0);
}

$idp = Auth::getTenantAdminIDP();
$pagelimit=Config::get('statistics_table_rows_per_page');

$topic = Utilities::arrayKeyOrDefaultString( $_GET, 't' );
$topic = Utilities::filter_regex( $topic, Utilities::FILTER_REGEX_PLAIN_STRING_UNDERSCORE );
if( !$topic || $topic == '' ) {
    Logger::haltWithErorr('nefarious activity suspected: attempt made on statistics_page without valid topic!');
}
$start = Utilities::arrayKeyOrDefault( $_GET, 'start', 0, FILTER_VALIDATE_INT );
$sort = Utilities::arrayKeyOrDefault( $_GET, 'sort', '' );
$sortdirection = Utilities::arrayKeyOrDefault( $_GET, 'sortdirection', FILTER_VALIDATE_INT ) ? 'DESC' : 'ASC';

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

switch ($topic) {
    case 'top_users':
        if (!in_array($sort,['User','Transfers','Size','Downloads']))
            $sort='User';
        echo '<tr sort="'.$sort.'"><th sort="User">'.Lang::translate('admin_users_section').'</th><th sort="Transfers">'.Lang::translate('admin_transfers_section').'</th><th sort="Size">'.Lang::translate('size').'</th><th sort="Downloads">'.Lang::translate('downloads').'</th></tr>'."\n";
        $sql=
            'SELECT '
           .'  t.user_email as "User", '
           .'  COUNT(DISTINCT t.id) AS "Transfers", '
           . ' SUM('.DBLayer::IF('(t.options LIKE \'%\\"encryption\\":true%\')','f.encrypted_size','f.size') . ') as "Size", '
           .'  SUM(t.download_count) as "Downloads" '
           .'FROM '
           .'  '.call_user_func('Transfer::getDBTable').' t JOIN '.call_user_func('File::getDBTable').' f ON f.transfer_id=t.id '
           .((!$idp) ?
             ''
             :
             'LEFT JOIN '.call_user_func('User::getDBTable').' u ON t.userid=u.id LEFT JOIN authidpview a ON u.authid=a.id '
           )
           .'WHERE '
           .((!$idp) ?
             ''
             :
             'a.idpid = :idp AND '
           )
           .'    ((DATE(t.created) >= NOW() - '.DBLayer::toIntervalDays(30).') OR '
           .'     (DATE(t.expires) >= NOW() - '.DBLayer::toIntervalDays(30).' AND DATE(t.expires) <= NOW())) '
                                                        ."    AND t.status = 'available' "
           .' GROUP BY t.user_email      '
           .' ORDER BY '.$sort.' '.$sortdirection
           .' LIMIT  '.$pagelimit
           .' OFFSET '.$start;
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
        if (!in_array($sort,['User','Transfers','Size','Downloads']))
            $sort='User';
        echo '<tr sort="'.$sort.'"><th sort="User">'.Lang::translate('admin_users_section').'</th><th sort="Transfers">'.Lang::translate('admin_transfers_section').'</th><th sort="Size">'.Lang::translate('size').'</th><th sort="Downloads">'.Lang::translate('downloads').'</th></tr>'."\n";
        $sql=
            'SELECT '
           .'  t.user_email as "User", '
           .'  COUNT(DISTINCT t.id) AS "Transfers", '
          . ' SUM('.DBLayer::IF('(t.options LIKE \'%\\"encryption\\":true%\')','f.encrypted_size','f.size') . ') as "Size", '
           .'  SUM(t.download_count) as "Downloads" '
           .'FROM '
           .'  '.call_user_func('Transfer::getDBTable').' t JOIN '.call_user_func('File::getDBTable').' f ON f.transfer_id=t.id '
           .((!$idp) ?
             ''
             :
             'LEFT JOIN '.call_user_func('User::getDBTable').' u ON t.userid=u.id LEFT JOIN authidpview a ON u.authid=a.id '
           )
           .'WHERE '
           .((!$idp) ?
             ''
             :
             'a.idpid = :idp AND '
           )
           .'    ((DATE(t.created) >= NOW() - '.DBLayer::toIntervalDays(30).') OR '
           .'     (DATE(t.expires) >= NOW() - '.DBLayer::toIntervalDays(30).' AND DATE(t.expires) <= NOW())) '
           .' GROUP BY t.user_email         '
           .' ORDER BY '.$sort.' '.$sortdirection
           .' LIMIT  '.$pagelimit
           .' OFFSET '.$start;
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
        if (!in_array($sort,['mime_type','total']))
            $sort='total';
        echo '<tr sort="'.$sort.'"><th sort="mime_type">'.Lang::translate('mime_types').'</th><th sort="total">'.Lang::translate('count').'</th></tr>'."\n";
        $sql=
            'SELECT '
           .'  f.mime_type as mime_type, count(f.mime_type) as total '
           .'FROM '
           .'  filesbywhoview f '
           .((!$idp) ?
             ''
             :
             'LEFT JOIN '.call_user_func('User::getDBTable').' u ON f.userid=u.id LEFT JOIN authidpview a ON u.authid=a.id '
           )
           .'WHERE '
           .((!$idp) ?
             ''
             :
             'a.idpid = :idp AND '
           )
           .'    ((DATE(f.created) >= NOW() - '.DBLayer::toIntervalDays(30).') OR '
           .'     (DATE(f.expires) >= NOW() - '.DBLayer::toIntervalDays(30).' AND DATE(f.expires) <= NOW())) '
           .'GROUP BY mime_type '
           .'ORDER BY '.$sort.' '.$sortdirection
           .' LIMIT  '.$pagelimit
           .' OFFSET '.$start;
        $placeholders=array();
        if ($idp)
            $placeholders[':idp'] = $idp;

        //error_log($sql);

        $statement = DBI::prepare($sql);
        $statement->execute($placeholders);
        $result = $statement->fetchAll();
        $i=$start;
        foreach($result as $row) {
            echo '<tr data-row="'.$i.'"><td>'.$row['mime_type'].'</td><td>'.number_format($row['total']).'</td></tr>'."\n";
            $i++;
        }
        for($i-=$start;$i<$pagelimit;$i++) {
            echo '<tr class="blank_row" data-row="'.($i+$start).'"data-row-blank="1"><td>&nbsp;</td><td></td></tr>'."\n";
        }
        break;

    case 'users_with_api_keys':
        if (!in_array($sort,['User','Date']))
            $sort='Date';
        echo '<tr sort="'.$sort.'"><th sort="User">'.Lang::translate('admin_users_section').'</th><th sort="Date">'.Lang::translate('date').'</th></tr>'."\n";
        $sql=
            'SELECT '
           .'  a.saml_user_identification_uid as "User", '
           .'  DATE(u.auth_secret_created) as "Date" '
           .'FROM '
           .'  authidpview a LEFT JOIN '.call_user_func('User::getDBTable').' u on a.id=u.authid '
           .'WHERE '
           .'  u.auth_secret IS NOT NULL '
           .((!$idp) ?
             ''
             :
             'AND a.idpid = :idp '
           )
           .'ORDER BY '.$sort.' '.$sortdirection
           .' LIMIT  '.$pagelimit
           .' OFFSET '.$start
        ;
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
        $createdDD = DBLayer::datediff('NOW()','firsttransfer');
        $sql=
            'SELECT '
           .'  additional_attributes, speed, gspeed, avgsize, minsize, maxsize, transfered, count, firsttransfer, os_name, browser_name, is_encrypted, '
           .'  (CASE WHEN '.$createdDD.' > 0 THEN count/'.$createdDD.' ELSE NULL END) as countperday '
           .'FROM '
           .'  browserstatsview '
           //.'ORDER BY count DESC, maxsize DESC '
           .' LIMIT  '.$pagelimit
           .' OFFSET '.$start;

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
                    echo Template::Q($row['browser']);
                }
                echo '</td>';
                echo '<td>';
                if (empty($row['os']))  {
                    echo 'Unknown';
                } else {
                    echo Template::Q($row['os']);
                }
                echo '</td>';
                echo '<td>';
                if ($row['additional_attributes'] === '{"encryption":true}')  {
                    echo is_encrypted_to_html(1);
                }
                elseif ($row['additional_attributes'] === '{"encryption":false}') {
                    echo is_encrypted_to_html(0);
                } else {
                    echo Template::Q($row['additional_attributes']);
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
