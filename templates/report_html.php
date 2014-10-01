<?php

$lines = array();
foreach($report->logs as $entry) {
    echo '<tr>';
    
    echo '<td>'.Utilities::formatDate($entry->created).'</td>';
    
    $lid = 'report_';
    if($report->target_type == 'Recipient')
        $lid .= 'recipient_';
        
    $lid .= 'event_'.$entry->event;
    
    $action = Lang::tr($lid)->r(
        array(
            'author' => $entry->author,
            'log' => $entry
        ),
        $entry->target
    );
    
    echo '<td>'.$action.'</td>';
    
    echo '</tr>';
}
