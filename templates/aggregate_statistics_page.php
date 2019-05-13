<h2>{tr:aggregate_statistics_page}</h2>
<?php
/**
 * This page creates a selection menu with many options that store
 * a querytype and eventtype to pass down to create a collection
 * of charts for a specific data set.
 * 
 * The aggregate_statistics_page_of_graphs_for_type.php is used to create one
 * or more charts and the graphs div is updated when the user makes a new selection
 * to show these new charts.
 */
?>

<?php if (!AggregateStatistic::enabled()) { ?>
    <p>{tr:feature_not_enabled}</p>
<?php } else { ?>

    <form>
        <select id="select" name="graphtype">
        <option >--- Please select a graph type ---</option>
            
    <?php

    $qtypes = AggregateStatisticsQueryType::all();
    foreach( $qtypes as $querytype ) {

        //
        // This is a subset of DBConstantStatsEvent::all()
        //
        $events = array( DBConstantStatsEvent::UPLOAD_STARTED
                       , DBConstantStatsEvent::DOWNLOAD_STARTED
                       , DBConstantStatsEvent::UPLOAD_NOCRYPT_STARTED
                       , DBConstantStatsEvent::DOWNLOAD_NOCRYPT_STARTED
                       , DBConstantStatsEvent::UPLOAD_ENCRYPTED_STARTED
                       , DBConstantStatsEvent::DOWNLOAD_ENCRYPTED_STARTED
                       , DBConstantStatsEvent::UPLOAD_MAXSIZE_ENDED
        );
        foreach( $events as $k => $eventtype ) {

            $eventtypetr = Lang::tr( "statevent_$eventtype" )->out();
            $querytypetr = Lang::tr( "$querytype" )->out();

            $desc = Lang::tr('aggregate_stats_graph_title_operation')
                        ->r('eventtype',$eventtypetr)
                        ->r('querytype',$querytypetr)
                        ->out();

            //
            // we only show size_total for max size graphs.
            //
            if( $eventtype == DBConstantStatsEvent::UPLOAD_MAXSIZE_ENDED ) {
                if( $querytype != "size_total" ) {
                    continue;
                }
                $desc = Lang::tr('aggregate_stats_'.$eventtype)->out();
            }
            
            $v = $querytype . ',' . $eventtype;
            
            echo '<option value="' . $v 
              .  '" data-eventtype="'.$eventtype
              .  '" data-querytype="'.$querytype
              .  '" >' . $desc . '</option>';
        }
        
        //
        // This is a subset of DBConstantStatsEvent::all()
        //
        // the storage graphs are presented slightly differently to
        // the above more general graphs and are restricted to only
        // size_total queries
        //
        $events = array( DBConstantStatsEvent::STORAGE_EXPIRED_TRANSFERS_SIZE
                       , DBConstantStatsEvent::STORAGE_FREE_SIZE
        );
        foreach( $events as $k => $eventtype ) {

            if( $querytype == "size_total" ) {
                $eventtypetr = Lang::tr( "statevent_$eventtype" )->out();
                $querytypetr = Lang::tr( "$querytype" )->out();

                $desc = Lang::tr('aggregate_stats_'.$eventtype)->out();
                $v = $querytype . ',' . $eventtype;
                
                echo '<option value="' . $v 
                  .  '" data-eventtype="'.$eventtype
                  .  '" data-querytype="'.$querytype
                  .  '" >' . $desc . '</option>';
            }
        }
    }
    ?>
    </select></form>

    <div id="graph"></div>

    <!--
         When the select form is changed we update the core of the
         page to show the graph the user has selected. The event
         and query type are passed to us through data fields.
       -->
    <script>
     $( document ).ready(function() {
         // Change the graph div to show what the user has selected
         $('select').change(function(){
             var selected = $(this).find('option:selected');
             var eventtype = selected.data('eventtype'); 
             var querytype = selected.data('querytype');
             if( !querytype || !eventtype ) {
                 $('#graph').text('');
                 return;
             }
             $('#graph').load('js/graph/aggregate_statistics_page_of_graphs_for_type.php?'
                            + 'eventtype='  + eventtype
                            + '&querytype=' + querytype
             );
         });
     });
    </script>

    
<?php } ?>


