---
title: FileSender Cloud Storage Backends
---

# FileSender Cloud Could Backends

FileSender 2.0 can store the uploaded file data in the cloud. Support
is currently being developed for storing data in Azure blobs and
Amazon S3 is another possible cloud target.

## Azure

At the moment a new container is created for each file and files are
uploaded to multiple blobs which are upload_chunk_size bytes in size.
This is the CloudAzure storage_type. If other styles of
upload/download are desired there might be other Azure storage types
created for FileSender to allow easy choice of schema.

### Setup

Azure support uses the PHP bindings. These bindings can be installed
through composer into the subdirectory of optional-dependencies which
is already setup to help you. FileSender is setup to look into the
optional-dependencies/azure directory for these supporting files if
you enable Azure in your configuration.

```
cd optional-dependencies/azure
.. download composer.phar and check it    ...
.. see https://getcomposer.org/download/  ...
php composer.phar install
```

### Azurite: a local Azure server

You may wish to test against the free and open source
[Azurite](https://github.com/azure/azurite) server clone. This can
be useful not only for development and CI but also for many other
reasons. Having a local Azure server can let you test if FileSender is
working with your PHP libraries and allowing upload and downloads etc
without having to hit the network. If a bug can be replicated using
Azurite then it allows developers to replicate the issue without
knowing about your exact cloud setup.

While you can use the -g "global" option as suggested on the azurite
site, another option is to just create a new directory and download
azurite as a normal system user into that directory. The code will be
installed into a subdirectory node_modules. 

```
$ npm install azurite
$ mkdir data
$ cd node_modules/.bin
$ cat start.sh 
#!/bin/bash

export http_proxy=
export https_proxy=
unset http_proxy
unset https_proxy

cd ./node_modules/.bin
./azurite -l ../../data

$ chmod +x start.sh
$ ./start.sh

```

### Configuration

The main configuration keys for Azure are the connection string to use
to connect and maybe other settings to use to store your data. You
might like to store the first in the optional config-passwords.php
file to keep it separate from your config.php file and reduce the risk
of accidentally sharing sensitive information.

The config-passwords.php has the same format as config.php but allows
you to keep passwords (databases, clouds, etc) out of the main
config.php file.

You might like to also have a switch in your config-passwords.php to
allow local testing without opening or editing the
config-passwords.php file. One option here is to setup the test config
in config-passwords.php. Since the test azure server is for local use
only and on 127.0.0.1 you can also put that into your main config.php
and only include the sensitive 'real' server setup in
config-passwords.php. The config-passwords.php file is loaded after
config.php so you can set a personal setting in config.php to switch
to a test connection string in config-passwords.php. Note that you have
to use Config::get() to access your sysadmin_setting_testcloud setting in
config-passwords.php.

This has the added plus of not having to open config-passwords.php to
see the local only test connection settings. Note that there should be
no closing `?>` in either the config.php or config-passwords.php
files.

```
# cat config.php
... since this is a local only test server, security is less interesting

$config['cloud_azure_connection_string'] = 'DefaultEndpointsProtocol=http;AccountName=devstoreaccount1;AccountKey=Eby8vdM02xNOcqFlqUwJPLlmEtlCDXJ1OUzFT50uSRZ6IFsuFq2UVErCz4I6tq/K1SZFPTOtr/KBHBeksoGMGw==;BlobEndpoint=http://127.0.0.1:10000/devstoreaccount1;';
$config['cloud_azure_other_settings'] = 'something public is ok';
$config['sysadmin_setting_testcloud'] = true;


# cat config-passwords.php
... the less you have to see this file the better ...
<?php

if( !Config::get('sysadmin_setting_testcloud') ) {
  $config['cloud_azure_connection_string'] = 'DefaultEndpointsProtocol=http;AccountName=devstoreaccount1;AccountKey=Eby8vdM02xNOcqFlqUwJPLlmEtlCDXJ1OUzFT50uSRZ6IFsuFq2UVErCz4I6tq/K1SZFPTOtr/KBHBeksoGMGw==;BlobEndpoint=http://myrealazure-provider:10000/devstoreaccount1;';
}
```



### Running a test

To help check if your setup can connect to a cloud server and do
something of use a small test script has been created. This script
will create the container if it does not exist already and write a
blob to the server before reading the data back again.

It is recommended to run this test against an Azurite server first and
then turn on your real credentials to run it against your real cloud
provider.

```
cd scripts/cloud/azure
php test-azure-simpleblob.php
```

### Use as a storage provider in FileSender

Now that you know that your configuration and software setup can talk
to your cloud provider you can switch over your storage_type to start
storing the file content in the cloud.


```
$config['storage_type'] = 'CloudAzure';
```

## Amazon S3


### Setup

Amazon S3 support uses the PHP bindings. These bindings can be installed
through composer into the subdirectory of optional-dependencies which
is already prepared to help you. FileSender is setup to look into the
optional-dependencies/s3 directory for these supporting files if
you enable S3 in your configuration.

```
cd optional-dependencies/s3
.. download composer.phar and check it    ...
.. see https://getcomposer.org/download/  ...
php composer.phar install
```

For more information on installing these bindings see [Amazon's page](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/getting-started_installation.html).


### Zenko CloudServer: your local S3 server

[Zenko CloudServer](https://github.com/scality/cloudserver) is an open source
S3 server implementation. This can be useful for testing and to see
that the FileSender code is working as you expect. [Installation](https://s3-server.readthedocs.io/en/latest/GETTING_STARTED.html#installation) of CloudServer is fairly simple and is also
shown below.

```
git clone https://github.com/scality/S3.git
cd ./S3
npm install
npm start
```

### Configuration

The main configuration keys for S3 are the connection string to use
to connect and maybe other settings to use to store your data. You
might like to store the first in the optional config-passwords.php
file to keep it separate from your config.php file and reduce the risk
of accidentally sharing sensitive information.

The config-passwords.php has the same format as config.php but allows
you to keep passwords (databases, clouds, etc) out of the main
config.php file. If the config-passwords.php file exists then it is
loaded after config.php.

See the configuration section for Azure above for information about how
you might setup the config.php and config-passwords.php so that you can
avoid opening the later file.

```
$config['cloud_s3_endpoint'] = 'http://localhost:8000';
$config['cloud_s3_key']      = 'accessKey1';
$config['cloud_s3_secret']   = 'verySecretKey1';

// optional, Ensure that the bucket exists if you want to use a
// single bucket. 
// $config['cloud_s3_bucket']   = 'filesender';
```

### Running a test

To help check if your setup can connect to a cloud server and do
something of use a small test script has been created. This script
will create the bucket if it does not exist already and write a 30
byte test data packet to the server before reading the data back
again.

It is recommended to run this test against a local CloudServer server first and
then turn on your real credentials to run it against your real cloud
provider.

```
cd scripts/cloud/s3
php test-s3-simple.php
```


### Use as a storage provider in FileSender

Now that you know that your configuration and software setup can talk
to your cloud provider you can switch over your storage_type to start
storing the file content in the cloud.


```
$config['storage_type'] = 'CloudS3';
```
