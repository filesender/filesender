<?php

$cgiuid = "";
$section = 'invite';
$sections = array('invite','current','transfers');

if(array_key_exists('as', $_REQUEST))
    $section = $_REQUEST['as'];
if(!strlen($section)) {
    $section = 'invite';
}
if(!in_array($section, $sections)) {
    throw new GUIUnknownAdminSectionException($section);
}


// allow a part of the page to handle a guest option
// and avoid it being handled a second time by other code
$guest_options_handled = array();

$new_guests_can_only_send_to_creator = false;


Guest::forcedOptions();
foreach (Guest::allOptions() as $name => $dfn) {
    if( $name == 'can_only_send_to_me' ) {
        $new_guests_can_only_send_to_creator_default = $dfn['default'];
        if (in_array('available', $dfn) && !$dfn['available']) {
            if (in_array('default', $dfn)) {
                $new_guests_can_only_send_to_creator = $dfn['default'];
            }
        }
    }
}


//
// Note that this needs to be called inside a div with guest_options or transfer_options
//
$displayoption = function($name, $cfg, $transfer = false)
use ( $new_guests_can_only_send_to_creator,
      $new_guests_can_only_send_to_creator_default )
{
    // don't show the option for get_a_link if they can't use it.
    if($name == 'get_a_link' && $new_guests_can_only_send_to_creator ) {
        return;
    }
    
    $default = $cfg['default'];
    if(Auth::isSP()) {
        if($transfer) {
            $default = Auth::user()->defaultTransferOptionState($name);
        } else {
            $default = Auth::user()->defaultGuestOptionState($name);
        }
    }
    if( $name == 'get_a_link' && $new_guests_can_only_send_to_creator_default ) {
        $default = false;
    }
    $checked = $default ? 'checked="checked"' : '';
    $extraDivAttrs = '';
    $hidden = '';
    if($transfer && in_array($name, array(TransferOptions::REDIRECT_URL_ON_COMPLETE))) {
        echo '<div class="custom-control custom-switch" data-option="'.$name.'" '. $extraDivAttrs .'>';
        echo '    <label for="'.$name.'">'.Lang::tr($name).'</label>';
        echo '    <input id="'.$name.'" class="custom-control-input" name="'.$name.'" type="text">';
        echo '    <br/>';
        echo '</div>';
        
    } else {
        $lockClassLabel = '';
        $lockClass = '';
        if($name == 'get_a_link' || $name == 'can_only_send_to_me') {
            $lockClass = 'get_a_link_lock';
        }
        echo '<div class="custom-control custom-switch '.$lockClass.'" '. $extraDivAttrs .'>';
        echo '  <input id="'.$name.'" class="custom-control-input" name="'.$name.'" type="checkbox" '.$checked.' />';
        echo '  <label for="'.$name.'" class="custom-control-label '.$lockClassLabel.'">'.Lang::tr($name).'</label>';
        echo '</div>';
    }
};


?>



<?php



