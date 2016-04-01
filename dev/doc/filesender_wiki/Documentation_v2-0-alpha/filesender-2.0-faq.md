#F.A.Q/troubleshooting for version 2.0


#Table of contents


#Problem: after logging on a user is simply redirected to the logon page. Logging of SimpleSAMLphp seems to indicate the user is not logged on, no authenticated session is created.
#Solution: if you are using SimpleSAMLphp ... ticket #1198, config directive ...

FileSender 2.0 explicitly sets the session.cookie.path to the site URL on initialisation. Usually this points to / and won't cause any
issues for the standard setup with simplesamlphp where simplesamlphp is in a separate URL space from
FileSender.

However, on my testing machine and for example for launching our filesender.uninett.no/beta service this
is a bit different and hence sessions between simplesamlphp and filesender are not accepted by filesender.
With Shibboleth this is not an issue as it uses its own session variable handling. SimpleSAMLphp is in php and shares with PHP.

Agreed solution: introduce a configuration parameter session_cookie_path. When using SimpleSAMLphp in a URL path other than /, you need to configure this to for example /.

Troubleshooting:

FileSize inconsistency:
- check filesize on server and compare to filesize on client!