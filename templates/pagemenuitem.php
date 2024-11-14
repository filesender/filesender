<?php

include_once "vidattr.php";

function pagelink($page) {
    if(!GUI::isUserAllowedToAccessPage($page)) return;
    $class = ($page == GUI::currentPage()) ? ' fs-link--active ' : '';

    echo '<div><a class="'.$class.'" href="?s='.$page.'">'.Lang::tr($page.'_page_link').'</a></div>';
}

function pagemenuitem($page) {
    global $vidattr;

    if(!GUI::isUserAllowedToAccessPage($page)) return;
    $class = ($page == GUI::currentPage()) ? ' fs-link--active ' : '';

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
    $icon = '';
    $faicon = '';


    // PUBLIC MENU
    if($page == 'help') {
        $icon = '<i class="fa fa-question-circle"></i> ';
    }
    if($page == 'about') {
        $icon = '<i class="fa fa-info-circle"></i> ';
    }
    if($page == 'privacy') {
        $icon = '<i class="fa fa-lock"></i> ';
    }

    // PRIVATE MENU
    if($page == 'upload') {
        $icon = '<i class="fi fi-add"></i> ';
    }
    if($page == 'transfers') {
        $icon = '<i class="fi fi-box"></i> ';
    }
    if($page == 'guests') {
        $icon = '<i class="fi fi-list"></i> ';
    }
    if($page == 'user') {
        $icon = '<i class="fi fi-settings"></i> ';
    }
    if($page == 'admin') {
        $icon = '<i class="fi fi-settings"></i> ';
    }
    if($page == 'statistics') {
        $icon = '<i class="fa fa-bar-chart"></i> ';
    }

    echo '<li>';
    echo '<a class="fs-link '.$class.'"  id="topmenu_'.$page.'" href="?s='.$page.$vidattr.'">'.$icon.'<span>'.$label.'</span>'.'</a>';
    echo '</li>';
}

