<!DOCTYPE html>
<?php
include_once "vidattr.php";

$headerclass = "header";

    try {
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
    } catch (Exception $e) {
        // this is just for $headerclass if they are a superuser.
        // nothing to do on failure
    }
?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo Lang::getCode() ?>" xml:lang="<?php echo Lang::getCode() ?>">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title><?php echo htmlspecialchars(Config::get('site_name')); ?></title>
<?php GUI::includeStylesheets() ?>
<?php GUI::includeFavicon() ?>
<?php GUI::includeScripts() ?>
        <script type="text/javascript" src="{path:filesender-config.js.php}"></script>
        <script type="text/javascript" src="{path:rest.php/lang?callback=lang.setTranslations<?php echo $vidattr ?>}"></script>
        <meta name="robots" content="noindex, nofollow" />
        <meta name="auth" content="noindex, nofollow" />
    </head>
    <body data-security-token="<?php echo Utilities::getSecurityToken() ?>" data-auth-type="<?php echo Auth::type() ?>">
