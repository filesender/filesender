<?php

include_once "vidattr.php";

function pagelink($page) {
    if(!GUI::isUserAllowedToAccessPage($page)) return;
    $class = ($page == GUI::currentPage()) ? ' fs-link--active ' : '';

    echo '<div><a class="'.$class.'" href="?s='.$page.'">'.Lang::tr($page.'_page_link').'</a></div>';
}

function pagemenuitem($page, $itemClass = '') {
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
    if( $page == 'transfers_guest' ) {
        $label = Lang::tr('transfers_page');
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
    if($page == 'transfers' || $page == 'transfers_guest') {
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

    echo $itemClass ? '<li class="'.$itemClass.'">' : '<li>';
    echo '<a class="fs-link '.$class.'"  id="topmenu_'.$page.'" href="?s='.$page.$vidattr.'">'.$icon.'<span>'.$label.'</span>'.'</a>';
    echo '</li>';
}

function pagemenudropdown($id, $label, $icon, $pages) {
    $pages = array_values(array_filter($pages, function($page) {
        return GUI::isUserAllowedToAccessPage($page);
    }));

    if(!count($pages)) return;

    if(count($pages) == 1) {
        pagemenuitem($pages[0]);
        return;
    }

    $class = in_array(GUI::currentPage(), $pages) ? ' fs-link--active ' : '';

    echo '<li class="fs-dropdown">';
    echo '<button type="button" class="fs-link fs-dropdown__toggle '.$class.'" id="topmenu_'.$id.'" aria-haspopup="true" aria-expanded="false" aria-controls="topmenu_'.$id.'_menu">';
    echo $icon.'<span>'.$label.'</span><i class="fi fi-chevron-down fs-dropdown__chevron"></i>';
    echo '</button>';
    echo '<ul class="fs-dropdown__menu" id="topmenu_'.$id.'_menu">';
    foreach($pages as $page) {
        pagemenuitem($page, 'fs-dropdown__item');
    }
    echo '</ul>';
    echo '</li>';
}

