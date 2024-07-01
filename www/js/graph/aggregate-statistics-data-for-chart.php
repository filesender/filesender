<?php
header('Content-Type: application/json');
require_once('../../../includes/init.php');

/**
 * This page writes the JSON data for a chart using the given data:
 * 
 * epochtype - string DBConstantEpochType
 * eventtype - string DBConstantStatsEvent
 * querytype - string AggregateStatisticsQueryType
 * 
 * Note that the above parameters must resolve to valid values
 * or page loading will halt.
 */

$epochtype = DBConstantEpochType::validateCGIParamOrDIE($_GET["epochtype"]);
$eventtype = DBConstantStatsEvent::validateCGIParamOrDIE($_GET["eventtype"]);
$querytype = AggregateStatisticsQueryType::validateCGIParamOrDIE($_GET["querytype"]);


//
// Prepare some variables for later use
//
$prettyPrint = false;
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


/**
 * Query the database
 */
if( $querytype == "incomplete" ) {

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
    if( $querytype == "size_mean" ) {
        $maxValue = max($maxValue,$row['sizemean']);
    } else {
        $maxValue = max($maxValue,$row['sizesum']);
    }
}
$valdiv = 1;
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

if( $querytype == "size_mean" || $querytype == "size_total" ) {
    $ylabel = Lang::tr($x_per_second)->out();
} else {
    $ylabel = Lang::tr("count")->out();
}

/**
 * The metadata for the chart itself.
 */
$data = array(
    'type' => 'line',
    'data' => array(
	'labels' => array(),
	'datasets' => array(
	    array(
		'data' => array(),
		'backgroundColor' => 'rgba(10,220,10,0.6)',
		'fill' => true,
		'spanGaps' => false
	    ),
	)
    ),
    'options' => array (
	'responsive' => true,
	'maintainAspectRatio' => false,
        'elements' => array(
            'line' => array(
                'tension' => 0,
            )
        ),
	'title' => array(
	    'display' => true,
	    'text' => Lang::tr('aggregate_stats_graph_title_per_time_interval')
                          ->r('eventtype',$eventtypetr)->r('epochtype',$epochtypetr)->out()
	),
        'plugins' => array(
                'legend' => array(
                    'position' => 'none'
                ),
        ),
	'scales' => array(
            'yAxes' => array(
                'display' => true,
                'title' => array (
                    'display' => true,
                    'text' => $ylabel
                ),
                'ticks' => array( 'min' => 0 ),
            ),
	)
    )
);

/**
 * Convert the selected data over to the right format for the graph
 *
 * This mostly consists of using $querytype to work out which column to get
 * the data from in the $result rows and putting that into data/datasets/idx
 */
foreach($result as $row) {

    $data['data']['labels'][]=$row['epoch'];
    $idx = 0;
    if( $querytype == "incomplete" ) {
        $data['data']['datasets'][$idx++]['data'][]=max(0,$row['eventcount']-$row['endcount']);
    } else if( $querytype == "count" ) {
        $data['data']['datasets'][$idx++]['data'][]=$row['eventcount'];
    } else if( $querytype == "size_mean" ) {
        $data['data']['datasets'][$idx++]['data'][]=$row['sizemean']/$valdiv;
    } else {
        $data['data']['datasets'][$idx++]['data'][]=$row['sizesum']/$valdiv;
    }
            
}

if( !$idx ) {
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
	'title' => array(
	    'display' => true,
	    'text' => Lang::tr('aggregate_stats_graph_no_data')
                          ->r('eventtype',$eventtypetr)->r('epochtype',$epochtypetr)->out()
	),
    ),
);
    
}

if( $prettyPrint ) {
    echo json_encode($data,JSON_PRETTY_PRINT);
} else { 
    echo json_encode($data);
}

?>
