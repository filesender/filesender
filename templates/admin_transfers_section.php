<?php

$auditlogs = Config::get('auditlog_lifetime') > 0;

$transfers_page = function($status) {
    $page_size = 15;
    $display_page_num = 4;

    switch($status) {
        case 'available': $selector = Transfer::AVAILABLE_NO_ORDER; break;
        case 'uploading': $selector = Transfer::UPLOADING_NO_ORDER; break;
        case 'closed':    $selector = Transfer::CLOSED_NO_ORDER;    break;
        case 'search':    $selector = Transfer::AVAILABLE_NO_ORDER; break;
        default: return;
    }

    $trsort = TransferQueryOrder::create();

    $idmin = Utilities::arrayKeyOrDefault( $_GET, 'idmin', -1, FILTER_VALIDATE_INT  );
    $idmax = Utilities::arrayKeyOrDefault( $_GET, 'idmax', -1, FILTER_VALIDATE_INT  );
    if( $status == 'search' ) {
        if( $idmin != -1 && $idmax != -1 ) {
            // Note that we are using semi validated data from above
            // and that this is an admin only page, so hacking is less likely.
            $selector .= " AND id >= $idmin AND id <= $idmax ";
        }
    }
    $placeholders=array();
    $senderemail_full_match = Utilities::arrayKeyOrDefault( $_POST, 'senderemail_full_match', '', FILTER_VALIDATE_BOOLEAN );
    $senderemailUnsanitized = Utilities::arrayKeyOrDefault( $_POST, 'senderemail', '' );
    $senderemail = Utilities::arrayKeyOrDefault( $_POST, 'senderemail', '', FILTER_SANITIZE_EMAIL );
    // if this is a full match then we can filter the email string.
    if( $senderemail_full_match ) 
        $senderemail = Utilities::arrayKeyOrDefault( $_POST, 'senderemail', '', FILTER_VALIDATE_EMAIL );
    
    if( $status == 'search' ) {
        
        if( $senderemail != '' && strlen($senderemail) ) {
            // Note that we are using semi validated data from above
            // and that this is an admin only page, so hacking is less likely.
            if( $senderemail_full_match ) {
                $selector .= " AND LOWER(user_email) = :senderemail ";
            } else {
                if( substr_compare($senderemailUnsanitized, '%', 0, 1 )) {
                    $senderemail = '%'.$senderemail;
                }
                if( substr_compare($senderemailUnsanitized, '%', -1, 1 )) {
                    $senderemail = $senderemail.'%';
                }
                
                $selector .= " AND LOWER(user_email) LIKE :senderemail ";
            }
            $placeholders[":senderemail"] = mb_strtolower($senderemail);
        } else {
            if( $senderemail_full_match ) {
                if( strlen(Utilities::arrayKeyOrDefault( $_POST, 'senderemail', '', FILTER_SANITIZE_EMAIL ))) {
                    // the email didn't validate so show no search results.
                    $selector .= ' and id < 0 ';
                }
            }
        }
    }
        
    $offset = array_key_exists($status.'_tpo', $_REQUEST) ? (int)$_REQUEST[$status.'_tpo'] : 0;
    $offset = max(0, $offset);

    // FIXME: move the code away from wanting to know the total.
    //       if the user has 1000 tuples do we really want to show 1000/15 direct page links
    //       or should we instead allow queries on timeframe etc.
    //
    // At offset zero with no interesting selector info we just assume there
    // are a bunch of results to avoid hitting the database for a count(*) right
    // at the start (there are three views by default and couint(*) might do a
    // seq scan to complete.
    if( false ) {
        $total_count = $page_size * $display_page_num;
    } else {
        $total_count = Transfer::count(array(
            'view'   => $trsort->getViewName(),
            'where'  => $selector . $trsort->getWhereClause($selector)
        ), $placeholders);
    }
    
    $entries = Transfer::all(array(
        'view'   => $trsort->getViewName(),
        'where'  => $selector . $trsort->getWhereClause($selector),
        'order'  => $trsort->getOrderByClause(),
        'count'  => $page_size,
        'offset' => $offset
    ), $placeholders);
    
    $navigation = '<div class="fs-paginator fs-paginator--center">'."\n";
    $transfersort = Utilities::getGETparam('transfersort','');

    $cgiminmax = "";
    if( $idmin >= 0 ) {
        $cgiminmax .= "&idmin=".$idmin;
    }
    if( $idmax >= 0 ) {
        $cgiminmax .= "&idmax=".$idmax;
    }
    

    if($offset) {
        $po = max(0, $offset - $page_size);
        $navigation .= '<a href="?s=admin&as=transfers&'.Template::Q($status).'_tpo=0&transfersort='.Template::Q($transfersort).Template::Q($cgiminmax).'#'.Template::Q($status).'_transfers" class="fs-link fs-link--circle"><i class="fa fa-angle-double-left"></i></a>'."\n";
        $navigation .= '<a href="?s=admin&as=transfers&'.Template::Q($status).'_tpo='.Template::Q($po).'&transfersort='.Template::Q($transfersort).Template::Q($cgiminmax).'#'.Template::Q($status).'_transfers" class="fs-link fs-link--circle"><i class="fi fi-chevron-left"></i></a>'."\n";
    }
    
    $start_index = $offset - $page_size * $display_page_num;
    if( $start_index < 0 ) {
        $start_index = 0;
    }
    $p = ceil(($start_index+1) / $page_size);
    $end_index = min($total_count, $offset + $page_size * ($display_page_num + 1));
    for($o=$start_index; $o < $end_index; $o += $page_size)
    {
        if($o >= $offset && $o < $offset + $page_size) {
            $navigation .= '<span class="fs-link fs-link--circle fs-link--current" aria-current="page">'.$p.'</span>'."\n";
        } elseif($o < $offset - $page_size * $display_page_num ||
                 $o >= $offset + $page_size * ($display_page_num + 1)) {
            // nothing
        } elseif( $o < $offset - $page_size * ($display_page_num - 1) ||
                  $o >= $offset + $page_size * $display_page_num ) {
            $navigation .= '<span class="fs-paginator__ellipsis">...</span>'."\n";
        } else {
            $navigation .= '<a href="?s=admin&as=transfers&'.Template::Q($status).'_tpo='.Template::Q($o).'&transfersort='.Template::Q($transfersort).Template::Q($cgiminmax).'#'.Template::Q($status).'_transfers" class="fs-link fs-link--circle">'.$p.'</a>'."\n";
        }
        
        $p++;
    }
    
    if($offset + $page_size < $total_count) {
        $no = $offset + $page_size;
        $lo = max(0, $total_count - ($total_count % $page_size ?: $page_size));
        $navigation .= '<a href="?s=admin&as=transfers&'.Template::Q($status).'_tpo='.Template::Q($no).'&transfersort='.Template::Q($transfersort).Template::Q($cgiminmax).'#'.Template::Q($status).'_transfers" class="fs-link fs-link--circle"><i class="fi fi-chevron-right"></i></a>'."\n";
        $navigation .= '<a href="?s=admin&as=transfers&'.Template::Q($status).'_tpo='.Template::Q($lo).'&transfersort='.Template::Q($transfersort).Template::Q($cgiminmax).'#'.Template::Q($status).'_transfers" class="fs-link fs-link--circle"><i class="fa fa-angle-double-right"></i></a>'."\n";
    }
    
    $navigation .= '</div>'."\n";
    
    if($total_count > $page_size)
        echo $navigation;
    
    Template::display('transfers_table', array(
        'status' => $status,
        'mode' => 'admin',
        'transfers' => $entries,
        'trsort' => $trsort,
        'limit' => $page_size
    ));
    
    if($total_count > $page_size)
        echo $navigation;
};

