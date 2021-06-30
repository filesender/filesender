<?php

/**
 * The templates/aggregate_statistics_page.php page allows the user to select which
 * graph type to display. This page is used to do that and we generate one or more
 * graphs for the given data:
 *  
 * querytype - string AggregateStatisticsQueryType
 * eventtype - string DBConstantStatsEvent
 *  
 * Note that the above parameters must resolve to valid values
 * or page loading will halt.
 * 
 * The js/graph/aggregate-statistics.js script is used to call 
 * aggregate-statistics-data-for-chart.php for each chart type.
*/

require_once('../../../includes/init.php');

if (!AggregateStatistic::enabled()) {
   echo '<p>' . Lang::tr( "feature_not_enabled" )->out() . '</p>';
} else { 

    // validate cgi parameters
    $querytype = AggregateStatisticsQueryType::validateCGIParamOrDIE($_GET["querytype"]);
    $eventtype = DBConstantStatsEvent::validateCGIParamOrDIE($_GET["eventtype"]);
    $eventNum  = DBConstantStatsEvent::lookup($eventtype);

    // prepare display strings
    $eventtypetr = Lang::tr( "statevent_$eventtype" )->out();
    $querytypetr = Lang::tr( "$querytype" )->out();

    $canvasprefix = "aggregateStatistics".$eventtype.$querytype;

    
    if ($querytype == "size_mean") {
        echo '<p>' . Lang::tr('size_shown_is_average_value_per_interval')->out() . '</p>';
    }
    if ($querytype == "size_total" && $eventNum < DBConstantStatsEvent::OFFSET_STORAGE ) {

        if( $eventtype != DBConstantStatsEvent::UPLOAD_MAXSIZE_ENDED ) {
            echo '<p>' . Lang::tr('size_shown_is_totals_per_interval')->out() . '</p>';
        }
    }
    if( $querytype == 'incomplete' ) {
        echo '<p>'.Lang::tr('aggregate_stats_graph_explain_incomplete').'</p>';
    }

    /**
     * Work out which graphs we want to display 
     */
    $a = array("15mChart"   => "fifteen_minutes"
              ,"HourChart"  => "hour"
              ,"DayChart"   => "day"
              ,"WeekChart"  => "week"
              ,"MonthChart" => "month"
              ,"YearChart"  => "year");

    if( $eventNum >= DBConstantStatsEvent::OFFSET_STORAGE
     && $eventNum <= DBConstantStatsEvent::OFFSET_STORAGE_END )
    {
        $a = array( "DayChart"  => "day"
                   ,"WeekChart"  => "week"
                   ,"MonthChart" => "month"
                   ,"YearChart"  => "year");
        
    }

    /*
     * Write the HTML for a table row for a given chart
     */
    function writeGraphTR($canvasprefix,$chartname) {
        echo '<tr><td>

              <div id="graph" class="uploadbulkgraph">
                    <div id="graphDiv" style="width:750px; height:400px; margin:0 auto">
                        <canvas id="' . $canvasprefix . $chartname . '"></canvas>
                    </div>
                </div>
            </td></tr>';
    }
       

    /*
     * Write all the graph HTML
     */
    echo '<table class="aggregatestatisticsgraph">';
    foreach( $a as $idtag => $epoch ) {
        writeGraphTR($canvasprefix,$idtag);
    }
    echo '</table>';
?>


        <script type="text/javascript" src="lib/chart.js/chart.min.js"></script>
        <script type="text/javascript" src="js/graph/aggregate-statistics.js"></script>
        <script>
         $( document ).ready(function() {
             <?php
             /*
              * Call aggregateStatisticsSetup() for each chart we have HTML for 
              */
             foreach( $a as $idtag => $epoch ) {
                 echo '   aggregateStatisticsSetup( "'.$canvasprefix.$idtag
                     .'","'.$epoch
                     .'","'.$eventtype
                     .'","'.$querytype
                     .'" );';
             }

             
             ?>
         }); 
        </script>
<?php } ?>


