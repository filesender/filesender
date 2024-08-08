---
title: FileSender Cloud Storage Backends
---

# FileSender Cloud Backends

FileSender 2.0 can store the uploaded file data in the cloud. Support
is currently being developed for storing data in Azure blobs and
Amazon S3 is another possible cloud target.

It was reported that performance with the S3 backend (and maybe others)
can be greatly increased by bumping up upload_chunk_size and related values.
For example using 40mb chunks:

```
$config['upload_chunk_size'] = 1024 * 1024 * 40;
$config['download_chunk_size'] = $config['upload_chunk_size'];
$config['upload_crypted_chunk_size'] = $config['upload_chunk_size'] + $config['upload_crypted_chunk_padding_size'];
```

Increasing upload_chunk_size also requires you to verify PHP's memory
limit is equal or bigger than about 5x upload_chunk_size.

### Configuration file

The main configuration keys for cloud back ends are the connection
string or other passwords to use to connect. You need to store the
these sensitive settings in the config/config_private.php file to keep it
separate from your config.php file and reduce the risk of accidentally
sharing sensitive information.

The config_private.php has the same format as config.php but allows
you to keep passwords (databases, clouds, etc) out of the main
config.php file. You must have the final `return` statement in the 
config_private.php file as only the variables explicitly returned from
that file will be used for the intended purpose.

See the distribution config/config_private.php.dist for a template to see
what your config/config_private.php should look like and what new keys
might be used in that file in the current release. 


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

See the note about Configuration file at the top of this page.

Other settinggs might look like
```
$config['cloud_azure_other_settings'] = 'something public is ok';
$config['sysadmin_setting_testcloud'] = true;
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

See the note about Configuration file at the top of this page.

The endpoint can be set in config.php like;
```
$config['cloud_s3_endpoint'] = 'http://localhost:8000';
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
