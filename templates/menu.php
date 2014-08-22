<?php

$pagemenuitem = function($page) use($current_page, $allowed_pages) {
    if(!in_array($page, $allowed_pages)) return;
    $class = ($page == $current_page) ? 'current' : '';
    echo '<li><a class="'.$class.'" id="topmenu_'.$page.'" href="?s='.$page.'">'.Lang::tr($page.'_page').'</a></li>';
};

?>

<div id="menu">
    <div class="leftmenu">
        <ul>
            <?php
            
            if(!Auth::isVoucher()) {
                $pagemenuitem('upload');
                
                $pagemenuitem('vouchers');
                
                $pagemenuitem('transfers');
                
                $pagemenuitem('admin');
            }
            
            ?>
        </ul>
    </div>
    
    <div class="rightmenu">
        <ul>
        <?php
            $helpurl = Config::get('helpURL');
            echo '<li><a href="'.($helpurl ? $helpurl : '#').'" id="topmenu_help">'.Lang::tr('help').'</a></li>';
            
            $abouturl = Config::get('aboutURL');
            echo '<li><a href="'.($abouturl ? $abouturl : '#').'" id="topmenu_about">'.Lang::tr('about').'</a></li>';
            
            if (Auth::isAuthenticated() && Auth::isSP()) {
                $url = AuthSP::logoffURL();
                if($url)
                    echo '<li><a href="'.$url.'" id="topmenu_logoff">'.Lang::tr('logoff').'</a></li>';
            }else{
                if(Config::get('auth_sp_embedded')) {
                    $menupage('logon');
                }else{
                    echo '<li><a href="'.AuthSP::logonURL().'" id="topmenu_logon">'.Lang::tr('logon').'</a></li>';
                }
            }
        ?>
        </ul>
    </div>
</div>
