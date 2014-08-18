<div class="box">
    <h1>{tr:site_splashead}</h1>
    
    {tr:site_splastext}
    
    <div class="logon">
        <?php
        
        $embed = Config::get('auth_sp_embed');
        
        if(!$embed) $embed = '<a id="btn_logon" href="'.AuthSP::logonURL().'">'.Lang::tr('logon').'</a>';
        
        echo $embed;
        
        ?>
    </div>
</div>
