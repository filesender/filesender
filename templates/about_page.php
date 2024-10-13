<?php
      include_once "pagemenuitem.php"
?>

<div id="dialog-about" title="About" class="fs-base-page">
    <div class="container">
        <div class="row">
            <div class="col">
                <?php
                    if (Auth::isAuthenticated()) {
                ?>
                    <div class="row">
                        <div class="col">
                            <a id='fs-back-link' class='fs-link fs-link--primary fs-link--no-hover fs-back-link'>
                                <i class='fi fi-chevron-left'></i>
                                <span>{tr:back_to_settings}</span>
                            </a>
                        </div>
                    </div>
                <?php
                    }
                ?>

                <div class="row">
                    <div class="col">
                        <div class="fs-base-page__header mt-5">
                            <h1>{tr:about_title}</h1>
                        </div>
                    </div>
                </div>

                <div class="fs-base-page__content">
                    {tr:about_text}
                </div>
            </div>
        </div>
    </div>
</div>

