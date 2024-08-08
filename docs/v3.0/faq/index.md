---
title: F.A.Q/troubleshooting for version 3.0
---

## Frequently Asked Questions for version 3.0


### No session after logging in via SimpleSAMLphp


Problem: after logging on a user is simply redirected to the logon page. Logging of SimpleSAMLphp seems to indicate the user is not logged on, no authenticated session is created.

Solution: if you are using SimpleSAMLphp ... ticket #1198, config directive ...

FileSender 3.0 explicitly sets the session.cookie.path to the site URL on initialisation. Usually this points to / and won't cause any
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

	cd /opt/filesender/simplesaml
	touch ./modules/exampleauth/enable

and then setup some users and passwords by editing
config/authsources.php. In the below a user 'tester' can login and
testdriver can login.

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

You will also have to edit your config/config.php file in your FileSender installation
to include the below configuration directives.

**FIXME**: Using an authsource directly in SSP 2.x is not allowed.

```
$config['auth_sp_saml_authentication_source'] ="example-userpass";
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
