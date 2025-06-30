---
title: F.A.Q/troubleshooting for version 2.0
---

## Frequently Asked Questions for version 2.0


### No session after logging in via SimpleSAMLphp


Problem: after logging on a user is simply redirected to the logon page. Logging of SimpleSAMLphp seems to indicate the user is not logged on, no authenticated session is created.

Solution: if you are using SimpleSAMLphp ... ticket #1198, config directive ...

FileSender 2.0 explicitly sets the session.cookie.path to the site URL on initialisation. Usually this points to / and won't cause any
issues for the standard setup with simplesamlphp where simplesamlphp is in a separate URL space from
FileSender.

However, on my testing machine and for example for launching our filesender.uninett.no/beta service this
is a bit different and hence sessions between simplesamlphp and filesender are not accepted by filesender.
With Shibboleth this is not an issue as it uses its own session variable handling. SimpleSAMLphp is in php and shares with PHP.

Agreed solution: introduce a configuration parameter session_cookie_path. When using SimpleSAMLphp in a URL path other than /, you need to configure this to for example /.

## SimpleSAMLphp for local users for small scale setup or testing

If you have a real authentication setup then skip this section.

Some smaller installations do not have access to a SAML server and
might like to authenticate without setting one up. To have some static
users and passwords to test things out with you can use the below commands.

For setup a local hosted SP you need a selfsigned certificate and
some mateadata files. The folders for that are located in /opt/filesender/simplesaml.

### SimpleSAMLphp config/config.php

First enable saml20-idp protocal and setup modules:
```
	 'enable.saml20-idp' => true,

	 'module.enable' => [
        'exampleauth' => true,
        'core' => true,
        'admin' => true,
        'saml' => true,
    ],
```

### SimpleSAMLphp config/authsources.php

It will be helpfull, to use the admin module of SimpleSAMLphp to check
new settings during the setup proces. With the activated module "admin"
you can the login here: https://YOURDOMAIN/simplesaml/module.php/admin/

Setup example-sp by editing
config/authsources.php. 
```
	// example-sp for local hosted idp
	'example-sp' => [
        'saml:SP',

        // The entity ID of this SP.
        'entityID' => 'https://localdomain/simplesaml/example-sp',

        // The entity ID of the IdP this SP should contact.
        // Can be NULL/unset, in which case the user will be shown a list of available IdPs.
        'idp' => 'urn:x-simplesamlphp:hosted-idp',

        // The URL to the discovery service.
        // Can be NULL/unset, in which case a builtin discovery service will be used.
        'discoURL' => null,

        /*
         * If SP behind the SimpleSAMLphp in IdP/SP proxy mode requests
         * AuthnContextClassRef, decide whether the AuthnContextClassRef will be
         * processed by the IdP/SP proxy or if it will be passed to the original
         * IdP in front of the IdP/SP proxy.
         */
        'proxymode.passAuthnContextClassRef' => false,

    ],
```
Setup some users and passwords by editing
config/authsources.php. In the below a user 'tester' can login and
'testdriver' can login.
```
	// userpass	
	'example-userpass' => array(
	      'exampleauth:UserPass',
	      'tester:testerpassword' => array(
	            'uid' => array('tester'),
	            'email' => array('tester@localhost.localdomain'),
	            'eduPersonTargetedID' => array('uid'),
	            'eduPersonAffiliation' => array('member', 'student'),
	      ),
	      'testdriver:testdriver' => array(
	            'uid' => array('testdriver'),
	            'email' => array('testdriver@localhost.localdomain'),
	            'eduPersonTargetedID' => array('uid'),
	            'eduPersonAffiliation' => array('member', 'student'),
	      ),
	      'employee:employeepass' => array(
	            'uid' => array('employee'),
	            'eduPersonAffiliation' => array('member', 'employee'),
	      ),
	),
```
Check the federation tab in the SimpleSAMLphp-admin-module.
There has to be the hosted entity "example-sp" now. Furthermore in
the "test" tab you can find the example-userpass auth source for
testing the credentials. The example-sp will not working yet!

### SimpleSAMLphp metadata

Now create the selfsigned certificate. Put it in the subfolder cert.
Lets say the names are
```
	example.crt
	example.key
```
Next create 3 metadata files in the subfolder metadata.

saml20-idp-hosted.php
```
<?php
$metadata['urn:x-simplesamlphp:hosted-idp'] = [
    /*
     * The hostname for this IdP. This makes it possible to run multiple
     * IdPs from the same configuration. '__DEFAULT__' means that this one
     * should be used by default.
     */
    'host' => '__DEFAULT__',

    /*
     * The private key and certificate to use when signing responses.
     * These can be stored as files in the cert-directory or retrieved
     * from a database.
     */
    'privatekey' => 'example.key',
    'certificate' => 'example.crt',

    /*
     * The authentication source which should be used to authenticate the
     * user. This must match one of the entries in config/authsources.php.
     */
    'auth' => 'example-userpass',
];
```
saml20-sp-remote.php
```
<?php
// ^^ dont' forget the first line
COPY THE CONTENT FROM FEDERATION TAB: host entity example-sp (SimpleSAMLphp Metadata)
```
saml20-idp-remote.php
```
<?php
// ^^ dont' forget the first line
COPY THE CONTENT FROM FEDERATION TAB: host entity urn:x-simplesamlphp:hosted-idp (SimpleSAMLphp Metadata)
```
Check the result in the federation tab. There should be 2 trused enties now:
- urn:x-simplesamlphp:hosted-idp
- https://localdomain/simplesaml/example-sp

### SimpleSAMLphp final test
Now use the "test" tab again, to test the example-sp auth source with your example credentials.

### setup filesender
You will also have to edit your config/config.php file in your FileSender installation
to include the below configuration directives.

```
$config['auth_sp_saml_authentication_source'] ="example-sp";
$config['auth_sp_saml_email_attribute'] = 'email';
```


If you have tested things and want to provide access to more than a
few users you might like to consider more [real world authentication with simplesamlphp](https://simplesamlphp.org/samlidp)
including LDAP, Windows Live, or SQL
stanza to move authentication over to something more useful or perhaps
using social network authentication such as using google, twitter, or
facebook.



### Troubleshooting:

FileSize inconsistency:
- check filesize on server and compare to filesize on client!
