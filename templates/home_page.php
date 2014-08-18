<div class="box">
    <h1>{tr:site_splashead}</h1>
    
    {tr:site_splastext}
    
    <?php
    
    if(!Auth::isAuthenticated()) {
        $embed = Config::get('auth_sp_embed');
        
        if($embed) echo '<div class="logon">'.$embed.'</div>';
    }
    
    ?>
</div>
