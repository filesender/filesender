<?php
include_once "pagemenuitem.php";
$ver = Config::get('service_aup_min_required_version');
?>
<div class="fs-base-page serviceaup" data-service-aup-version="<?php echo $ver ?>">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="fs-base-page__header">
                    <h1>{tr:service_aup_header}</h1>
                </div>
                <?php echo Lang::tr('service_aup_body_version_' . $ver ) ?>

                <div class="service_aup_accept">
                    <a href="#" class="fs-button">
                        <i class="fa fa-lg fa-check"></i>
                        {tr:ui2_accept_aup_1}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{path:js/service_aup_page.js}"></script>
