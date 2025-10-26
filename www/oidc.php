<?php

require_once('../includes/init.php');
require_once __DIR__.'/../optional-dependencies/oidc/vendor/autoload.php';

use Jumbojett\OpenIDConnectClient;

function getOidcClient() {
    static $client = null;
    
    if ($client !== null) {
        return $client;
    }

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

    $client = new OpenIDConnectClient($oidcIssuer, $oidcClientId, $oidcClientSecret);

    $scopes = Config::get('auth_sp_oidc_scopes');
    if (is_array($scopes)) {
        // Remove 'openid' from configured scopes to avoid duplication
        $scopes = array_filter($scopes, function($scope) {
            return $scope !== 'openid';
        });
        $scopes = array_unique($scopes);
        $client->addScope($scopes);
    }
    
    return $client;
}

function oidcLogin($target = null) {
    Logger::info("OIDC login attempt.");

    $client = getOidcClient();

    if ($target) {
        $_SESSION['oidc_target_url'] = $target;
    }

    $redirectUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
    $client->setRedirectURL($redirectUrl);

    try {
        $client->authenticate();
        $_SESSION['oidc_user_info'] = $client->requestUserInfo();
        $_SESSION['oidc_access_token'] = $client->getAccessToken();

        Logger::info("OIDC login successful for user: " . $_SESSION['oidc_user_info']->sub);
    } catch (Exception $e) {
        Logger::error("OIDC login failed: " . $e->getMessage());
        throw new Exception("OIDC login failed", 1, $e);
    }

    $target = $_SESSION['oidc_target_url'] ?? null;
    if (!$target) {
        $landing_page = Config::get('landing_page') ?: 'upload';
        $target = Utilities::http_build_query(['s' => $landing_page]);
    }

    unset($_SESSION['oidc_target_url']);
    
    header('Location: ' . $target);
    exit;
}

function oidcLogout($target = null) {
    Logger::info("OIDC logout attempt.");

    $client = getOidcClient();
    $accessToken = $_SESSION['oidc_access_token'] ?? null;

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

$target = isset($_GET['target']) ? filter_var(urldecode($_GET['target']), FILTER_SANITIZE_URL) : '';

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