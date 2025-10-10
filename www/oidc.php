<?php

require_once('../includes/init.php');
require_once __DIR__.'/../optional-dependencies/oidc/vendor/autoload.php';

use Jumbojett\OpenIDConnectClient;

$oidcClient = null;

function getOidcClient() {
    global $oidcClient;

    if ($oidcClient === null) {
        $oidcIssuer = Config::get('auth_sp_oidc_issuer');
        $oidcClientId = ConfigPrivate::get('auth_sp_oidc_client_id');
        $oidcClientSecret = ConfigPrivate::get('auth_sp_oidc_client_secret');

        if (empty($oidcIssuer)) {
            throw new ConfigMissingParameterException('auth_sp_oidc_issuer');
        }
        if (empty($oidcClientId)) {
            throw new ConfigMissingParameterException('auth_sp_oidc_client_id');
        }
        if (empty($oidcClientSecret)) {
            throw new ConfigMissingParameterException('auth_sp_oidc_client_secret');
        }

    $oidcClient = new OpenIDConnectClient($oidcIssuer, $oidcClientId, $oidcClientSecret);
    }
return $oidcClient;
}

function oidcLogin($target = null) {
    global $oidcClient;
    $client = getOidcClient();

    $redirectUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "?target=" . urlencode($target);
    $client->setRedirectURL($redirectUrl);
    $client->addScope(['openid', 'profile', 'email']);

    try {
        $client->authenticate();
        $_SESSION['oidc_user_info'] = $client->requestUserInfo();
        $_SESSION['oidc_access_token'] = $client->getAccessToken();

        Logger::info("OIDC login successful for user: " . $_SESSION['oidc_user_info']->sub);
    } catch (Exception $e) {
        Logger::error("OIDC login failed: " . $e->getMessage());
        throw new Exception("OIDC login failed", 1, $e);
    }

    if (!$target) {
        $landing_page = Config::get('landing_page') ?: 'upload';
        $target = Utilities::http_build_query(['s' => $landing_page]);
    }
    
    header('Location: ' . $target);
    exit;
}

function oidcLogout($target = null) {
    Logger::info("OIDC logout attempt.");

    global $oidcClient;
    $client = getOidcClient();
        
    $accessToken = $_SESSION['oidc_access_token'];

    try {
        if (!empty($accessToken)) {
            $client->revokeToken($accessToken, 'access_token');
        }
    } catch (Exception $e) {
        Logger::error("OIDC access token revocation failed: " . $e->getMessage());
    }

    session_destroy();
    
    Logger::info("OIDC logout successful.");

    if (!$target) {
        $target = Config::get('site_logouturl') ?: '/';
    }
    
    header('Location: ' . $target);
    exit;
}

$target = isset($_GET['target']) ? urldecode($_GET['target']) : '';

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'login') {
        oidcLogin($target);
    } elseif ($_GET['action'] === 'logout') {
        oidcLogout($target);
    }
}

if (isset($_GET['code'])) {
    oidcLogin($target);
}