<?php


function pagelink($page) {
    if(!GUI::isUserAllowedToAccessPage($page)) return;
    $class = ($page == GUI::currentPage()) ? 'current' : '';
    
    echo '<div><a class="'.$class.'" href="?s='.$page.'">'.Lang::tr($page.'_page_link').'</a></div>';
}

function pagemenuitem($page) {
    if(!GUI::isUserAllowedToAccessPage($page)) return;
    $class = ($page == GUI::currentPage()) ? 'current' : '';
    echo '<li><a class="'.$class.'" id="topmenu_'.$page.'" href="?s='.$page.'">'.Lang::tr($page.'_page').'</a></li>';
}

