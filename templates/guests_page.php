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
        echo '<div class="form-check form-switch custom-control custom-switch" data-option="'.$name.'" '. $extraDivAttrs .'>';
        echo '    <label for="'.$name.'">'.Lang::tr($name).'</label>';
        echo '    <input id="'.$name.'" class="form-check-input" name="'.$name.'" type="text">';
        echo '    <br/>';
        echo '</div>';

    } else {
        $lockClassLabel = '';
        $lockClass = '';
        if($name == 'get_a_link' || $name == 'can_only_send_to_me') {
            $lockClass = 'get_a_link_lock';
        }
        echo '<div class="form-check form-switch custom-control custom-switch '.$lockClass.'" '. $extraDivAttrs .'>';
        echo '  <input id="'.$name.'" class="form-check-input" name="'.$name.'" type="checkbox" '.$checked.' />';
        echo '  <label for="'.$name.'" class="form-check-label '.$lockClassLabel.'">'.Lang::tr($name).'</label>';
        echo '</div>';
    }
};

$read_only_mode = Config::get('read_only_mode');


?>

<?php if($read_only_mode) { ?>
<div class="box">
    {tr:read_only_mode}
</div>
<?php } else { ?>
<div class="fs-invitations">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="fs-invitations__header">
                    <h1>{tr:guests_page}</h1>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-6 offset-lg-3">
                <div class="fs-invitations__action">
                    <h2>{tr:invitation_title}</h2>
                    <p>
                        {tr:invitation_description}
                    </p>

                    <a href="?s=new_invitation" class="fs-button">
                        <i class="fa fa-plus"></i>
                        <span>{tr:new_invitation}</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="fs-invitations__list">
                    <h2>{tr:all_my_invitations}</h2>
                    <p>
                        {tr:invitation_overview}
                    </p>

                    <?php
                    Template::display('guests_table',
                        array('guests' => Guest::fromUserAvailable(Auth::user())));
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<script type="text/javascript" src="{path:js/guests_page.js}"></script>
