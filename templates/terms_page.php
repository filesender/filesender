<?php
include_once "pagemenuitem.php"
?>

<div id="dialog-about" title="About" class="fs-base-page">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="fs-base-page__header">
                    <?php
                    if (Auth::isAuthenticated()) {
                        echo "<a id='fs-back-link' class='fs-link fs-link--circle'>
                                <i class='fa fa-angle-left'></i>
                            </a>";
                    }
                    ?>
                    <h1>{tr:terms_title}</h1>
                </div>

                <div class="fs-base-page__content">
                    {tr:terms_text}
                </div>
            </div>
        </div>
    </div>
</div>

