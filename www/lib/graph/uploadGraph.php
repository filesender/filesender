<?php
header('Content-Type: application/json');
require_once('../../../includes/init.php');

$minSz = Config::get('upload_graph_bulk_min_file_size_to_consider');

// FIXME:MYSQLONLY
$statement = DBI::prepare(
    'SELECT Days.Date, Speed.Speed, Speed.EnSpeed '
  . 'FROM (SELECT (SELECT Date(NOW() - INTERVAL 30 DAY)) + INTERVAL a + b DAY Date '
  . '        FROM (SELECT 0 a UNION SELECT 1 a UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 '
  . '                     UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 ) d, '
  . '             (SELECT 0 b UNION SELECT 10 UNION SELECT 20 UNION SELECT 30 UNION SELECT 40) m '
  . '        WHERE (SELECT Date(NOW() - INTERVAL 30 DAY)) + INTERVAL a + b DAY <= (select date(now())) '
  . '        ORDER BY a + b) as Days LEFT '
  . ' JOIN (SELECT DATE(created) as Date, '
  . '              AVG(IF(LOCATE("\"encryption\":true",additional_attributes)=0,size/time_taken,NULL)) as Speed, '
  . '              AVG(IF(LOCATE("\"encryption\":true",additional_attributes)<>0,size/time_taken,NULL)) as EnSpeed, '
  . '              COUNT(IF(LOCATE("\"encryption\":true",additional_attributes)=0,id,NULL)) as Count, '
  . '              COUNT(IF(LOCATE("\"encryption\":true",additional_attributes)<>0,id,NULL)) as EnCount '
  . '       from StatLogs '
  . '      WHERE event="file_uploaded" '
  . '            AND created>NOW() - INTERVAL 31 DAY '
  . '            AND size > ' . $minSz . ' '
  . '      GROUP BY Date) as Speed ON Days.Date=Speed.Date '
  . ' ORDER BY Days.Date');

$statement->execute(array(':id' => 'pointless'));
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
                    'scaleLabel' => array( display => true,
                                            labelString => 'MB/sec'
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

    $row['Speed']=round($row['Speed']==0?(array_sum($lastSpeed)/count($lastSpeed)):($row['Speed']/1048576),2);
    $row['EnSpeed']=round($row['EnSpeed']==0?(array_sum($lastEnSpeed)/count($lastEnSpeed)):($row['EnSpeed']/1048576),2);

    if ($row['Speed']>0) { 
	array_shift($lastSpeed);
	$lastSpeed[]=$row['Speed'];
    }
    if ($row['EnSpeed']>0) {
	array_shift($lastEnSpeed);
	$lastEnSpeed[]=$row['EnSpeed'];
    }

    if ($row['Speed']<=0) $row['Speed']=null;
    if ($row['EnSpeed']<=0) $row['EnSpeed']=null;

    $data['data']['labels'][]=$row['Date'];
    $data['data']['datasets'][0]['data'][]=$row['EnSpeed'];
    $data['data']['datasets'][1]['data'][]=$row['Speed'];
    
    //	$yMax=max($yMax,$row['EnSpeed'],$row['Speed']);
}
//$yMax=round($yMax*0.12)*10;
//$data['options']['scales']['yAxes'][0]['ticks']['max']=$yMax;

echo json_encode($data/*,JSON_PRETTY_PRINT*/);

?>
