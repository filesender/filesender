<?php
    $nosort = false;

    if(!isset($trsort))  $nosort = true;
    if(!isset($status)) $status = 'available';
    if(!isset($mode)) $mode = 'user';
    if(!isset($transfers) || !is_array($transfers)) $transfers = array();
    if(!isset($limit)) $limit = 2;
    if(!isset($offset)) $offset = 0;
    if(!isset($pagerprefix)) $pagerprefix = '';
    if(!isset($trsort)) $trsort = TransferQueryOrder::create();

    $show_guest = isset($show_guest) ? (bool)$show_guest : false;
    $extend = (bool)Config::get('allow_transfer_expiry_date_extension');
    $audit = (bool)Config::get('auditlog_lifetime') ? '1' : '';
    $haveNext = 0;
    $havePrev = 0;

    $isAdmin = false;
    $showAdminExtend = false;
    if (Auth::isAuthenticated()) {
        if (Auth::isAdmin()) {

            $isAdmin = true;

            if(Config::get('allow_transfer_expiry_date_extension_admin')) {
                $showAdminExtend = true;
            }
        }
    }

    $cgiuid = "";
    if (Auth::isAuthenticated()) {
        if (Auth::isAdmin()) {

            $uid = Utilities::arrayKeyOrDefault( $_GET, 'uid', 0, FILTER_VALIDATE_INT  );
            if( $uid ) {
                $cgiuid = "&uid=".$uid;
            }
        }
    }

    $cgiminmax = "";
    $idmin = Utilities::arrayKeyOrDefault( $_GET, 'idmin', -1, FILTER_VALIDATE_INT  );
    $idmax = Utilities::arrayKeyOrDefault( $_GET, 'idmax', -1, FILTER_VALIDATE_INT  );
    if( $idmin >= 0 ) {
        $cgiminmax .= "&idmin=".$idmin;
    }

    if( $idmax >= 0 ) {
        $cgiminmax .= "&idmax=".$idmax;
    }

    if (!function_exists('clickableHeader')) {

        function clickableHeader($displayName,$trsortcol,$trsort,$nosort,$title = null) {

            if( $nosort ) {
                echo $displayName;
                return;
            }

            $qa = array(
                's' => Utilities::getGETparam('s','')
              , 'transfersort' => $trsort->clickableSortValue($trsortcol)
              , 'as' => Utilities::getGETparam('as','')
            );

            if (Auth::isAuthenticated()) {
                if (Auth::isAdmin()) {

                    $uid = Utilities::arrayKeyOrDefault( $_GET, 'uid', 0, FILTER_VALIDATE_INT  );
                    if( $uid ) {
                        $qa["uid"] = $uid;
                    }
                }
            }
            $idmin = Utilities::arrayKeyOrDefault( $_GET, 'idmin', -1, FILTER_VALIDATE_INT  );
            $idmax = Utilities::arrayKeyOrDefault( $_GET, 'idmax', -1, FILTER_VALIDATE_INT  );
            if( $idmin >= 0 ) {
                $qa["idmin"] = $idmin;
            }
            if( $idmax >= 0 ) {
                $qa["idmax"] = $idmax;
            }

            $tr_url = Utilities::http_build_query($qa);
            echo '<a href="' . $tr_url . '" ';
            if( !empty($title)) {
                echo ' title="' . $title . '" ';
            }
            echo ' >';
            echo $displayName;
            echo ' ' . $trsort->screenArrowHTML($trsortcol);
            echo '</a>';
        }
    }

    // This allows us to key informational displays to a large
    // part of the row.
    $maxColSpan = 8;
    if($show_guest) {
        $maxColSpan = 9;
    }

    if( count($transfers) > $limit ) {
        $haveNext = 1;
        $transfers = array_slice($transfers,0,$limit);
    }

    if( $offset > 0 ) {
        $havePrev = 1;
    }

    $showPager = $havePrev || $haveNext;
