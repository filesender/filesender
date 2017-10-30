<?php

    $openoffset   = $_GET['openoffset'];
    $openlimit    = $_GET['openlimit'];
    $closedoffset = $_GET['closedoffset'];
    $closedlimit  = $_GET['closedlimit'];

    if(!isset($openoffset))   $openoffset   = 0;
    if(!isset($openlimit))    $openlimit    = 10;
    if(!isset($closedoffset)) $closedoffset = 0;
    if(!isset($closedlimit))  $closedlimit  = 5;


?>
<div class="box">
    <?php Template::display('transfers_table', array(
        'status' => 'available',
        'mode' => 'user',
        'transfers' => Transfer::fromUser(Auth::user(), false, $openlimit+1, $openoffset ),
        'limit' => $openlimit,
        'offset' => $openoffset,
        'pagerprefix' => 'open',
        'header' => '{tr:available_transfers}'
    )) ?>
    
    <?php if(Config::get('auditlog_lifetime') > 0) { ?>
    <?php Template::display('transfers_table', array(
        'status' => 'closed',
        'mode' => 'user',
        'transfers' => Transfer::fromUser(Auth::user(), true, $closedlimit+1, $closedoffset ),
        'limit' => $closedlimit,
        'offset' => $closedoffset,
        'pagerprefix' => 'closed',
        'header' => '{tr:closed_transfers}'
    )) ?>
    <?php } ?>

    <?php if(Config::get('auth_remote_user_enabled')) echo '<h2>API key</h2>'.Auth::user()->auth_secret; ?>
</div>