?>
<div class="fs-admin__block">
    <h4>{tr:admin_transfers_section}</h4>
    <p>{tr:admin_transfers_page_sections}</p>
    <ul class="fs-badge-list fs-admin__sections">
        <li class="fs-badge"><a href="#search_transfers">{tr:search_transfers}</a></li>
        <li class="fs-badge"><a href="#available_transfers">{tr:available_transfers}</a></li>
        <li class="fs-badge"><a href="#uploading_transfers">{tr:uploading_transfers}</a></li>
        <?php if($auditlogs) { ?>
            <li class="fs-badge"><a href="#closed_transfers">{tr:closed_transfers}</a></li>
        <?php } ?>
    </ul>
</div>

<?php
$idmin = Utilities::arrayKeyOrDefault( $_GET, 'idmin', 0, FILTER_VALIDATE_INT  );
$idmax = Utilities::arrayKeyOrDefault( $_GET, 'idmax', -1, FILTER_VALIDATE_INT  );
if( $idmax == -1 ) {
    $idmax = '';
}

$senderemail_full_match = Utilities::arrayKeyOrDefault( $_POST, 'senderemail_full_match', '', FILTER_VALIDATE_BOOLEAN );
$senderemail = Utilities::arrayKeyOrDefault( $_POST, 'senderemail', '' ); // we don't want to FILTER_SANITIZE_EMAIL here
$senderemail_full_match_extra = '';
if( $senderemail_full_match ) {
    $senderemail_full_match_extra = ' checked ';
}
?>
<div class="fs-admin__block" id="search_transfers">
    <?php if($auditlogs) { ?>
        <h4>{tr:search_transfers}</h4>
    <?php } ?>

    <p>{tr:search_transfer_id_inclusive_description}</p>
    <div class="fs-admin__fields search">
        <div class="fs-input-group">
            <label for="idmin" class="mandatory">{tr:minimum}</label>
            <input type="text" id="idmin" name="idmin" value="<?php echo $idmin ?>" />
        </div>
        <div class="fs-input-group">
            <label for="idmax" class="mandatory">{tr:maximum}</label>
            <input type="text" id="idmax" name="idmax" value="<?php echo $idmax ?>" />
        </div>
        <button type="button" class="fs-button" name="idbutton">
            <i class="fi fi-search"></i>
            <span>{tr:search}</span>
        </button>
    </div>

    <p>{tr:search_transfer_by_sender_email_description}</p>
    <form action="{path:?s=admin&as=transfers}" method="post" class="search">
        <input type="hidden" name="s" value="admin" />
        <label class="fs-checkbox">
            <label id="senderemail_full_match_label" for="senderemail_full_match">{tr:email_full_match_search}</label>
            <input id="senderemail_full_match" name="senderemail_full_match" type="checkbox" <?php echo $senderemail_full_match_extra ?>>
            <span class="fs-checkbox__mark"></span>
        </label>
        <div class="fs-admin__fields">
            <div class="fs-input-group">
                <label for="senderemail" class="mandatory">{tr:sender_email_search}</label>
                <input type="text" id="senderemail" name="senderemail" value="<?php echo Template::sanitizeOutputEmail($senderemail) ?>" />
            </div>
            <button type="submit" class="fs-button">
                <i class="fi fi-search"></i>
                <span>{tr:search}</span>
            </button>
        </div>
    </form>

    <div class="fs-admin__table">
        <?php $transfers_page('search') ?>
    </div>
</div>

<div class="fs-admin__block" id="available_transfers">
    <?php if($auditlogs) { ?>
        <h4>{tr:available_transfers}</h4>
    <?php } ?>
    <div class="fs-admin__table">
        <?php $transfers_page('available') ?>
    </div>
</div>

<div class="fs-admin__block" id="uploading_transfers">
    <?php if($auditlogs) { ?>
        <h4>{tr:uploading_transfers}</h4>
    <?php } ?>
    <div class="fs-admin__table">
        <?php $transfers_page('uploading') ?>
    </div>
</div>

<?php if($auditlogs) { ?>
    <div class="fs-admin__block" id="closed_transfers">
        <h4>{tr:closed_transfers}</h4>
        <div class="fs-admin__table">
            <?php $transfers_page('closed') ?>
        </div>
    </div>
<?php } ?>

<script type="text/javascript" src="{path:js/admin_transfers.js}"></script>
