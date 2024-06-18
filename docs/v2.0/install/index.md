---
title: Installation - Linux Source 2.x from Git
---

## About this documentation

This is the installation documentation for installing the FileSender
2.x releases on Linux. See the
[releases](https://github.com/filesender/filesender/releases) page for
up to date information about recent releases. This guide is written
for installation from source on the RedHat/CentOS or Debian platform
but any Linux variant should work with some modifications (most
notably about installing the required additional software packages).

Our hope is that FileSender installation should take less than an hour.
There are docker images of FileSender available which you might like
to use to quickly see if this is the software that you are looking for.

While efforts have been made to make sure this documentation does not
contain mistakes and is as clear as possible if you see an issue
please report it so we can improve this page for everybody. Please see
[Documentation update page](/filesender/patchdocs) for more information about how
you can report issues with and update the documentation.


### This documentation was tested with

* RedHat/CentOS (7)
* Debian (9, Stretch with [apache and postgresql] and [apache and mariadb])
* Fedora (28 with apache and postgresql)

### Dependencies

* Apache (or nginx) and PHP version 7.3 or later.
* A PostgreSQL or MariaDB database (10.0 or above, 10.2 or later recommended).
* A big filesystem (or cloud backed).
* [SimpleSamlPhp](https://simplesamlphp.org/download/) 1.19.7 or
  newer. There is some support for the 2.x series of SimpleSamlPhp.
  You may need to modify some files in your SimpleSamlPhp for this to
  work, see [issue 1467](https://github.com/filesender/filesender/issues/1467).

Note that older versions of PHP may work, but they are not supported
by the PHP project so it is recommended to avoid them in production. Likewise,
older SimpleSamlPhp versions are likely to work but may contain issues which
have been resolved. Version 10.2 or later of MariaDB is highly recommended.

# Step 0 - Choose your options

For the Web server you can use either Apache or NGINX. For a database
you can use PostgreSQL or MySQL. There are multiple versions of the steps
for the Web server setup, one for each supported server.


# Step 1-apache - Install Apache and PHP

On RedHat/CentOS, run:

```
dnf install -y httpd mod_ssl php php-mbstring php-xml php-json php-intl
```

On Debian, run:

```
apt-get install -y apache2 libapache2-mod-php php php-mbstring php-xml php-json php-intl
```

# Step 1-nginx - Install NGINX and PHP

Its for Debian/Ubuntu use a modern Nginx (after v.0.8) and php-fpm (fpm-fcgi).

	sudo apt-get install nginx php-fpm 



# Step 2 - Install the FileSender package

You can get the source either by [downloading a
release](https://github.com/filesender/filesender/releases) or by
using git. In both cases you should end up with a directory
/opt/filesender containing the installation. The /opt/filesender
directory will contain a filesender subdirectory with the FileSender
code in it and a simplesaml subdirectory containing the authentication
library used by FileSender.

When looking at the below steps it might be a little bit confusing
having a filesender directory inside the /opt/filesender directory but
this is done so that the SimpleSAMLphp library can live alongside your
filesender directory and the entire project is contained inside the
/opt/filesender directory. When you have reached the end of Step 4 you should
see something like the following:

```
# ls -l /opt/filesender
total 8
drwxrwxr-x. 21 root root 4096 Jun  6 15:28 filesender
lrwxrwxrwx.  1 root root   20 Jun  6 15:41 simplesaml -> simplesamlphp-1.19.7
drwxr-xr-x. 23 root root 4096 Mar  3 01:04 simplesamlphp-1.19.7

# ls -l /opt/filesender/filesender/
total 160
drwxrwxr-x. 10 root   root    4096 May 21 12:17 classes
-rw-rw-r--.  1 root   root     313 May 21 12:17 composer.json
-rw-rw-r--.  1 root   root   63666 May 21 12:17 composer.lock
...
```

## Installing from source archives

Download the source archive into a directory, for example ~/src. Lets
assume you have downloaded a release and have a file at
/tmp/filesender-2.0.tar.gz. The tarball will include the release
tags in the directory name which might make for longer URLs than you
might like. So in the below the filesender directory is renamed.


```
su -l
mkdir -p /opt/filesender
cd       /opt/filesender
tar xzvf /tmp/filesender-2.0.tar.gz
mv filesender-filesender-2.0  filesender
```


## Using FileSender using git

Install the Git package with one of the following commands.

```
# on RedHat/CentOS
dnf install -y git

# on Debian:
apt-get install -y git
```


Install the FileSender 2.0 from the GIT repository use the following
commands. The `master` branch will always contain the latest release.
You can select explicit versions using the release tag of the version
you wish to run from the
[Releases](https://github.com/filesender/filesender/releases) page. If
you wish to test a feature that is in development and has been merged
but is not part of any release yet you might like to checkout the
`development` branch which contains all merged updates.

The version 3.0 alpha series has an updated UI using Bootstrap.
Similar to the master and development there are `master3` and
`development3` which are the latest release and current development
code respectively.

In the example code below I am going to use the latest release in the
2.x series. You can see the tag (version string) that you need for git
to get an explicit version by looking on the
[Releases](https://github.com/filesender/filesender/releases) page and
on the left will be the tag shown for every release next to a little
ticket icon.


```
su -l
mkdir /opt/filesender
cd    /opt/filesender
git clone --depth 1 --branch master https://github.com/filesender/filesender.git filesender

cd /opt/filesender/filesender
git checkout master
```

You can bring down new releases to an existing git repository and then
directly checkout new releases in the future.



# Step 3 - Setup the FileSender configuration

We ship the FileSender tarball with `config_sample.php` file rather
than directly providing a `config.php` to make life easier when
packaging the software.

Note that if you wish to support old browsers there are some options you
might like to add to your configuration.

* If you would like to support IE11 and use encryption then you will
  need to enable a legacy encryption key version using the
  [encryption_key_version_new_files](https://docs.filesender.org/v2.0/admin/configuration/#encryption_key_version_new_files)
  directive.

Initialise config file and set permissions right. Make the files, tmp
and log directories writable by the web daemon user (`apache` on
RedHat/CentOS, `www-data` on Debian), copy the config file in place
from the template and allow the web daemon user to read the config.php
configuration file:

On all distributions run:

```
cd /opt/filesender/filesender
cp config/config_sample.php config/config.php
mkdir -p tmp files log
chmod o-rwx tmp files log config/config.php
```

On RedHat/CentOS, run:

```
chown apache:apache tmp files log
chgrp apache config/config.php
dnf install -y policycoreutils-python-utils
semanage fcontext -a -t httpd_sys_content_t '/opt/filesender(/.*)?'
semanage fcontext -a -t httpd_sys_rw_content_t '/opt/filesender/(log|tmp|files)(/.*)?'
setsebool -P httpd_can_sendmail on
restorecon -R /opt/filesender
```

On Debian, run:

```
chown www-data:www-data tmp files log
chgrp www-data config/config.php
```

* **NOTE**: If you use NFS storage for user files on RedHat/CentOS, mount it with the following option: `context=system_u:object_r:httpd_sys_rw_content_t:s0`.
* **DO NOT** enable `httpd_use_nfs`. If you did so before, roll back using `setsebool -P httpd_use_nfs off`.



# Step 4 - Install and configure SimpleSAMLphp

FileSender uses [SimpleSAMLphp](https://simplesamlphp.org/) when it
wants to authenticate a user. SimpleSAMLphp provides many different
mechanisms to authenticate users and can handle large amounts of
users.

Following these instructions will set you up with a SimpleSAMLphp
installation that uses Feide RnD's OpenIdP to authenticate users.
There is also [some
information](../faq/#simplesamlphp-for-local-users-for-small-scale-setup-or-testing)
if you would prefer to setup some username and passwords for local
authentication for development and testing. When you move to a
production service you probably want to change that to only support
authentication sources of your choice.

All versions of FileSender currently use the SimpleSAMLphp 1.x series. For example, version 1.19.7 of SimpleSAMLphp.
[Download SimpleSAMLphp](https://simplesamlphp.org/download/). Other
[(later or older) versions](https://github.com/simplesamlphp/simplesamlphp/releases) will
probably work. The continuous integration in FileSender has an
installation of SimpleSAMLphp the [setup
script](https://github.com/filesender/filesender/blob/master/ci/scripts/simplesamlphp-setup.sh)
shows the version currently used there. 

* **NOTE**: you will of course remember to check [the sha256 hash of the tar file](https://github.com/simplesamlphp/simplesamlphp/releases), right?

Extract SimpleSAMLphp in a suitable directory and create symlink:

```
mkdir -p ~/src
cd ~/src
wget https://github.com/simplesamlphp/simplesamlphp/releases/download/v1.19.7/simplesamlphp-1.19.7.tar.gz

php /opt/filesender/filesender/scripts/install/simplesamlphp-extract-sha256-from-release-notes.php https://github.com/simplesamlphp/simplesamlphp/releases/tag/v1.19.7 >| checklist
echo " simplesamlphp-1.19.7.tar.gz" >> checklist
sha256sum --check checklist
 simplesamlphp-1.19.7.tar.gz: OK

mkdir -p /opt/filesender
cd /opt/filesender
tar xvzf ~/src/simplesamlphp-1.19.7.tar.gz
ln -s simplesamlphp-1.19.7 simplesaml


```

* **SECURITY NOTE**: we only want *the user interface files* to be directly accessible for the world through the web server, not any of the other files. We will not extract the SimpleSAMLphp package in the `/var/www` directory (the standard Apache document root) but rather in a specific `/opt` tree. We'll point to the SimpleSAML web directory with a web server alias.

Copy standard configuration files to the right places:

```
cd /opt/filesender/simplesaml
cp -r config-templates/*.php config/
cp -r metadata-templates/*.php metadata/
```

There are some thoughts on updates to your SimpleSAMLphp configuration
which may improve security. If you have a recommendations for things
people might like to consider please create a pull request on this
file with your recommendations. Note that session.cookie.secure can
only be set if you only allow access to your FileSender instance over
HTTPS, which is highly recommended.

```
cd /opt/filesender/simplesaml
edit config/config.php

  'showerrors' => false,
  'errorreporting' => false,
   ...
  'session.cookie.secure' => true,        // https site only!
  'session.cookie.samesite' => 'Strict',  // cookie option SameSite=Strict
  'session.phpsession.httponly' => true,  // cookie option HttpOnly
  
  
   ...
  'admin.protectindexpage' => true,
  'admin.protectmetadata' => true,
  ...
  'module.enable' => [
      'sanitycheck' => false,
      'admin' => false,
  ],
  ...
```

Set the default salt and admin password to something other than the defaults.
Note that the command used in setting the SALT and password are taken from the
config file itself.

* **NOTE**: Replace the PASSWORD line with a choice of your own!

```
PASSWORD=something-really-long-and-wonderful-written-near-the-keyboard

cd config
SALT=$(LC_CTYPE=C tr -c -d '0123456789abcdefghijklmnopqrstuvwxyz' </dev/urandom | dd bs=32 count=1 2>/dev/null;echo);
sed -i -e "s@'secretsalt' => 'defaultsecretsalt'@'secretsalt' => '$SALT'@g" config.php

HASH=$(echo $PASSWORD | ../bin/pwgen.php | tail -2 | head -1 | cut -c3-200);
sed -i -e "s@'auth.adminpassword' => '123'@'auth.adminpassword' => '$HASH'@g" config.php

```

The below commands will remove some possible functionality such as the
admin interface and some pages in the core module from being
available. Note that the authenticate.php page is there to help verify
authentication and does not seem to be used in the normal flow of
login and logout. The above admin.protect config settings should also
remove these pages from regular user access.

```
cd www
rm -f resources/jquery-1.8.js
rm -f resources/jquery-ui-1.8.js
rm -rf admin
cd ..

cd modules/core/www
rm -f frontpage_auth.php
rm -f frontpage_config.php
rm -f frontpage_federation.php
rm -f frontpage_welcome.php
rm -f authenticate.php
rm -f show_metadata.php
rm -f login-admin.php

```

To tailor your [SimpleSAMLphp](https://simplesamlphp.org/) installation
to match your local site's needs please check the SimpleSAMLphp [installation and
configuration documentation](https://simplesamlphp.org/docs). When
connecting to an Identity provider make sure all the required
attributes are sent by the identity provider. See the section on [IdP
attributes](../admin/reference/#idp_attributes) in the Reference
Manual for details.

* **NOTE**: It's outside the scope of this document to explain how to configure an authentication backend. The software has built-in support for [SAML](https://simplesamlphp.org/docs/stable/ldap:ldap), [LDAP](https://simplesamlphp.org/docs/stable/ldap:ldap), [Radius](https://simplesamlphp.org/docs/stable/radius:radius) and [many more](https://simplesamlphp.org/docs/stable/simplesamlphp-idp#section_2).

The below are some URLs that will be disabled by the above configuration.
You might like to load them and see that you are happy with the results.

```
https://.../simplesaml/module.php/sanitycheck/index.php
https://.../simplesaml/admin/phpinfo.php
https://.../simplesaml/module.php/core/frontpage_config.php
https://.../simplesaml/module.php/core/authenticate.php
https://.../simplesaml/module.php/saml/sp/metadata.php/default-sp?output=xhtml
```

The default redirect for https://.../simplesaml/ will be to
https://.../simplesaml/module.php/core/frontpage_welcome.php so you might like to
HTTP redirect that to your filesender instance. A user should not load
that raw URL so you might also like to consider it suspicious activity
and log the event for investigation.


# Step 5 - Web Server Security

It is highly recommended to only offer the FileSender service over
HTTPS. This prevents information used in a secure session from
accidentally being leaked by unintended unsure HTTP requests.

By default the configuration and setup for Apache and NGINX both use
X-Frame-Options sameorigin and the configuration for FileSender itself
will try to add that policy to pages if there is no existing policy in
place. You can change the later by setting the header_x_frame_options
config.php key to either sameorigin, deny, or none. Values that are not
listed in the documentation for header_x_frame_options will cause a
site halt until the configuration is restored to a valid value.

If you are not running FileSender inside another web application you
might like to set X-Frame-Options to deny in both your web server and
the header_x_frame_options filesender config.php setting. This will
inform the browser to fail to load any part of your site in a frame
which will help strengthen your site against clickjacking.

You may also like to edit cookies at the web server level to set
"SameSite=Strict" as part of each cookie. Setting the
[SameSite](https://www.owasp.org/index.php/SameSite) parameter has
been added to the apache template shown below. This will prevent the
SimpleSAML authentication cookies being sent to the site from cross
site requests.

The apache template configuration file also sets the HTTP
Strict-Transport-Security (HSTS) header for all FileSender responses
not just php pages.



# Step 5-apache - Configure Apache

A default configuration file for apache is shipped with FileSender in the
config-templates/apache directory. You might like to view
the current version [online](https://github.com/filesender/filesender/tree/master/config-templates/apache).

The apache config file is provided in config-templates/apache and
should be copied to one of the following locations depending on your
distribution:

On RedHat/CentOS, run:
```
cd /opt/filesender/filesender/
cp config-templates/apache/filesender.conf /etc/httpd/conf.d/
```

On Debian, run:

```
cd /opt/filesender/filesender/
cp config-templates/apache/filesender.conf /etc/apache2/sites-available/
a2enmod alias headers ssl
a2ensite default-ssl filesender
systemctl restart apache2
```

# Step 5-nginx - Configure NGINX


Edit file /etc/nginx/nginx.conf

```
user www-data;
worker_processes 4;
pid /run/nginx.pid;
events {
        worker_connections 1024;
        use epoll;
}
http {
        sendfile on;
        tcp_nopush on;
        tcp_nodelay on;
        keepalive_timeout 65;
        types_hash_max_size 2048;
        include /etc/nginx/mime.types;
        default_type application/octet-stream;
        access_log /var/log/nginx/access.log;
        error_log /var/log/nginx/error.log;
        gzip on;
        gzip_disable "MSIE [1-6]\.(?!.*SV1)";
        include /etc/nginx/conf.d/*.conf;
        include /etc/nginx/sites-enabled/*;
}
```

Then setup your site file similar to the below. This
would be in a file such as /etc/nginx/sites-enabled/filesender.example.com.

```
server {
        add_header X-Frame-Options sameorigin always;
        client_body_buffer_size 256k;
        client_max_body_size 32m;
        server_name filesender.example.org;
        index index.php;
        error_page 500 502 503 504 /50x.html;
        root /opt/filesender/www;
        location = /50x.html {
            root   /usr/share/nginx/html;
        }
        location / {
            try_files $uri $uri/ /index.php?args;
        }
        location ~ [^/]\.php(/|$) {
            fastcgi_split_path_info  ^(.+\.php)(/.+)$;
            fastcgi_pass  localhost:9090;
            include       fastcgi_params;
            fastcgi_intercept_errors on;
            fastcgi_param PATH_INFO       $fastcgi_path_info;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        }
        location ^~ /saml {
            alias /opt/filesender/saml/www;
            location ~ ^(?<prefix>/saml)(?<phpfile>.+?\.php)(?<pathinfo>/.*)?$ {
                include fastcgi_params;
                fastcgi_pass  localhost:9090;
                fastcgi_param SCRIPT_FILENAME $document_root$phpfile;
                fastcgi_param PATH_INFO       $pathinfo if_not_empty;
            }
        }
        location ~* \.(ico|docx|doc|xls|xlsx|rar|zip|jpg|jpeg|txt|xml|pdf|gif|png|css|js)$ {
            root   /opt/filesender/www/;
        }
        location ~ /\. {
                deny all;
        }
}
```

And sure that your fastcgi_params file ( /etc/nginx/fastcgi_params ) looks like the following:

```
fastcgi_param   QUERY_STRING            $query_string;
fastcgi_param   REQUEST_METHOD          $request_method;
fastcgi_param   CONTENT_TYPE            $content_type;
fastcgi_param   CONTENT_LENGTH          $content_length;

fastcgi_param   SCRIPT_FILENAME         $request_filename;
fastcgi_param   SCRIPT_NAME             $fastcgi_script_name;
fastcgi_param   REQUEST_URI             $request_uri;
fastcgi_param   DOCUMENT_URI            $document_uri;
fastcgi_param   DOCUMENT_ROOT           $document_root;
fastcgi_param   SERVER_PROTOCOL         $server_protocol;

fastcgi_param   GATEWAY_INTERFACE       CGI/1.1;
fastcgi_param   SERVER_SOFTWARE         nginx/$nginx_version;

fastcgi_param   REMOTE_ADDR             $remote_addr;
fastcgi_param   REMOTE_PORT             $remote_port;
fastcgi_param   SERVER_ADDR             $server_addr;
fastcgi_param   SERVER_PORT             $server_port;
fastcgi_param   SERVER_NAME             $server_name;
fastcgi_param   HTTPS                   $https if_not_empty;
fastcgi_param   REDIRECT_STATUS         200;
```


And just set correct port ( for example port 9090 ) at file /etc/php5/fpm/pool.d/www.conf

```
...
listen = 127.0.0.1:9090
...
```


# Step 6 - Install and configure database

## Option a - PostgreSQL

On RedHat/CentOS, run:

	dnf install -y php-pgsql postgresql-server

On Debian, run:

	apt-get install -y postgresql php-pgsql

FileSender uses password based database logins and by default assumes
that PostgreSQL is configured to accept password based sessions on
'localhost'. You should check and when needed change the relevant
settings in the PostgreSQL pg_hba.conf configuration file. This file
should have the following entries with **md5** listed as METHOD for
local IPv4 and IPv6 connections:

```
# Database administrative login by UNIX sockets local all postgres peer
# TYPE DATABASE USER CIDR-ADDRESS METHOD
# "local" is for Unix domain socket connections only local all all peer
# IPv6 local connections: host all all ::1/128 md5
# IPv4 local connections: host all all 127.0.0.1/32 md5
```

On Debian based systems this file will be in
`/etc/postgresql/<version>/main/pg_hba.conf`. On Red Hat/Fedora based
systems this file will be in `/var/lib/pgsql/data/pg_hba.conf`. When
changing the pg_hba.conf file you'll have to restart the database
server with (version number may be different or not needed depending
on your system):

```
service postgresql reload
```

Now create the database user `filesender` without special privileges
and with a password. The command will prompt you to specify and
confirm a password for the new database user. *This is the password
you need to configure in the FileSender configuration file later on*.

```
# su -l postgres
$ createuser -S -D -R -P filesender
Enter password for new role: <secret>
Enter it again: <secret>
```

This will create a database user **filesender** without special
privileges, and with a password. This password you will have to
configure in the filesender config.php later on.

Create the filesender database with UTF8 encoding owned by the newly
created filesender user:

```
# su -l postgres
$ createdb -E UTF8 -O filesender filesender
```


## Option b - MySQL

On RedHat/CentOS, run:

	yum install -y mariadb-server php-mysqlnd

On Debian, run:

	apt-get install -y mariadb-server php-mysql 

Create the filesender database. It is recommended to create two users for the database,
one for normal web usage and another with higher abilities to allow the database setup
and migration script to use. This setup requires the code in FileSender release 2.6 or above to work.
If you are on a lower version of FileSender you will have to grant permission to the normal filesender
user and perhaps grant and remove the DROP and REFERENCES from that user when running database.php.

```
mysql -u root -p
CREATE DATABASE `filesender` DEFAULT CHARACTER SET utf8mb4;

GRANT USAGE ON *.* TO 'filesender'@'localhost'
      IDENTIFIED BY '__<your password>__';
GRANT CREATE, CREATE VIEW, ALTER, SELECT, INSERT, INDEX, UPDATE, DELETE
      ON `filesender`.* TO 'filesender'@'localhost';

GRANT USAGE ON *.* TO 'filesenderadmin'@'localhost'
      IDENTIFIED BY '__<your admin password>__';
GRANT CREATE, CREATE VIEW, ALTER, SELECT, INSERT, INDEX, UPDATE, DELETE, DROP, REFERENCES
      ON `filesender`.* TO 'filesenderadmin'@'localhost';

FLUSH PRIVILEGES;
exit
```

Note that the drop and references permissions are only needed by the
database setup and upgrade script (scripts/upgrade/database.php).
Unfortunately the permission to drop a view is not separate from the
normal drop permission which also allows a table to be deleted. If you
choose to use the same user to run the site and run the database.php
migration script then it is recommended to run to following during
production to remove this ability to drop views (and tables) from the
filesender database user. Remember to allow drop again when you are
upgrading your FileSender installation by running the upgrade script
(scripts/upgrade/database.php).


```
mysql -u root -p
REVOKE DROP ON `filesender`.* FROM 'filesender'@'localhost';
FLUSH PRIVILEGES;
```



# Step 7 - Configure PHP

## Automatic

A sample settings file is provided with FileSender in
**config-templates/filesender-php.ini**. If you don't feel like
manually editing your php.ini file, copy the filesender-php.ini file
to your **/etc/php.d/** (RedHat/CentOS) or
**/etc/php/7.0/apache2/conf.d/** (Debian) directory to activate those
settings.

On **RedHat/CentOS**, run:

	service httpd reload

On **Debian**, run:

	service apache2 reload

## Manual

Turn on logging:

        log_errors = on
        error_log = syslog

Enable secure cookie handling to protect sessions:

        session.cookie_secure = On
        session.cookie_httponly = On

Reload your Apache server to activate the changes to your php.ini.

On **RedHat/CentOS**, run:

	service httpd reload

On **Debian**, run:

	service apache2 reload

# Step 8 - Update your FileSender config.php

Edit your /opt/filesender/filesender/config/config.php to reflect the
your settings. Be sure to at least set `$config['site_url']`, contact
details, database settings and authentication configuration. The
configuration file is self-explanatory.

The main settings you will want to inspect and update are shown below.
You will want to change URLs shown below from 127.0.0.1 to your host name.
Email addresses shown as `root@localhost.localdomain` should be updated.
You will want to update all FIXME in password fields.

```
//
// Email and URL settings to update
//
// String, URL of the application
$config['site_url'] = 'https://127.0.0.1/filesender';                

// Url of simplesamlphp
$config['auth_sp_saml_simplesamlphp_url'] ='https://127.0.0.1/simplesaml/';

// String, UID's (from  $config['saml_uid_attribute'])
// that have Administrator permissions
$config['admin'] = 'root@localhost.localdomain'; 
                                                       
// String, email  address(es, separated by ,)
// to receive administrative messages (low disk  space warning)
$config['admin_email'] ='root@localhost.localdomain'; 
                                                             
// String, default no-reply email  address
$config['email_reply_to'] ='root@localhost.localdomain';


//
// Database settings to update
//
// mysql or pgsql
$config["db_type"] ='mysql';

// password for regular use
$config['db_password'] ='FIXME';

// if the database update script needs more privileges (mysql) then set this as well
$config['db_username_admin'] = 'filesenderadmin';
$config['db_password_admin'] = 'FIXME';


```


# Step 9 - Initialise the FileSender database

Run:

	php /opt/filesender/filesender/scripts/upgrade/database.php

# Step 10 - Configure the FileSender clean-up cron job

```
# cd /opt/filesender/filesender 
# cp config-templates/cron/filesender /etc/cron.daily/filesender
# chmod +x /etc/cron.daily/filesender
```

# Step 10b - Install some python dependancies if you wish to use the filesender.py command line client

The filesender.py script uses some extra libraries. These can be installed either
through your distribution packages or directly with the pip command as shown below.

```
pip3 install requests urllib3
```

On a Fedora based distribution you might install these with:
```
dnf install python3-requests python3-urllib3
```

On a Debian based distribution you might install these with:
```
apt-get install python3-requests python3-urllib3
```





# Step 11 - Optional local about, help, and landing pages

FileSender has provisions to allow you to have a local page for about,
help, and the landing (splash) page the user sees on your FileSender
site. While you could directly edit the page template for your
language doing that would not preserve your changes when you upgrade
FileSender.

If you want a local about, help, or splash page create and edit a file
with the postfix ".local.php" and that local page will be served to
the user instead of the default.

For example, the default help page for English language users might be from

/opt/filesender/language/en_AU/help_text.html.php

So you create a new page at the following location with your site
specific help text in it which would be served instead of the default.

/opt/filesender/language/en_AU/help_text.html.php.local.php

# Step 12 - Start using FileSender

Visit the URL to your FileSender instance.

	https://<your site>/filesender/

If you get an error you might like to check your php log files
/var/log/php-fpm and apache logs, then the filesender logs at
/opt/filesender/filesender/log.


* **NOTE**: If you want your site to be available on `https://<your site>/`, without the /filesender, set `DocumentRoot /opt/filesender/filesender/www` in Apache and remember to update your `$config['site_url']` accordingly.

# Perfection

## SElinux

If you use RedHat/CentOS, you have SElinux installed. SElinux protects
your system from unauthorised changes by attackers. FileSender
supports SElinux, and if you followed this guide you have set up
SElinux correctly with file storage in the same directory as
FileSender.

If you want to store files on another location, set the context of
this location to `httpd_sys_rw_content_t`, otherwise FileSender will
fail trying to write there. If the other location is on an NFS share,
be sure to set the following mount flag:

* `context=system_u:object_r:httpd_sys_rw_content_t:s0`

Example `/etc/fstab` line:

	nfs.filesender.org:/var/lib/filesender /var/lib/filesender nfs noexec,nolock,nfsvers=3,context=system_u:object_r:httpd_sys_rw_content_t:s0 0 0

### SEbooleans

#### httpd_can_sendmail

MUST be on for Apache to be able to send mail.

* `setsebool -P httpd_can_sendmail on`

#### httpd_use_nfs

MUST be off, use `context=system_u:object_r:httpd_sys_rw_content_t:s0` as a mount option instead if you use NFS.

* `setsebool -P httpd_use_nfs off`

#### httpd_can_network_connect_db

MAY be on, if you do not run the database on the local host.

* `setsebool -P httpd_can_network_connect_db on` (database is on another host)
* `setsebool -P httpd_can_network_connect_db off` (database is on localhost)

## HTTPS Only

Its good practice to disallow plain HTTP traffic and allow HTTPS only. Make a file in one of the following locations:

* **/etc/httpd/conf.d/000-forcehttps.conf** (RedHat/CentOS)
* **/etc/apache2/sites-available/000-forcehttps.conf** (Debian)

Add the following:

	<VirtualHost *:80>
		Redirect / https://filesender.example.org/
	</VirtualHost>

On **RedHat/CentOS**, run:

	service httpd reload

On **Debian**, run:

	a2ensite 000-forcehttps
	a2dissite 000-default
	service apache2 reload

## FileSender as main page

If you don't want your users to have to type `/filesender` after the hostname, you can add the following line to your filesender Apache configuration:

	RedirectMatch ^/(?!filesender/|simplesaml/)(.*) https://filesender.example.org/filesender/$1


## Issues and Bugs

Please inspect and report bugs on the [GitHub Issue
Tracker](https://github.com/filesender/filesender/issues)

