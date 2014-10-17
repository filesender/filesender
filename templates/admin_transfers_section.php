<h2>{tr:admin_transfers_section}</h2>

<?php if(Config::get('auditlog_lifetime') > 0) { ?><h3>{tr:available_transfers}</h3><?php } ?>
<?php Template::display('transfers_table', array(
    'status' => 'available',
    'mode' => 'admin',
    'transfers' => Transfer::all(Transfer::AVAILABLE)
)) ?>

<?php if(Config::get('auditlog_lifetime') > 0) { ?>
<h3>{tr:closed_transfers}</h3>
<?php Template::display('transfers_table', array(
    'status' => 'closed',
    'mode' => 'user',
    'transfers' => Transfer::all(Transfer::CLOSED)
)) ?>
<?php } ?>
