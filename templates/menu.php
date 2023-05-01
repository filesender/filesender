<?php

include_once "pagemenuitem.php";
include_once "vidattr.php";

$maybe_display_aggregate_statistics_menu = false;

$LanguageSelectorShown = false;
if(Config::get('lang_selector_enabled') && (count(Lang::getAvailableLanguages()) > 1)) {
    $LanguageSelectorShown   = true;
}

?>

<div class="row">
    <div class="col-xl-12">
        <nav class="nav nav-pills navbar-expand-md dnav-fill navbar-light navbar-bg navbar-fixed-top ">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarFilesender" aria-controls="navbarFilesender" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	    </button>
            <div class="collapse navbar-collapse" id="navbarFilesender">
            <?php
            
            if(!Auth::isGuest()) {
                pagemenuitem('upload');

                if(Config::get('guest_support_enabled')) {
                    pagemenuitem('guests');
                }
                
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
                if( Config::get('auth_sp_type') == "saml" ) {
                    
                    $link = Utilities::sanitizeOutput($url);
                    $txt = Lang::tr('logoff');
                    echo <<<EOT
                    <li>
                      <form action="$link" method="post" >
                        <button class="logoutbutton" type="submit" >$txt</button>
                      </form>
                    </li>
EOT;
                } else {
                    if($url) {
                        $faicon = 'fa-sign-out';
                        $icon = '<i class="fa '.$faicon.'"></i> ';
                        
                        echo '<div class="nav-item"><a class="p-2 nav-link" href="'.Utilities::sanitizeOutput($url).'" id="topmenu_logoff">'.$icon.Lang::tr('logoff').'</a></div>';
                    }
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


                
<?php if($LanguageSelectorShown): ?>
            
            <li class="nav-item dropdown language-selector">

                <?php 
                $code = Lang::getCode();
                foreach(Lang::getAvailableLanguages() as $id => $dfn) {
                    if($id == $code) {
                        $specificid = $dfn['specific-id'];
                        echo '<a class="nav-link dropdown-toggle language-dropdown-toggle" ';
                        echo ' href="#" ';
                        echo ' id="toplangdropdownlabel" ';
                        echo ' data-bs-toggle="dropdown" ';
                        echo ' aria-haspopup="true" ';
                        echo ' aria-expanded="false"> ';
                        echo '  <span class="fi fi-'.$specificid.'"> </span> '.Utilities::sanitizeOutput($dfn['name']).'</a> ';
                    }
                }
                ?>
                
                <div class="dropdown-menu" aria-labelledby="toplangdropdownlabel" id="toplangdropdown">
                    <?php 
                    $code = Lang::getCode();
                    foreach(Lang::getAvailableLanguages() as $id => $dfn) {
                        $specificid = $dfn['specific-id'];
                        $selected = ($id == $code) ? 'selected="selected"' : '';
                        echo '<a class="dropdown-item toplangdropitem" data-id="'.$id.'"  href="#">';
                        echo '<span class="fi fi-'.$specificid.'"> </span> '.Utilities::sanitizeOutput($dfn['name']).'</a>';
                        
                    }
                    ?>
                </div>
            </li>
<?php endif; ?>
          </div>  
        </nav>
    </div>
</div>

</header>
</div>

<div class="container">
<div id="wrap">

