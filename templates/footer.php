            <div id="footer">
                {tr:site_footer}
                
            <?php
                $versionDisplay = '';
                
                //if(Config::get('site_showStats')) $versionDisplay .= $functions->getStats(); // TODO
        
                //if(Config::get('versionNumber')) $versionDisplay .= FileSender_Version::VERSION; // TODO
        
                echo '<div class="versionnumber">'.$versionDisplay.'</div>';
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
