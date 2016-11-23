<div class="box">
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

    <?php if(Config::get('auth_remote_user_enabled')) echo '<h2>API key</h2>'.Auth::user()->auth_secret; ?>
</div>
