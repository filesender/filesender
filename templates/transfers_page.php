<div class="box">
    <h1>{tr:transfers_page}</h1>
    
    <?php if(Config::get('auditlog_lifetime') > 0) { ?><h2>{tr:available_transfers}</h2><?php } ?>
    <?php Template::display('transfers_table', array(
        'status' => 'available',
        'mode' => 'user',
        'transfers' => Transfer::fromUser(Auth::user())
    )) ?>
    
    <?php if(Config::get('auditlog_lifetime') > 0) { ?>
    <h2>{tr:closed_transfers}</h2>
    <?php Template::display('transfers_table', array(
        'status' => 'closed',
        'mode' => 'user',
        'transfers' => Transfer::fromUser(Auth::user(), true)
    )) ?>
    <?php } ?>
</div>
