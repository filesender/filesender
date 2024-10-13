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
    <div class="container">
        <nav>
                <a class="fs-link fs-link--no-hover" href="<?php echo GUI::path() ?>">
                    <?php GUI::includeLogo() ?>
                    <h2>FileSender</h2>
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

                        if (Auth::isAuthenticated() && Auth::isSP())
                        {
                            $icon = '<i class="fi fi-logout"></i> ';

                            $url = AuthSP::logoffURL();
                            if($url) {
                                echo '<li>';
                                echo '<a class="fs-link"  href="'.Utilities::sanitizeOutput($url).'">'.$icon.'<span>'.Lang::tr('logout-link').'</span>'.'</a>';
                                echo '</li>';
                            }
                        }
                    ?>

                </ul>
            </nav>
    </div>
</header> <!-- New UI header - END -->
