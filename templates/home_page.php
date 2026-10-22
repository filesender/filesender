<div class="fs-base-page">
    <div class="container">
        <div class="row">
            <div class="col">
                {tr:site_splash}

                <?php
                if(!Auth::isAuthenticated()) {
                    $embed = Config::get('auth_sp_embed');

                    if($embed) echo '<div class="logon fs-base-page__actions">'.$embed.'</div>';
                }
                ?>
                <script type="text/javascript" src="{path:js/home_page.js}"></script>
            </div>
        </div>
    </div>
</div>
