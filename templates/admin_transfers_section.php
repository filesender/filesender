<h2>{tr:admin_transfers_section}</h2>

<?php Template::display('transfers_table', array('transfers' => Transfer::all(Transfer::AVAILABLE))) ?>
