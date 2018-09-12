<?php

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
            }
            
            ?>
        </ul>
    </div>
    
    <div class="rightmenu">
        <ul>
        <?php
            if(Config::get('lang_selector_enabled') && (count(Lang::getAvailableLanguages()) > 1)) {
                $opts = array();
                $code = Lang::getCode();
                foreach(Lang::getAvailableLanguages() as $id => $dfn) {
                    $selected = ($id == $code) ? 'selected="selected"' : '';
                    $opts[] = '<option value="'.$id.'" '.$selected.'>'.Utilities::sanitizeOutput($dfn['name']).'</option>';
                }
                
                echo '<li><select id="language_selector">'.implode('', $opts).'</select></li>';
            }

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
