<?php

include_once "pagemenuitem.php";

$maybe_display_aggregate_statistics_menu = false;

$LanguageSelectorShown = false;
$LanguageSelectorOptions = array();

if(Config::get('lang_selector_enabled') && (count(Lang::getAvailableLanguages()) > 1)) {
    $LanguageSelectorShown   = true;
    $LanguageSelectorOptions = array();
    $code = Lang::getCode();
    foreach(Lang::getAvailableLanguages() as $id => $dfn) {
        $selected = ($id == $code) ? 'selected="selected"' : '';
        $LanguageSelectorOptions[] = '<option value="'.$id.'" '.$selected.'>'.Utilities::sanitizeOutput($dfn['name']).'</option>';
    }
}

?>

<div class="row">
    <div class="col-12">
        <nav class="nav nav-pills nav-fill ">

            <?php
            
            if(!Auth::isGuest()) {
                pagemenuitem('upload');
                
                pagemenuitem('guests');
                
                pagemenuitem('transfers');
                
                if(Config::get('user_page'))
                    pagemenuitem('user');
                
                pagemenuitem('statistics');
                
                pagemenuitem('admin');

                if( $maybe_display_aggregate_statistics_menu ) {
                    if (AggregateStatistic::enabled()) {
                        pagemenuitem('aggregate_statistics');
                    }
                }
                    
            }
            
            pagemenuitem('help');
            pagemenuitem('about');
            pagemenuitem('privacy');

            if (Auth::isAuthenticated() && Auth::isSP())
            {
                $url = AuthSP::logoffURL();
                if($url) {
                    $faicon = 'fa-sign-out';
                    $icon = '<i class="fa '.$faicon.'"></i> ';
                    
                    echo '<div class="nav-item"><a class="p-2 nav-link" href="'.Utilities::sanitizeOutput($url).'" id="topmenu_logoff">'.$icon.Lang::tr('logoff').'</a></div>';
                }
            }
            else if (!Auth::isGuest())
            {
                $faicon = 'fa-sign-in';
                $icon = '<i class="fa '.$faicon.'"></i> ';
                
                if(Config::get('auth_sp_embedded')) {
                    pagemenuitem('logon');
                }else{
                    echo '<div class="nav-item"><a class="p-2 nav-link" href="'.Utilities::sanitizeOutput(AuthSP::logonURL()).'" id="topmenu_logon">'.$icon.Lang::tr('logon').'</a></div>';
                }
            }
        ?>

        </nav>
    </div>
</div>
<?php if($LanguageSelectorShown): ?>
    <div class="row">
        <div class="col-12">
            <div class="form-inline float-right">
                <div class="form-group">
                    <label for="language_selector" class="mr-1"><?php echo Lang::tr('user_lang') ?></label>
                    <select class="form-control" id="language_selector"><?php echo implode('', $LanguageSelectorOptions) ?></select>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

</header>
</div>

<div class="container">
<div id="wrap">

