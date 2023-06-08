        <footer>
            <div class="container">
                <div class="fs-footer-container">
                    <div class="fs-footer-container--brand-list">
                        <a href="" class="fs-link fs-link--no-hover">
                            <img src="{cfg:site_url}/images/filesender-initiative.jpg" alt="Brand">
                        </a>
                    </div>
                </div>
            </div>

<!--            --><?php //if(Disclosed::isDisclosed('version')) { ?>
                <div class="fs-version">
                    <?php echo Lang::tr('version') ?>
<!--                    v--><?php //echo Version::code() ?>
                    v3.0
                </div>
<!--            --><?php //} ?>
        </footer>
    </body>
</html>
