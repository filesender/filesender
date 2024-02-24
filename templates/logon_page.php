<div class="core">
    {tr:site_splash}
    
    <div class="logon">
        <?php
        $page = null;
        if (array_key_exists('s', $_REQUEST)) {
            $args = array('s' => $_REQUEST['s']);
            if( $_REQUEST['s'] == 'transfer_detail' ) {
                $args = array_merge( $args, array('transfer_id' => $_REQUEST['transfer_id']));
            }
            if( $_REQUEST['s'] == 'invitation_detail' ) {
                $args = array_merge( $args, array('guest_id' => $_REQUEST['guest_id']));
            }
            $page = Utilities::http_build_query( $args );
        }
        echo GUI::getLoginButton($page);
        ?>
    </div>
    <script type="text/javascript" src="{path:js/logon_page.js}"></script>
    
</div>
