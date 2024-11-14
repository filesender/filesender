        <footer>
            <div class="container">
                <div class="fs-footer-container">
                    <small>{tr:copyright}</small>
                    <img class="fs-footer-container__brand fs-footer-container__brand--desktop" src="{cfg:site_url}/images/filesender-initiative.png" alt="Brand">
                    <img class="fs-footer-container__brand fs-footer-container__brand--mobile" src="{cfg:site_url}/images/filesender-initiative_mobile.png" alt="Brand">
                </div>
            </div>

            <?php if(Disclosed::isDisclosed('version')) { ?>
                <div class="fs-version">
                    <?php echo Lang::tr('version') ?>
                    <?php echo Version::code() ?>
                </div>
            <?php } ?>

        </footer>
    </body>
</html>