?>

<table class="fs-table fs-table--responsive fs-table--selectable fs-table--striped" data-status="<?php echo $status ?>" data-mode="<?php echo $mode ?>" data-audit="<?php echo $audit ?>">
    <thead>
    <tr>
        <th>
            <?php clickableHeader('{tr:transfer_id_short}',TransferQueryOrder::COLUMN_ID,$trsort,$nosort,'{tr:transfer_id}'); ?>
        </th>

        <?php if($show_guest) { ?>
            <th>
                {tr:guest}
            </th>
        <?php } ?>

        <th>
            <?php clickableHeader('{tr:recipients}',TransferQueryOrder::COLUMN_RECIPIENTS,$trsort,$nosort); ?>
        </th>

        <th>
            <?php clickableHeader('{tr:size}',TransferQueryOrder::COLUMN_SIZE,$trsort,$nosort); ?>
        </th>

        <th>
            <?php clickableHeader('{tr:files}',TransferQueryOrder::COLUMN_FILE,$trsort,$nosort); ?>
        </th>

        <th>
            <?php clickableHeader('{tr:downloads}',TransferQueryOrder::COLUMN_DOWNLOAD,$trsort,$nosort); ?>
        </th>

        <th>
            <?php clickableHeader('{tr:expires}',TransferQueryOrder::COLUMN_EXPIRES,$trsort,$nosort); ?>
        </th>

