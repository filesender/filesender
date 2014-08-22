<h2>{tr:transfers}</h2>

<?php Template::display('transfers_table', array('transfers' => Transfer::all(Transfer::AVAILABLE))) ?>
