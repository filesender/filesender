<?php

$pagemenuitem = function($page) {
    if(!GUI::isUserAllowedToAccessPage($page)) return;
    $class = ($page == GUI::currentPage()) ? 'current' : '';
    echo '<div><a class="'.$class.'" href="?s='.$page.'">'.Lang::tr($page.'_page_link').'</a></div>';
};
?>

<div class="box">

    <div id="dialog-about" title="About">
        {tr:about_text}
    </div>

    <?php 
    if (AggregateStatistic::enabled()) {
        $pagemenuitem('aggregate_statistics');
    }
    ?>
</div>
