<?php

require_once dirname(__FILE__) . '/../common/CommonUnitTestCase.php';

class AuthSPSamlTest extends CommonUnitTestCase
{
    private $authAttributes;
    private $authUser;
    private $configParameters;

    protected function setUp(): void
    {
        $this->authAttributes = $this->getStaticProperty('Auth', 'attributes');
        $this->authUser = $this->getStaticProperty('Auth', 'user');
        $this->configParameters = $this->getStaticProperty('Config', 'parameters');

        $this->setStaticProperty('config', array(
            'uid_attribute' => 'uid',
            'name_attribute' => 'cn',
            'email_attribute' => 'mail',
            'simplesamlphp_location' => '',
            'simplesamlphp_url' => '',
            'authentication_source' => 'test',
            'testsuite_run_locally' => true,
        ));
        $this->setStaticProperty('simplesamlphp_auth_simple', new AuthSPSamlTestSimple());
        $this->setStaticProperty('isAuthenticated', true);
        $this->setStaticProperty('attributes', null);
    }

    protected function tearDown(): void
    {
        $this->setStaticProperty('config', null);
        $this->setStaticProperty('simplesamlphp_auth_simple', null);
        $this->setStaticProperty('isAuthenticated', null);
        $this->setStaticProperty('attributes', null);
        $this->setStaticPropertyValue('Auth', 'attributes', $this->authAttributes);
        $this->setStaticPropertyValue('Auth', 'user', $this->authUser);
        $this->setConfigParameters($this->configParameters);
    }

    public function testAttributesExposeSamlIdentityProvider(): void
    {
        $this->setConfigParameters(array(
            'auth_sp_additional_attributes' => array(),
            'auth_warn_session_expired' => false,
        ));

        $attributes = AuthSPSaml::attributes();

        $this->assertSame('https://idp.example.invalid/entity', $attributes['idp']);
    }

    public function testIdentityProviderSelectsConfigurationProfile(): void
    {
        $this->assertProfileIsSelectedForIdentityProvider(
            'https://idp.example.invalid/entity',
            'SAML IdP test'
        );
    }

    public function testDifferentIdentityProviderRetainsDefaultConfiguration(): void
    {
        $this->assertProfileIsSelectedForIdentityProvider(
            'https://other-idp.example.invalid/entity',
            'Default site'
        );
    }

    public function testMissingIdentityProviderRetainsDefaultConfiguration(): void
    {
        $this->assertProfileIsSelectedForIdentityProvider(null, 'Default site');
    }

    private function assertProfileIsSelectedForIdentityProvider($idp, $expectedSiteName): void
    {
        try {
            $authAttributes = new ReflectionProperty('Auth', 'attributes');
            $authUser = new ReflectionProperty('Auth', 'user');

            $authAttributes->setValue(null, array('idp' => $idp));
            $authUser->setValue(null, true);

            $this->setConfigParameters(array(
                'auth_config_regex_files' => array(
                    'idp' => array(
                        '^https://idp\\.example\\.invalid/entity$' => 'saml-idp-test'
                    ),
                ),
                'site_name' => 'Default site',
                'testsuite_run_locally' => true,
            ));

            $method = new ReflectionMethod('Config', 'handleConfigRegexFiles');
            $method->invoke(null, 'auth_config_regex_files', false);

            $this->assertSame($expectedSiteName, Config::get('site_name'));
        } finally {
        }
    }

}

class AuthSPSamlTestSimple
{
    public function getAttributes()
    {
        return array(
            'uid' => array('test-user'),
            'cn' => array('Test User'),
            'mail' => array('test@example.invalid'),
        );
    }

    public function getAuthData($name)
    {
        if ($name == 'saml:sp:IdP') {
            return 'https://idp.example.invalid/entity';
        }

        return null;
    }
}
