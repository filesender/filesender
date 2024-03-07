<?php

include_once "vidattr.php";

function pagelink($page) {
    if(!GUI::isUserAllowedToAccessPage($page)) return;
    $class = ($page == GUI::currentPage()) ? 'current' : '';
    
    echo '<div><a class="'.$class.'" href="?s='.$page.'">'.Lang::tr($page.'_page_link').'</a></div>';
}

function pagemenuitem($page) {
    global $vidattr;
    
    if(!GUI::isUserAllowedToAccessPage($page)) return;
    $class = ($page == GUI::currentPage()) ? 'current' : '';

    $label = Lang::tr($page.'_page');
    if( $page == 'transfers' ) {
        if (Auth::isAuthenticated()) {
            if (Auth::isAdmin()) {
                $uid = Utilities::arrayKeyOrDefault( $_GET, 'uid', 0, FILTER_VALIDATE_INT  );
                if( $uid ) {
                    $label = $label = Lang::tr($page.'_uid_page');
                    $class .= ' red';
                }
            }
        }
    }
    echo '<li><a class="'.$class.'" id="topmenu_'.$page.'" href="?s='.$page.$vidattr.'">'.$label.'</a></li>';
}

