<div class="fs-base-page">
    <div class="container">
        <div class="row">
            <div class="col">
                <?php if (Auth::isAuthenticated()) { ?>
                    <a id="fs-back-link" class="fs-link fs-link--primary fs-link--no-hover fs-back-link fs-base-page__back">
                        <i class="fi fi-chevron-left"></i>
                        <span>{tr:back_to_settings}</span>
                    </a>
                <?php } ?>

                <div class="fs-base-page__header">
                    <h1>{tr:terms_title}</h1>
                </div>

                <div class="fs-base-page__content">
                    {tr:terms_text}
                </div>
            </div>
        </div>
    </div>
</div>