<!--        <th>-->
<!--            {tr:actions}-->
<!--        </th>-->
    </tr>
    </thead>
    <tbody>
    <?php foreach($transfers as $transfer) { ?>
        <tr id="transfer_<?php echo $transfer->id ?>"
            class="transfer objectholder fs-table__row fs-table__row--clickable"
            data-transfer
            data-id="<?php echo $transfer->id ?>"
            data-recipients-enabled="<?php echo $transfer->getOption(TransferOptions::GET_A_LINK) ? '' : '1' ?>"
            data-errors="<?php echo count($transfer->recipients_with_error) ? '1' : '' ?>"
            data-expiry-extension="<?php echo $transfer->expiry_date_extension ?>"
            data-key-version="<?php echo $transfer->key_version; ?>"
            data-key-salt="<?php echo $transfer->salt; ?>"
            data-password-version="<?php echo $transfer->password_version; ?>"
            data-password-encoding="<?php echo $transfer->password_encoding_string; ?>"
            data-password-hash-iterations="<?php echo $transfer->password_hash_iterations; ?>"
            data-client-entropy="<?php echo $transfer->client_entropy; ?>"
        >

            <td data-label="{tr:transfer_id_short}">
                <?php
                echo $transfer->id;
                if( $transfer->is_encrypted ) {
                    echo '&nbsp;<span class="fa fa-lock" title="{tr:file_encryption}"></span>';
                }
                ?>
            </td>

            <?php if($show_guest) { ?>
                <td data-label="{tr:guest}">
                    <?php if($transfer->guest) echo '<abbr title="'.Template::sanitizeOutput($transfer->guest->identity).'">'.Template::sanitizeOutput($transfer->guest->name).'</abbr>' ?>
                </td>
            <?php } ?>

            <td data-label="{tr:recipients}">
                <?php
                $items = array();
                foreach(array_slice($transfer->recipients, 0, 3) as $recipient) {
                    if(in_array($recipient->email, Auth::user()->email_addresses)) {
                        $items[] = '<abbr title="'.Template::sanitizeOutputEmail($recipient->email).'">'.Lang::tr('me').'</abbr>';
                    } else if($recipient->email) {
                        $items[] = '<a href="mailto:'.Template::sanitizeOutputEmail($recipient->email).'">'.Template::sanitizeOutput($recipient->identity).'</a>';
                    } else {
                        $items[] = '<abbr title="'.Lang::tr('anonymous_details').'">'.Lang::tr('anonymous').'</abbr>';
                    }
                }

                if(count($transfer->recipients) > 3)
                    $items[] = '<span class="clickable expand">'.Lang::tr('n_more')->r(array('n' => count($transfer->recipients) - 3)).'</span>';

                echo implode('<br />', $items);
                ?>
            </td>

            <td data-label="{tr:size}">
                <?php echo Utilities::formatBytes($transfer->size) ?>
            </td>

            <td data-label="{tr:files}">
                <?php
                $maxlen = 32;
                $items = array();
                foreach(array_slice($transfer->files, 0, 3) as $file) {
                    $name = $file->path;
                    $name_shorten_by = (int) (mb_strlen((string) count($transfer->downloads))+mb_strlen(Lang::tr('see_all'))+3)/2;
                    if(mb_strlen($name) > 28-$name_shorten_by) {
                        if(count($transfer->downloads)) $name = mb_substr($name, 0, 23-$name_shorten_by).'...';
                        else $name = mb_substr($name, 0, 23).'...';
                    }
                    $items[] = '<span title="'.Template::sanitizeOutput($file->path).'">'.Template::sanitizeOutput($name).'</span>';
                }

                if(count($transfer->files) > 3)
                    $items[] = '<span class="clickable expand">'.Lang::tr('n_more')->r(array('n' => count($transfer->files) - 3)).'</span>';

                echo implode('<br />', $items);
                ?>
            </td>

            <td data-label="{tr:downloads}">
                <?php
                    $dc = count($transfer->downloads);
                    echo $dc;
                ?>
            </td>

            <td data-label="{tr:expires}">
                <?php echo Utilities::formatDate($transfer->expires) ?>
            </td>
        </tr>
    <?php } ?>

    <?php if(!count($transfers)) { ?>
        <tr>
            <td colspan="<?php echo $maxColSpan ?>">{tr:no_transfers}</td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<?php
    if( $havePrev || $haveNext ) {
        echo "<div class='fs-paginator fs-paginator--center'>";
        $base = '?s=' . htmlspecialchars($_GET['s']);
        $cgioffset = $pagerprefix . 'offset';
        $cgilimit  = $pagerprefix . 'limit';
        $nextPage  = $offset+$limit;
        $transfersort = Utilities::getGETparam('transfersort','');
        $cgias = Utilities::getGETparam('as','');
        $nextLink  = "$base&$cgioffset=$nextPage&$cgilimit=$limit&transfersort=$transfersort&as=$cgias$cgiuid$cgiminmax&nextlink=1";

        if( $havePrev ) {
            $prevPage = max(0,$offset-$limit);
            echo "<a class='fs-link fs-link--circle' href='$base&$cgioffset=0&$cgilimit=$limit&transfersort=$transfersort&as=$cgias$cgiuid$cgiminmax'><i class='fa fa-angle-double-left'></i></a>";
            echo "<a class='fs-link fs-link--circle' href='$base&$cgioffset=$prevPage&$cgilimit=$limit&transfersort=$transfersort&as=$cgias$cgiuid$cgiminmax'><i class='fa fa-angle-left'></i></a>";
        } else {
            echo "<a class='fs-link fs-link--circle fs-link--disabled' href='javascript:void(0)'><i class='fa fa-angle-double-left'></i></a>";
            echo "<a class='fs-link fs-link--circle fs-link--disabled' href='javascript:void(0)'><i class='fa fa-angle-left'></i></a>";
        }

        if( $haveNext ) {
            echo "<a class='fs-link fs-link--circle' href='$nextLink'><i class='fa fa-angle-right'></i></a>";
        } else {
            echo "<a class='fs-link fs-link--circle fs-link--disabled' href='javascript:void(0)'><i class='fa fa-angle-right'></i></a>";
        }

        echo "</div>";
    }
?>

<script type="text/javascript" src="{path:js/transfers_table.js}"></script>
