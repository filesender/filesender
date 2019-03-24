<?php

$maybe_display_aggregate_statistics_menu = false;

$pagemenuitem = function($page) {
    if(!GUI::isUserAllowedToAccessPage($page)) return;
    $class = ($page == GUI::currentPage()) ? 'current' : '';
    echo '<li><a class="'.$class.'" id="topmenu_'.$page.'" href="?s='.$page.'">'.Lang::tr($page.'_page').'</a></li>';
};

?>




<div id="menu">
    <div class="leftmenu">
        <ul>
            <?php
            
            if(!Auth::isGuest()) {
                $pagemenuitem('upload');
                
                $pagemenuitem('guests');
                
                $pagemenuitem('transfers');
                
                if(Config::get('user_page'))
                    $pagemenuitem('user');
                
                $pagemenuitem('admin');

                if( $maybe_display_aggregate_statistics_menu ) {
                    if (AggregateStatistic::enabled()) {
                        $pagemenuitem('aggregate_statistics');
                    }
                }
                    
            }
            
            ?>
        </ul>
    </div>
    
    <div class="rightmenu">
        <ul>
        <?php
            $pagemenuitem('help');
            $pagemenuitem('about');
            $pagemenuitem('privacy');

            if (Auth::isAuthenticated() && Auth::isSP()) {
                $url = AuthSP::logoffURL();
                if($url)
                    echo '<li><a href="'.Utilities::sanitizeOutput($url).'" id="topmenu_logoff">'.Lang::tr('logoff').'</a></li>';
            }else if (!Auth::isGuest()){
                if(Config::get('auth_sp_embedded')) {
                    $pagemenuitem('logon');
                }else{
                    echo '<li><a href="'.Utilities::sanitizeOutput(AuthSP::logonURL()).'" id="topmenu_logon">'.Lang::tr('logon').'</a></li>';
                }
            }
        ?>
        </ul>
            
    </div>

    
</div>

