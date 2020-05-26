<?php

    $openoffset   = Utilities::arrayKeyOrDefault( $_GET, 'openoffset',    0, FILTER_VALIDATE_INT  );
    $openlimit    = Utilities::arrayKeyOrDefault( $_GET, 'openlimit',    15, FILTER_VALIDATE_INT  );
    $closedoffset = Utilities::arrayKeyOrDefault( $_GET, 'closedoffset',  0, FILTER_VALIDATE_INT  );
    $closedlimit  = Utilities::arrayKeyOrDefault( $_GET, 'closedlimit',  15, FILTER_VALIDATE_INT  );

    $trsort = TransferQueryOrder::create();

    $displayClosedTransfers = Config::get('auditlog_lifetime') > 0;

    $section = 'available';
    $sections = array('available','closed');
    if(array_key_exists('as', $_REQUEST))
        $section = $_REQUEST['as'];
    if(!strlen($section)) {
        $section = 'available';
    }
    if(!in_array($section, $sections)) {
        throw new GUIUnknownAdminSectionException($section);
    }

    $cgiuid = "";

    $user = Auth::user();
    if (Auth::isAuthenticated()) {
        if (Auth::isAdmin()) {
            
            $uid = Utilities::arrayKeyOrDefault( $_GET, 'uid', 0, FILTER_VALIDATE_INT  );
            if( $uid ) {
                if( Config::get('admin_can_view_user_transfers_page')) {
                    $user = User::fromId( $uid );
                }
                $cgiuid = "&uid=".$uid;
            }
        }
    }
?>
<div class="box">

    <div class="menu">
        <ul>
        <?php foreach($sections as $s) { ?>
            <li class="<?php if($s == $section) echo 'current' ?>">
                <a href="?s=transfers&as=<?php echo $s . $cgiuid ?>">
                    <?php echo Lang::tr($s.'_transfers') ?>
                </a>
            </li>
        <?php } ?>
        </ul>
    </div>

    <div class="<?php echo $section ?>_section section">

        <?php 
        if( $section == 'closed' ) {
            Template::display('transfers_table', array(
                'status' => 'closed',
                'mode' => 'user',
                'transfers' => Transfer::fromUserOrdered($user,
                                                         $trsort->getViewName(),
                                                         $trsort->getOrderByClause(),
                                                         true, $closedlimit+1, $closedoffset ),
                'limit' => $closedlimit,
                'offset' => $closedoffset,
                'pagerprefix' => 'closed',
                'header' => '{tr:closed_transfers}',
                'trsort' => $trsort
            ));
        } else {
            Template::display('transfers_table', array(
                'status' => 'available',
                'mode' => 'user',
                'transfers' => Transfer::fromUserOrdered($user,
                                                         $trsort->getViewName(),
                                                         $trsort->getOrderByClause(),
                                                         false, $openlimit+1, $openoffset ),
                'limit' => $openlimit,
                'offset' => $openoffset,
                'pagerprefix' => 'open',
                'header' => '{tr:available_transfers}',
                'trsort' => $trsort
            ));
        }
        ?>
    </div>

</div>
