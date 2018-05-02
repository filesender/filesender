<?php
header('Content-Type: application/json');
require_once('../../../includes/init.php');

$minSz = Config::get('upload_graph_bulk_min_file_size_to_consider');

$encF = 'additional_attributes LIKE \'%\"encryption\":false%\'';
$encT = 'additional_attributes LIKE \'%\"encryption\":true%\'';

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
  . '   AVG(case WHEN time_taken > 0 AND ' . $encF . ' THEN size/time_taken ELSE null END) as speed, '
  . '   AVG(case WHEN time_taken > 0 AND ' . $encT . ' THEN size/time_taken ELSE null END) as enspeed, '
  . '   AVG(case WHEN ' . $encF . ' THEN id ELSE null END) as count, '
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

$data = array(
    'type' => 'line',
    'data' => array(
	'labels' => array(),
	'datasets' => array(
	    array(
		'label' => 'Encryption in transit & rest',
		'data' => array(),
		'backgroundColor' => 'rgba(10,220,10,0.6)',
		'spanGaps' => true
	    ),
	    array(
		'label' => 'Encryption in transit',
		'data' => array(),
		'backgroundColor' => 'rgba(255,147,02,0.6)',
		'spanGaps' => true
	    )
	)
    ),
    'options' => array (
	'responsive' => true,
	'maintainAspectRatio' => false,
	'title' => array(
	    'display' => true,
	    'text' => 'Global Average Upload Speed of Files over ' . Utilities::formatBytes($minSz)
	),
	'legend' => array(
	    'position' => 'bottom'
	),
	'scales' => array(
	    'yAxes' => array(
		array(
		    'ticks' => array( 'min' => 0 ),
                    'scaleLabel' => array( 'display' => true,
                                            'labelString' => 'MB/sec'
                                         ),
		),
                
	    )
	)
    )
);

$lastSpeed=array(0);
$lastEnSpeed=array(0);
//$yMax = 0;
foreach($result as $row) {

    $row['speed']=round($row['speed']==0?(array_sum($lastSpeed)/count($lastSpeed)):($row['speed']/1048576),2);
    $row['enspeed']=round($row['enspeed']==0?(array_sum($lastEnSpeed)/count($lastEnSpeed)):($row['enspeed']/1048576),2);

    if ($row['speed']>0) { 
	array_shift($lastSpeed);
	$lastSpeed[]=$row['speed'];
    }
    if ($row['enspeed']>0) {
	array_shift($lastEnSpeed);
	$lastEnSpeed[]=$row['enspeed'];
    }

    if ($row['speed']<=0) $row['speed']=null;
    if ($row['enspeed']<=0) $row['enspeed']=null;

    $data['data']['labels'][]=$row['date'];
    $data['data']['datasets'][0]['data'][]=$row['enspeed'];
    $data['data']['datasets'][1]['data'][]=$row['speed'];
    
    //	$yMax=max($yMax,$row['EnSpeed'],$row['Speed']);
}
//$yMax=round($yMax*0.12)*10;
//$data['options']['scales']['yAxes'][0]['ticks']['max']=$yMax;

echo json_encode($data/*,JSON_PRETTY_PRINT*/);

?>
