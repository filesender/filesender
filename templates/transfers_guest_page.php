<?php


if(!isset($guest)) $guest = null;
if(!isset($guest_id)) $guest_id = 0;

function notfound() {
    echo <<<EOF
    <div class="fs-invitation-detail" data-id="<?php echo $guest_id; ?>">
        <div class="container">
            <div class="row">
                <h1>{tr:guest_not_found} $vid</h1>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="{path:js/guest_upload_history_page.js}"></script>
EOF;
    return;
}

if (array_key_exists('vid', $_REQUEST)) {
    $vid = $_REQUEST['vid'];

    if( !Utilities::isValidUID($vid) ) {
        notfound();
        return;
    }
}


if(!Auth::isGuest()) {
    notfound();
    return;
}
if( !Config::get('guest_transfers_page_support_enabled')) {
    notfound();
    return;
}

if(Auth::isGuest()) {

    try {
        $guest = AuthGuest::getGuest();
    }
    catch( GuestExpiredException $e ) {
        notfound();
        return;
    }
    
    $guest = AuthGuest::getGuest();
    
}

if( !$guest ) {
    notfound();
    return;
}
        

if (!function_exists('clickableHeader')) {

    function clickableHeader($displayName,$trsortcol,$trsort,$nosort,$title = null) {        
        echo $displayName;
        return;
    }
}


$transfers = Transfer::fromGuest($guest);

?>




<div class="fs-invitation-detail">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="fs-invitation-detail__header">
                    <p>{tr:transfer_guest_page_subtitle}</p>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-12 col-sm-12 col-md-12">
                <div class="fs-invitation-detail__guest-list">

                    <table class="fs-table fs-table--responsive fs-table--selectable fs-table--striped" >
                        <thead>
                            <tr>
                                <th>
                                    <?php clickableHeader('{tr:size}',TransferQueryOrder::COLUMN_SIZE,$trsort,$nosort); ?>
                                </th>
                    
                                <th>
                                    <?php clickableHeader('{tr:files}',TransferQueryOrder::COLUMN_FILE,$trsort,$nosort); ?>
                                </th>
                                
                                <th>
                                    <?php clickableHeader('{tr:expires}',TransferQueryOrder::COLUMN_EXPIRES,$trsort,$nosort); ?>
                                </th>

                            </tr>
                        </thead>
                        <tbody>

                            <?php  foreach($transfers as $transfer) { ?>
                                <tr
                                    class="transfer objectholder fs-table__row fs-table__row--clickable"
                                >

                                    <td data-label="{tr:size}">
                                        <?php echo Utilities::formatBytes($transfer->size) ?>
                                    </td>
                                    
                                    <td data-label="{tr:files}">
                                        <?php
                                        $items = GUI::getFileNamesForDisplay( $transfer, false );
                                        echo implode('<br />', $items);
                                        ?>
                                    </td>

                                    <td data-label="{tr:expires}">
                                        <?php echo Utilities::formatDate($transfer->expires) ?>
                                    </td>
                                    
                                </tr>
                                <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


    </div>
</div>

