<!-- New UI main container - BEGIN -->
<main>
    <div id="page" class="<?php echo GUI::currentPage() ?>_page">

        <noscript>
            <div class="error message">
                {tr:noscript}
            </div>
        </noscript>

        <?php Template::display(GUI::currentPage().'_page', $vars) ?>
    </div>
</main> <!-- New UI main container - END -->
