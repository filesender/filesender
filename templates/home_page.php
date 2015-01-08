<div class="box">
    {tr:site_splash}
    
    <?php
    
    if(!Auth::isAuthenticated()) {
        $embed = Config::get('auth_sp_embed');
        
        if($embed) echo '<div class="logon">'.$embed.'</div>';
    }
    
    ?>
</div>
