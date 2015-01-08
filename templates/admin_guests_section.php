<h2>{tr:admin_guests_section}</h2>

<?php Template::display('guests_table', array(
    'status' => 'available',
    'mode' => 'admin',
    'guests' => Guest::all(Guest::AVAILABLE)
)) ?>
