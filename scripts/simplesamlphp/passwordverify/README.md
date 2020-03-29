
This is a work in progress created in March 2020. This should help
small deployments of FileSender be created to allow people to share
files using FileSender.

The idea is to allow authentication against password hashes stored in
the same database that FileSender is using for other activities.

The passwords are using ARGON2ID and only the encoded password
containing the salt and hash are stored in the database itself.

I will shortly be looking at how to add new users to the system and
and initial web interface to set and reset passwords. Having this SAML
plugin allows the web interface to evolve and improve and be updated.

In order to use this setup you have to do the following steps. Details
are provided below

* Apply a small (mainly two word) change to SQL.php in your SimpleSAMLphp installation (patch-to-simplesamlphp.patch)
* Copy PasswordVerify.php to your SimpleSAMLphp installation (/opt/simplesamlphp/modules/sqlauth/lib/Auth/Source)
* Run filesender-config-to-authsources-fragment.php and put that output into /opt/simplesamlphp/config/authsources.php
* Select the filesender-dbauth SAML target in your /opt/filesender/config/config.php file
  ($config['auth_sp_saml_authentication_source'] ="filesender-dbauth";)
* Enable the filesender Web interface for managing the user passwords in it's web interface. This will be
  an option to turn on in /opt/filesender/config/config.php.
  ($config['using_local_saml_dbauth'] = 1;)


# Apply a small (mainly two word) change to SQL.php

```
cd ./simplesamlphp-1.18.5/
patch -p1 < /opt/filesender/scripts/simplesamlphp/passwordverify/patch-to-simplesamlphp.patch
```

# Copy PasswordVerify.php to your SimpleSAMLphp installation

```
cp /opt/filesender/scripts/simplesamlphp/passwordverify/PasswordVerify.php ./modules/sqlauth/lib/Auth/Source/
chgrp apache ./modules/sqlauth/lib/Auth/Source/PasswordVerify.php
```

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
 'default-sp' => [
        'saml:SP',
 ...
 ],

  <<< INSERT-THE-ABOVE-FROM-filesender-config-to-authsources-fragment.php-HERE >>>

```

# Select the filesender-dbauth SAML target

```
vi /opt/filesender/config/config.php
   ...
   // move to end of file
   $config['auth_sp_saml_authentication_source'] ="filesender-dbauth";

```

# Enable the filesender Web interface for managing the user passwords
```
vi /opt/filesender/config/config.php
   ...
   // move to end of file
  $config['using_local_saml_dbauth'] = 1;
```


