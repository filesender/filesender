<h2>{tr:aggregate_statistics_page}</h2>


<?php if (!AggregateStatistic::enabled()) { ?>
    <p>{tr:feature_not_enabled}</p>
<?php } else { ?>

    <?php

    $qtypes = array('size','count','incomplete');
    foreach( $qtypes as $querytype ) {
        $events = array("upload_started","download_started");
        foreach( $events as $k => $eventtype ) {
    
            $eventtypetr = Lang::tr( "statevent_$eventtype" )->out();
            $querytypetr = Lang::tr( "$querytype" )->out();

            $canvasprefix = "aggregateStatistics".$eventtype.$querytype;
    ?>
    
        <h3><?php echo Lang::tr('aggregate_stats_graph_title_operation')
                           ->r('eventtype',$eventtypetr)->r('querytype',$querytypetr)->out() ?></h3>

        <?php
        if( $querytype == 'incomplete' ) {
            echo '<p>'.Lang::tr('aggregate_stats_graph_explain_incomplete').'</p>';
        }
        ?>
        <table class="aggregatestatisticsgraph">
            <tr><td>

                <div id="graph" class="uploadbulkgraph">
                    <div id="graphDiv" style="width:350px; height:200px; margin:0 auto">
                        <canvas id="<?php echo $canvasprefix ?>15mChart"></canvas>
                    </div>
                </div>
            </td><td>

                <div id="graph" class="uploadbulkgraph">
                    <div id="graphDiv" style="width:350px; height:200px; margin:0 auto">
                        <canvas id="<?php echo $canvasprefix ?>HourChart"></canvas>
                    </div>
                </div>
            </td></tr><tr><td>

                <div id="graph" class="uploadbulkgraph">
                    <div id="graphDiv" style="width:350px; height:200px; margin:0 auto">
                        <canvas id="<?php echo $canvasprefix ?>WeekChart"></canvas>
                    </div>
                </div>
            </td><td>

                <div id="graph" class="uploadbulkgraph">
                    <div id="graphDiv" style="width:350px; height:200px; margin:0 auto">
                        <canvas id="<?php echo $canvasprefix ?>MonthChart"></canvas>
                    </div>
                </div>
            </td></tr><tr><td>

                <div id="graph" class="uploadbulkgraph">
                    <div id="graphDiv" style="width:350px; height:200px; margin:0 auto">
                        <canvas id="<?php echo $canvasprefix ?>YearChart"></canvas>
                    </div>
                </div>
            </td></tr></table>

        <script type="text/javascript" src="{path:lib/chartjs/Chart.bundle.min.js}"></script>
        <script type="text/javascript" src="{path:js/graph/aggregate-statistics.js}"></script>
        <script>
         $( document ).ready(function() {
             <?php
             $a = array("15mChart"   => "fifteen_minutes"
                       ,"HourChart"  => "hour"
                       ,"WeekChart"  => "week"
                       ,"MonthChart" => "month"
                       ,"YearChart"  => "year");
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
<?php } ?>
<?php } ?>


