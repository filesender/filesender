<?php


$lines = array();

foreach($report->logs as $entry) {
    $date = Utilities::formatDate($entry->created, true);
    
    $lid = 'report_';
    if($report->target_type == 'Recipient')
        $lid .= 'recipient_';
    
    if($entry->author_type == 'Guest')
        $lid .= 'guest_';

    if($entry->author_type == 'Guest'
       && $entry->author->id != -1
       && $entry->author->is($entry->target->owner))
    {
        $lid .= 'owner_';
    }
    
    $lid .= 'event_'.$entry->event;
    
    $action = Lang::tr($lid)->r(
        array(
            'event' => $entry->event,
            'date' => $entry->created,
            'time_taken' => $entry->time_taken,
            'author' => $entry->author,
            'log' => $entry
        ),
        $entry->target
    );

    $nextline = array('date' => $date, 'action' => $action );
    if( Config::get('reports_show_ip_addr')) {
        $nextline += array('ip' => $entry->ip);
    }
    $lines[] = $nextline;
}

// Find longest date to compute first column width
$date_length = max(array_map(function($line) {
    return mb_strlen($line['date']);
}, $lines));

$line_length = 76;

$date = (string)Lang::tr('date');
echo $date.str_repeat(' ', $date_length - mb_strlen($date) + 2).Lang::tr('action')."\n\n";

$line_spacer = str_repeat(' ', $date_length + 3);

$line_text_length = $line_length - $date_length - 3;

$print_action = function($action) use($line_spacer, $line_text_length) {
    $words = preg_split('`(\s+)`', trim($action), -1, PREG_SPLIT_DELIM_CAPTURE);
    
    $first = true;
    while($words) {
        $line = array_shift($words);
        while($words && mb_strlen($line.$words[0].(count($words) > 1 ? $words[1]: '')) < $line_text_length)
            $line .= array_shift($words).array_shift($words);
        
        $line = trim($line);
        if(!$line) continue;
        
        echo ($first ? '' : $line_spacer).$line."\n";
        $first = false;
    }
};

foreach($lines as $line) {
    echo $line['date'].str_repeat(' ', $date_length - mb_strlen($line['date'])).'  ';
    
    $print_action($line['action']);
    
    if(array_key_exists('ip', $line))
        echo $line_spacer.Lang::tr('ip').' '.$line['ip']."\n";
    
    echo "\n";
}
