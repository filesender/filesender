
This setup was created at the start of the COVID pandemic to help allow
smaller installations of FileSender to be created. The work "PasswordVerify.php"
has since had the core of the work merged into the SimpleSAMLphp project.

https://github.com/simplesamlphp/simplesamlphp-module-sqlauth/blob/master/src/Auth/Source/PasswordVerify.php

The idea is to allow authentication against password hashes stored in
the same database that FileSender is using for other activities.

The passwords are using ARGON2ID and only the encoded password
containing the salt and hash are stored in the database itself.

I will shortly be looking at how to add new users to the system and
and initial web interface to set and reset passwords. Having this SAML
plugin allows the web interface to evolve and improve and be updated.

In the most basic form sspsmall will install SimpleSAMLphp 2.3 for you
and will have the database setup as an authentication method. You
should replace the database stanza in your sspsmall installation with
the one generated with filesender-config-to-authsources-fragment.php
and point your FileSender install at the SP that was setup in your
sspsmall installation.

In order to use this setup you have to do the following steps. More
details are provided below

* Use a recent SimpleSAMLphp 2.3 full distribution which includes the PasswordVerify.php file.
* Run filesender-config-to-authsources-fragment.php and put that output into /opt/simplesamlphp/config/authsources.php
* Setup SimpleSAMLphp 2.3 as an SP and IdP and using the filesender-dbauth target as your authentication method.
  To do this you might like to look at the sspsmall project which allows such a configuration of SimpleSAMLphp
  to be setup on your local machine. https://github.com/monkeyiq/sspsmall
* Tell FileSender to use your new SSP SP in your /opt/filesender/config/config.php file
  ($config['auth_sp_saml_authentication_source'] ="filesender-sp";)
* Enable the filesender Web interface for managing the user passwords in it's web interface. This will be
  an option to turn on in /opt/filesender/config/config.php.
  ($config['using_local_saml_dbauth'] = 1;)




# Run filesender-config-to-authsources-fragment.php

```
cd /opt/filesender/scripts/simplesamlphp/passwordverify
php filesender-config-to-authsources-fragment.php

'filesender-dbauth' => [
    'sqlauth:PasswordVerify',
    'dsn' => 'pgsql:host=localhost;port=5432;dbname=filesender',
    'username' => 'filesender',
    'password' => 'THISISSERIOUS',
    'query' => 'select saml_user_identification_uid as uid, saml_user_identification_uid as email, passwordhash, created, \'uid\' as eduPersonTargetedID from authentications where saml_user_identification_uid = :username ',
],

vi /opt/simplesamlphp/config/authsources.php

 ...
 'filesender-sp' => [
        'saml:SP',
 ...
 ],

  <<< INSERT-THE-ABOVE-FROM-filesender-config-to-authsources-fragment.php-HERE >>>

```

# Select the filesender-sp target

```
vi /opt/filesender/config/config.php
   ...
   // move to end of file
   $config['auth_sp_saml_authentication_source'] ="filesender-sp";

```

# Enable the filesender Web interface for managing the user passwords
```
vi /opt/filesender/config/config.php
   ...
   // move to end of file
  $config['using_local_saml_dbauth'] = 1;
```


