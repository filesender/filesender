<?php
include_once "pagemenuitem.php";
$ver = Config::get('service_aup_min_required_version');
?>
<div class="box serviceaup" data-service-aup-version="<?php echo $ver ?>">

    <h1>{tr:service_aup_header}</h1>

    <?php echo Lang::tr('service_aup_body_version_' . $ver ) ?>
    
    <div class="service_aup_accept">
        <a href="#">
            <span class="fa fa-lg fa-check"></span>
            {tr:ui2_accept_aup_1}
        </a>
    </div>

    
    
</div>

<script type="text/javascript" src="{path:js/service_aup_page.js}"></script>
