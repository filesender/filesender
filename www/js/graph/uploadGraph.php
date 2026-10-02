<?php
header('Content-Type: application/json');
require_once('../../../includes/init.php');

$minSz = Config::get('upload_graph_bulk_min_file_size_to_consider');

$encO = Config::get('encryption_mandatory');
$encF = 'additional_attributes LIKE \'%\"encryption\":false%\'';
$encT = 'additional_attributes LIKE \'%\"encryption\":true%\'';


if( Utilities::isTrue( Config::get('upload_graph_use_cache_table'))) {

    $sql = 'SELECT date,speed,enspeed from UploadGraphs order by date asc';
    $statement = DBI::prepare($sql);
    $statement->execute(array());
    $result = $statement->fetchAll();

} else {

    $sql =
        'SELECT days.date, speed.speed, speed.enspeed '
        . 'FROM (SELECT (SELECT Date(NOW() - INTERVAL \'30\' DAY)) + '
      . DBLayer::toIntervalDays("a+b") . ' date '
               . '        FROM (SELECT 0 a UNION SELECT 1 a UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 '
               . '                     UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 ) d, '
               . '             (SELECT 0 b UNION SELECT 10 UNION SELECT 20 UNION SELECT 30 UNION SELECT 40) m '
               . '        WHERE (SELECT Date(NOW() - INTERVAL \'30\' DAY)) + '
               . DBLayer::toIntervalDays("a+b") . ' <= (select date(now())) '
                        . '        ORDER BY a + b) as days LEFT '
                        . ' JOIN (SELECT DATE(created) as date, '
                         .($encO ? '0 as speed, ' : '   AVG(case WHEN time_taken > 0 AND ' . $encF . ' THEN size/time_taken ELSE null END) as speed, ' )
                        . '   AVG(case WHEN time_taken > 0 AND ' . $encT . ' THEN size/time_taken ELSE null END) as enspeed, '
                         .($encO ? '0 as count, ' : '   AVG(case WHEN ' . $encF . ' THEN id ELSE null END) as count, ' )
                        . '   AVG(case WHEN ' . $encT . ' THEN id ELSE null END) as encount '
                        . '       from StatLogs '
                        . '      WHERE event=\'file_uploaded\' '
                        . '            AND created>NOW() - INTERVAL \'31\' DAY '
                        . '            AND size > ' . $minSz . ' '
                        . '      GROUP BY Date) as speed on days.date=speed.date '
                        . ' ORDER BY days.date';
    
    $statement = DBI::prepare($sql);
    $statement->execute(array());
    $result = $statement->fetchAll();
}



$data = array(
    'type' => 'line',
    'data' => array(
	'labels' => array(),
	'datasets' => array(
	    array(
                'label' => Lang::tr('upload_page_graph_encryption_in_transit_and_rest')->out(),
		'data' => array(),
		'backgroundColor' => 'rgba(10,220,10,0.6)',
		'fill' => true,
		'spanGaps' => true
	    ),
	    array(
                'label' => Lang::tr('upload_page_graph_encryption_in_transit')->out(),
		'data' => array(),
		'backgroundColor' => 'rgba(255,147,02,0.6)',
		'fill' => true,
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
	        'text' => Lang::tr('upload_page_graph_title_upload_speed_of_files_over')->r('size',Utilities::formatBytes($minSz))->out()
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

$lastSpeed=array(0);
$lastEnSpeed=array(0);
//$yMax = 0;
foreach($result as $row) {
    $label = $row['date'];
    $label = preg_replace('/ 00:00:00/','',$label);
    $data['data']['labels'][]=$label;
    
    if (!$encO) {
        $row['speed']=round($row['speed']==0?(array_sum($lastSpeed)/count($lastSpeed)):($row['speed']/1048576),2);
        if ($row['speed']>0) {
            array_shift($lastSpeed);
            $lastSpeed[]=$row['speed'];
        }
        if ($row['speed']<=0) $row['speed']=null;
        $data['data']['datasets'][1]['data'][]=$row['speed'];
    }

    $row['enspeed']=round($row['enspeed']==0?(array_sum($lastEnSpeed)/count($lastEnSpeed)):($row['enspeed']/1048576),2);
    if ($row['enspeed']>0) {
        array_shift($lastEnSpeed);
        $lastEnSpeed[]=$row['enspeed'];
    }
    if ($row['enspeed']<=0) $row['enspeed']=null;
    $data['data']['datasets'][0]['data'][]=$row['enspeed'];

    //  $yMax=max($yMax,$row['EnSpeed'],$row['Speed']);
}
//$yMax=round($yMax*0.12)*10;
//$data['options']['scales']['yAxes'][0]['ticks']['max']=$yMax;

if ($encO) {
    unset($data['data']['datasets'][1]);
}

echo json_encode($data);
//echo json_encode($data,JSON_PRETTY_PRINT);
