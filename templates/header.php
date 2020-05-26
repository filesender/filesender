<!DOCTYPE html>
<?php
    $headerclass = "header";

    if (Auth::isAuthenticated()) {
        if (Auth::isAdmin()) {
            
            $uid = Utilities::arrayKeyOrDefault( $_GET, 'uid', 0, FILTER_VALIDATE_INT  );
            if( $uid ) {
                if( Config::get('admin_can_view_user_transfers_page')) {
                    $headerclass = 'headeruid';
                }
            }
        }
    }
?>

<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo Lang::getCode() ?>" xml:lang="<?php echo Lang::getCode() ?>">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        
        <title><?php echo htmlspecialchars(Config::get('site_name')); ?></title>
        
        <?php GUI::includeStylesheets() ?>
        
        <?php GUI::includeFavicon() ?>
        
        <?php GUI::includeScripts() ?>
        
        <script type="text/javascript" src="{path:filesender-config.js.php}"></script>
        
        <script type="text/javascript" src="{path:rest.php/lang?callback=lang.setTranslations}"></script>
        
        <meta name="robots" content="noindex, nofollow" />
        
        <meta name="auth" content="noindex, nofollow" />
    </head>
    
    <body data-security-token="<?php echo Utilities::getSecurityToken() ?>" data-auth-type="<?php echo Auth::type() ?>">
        <div id="wrap">
            
            <div id="<?php echo $headerclass; ?>">
                <a href="<?php echo GUI::path() ?>">
                    <?php GUI::includeLogo() ?>
                    
                    <?php if(Config::get('site_name_in_header')) { ?>
                    <div class="site_name"><?php echo Config::get('site_name') ?></div>
                    <div class="site_baseline"><?php echo Config::get('site_baseline') ?></div>
                    <?php } ?>
                </a>
            </div>

                        <?php
                        if(Config::get('lang_selector_enabled') && (count(Lang::getAvailableLanguages()) > 1)) {
                            echo '<div id="langmenu">';
                            echo '   <div class="rightlangmenu">';
                            echo '       <ul>';
                            $opts = array();
                            $code = Lang::getCode();
                            foreach(Lang::getAvailableLanguages() as $id => $dfn) {
                                $selected = ($id == $code) ? 'selected="selected"' : '';
                                $opts[] = '<option value="'.$id.'" '.$selected.'>'.Utilities::sanitizeOutput($dfn['name']).'</option>';
                            }
                        
                            echo '<li><label>'.Lang::tr('user_lang').'</label><select id="language_selector">'.implode('', $opts).'</select></li>';
                            echo '</ul></div></div>';
                        }
                        ?>
            
