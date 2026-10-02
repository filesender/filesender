<div class="fs-admin__block">
    <h4>{tr:admin_guests_section}</h4>

    <div class="fs-admin__table">
        <?php Template::display('guests_table', array(
            'status' => 'available',
            'mode' => 'admin',
            'guests' => Guest::all(Guest::AVAILABLE)
        )) ?>
    </div>
</div>