////////////////////////
// begin html part
////////////////////////
?>

    <ul class="nav nav-tabs nav-tabs-coretop">
        <?php foreach($sections as $s) { ?>
            <li class="nav-item">
                <a class="nav-link <?php if($s == $section) echo 'active'; else echo 'nav-link-coretop ' ?>" href="?s=guests&as=<?php echo $s . $cgiuid ?>">
                    <?php echo Lang::tr($s.'_guest') ?>
                </a>
            </li>
        <?php } ?>
    </ul>

    <div class="core-tabbed">
    
    <div class="<?php echo $section ?>_section section">
        <?php if( $section == 'invite' ): ?>
    
            <div id="send_voucher" class="">
                <div class="disclamer">
                    {tr:send_new_voucher}
                </div>

                <table class="two_columns">
                    <tr>
                        <td class="">
                            <div class="fieldcontainer">
                                <?php $emails = Auth::user()->email_addresses ?>

                                <label for="from" class="mandatory">{tr:from} :</label>

                                <?php if (count($emails) > 1) { ?>

                                    <select id="from" name="from">
                                        <?php foreach ($emails as $email) { ?>
                                            <option><?php echo Template::sanitizeOutputEmail($email) ?></option>
                                        <?php } ?>
                                    </select>

                                <?php } else echo Template::sanitizeOutputEmail($emails[0]) ?>
                            </div>

                            <div class="form-group">
                                <label for="to" class="mandatory">{tr:to} :</label>

                                <div class="recipients"></div>

                                <input id="to" name="to" type="email"
                                       class="form-control"
                                       multiple title="{tr:email_separator_msg}" value="" placeholder="{tr:enter_to_email}" />
                            </div>

                            <div class="form-group">
                                <label for="subject">{tr:subject} ({tr:optional}) :</label>

                                <input id="subject" name="subject" type="text"
                                       class="form-control" />
                            </div>

                            <div class="form-group">
                                <label for="message">{tr:message} ({tr:optional}) : </label>

                                <label class="invalid" id="message_can_not_contain_urls" style="display:none;">{tr:message_can_not_contain_urls}</label>
                                <textarea id="message" name="message" rows="4" class="form-control"></textarea>
                            </div>
                        </td>

                        <td class="">
                            <div class="form-group">

                                <div class="guest_options options_box">
                                    <?php
                                    foreach(Guest::availableOptions(true) as $name => $cfg) {
                                        if( $name == 'does_not_expire' ) {
                                            $guest_options_handled[$name] = 1;
                                            $displayoption($name, $cfg, false);
                                        }
                                    }
                                    ?>
                                </div>
                                
                                <label for="expires" id="datepicker_label" class="mandatory">{tr:expiry_date}:</label>
                                
                                <input id="expires" name="expires" type="text" autocomplete="off" class="form-control"
                                       title="<?php echo Lang::trWithConfigOverride('dp_date_format_hint')->r(array('max' => Config::get('max_guest_days_valid'))) ?>"
                                       data-epoch="<?php echo Transfer::getDefaultExpire() ?>"
                                />

                                
                            </div>
                            

                            <div class="guest_options options_box">
                                <h3>{tr:guest_options}</h3>
                                
                                <div class="basic_options">
                                    <?php foreach(Guest::availableOptions(false) as $name => $cfg) $displayoption($name, $cfg, false) ?>
                                </div>
                                
                                <?php if(count(Guest::availableOptions(true))) { ?>
                                    <div class="fieldcontainer">
                                        <a class="toggle_advanced_options" href="#">{tr:advanced_settings}</a>
                                    </div>
                                    
                                    <div class="accordion" class="advanced_options" id="advanced_options">
                                        <div class="card">
                                            <div class="card-header" id="headingOne" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">{tr:advanced_settings}</div>
                                            <div id="collapseOne" class="collapse collapsed" aria-labelledby="headingOne" data-parent="#advanced_options">
                                                <div class="card-body">
                                            
                                                    <?php
                                                    foreach(Guest::availableOptions(true) as $name => $cfg) {
                                                        if( !array_key_exists($name,$guest_options_handled)) {
                                                            $displayoption($name, $cfg, false);
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>     
                                <?php } ?>
                            </div>
                            
                            <div class="transfer_options options_box">
                                <h3>{tr:guest_transfer_options}</h3>
                                
                                <div class="basic_options">
                                    <?php foreach(Transfer::availableOptions(false) as $name => $cfg) $displayoption($name, $cfg, true) ?>
                                </div>
                                
                                <?php if(count(Transfer::availableOptions(true))) { ?>
                                    <div class="accordion" class="advanced_options_tr" id="advanced_options_tr">
                                        <div class="card">
                                            <div class="card-header" id="headingOneTr" data-toggle="collapse" data-target="#collapseAdvTr" aria-expanded="true" aria-controls="collapseAdvTr">{tr:advanced_settings}</div>
                                            <div id="collapseAdvTr" class="collapse collapsed" aria-labelledby="headingOneTr" data-parent="#advanced_options_tr">
                                                <div class="card-body">
                                                    <?php foreach(Transfer::availableOptions(true) as $name => $cfg) $displayoption($name, $cfg, true) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="buttons">
                    <button type="button" class="send btn btn-primary">
                        <span class="fa fa-envelope fa-lg"></span> {tr:send_voucher}
                    </button>
                </div>
            </div>
            
        <?php endif; ?>
        <?php if( $section == 'current' ): ?>
            <div class="box">
                <h1>{tr:guests}</h1>
                
                <?php
                Template::display('guests_table',
                                  array('guests' => Guest::fromUserAvailable(Auth::user())));
                ?>
            </div>
        <?php endif; ?>
        <?php if( $section == 'transfers' ): ?>
            <div class="box">
                <h1>{tr:guests_transfers}</h1>
                
                <?php

                $user_can_only_view_guest_transfers_shared_with_them = Config::get('user_can_only_view_guest_transfers_shared_with_them');
                $transfers = Transfer::fromGuestsOf(Auth::user(), $user_can_only_view_guest_transfers_shared_with_them);
                Template::display('transfers_table',
                                  array('transfers' => $transfers,
                                        'show_guest' => true));
                ?>
            </div>
        <?php endif; ?>
    </div>

</div>
    
<script type="text/javascript" src="{path:js/guests_page.js}"></script>
