<?php

$auditlogs = Config::get('auditlog_lifetime') > 0;

$transfers_page = function($status) {
    $page_size = 15;

    switch($status) {
        case 'available': $selector = Transfer::AVAILABLE_NO_ORDER; break;
        case 'uploading': $selector = Transfer::UPLOADING_NO_ORDER; break;
        case 'closed':    $selector = Transfer::CLOSED_NO_ORDER;    break;
        default: return;
    }

    $trsort = TransferQueryOrder::create();
        
    $offset = array_key_exists($status.'_tpo', $_REQUEST) ? (int)$_REQUEST[$status.'_tpo'] : 0;
    $offset = max(0, $offset);

    // FIXME: move the code away from wanting to know the total.
    //       if the user has 1000 tuples do we really want to show 1000/15 direct page links
    //       or should we instead allow queries on timeframe etc.
    $total_count = 100;
    $entries = Transfer::all(array(
        'view'   => $trsort->getViewName(),
        'where'  => $selector . $trsort->getWhereClause($selector),
        'order'  => $trsort->getOrderByClause(),
        'count'  => $page_size,
        'offset' => $offset
    ));
    
    $navigation = '<div class="transfers_list_page_navigation">'."\n";
    $transfersort = Utilities::getGETparam('transfersort','');

    if($offset) {
        $po = max(0, $offset - $page_size);
        $navigation .= '<a href="?s=admin&as=transfers&'.$status.'_tpo=0&transfersort='.$transfersort.'#'.$status.'_transfers"><span class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-angle-double-left fa-stack-1x fa-inverse"></i></span></a>'."\n";
        $navigation .= '<a href="?s=admin&as=transfers&'.$status.'_tpo='.$po.'&transfersort='.$transfersort.'#'.$status.'_transfers"><span class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-angle-left fa-stack-1x fa-inverse"></i></span></a>'."\n";
    }
    
    $p = 1;
    for($o=0; $o<$total_count; $o+=$page_size) {
        if($o >= $offset && $o < $offset + $page_size) {
            $navigation .= '<span>'.$p.'</span>'."\n";
        } else {
            $navigation .= '<a href="?s=admin&as=transfers&'.$status.'_tpo='.$o.'&transfersort='.$transfersort.'#'.$status.'_transfers">'.$p.'</a>'."\n";
        }
        
        $p++;
    }
    
    if($offset + $page_size < $total_count) {
        $no = $offset + $page_size;
        $lo = $total_count - ($total_count % $page_size);
        $navigation .= '<a href="?s=admin&as=transfers&'.$status.'_tpo='.$no.'&transfersort='.$transfersort.'#'.$status.'_transfers"><span class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-angle-right fa-stack-1x fa-inverse"></i></span></a>'."\n";
        $navigation .= '<a href="?s=admin&as=transfers&'.$status.'_tpo='.$lo.'&transfersort='.$transfersort.'#'.$status.'_transfers"><span class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-angle-double-right fa-stack-1x fa-inverse"></i></span></a>'."\n";
    }
    
    $navigation .= '</div>'."\n";
    
    if($total_count > $page_size)
        echo $navigation;
    
    Template::display('transfers_table', array(
        'status' => $status,
        'mode' => 'admin',
        'transfers' => $entries,
        'trsort' => $trsort
    ));
    
    if($total_count > $page_size)
        echo $navigation;
};

echo '<h2>{tr:admin_transfers_section}</h2>'."\n";


// available
echo '<span id="available_transfers"></span>'."\n";
if($auditlogs)
    echo '<h3>{tr:available_transfers}</h3>'."\n";

$transfers_page('available');


// uploading
echo '<span id="uploading_transfers"></span>'."\n";
if($auditlogs)
    echo '<h3>{tr:uploading_transfers}</h3>'."\n";

$transfers_page('uploading');

// closed
if($auditlogs) {
    echo '<span id="closed_transfers"></span>'."\n";
    echo '<h3>{tr:closed_transfers}</h3>'."\n";
    
    $transfers_page('closed');
}
