            <div id="footer">
                {tr:site_footer}
                
                <?php if(Disclosed::isDisclosed('version')) { ?>
                <div class="version"><?php echo Version::code() ?></div>
                <?php } ?>
            <?php
                //if(Config::get('site_showStats')) $versionDisplay .= $functions->getStats(); // TODO
            ?>
            </div>
        </div>
        
        <!-- Version <?php //echo FileSender_Version::VERSION; ?> -->
    </body>
</html>
