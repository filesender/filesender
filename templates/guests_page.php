<?php

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
        echo '<div class="fieldcontainer" data-option="'.$name.'" '. $extraDivAttrs .'>';
        echo '    <label for="'.$name.'">'.Lang::tr($name).'</label>';
        echo '    <input id="'.$name.'" name="'.$name.'" type="text">';
        echo '    <br/>';
        echo '</div>';
        
    } else {
        $lockClassLabel = '';
        $lockClass = '';
        if($name == 'get_a_link' || $name == 'can_only_send_to_me') {
            $lockClass = 'get_a_link_lock';
        }
        echo '<div class="fieldcontainer '.$lockClass.'" '. $extraDivAttrs .'>';
        echo '  <input id="'.$name.'" name="'.$name.'" type="checkbox" '.$checked.' />';
        echo '  <label for="'.$name.'" class="'.$lockClassLabel.'">'.Lang::tr($name).'</label>';
        echo '</div>';
    }
};

$read_only_mode = Config::get('read_only_mode');


?>



<?php 
////////////////////////
// begin html part
////////////////////////
?>
<?php if($read_only_mode) { ?>
<div class="box">
    {tr:read_only_mode}
</div>
<?php } else { ?>
<div class="box">

    <div id="send_voucher" class="box">
        <div class="disclamer">
            {tr:send_new_voucher}
        </div>

        <table class="two_columns">
            <tr>
                <td class="box">
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

                    <div class="fieldcontainer">
                        <label for="to" class="mandatory">{tr:to} :</label>

                        <div class="recipients"></div>

                        <input id="to" name="to" type="email" multiple title="{tr:email_separator_msg}" value="" placeholder="{tr:enter_to_email}" />
                    </div>

                    <div class="fieldcontainer">
                        <label for="subject">{tr:subject} ({tr:optional}) :</label>

                        <input id="subject" name="subject" type="text"/>
                    </div>

                    <div class="fieldcontainer">
                        <label for="message">{tr:message} ({tr:optional}) : </label>

                        <label class="invalid" id="message_can_not_contain_urls">{tr:message_can_not_contain_urls}</label>
                        <textarea id="message" name="message" rows="4"></textarea>
                    </div>
                </td>

                <td class="box">
                    <div class="fieldcontainer">

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
                        
                        <input id="expires" name="expires" type="text" autocomplete="off"
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
                        
                         <div class="advanced_options">
                             <?php
                             foreach(Guest::availableOptions(true) as $name => $cfg) {
                                 if( !array_key_exists($name,$guest_options_handled)) {
                                     $displayoption($name, $cfg, false);
                                 }
                             }
                             ?>
                         </div>     
                        <?php } ?>
                    </div>
                    
                    <div class="transfer_options options_box">
                        <h3>{tr:guest_transfer_options}</h3>
                        
                        <div class="basic_options">
                            <?php foreach(Transfer::availableOptions(false) as $name => $cfg) $displayoption($name, $cfg, true) ?>
                        </div>
                        
                        <?php if(count(Transfer::availableOptions(true))) { ?>
                        <div class="fieldcontainer">
                            <a class="toggle_advanced_options" href="#">{tr:advanced_settings}</a>
                        </div>
                        
                         <div class="advanced_options">
                            <?php foreach(Transfer::availableOptions(true) as $name => $cfg) $displayoption($name, $cfg, true) ?>
                         </div>     
                        <?php } ?>
                    </div>
                </td>
            </tr>
        </table>

        <div class="buttons">
            <a href="#" class="send">
                <span class="fa fa-envelope fa-lg"></span> {tr:send_voucher}
            </a>
        </div>
    </div>
</div>
<?php } ?>

<div class="box">
    <h1>{tr:guests}</h1>
    
    <?php
    Template::display('guests_table',
                      array('guests' => Guest::fromUserAvailable(Auth::user())));
    ?>
</div>

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
    
<script type="text/javascript" src="{path:js/guests_page.js}"></script>
