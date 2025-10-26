<?php

// Require environment (fatal)
if (!defined('FILESENDER_BASE')) {
    die('Missing environment');
}

/**
 * OIDC service provider authentication class
 *
 * Handles OpenID provider authentication.
 */
class AuthSPOidc
{   
    /**
     * Cache config
     */
    private static $config = null;
    
    /**
     * Cache attributes
     */
    private static $attributes = null;

    /**
     * Authentication check.
     *
     * @return bool
     */
    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['oidc_user_info']);
    }

    /**
     * Retreive user attributes.
     *
     * @retrun array
     */
    public static function attributes()
    {
        if (is_null(self::$attributes)) {
            if (!self::isAuthenticated()) {
                throw new AuthSPAuthenticationNotFoundException();
            }

            $rawAttributes = $_SESSION['oidc_user_info'];

            $attributes = [];

            // Wanted attributes
            $attributes['uid'] = $rawAttributes->{Config::get('auth_sp_oidc_uid_attribute') ?? 'sub'};
            $attributes['email'] = [$rawAttributes->{Config::get('auth_sp_oidc_email_attribute') ?? 'email'}];
            $attributes['name'] = $rawAttributes->{Config::get('auth_sp_oidc_name_attribute') ?? 'name'};

            if (!$attributes['uid']) {
                throw new AuthSPMissingAttributeException(
                    'uid',
                    $rawAttributes,
                    'uid_attribute',
                    self::$config['uid_attribute']
                );
            }

            if (!$attributes['email']) {
                throw new AuthSPMissingAttributeException(
                    'email',
                    $rawAttributes,
                    'email_attribute',
                    self::$config['email_attribute']
                );
            }

            foreach ($attributes['email'] as $email) {
                if (!Utilities::validateEmail($email)) {
                    throw new AuthSPBadAttributeException('email');
                }
            }

            if (!$attributes['name']) {
                $attributes['name'] = substr($attributes['email'][0], 0, strpos($attributes['email'][0], '@'));
            }

            // Gather additional attributes if required
            $additionalAttributes = Config::get('auth_sp_additional_attributes');
            if ($additionalAttributes) {
                $attributes['additional'] = [];
                foreach ($additionalAttributes as $key => $from) {
                    if (is_numeric($key) && is_callable($from)) {
                        continue;
                    }

                    if (is_callable($from) && !is_string($from)) {
                        $value = $from($rawAttributes);
                    } elseif (isset($rawAttributes->$from)) {
                        $value = $rawAttributes->$from;
                    } else {
                        $value = null;
                    }

                    $attributes['additional'][is_numeric($key) ? $from : $key] = $value;
                }
            }
            
            // Check group requirements
            $requiredGroups = Config::get('auth_sp_oidc_required_groups');
            if ($requiredGroups) {
                $groupsClaim = Config::get('auth_sp_oidc_groups_claim') ?? 'groups';
                $userGroups = $rawAttributes->{$groupsClaim} ?? [];
                
                if (!is_array($userGroups)) {
                    $userGroups = [$userGroups];
                }
                
                $requiredGroups = is_array($requiredGroups) ? $requiredGroups : [$requiredGroups];
                
                $groupMatch = false;
                foreach ($requiredGroups as $requiredGroup) {
                    if (in_array($requiredGroup, $userGroups)) {
                        $groupMatch = true;
                        break;
                    }
                }
                
                if (!$groupMatch) {
                    throw new AuthUserNotAllowedException();
                }
            }
            
            self::$attributes = $attributes;
        }
        
        return self::$attributes;
    }

    /**
     * Generate the logon URL.
     *
     * @param $target
     *
     * @retrun string
     */
    public static function logonURL($target = null)
	{
        $url = Utilities::http_build_query(array(
            'action' => 'login',
            'target' => $target,
        ), 'oidc.php' . '?');
        
        return $url;
	}

    /**
     * Generate the logoff URL.
     *
     * @param $target
     *
     * @retrun string
     */
    public static function logoffURL($target = null)
	{
        $url = Utilities::http_build_query(array(
            'action' => 'logout',
            'target' => $target,
        ), 'oidc.php' . '?');
        
        return $url;
	}

}