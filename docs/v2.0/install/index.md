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
[Documentation update page](/patchdocs) for more information about how
you can report issues with and update the documentation.


### This documentation was tested with

* RedHat/CentOS (7)
* Debian (9, Stretch with [apache and postgresql] and [apache and mariadb])
* Fedora (28 with apache and postgresql)

### Dependencies

* Apache (or nginx) and PHP from your distribution.
* A PostgreSQL or MySQL database.
* A big filesystem (or cloud backed).
* [SimpleSamlPhp](https://simplesamlphp.org/download) 1.15.4 or newer.

# Step 0 - Choose your options

For the Web server you can use either Apache or NGINX. For a database
you can use PostgreSQL or MySQL. There are multiple versions of the steps
for the Web server setup, one for each supported server.


# Step 1-apache - Install Apache and PHP

On RedHat/CentOS, run:

```
dnf install -y httpd mod_ssl php php-mbstring php-xml php-json
```

On Debian, run:

```
apt-get install -y apache2 php7.0 php7.0-mbstring php7.0-xml php7.0-json libapache2-mod-php7.0
```

# Step 1-nginx - Install NGINX and PHP

Its for Debian/Ubuntu use a modern Nginx (after v.0.8) and php-fpm (fpm-fcgi).

	sudo apt-get install nginx php7.0-fpm 



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
lrwxrwxrwx.  1 root root   20 Jun  6 15:41 simplesaml -> simplesamlphp-1.15.4
drwxr-xr-x. 23 root root 4096 Mar  3 01:04 simplesamlphp-1.15.4

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
/root/src/filesender-2.0.tar.gz. The tarball will include the release
tags in the directory name which might make for longer URLs than you
might like. So in the below the filesender directory is renamed.


```
su -l
mkdir -p /opt/filesender
cd       /opt/filesender
tar xzvf /root/src/filesender-2.0.tar.gz
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


Install the FileSender 2.0 beta branch from the GIT repository use the
following commands. You will need to know the release tag of the
version you wish to run from the
[Releases](https://github.com/filesender/filesender/releases) page. Or
you can run "master" if you just want the latest at a specific point
in time. You might do this to test a new bugfix that is not in any
current release yet.

In the example code below I am going to use version filesender-2.0.
You can see the tag (version string) that you need for git by looking
on the [Releases](https://github.com/filesender/filesender/releases)
page and on the left will be the tag shown for every release next to a
little ticket icon.


```
su -l
mkdir /opt/filesender
cd    /opt/filesender
git clone https://github.com/filesender/filesender.git filesender

cd /opt/filesender/filesender
git checkout filesender-2.0
```

You can bring down new releases to an existing git repository and then
directly checkout new releases in the future.



# Step 3 - Setup the FileSender configuration

We ship the FileSender tarball with `config_sample.php` file rather
than directly providing a `config.php` to make life easier when
packaging the software.

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

Following these instructions will set you up with a
SimpleSAMLphp installation that uses Feide RnD's OpenIdP to
authenticate users. When you move to a production service you probably
want to change that to only support authentication sources of your
choice.

[Download SimpleSAMLphp](https://simplesamlphp.org/download).
Other [(later or older) versions](https://simplesamlphp.org/archive) will probably work.
For the FileSender 2.0 release we tested with version 1.14.13.
In the below I will assume you have downloaded SimpleSAMLphp
to a file at /root/src/simplesamlphp-1.15.4.tar.gz.

* **NOTE**: you will of course remember to check [the sha256 hash of the tar file](https://simplesamlphp.org/archive), right?

Extract SimpleSAMLphp in a suitable directory and create symlink:

```
mkdir -p /opt/filesender
cd /opt/filesender
tar xvzf /root/src/simplesamlphp-1.15.4.tar.gz
ln -s simplesamlphp-1.15.4 simplesaml
```

* **SECURITY NOTE**: we only want *the user interface files* to be directly accessible for the world through the web server, not any of the other files. We will not extract the SimpleSAMLphp package in the `/var/www` directory (the standard Apache document root) but rather in a specific `/opt` tree. We'll point to the SimpleSAML web root with a web server alias.

Copy standard configuration files to the right places:

```
cd /opt/filesender/simplesaml
cp -r config-templates/*.php config/
cp -r metadata-templates/*.php metadata/
```

To tailor your [SimpleSAMLphp](http://simplesamlphp.org/) installation
to match your local site's needs please check the SimpleSAMLphp [installation and
configuration documentation](http://simplesamlphp.org/docs). When
connecting to an Identity provider make sure all the required
attributes are sent by the identity provider. See the section on [IdP
attributes](../admin/reference/#idp_attributes) in the Reference
Manual for details.

* **NOTE**: It's outside the scope of this document to explain how to configure an authentication backend. The software has built-in support for [SAML](https://simplesamlphp.org/docs/stable/ldap:ldap), [LDAP](https://simplesamlphp.org/docs/stable/ldap:ldap), [Radius](https://simplesamlphp.org/docs/stable/radius:radius) and [many more](https://simplesamlphp.org/docs/stable/simplesamlphp-idp#section_2).

There is also [some
information](../faq/#simplesamlphp-for-local-users-for-small-scale-setup-or-testing)
if you would prefer to setup some username and passwords for local
authentication for development and testing.

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
        client_body_buffer_size 256k;
        client_max_body_size 32m;
        server_name filesender.domain.tld;
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

	apt-get install -y postgresql php7.0-pgsql

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

	yum install -y mariadb php-mysql mysql_secure_installation

On Debian, run:

	apt-get install -y mariadb-server php7.0-mysql 

Create the filesender database:

```
mysql -u root -p
CREATE DATABASE `filesender` DEFAULT CHARACTER SET utf8mb4;
GRANT USAGE ON *.* TO 'filesender'@'localhost' IDENTIFIED BY '<your password>';
GRANT CREATE, ALTER, SELECT, INSERT, INDEX, UPDATE, DELETE ON `filesender`.* TO 'filesender'@'localhost';
FLUSH PRIVILEGES;
exit
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

To allow for max. 2 GB Flash uploads change these settings to the values indicated:

	max_input_time = 3600 ; in seconds
	upload_max_filesize = 2047M ; in M, the default value is 2MB
	post_max_size = 2146446312 ; in M, 2047M + 10K

* **NOTE**: when you edit your FileSender config.php remember to change `$config['max_flash_upload_size']` to match your `upload_max_filesize`. If they are not the same FileSender will use the lowest value as the actual maximum upload size for Flash uploads.

Ensure the php temporary upload directory points to a location with enough space:

	upload_tmp_dir = /tmp

* **NOTE**: You probably want to point this to the same directory you will use as your HTML5 upload temp directory (`$config['site_temp_filestore']`).
* **NOTE**: that this setting is for all PHP-apps, not only for filesender.

Turn on logging:

	log_errors = on error_log = syslog

Enable secure cookie handling to protect sessions:

	session.cookie_secure = On session.cookie_httponly = On

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



# Step 9 - Initialise the FileSender database

Run:

	php /opt/filesender/filesender/scripts/upgrade/database.php

# Step 10 - Configure the FileSender clean-up cron job

```
# cd /opt/filesender/filesender 
# cp config-templates/cron/filesender /etc/cron.daily/filesender
# chmod +x /etc/cron.daily/filesender
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

# Support and Feedback

See [Support and Mailing
lists](https://www.assembla.com/wiki/show/file_sender/Support_and_Mailinglists)
and [Feature
requests](https://www.assembla.com/wiki/show/file_sender/Feature_requests).
