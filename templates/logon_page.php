<div class="box">
    {tr:site_splash}
    
    <div class="logon">
        <?php
        $page = null;
        if (array_key_exists('s', $_REQUEST)) {
            $page = Utilities::http_build_query(array('s' => $_REQUEST['s']));
        }
        echo GUI::getLoginButton($page);
        ?>
    </div>
</div>
