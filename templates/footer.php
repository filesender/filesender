            <div id="footer">
                {tr:site_footer}
                
                <?php if(Disclosed::isDisclosed('version')) { ?>
                <div class="version"><?php echo Version::code() ?></div>
                <?php } ?>
            </div>
        </div>
    </body>
</html>
