<?php

include_once "pagemenuitem.php";
include_once "vidattr.php";

$maybe_display_aggregate_statistics_menu = false;

$LanguageSelectorShown = false;
if(Config::get('lang_selector_enabled') && (count(Lang::getAvailableLanguages()) > 1)) {
    $LanguageSelectorShown   = true;
}

?>

<!-- New UI header - BEGIN -->
<header>

    <nav>
        <a class="fs-link fs-link--no-hover" href="{cfg:site_url}">
            <img src="{cfg:site_url}images/filesender-logo.svg" alt="Filesender Logo">
        </a>
        <ul>
            <?php

                if(!Auth::isGuest()) {
                    pagemenuitem('upload');

                    pagemenuitem('transfers');

                    if(Config::get('guest_support_enabled')) {
                        pagemenuitem('guests');
                    }

                    if(Config::get('user_page')) {
                        pagemenuitem('user');
                    }

                    pagemenuitem('statistics');

                    pagemenuitem('admin');

                    if( $maybe_display_aggregate_statistics_menu ) {
                        if (AggregateStatistic::enabled()) {
                            pagemenuitem('aggregate_statistics');
                        }
                    }

                }

                if (!Auth::isAuthenticated()) {
                    pagemenuitem('help');
                    pagemenuitem('about');
                    pagemenuitem('privacy');
                }

                if (Auth::isAuthenticated() && Auth::isSP())
                {
                    $faicon = 'fa-sign-out';
                    $icon = '<i class="fa '.$faicon.'"></i> ';

                    $url = AuthSP::logoffURL();
                    if( Config::get('auth_sp_type') == "saml" ) {

                        $link = Utilities::sanitizeOutput($url);
                        $txt = Lang::tr('logoff');
                        echo <<<EOT
                        <li>
                          <form action="$link" method="post" >
                            <button class="fs-link" type="submit" >
                                ${icon}
                                <span>$txt</span>
                            </button>
                          </form>
                        </li>
EOT;
                    } else {
                        if($url) {
                            echo '<li><a class="fs-link" href="'.Utilities::sanitizeOutput($url).'" id="topmenu_logoff">'.$icon.Lang::tr('logoff').'</a></li>';
                        }
                    }
                }
                if (!Auth::isAuthenticated() && !Auth::isSP() && !Auth::isGuest())
                {
                    $faicon = 'fa-sign-in';
                    $icon = '<i class="fa '.$faicon.'"></i> ';

                    if(Config::get('auth_sp_embedded')) {
                        pagemenuitem('logon');
                    }else{
                        echo '<li><a class="fs-link" href="'.Utilities::sanitizeOutput(AuthSP::logonURL()).'" id="topmenu_logon">'.$icon.'<span>'.Lang::tr('logon').'</span>'.'</a></li>';
                    }
                }
            ?>

<!--            --><?php //if($LanguageSelectorShown): ?>
<!---->
<!--                <li class="nav-item dropdown language-selector">-->
<!---->
<!--                    --><?php
//                    $code = Lang::getCode();
//                    foreach(Lang::getAvailableLanguages() as $id => $dfn) {
//                        if($id == $code) {
//                            $specificid = $dfn['specific-id'];
//                            echo '<a class="nav-link dropdown-toggle language-dropdown-toggle" ';
//                            echo ' href="#" ';
//                            echo ' id="toplangdropdownlabel" ';
//                            echo ' data-bs-toggle="dropdown" ';
//                            echo ' aria-haspopup="true" ';
//                            echo ' aria-expanded="false"> ';
//                            echo '  <span class="fi fi-'.$specificid.'"> </span> '.Utilities::sanitizeOutput($dfn['name']).'</a> ';
//                        }
//                    }
//                    ?>
<!---->
<!--                    <div class="dropdown-menu" aria-labelledby="toplangdropdownlabel" id="toplangdropdown">-->
<!--                        --><?php
//                        $code = Lang::getCode();
//                        foreach(Lang::getAvailableLanguages() as $id => $dfn) {
//                            $specificid = $dfn['specific-id'];
//                            $selected = ($id == $code) ? 'selected="selected"' : '';
//                            echo '<a class="dropdown-item toplangdropitem" data-id="'.$id.'"  href="#">';
//                            echo '<span class="fi fi-'.$specificid.'"> </span> '.Utilities::sanitizeOutput($dfn['name']).'</a>';
//
//                        }
//                        ?>
<!--                    </div>-->
<!--                </li>-->
<!--            --><?php //endif; ?>
        </ul>
    </nav>


</header> <!-- New UI header - END -->

