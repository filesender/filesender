<?php
header('Content-Type: application/json');
require_once('../../../includes/init.php');


// These calls to validateCGIParamOrDIE() are defensive
// and should use regex and direct lookup to ensure
// we have valid data from the _GET[]
$epochtype = DBConstantEpochType::validateCGIParamOrDIE($_GET["epochtype"]);
$eventtype = DBConstantStatsEvent::validateCGIParamOrDIE($_GET["eventtype"]);

$v = $_GET["querytype"];
if (!preg_match('`^[a-z_-]{1,40}$`', $v)) {
    Logger::haltWithErorr('invalid query type given '.$v);
}
$queryType = $v;


//
// Prepare some variables for later use
//
$eventtypetr = Lang::tr( "statevent_$eventtype" )->out();
$epochtypetr = Lang::tr( "epoch_" . $epochtype )->out();
$ended = '';
$limit = 40;
if( $epochtype == "hour" ) {
    $limit = 24;
}
if( $epochtype == "month" ) {
    $limit = 12;
}
if( $epochtype == "year" ) {
    $limit = 10;
}


//
// Query the database
//
if( $queryType == "incomplete" ) {

    $ended = $eventtype;
    $ended = preg_replace("/_started$/", "_ended", $eventtype);
    
    $statement = DBI::prepare('select vv.eventcount as endcount, agg.*  from aggregatestatisticsview agg '
                            . ' join aggregatestatisticsview vv on agg.epoch=vv.epoch and agg.epochtype = vv.epochtype '
                            . ' WHERE agg.epochtype = :epochtype'
                            . '   AND agg.eventtype = :eventtype'
                            . '   AND vv.eventtype = :ended'
                            . ' ORDER BY agg.epoch desc '
                            . ' LIMIT '.$limit.' '
    );
    $statement->execute(array(':epochtype' => DBConstantEpochType::lookup($epochtype)
                             ,':eventtype' => DBConstantStatsEvent::lookup($eventtype)
                             ,':ended' => DBConstantStatsEvent::lookup($ended) ));
    
    
} else {

    // size and count share the same query
    $statement = DBI::prepare('select * from aggregatestatisticsview '
                            . ' WHERE epochtype = :epochtype'
                            . '   AND eventtype = :eventtype'
                            . ' ORDER BY epoch desc '
                            . ' LIMIT '.$limit.' '
    );
    $statement->execute(array(':epochtype' => DBConstantEpochType::lookup($epochtype)
                             ,':eventtype' => DBConstantStatsEvent::lookup($eventtype) ));

}

$result = $statement->fetchAll();


//
// Setup the graph metadata
//
$maxValue = 0;
foreach($result as $row) {
    $maxValue = max($maxValue,$row['sizemean']);
}
$x_per_second = 'size_tb';
if( $maxValue < 1024*1024*1024*1024 ) {
    $x_per_second = 'size_gb';
    $valdiv = 1024*1024*1024;
}
if( $maxValue < 1024*1024*1024 ) {
    $x_per_second = 'size_mb';
    $valdiv = 1024*1024;
}
if( $maxValue < 1024*1024 ) {
    $x_per_second = 'size_kb';
    $valdiv = 1024;
}

if( $queryType == "size" ) {
    $ylabel = Lang::tr($x_per_second)->out();
} else {
    $ylabel = Lang::tr("count")->out();
}

$data = array(
    'type' => 'line',
    'data' => array(
	'labels' => array(),
	'datasets' => array(
	    array(
		'data' => array(),
		'backgroundColor' => 'rgba(10,220,10,0.6)',
		'spanGaps' => false
	    ),
	)
    ),
    'options' => array (
	'responsive' => true,
	'maintainAspectRatio' => false,
	'title' => array(
	    'display' => true,
	    'text' => Lang::tr('aggregate_stats_graph_title_per_time_interval')
                          ->r('eventtype',$eventtypetr)->r('epochtype',$epochtypetr)->out()
	),
	'legend' => array(
            'position' => 'none'
	),
	'scales' => array(
	    'yAxes' => array(
		array(
		    'ticks' => array( 'min' => 0 ),
                    'scaleLabel' => array( 'display' => true,
                                            'labelString' => $ylabel,
                                         ),
		),
	    ),
            'xAxes' => array(
                array(
                    'type' => 'time',
		    'ticks' => array( 'min' => 0 ),
                ),
            ),
	)
    )
);

//
// Convert the selected data over to the right format for the graph
//
foreach($result as $row) {

    $data['data']['labels'][]=$row['epoch'];
    $idx = 0;
    if( $queryType == "incomplete" ) {
        $data['data']['datasets'][$idx++]['data'][]=max(0,$row['eventcount']-$row['endcount']);
    } else if( $queryType == "count" ) {
        $data['data']['datasets'][$idx++]['data'][]=$row['eventcount'];
    } else {
        $data['data']['datasets'][$idx++]['data'][]=$row['sizemean']/$valdiv;
    }
            
}

echo json_encode($data/*,JSON_PRETTY_PRINT*/);
//echo json_encode($data,JSON_PRETTY_PRINT);

?>
