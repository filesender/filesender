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
        
        <?php if(!Config::get('helpURL')) { ?>
        <div id="dialog-help" style="display:none" title="{tr:help}">
            {tr:help_text}
        </div>
        <?php } ?>
        
        <?php if(!Config::get('aboutURL')) { ?>
        <div id="dialog-about" style="display:none" title="{tr:about}">
            {tr:about_text}
        </div>
        <?php } ?>
        
        <!-- Version <?php //echo FileSender_Version::VERSION; ?> -->
    </body>
</html>
