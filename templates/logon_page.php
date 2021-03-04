<div class="core">
    {tr:site_splash}
    
    <div class="logon">
        <?php
        
        $embed = Config::get('auth_sp_embed');
        
        if(!$embed) $embed = '<a class="btn btn-primary" id="btn_logon" href="'.AuthSP::logonURL().'">'.Lang::tr('logon').'</a>';
        
        echo $embed;
        
        ?>
    </div>
</div>
