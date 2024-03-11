
This is a javascript REST client for FileSender. It allows uploading
and downloading of encrypted transfers from the command line.
Transfers can also be seen from the web interface and downloaded with
the same passphrase.

The javascript REST client uses the same javascript code that the
browser uses to perform the transfer. This includes the encryption and
decryption of data performed by crypto_app.js. The REST client uses a
nodejs implementation of WebCrypto to enable this functionality.

Download is performed using the same URL that would be loaded in the
browser, information is retrieved from the supplied token. All saved
files are placed into a new subdirectory with the token value as the
directory name. By default all the files of an encrypted transfer are
downloaded into that new subdirectory.

Configuration is done using the same filesender.py.ini file as the
python client uses. In the future perhaps a filesender.js.ini will be
checked too and if it exists used in preference to the
filesender.py.ini file.

If you are using self signed certificates then the strictssl
configuration option might be useful along with the
NODE_TLS_REJECT_UNAUTHORIZED environment variable.

For example:

```
$ export NODE_TLS_REJECT_UNAUTHORIZED='0'
$ cat ~/.filesender/filesender.py.ini
[system]
base_url = https://example.com/filesender/rest.php
default_transfer_days_valid = 10
strictssl = false

[user]
username = tester@example.com
apikey = 2c....43
```

You will need to npm install some modules for the client to work.

```
$ cd scripts/client/nodeclient
$ npm install 
```


General usage for explicit file upload and entire transfer download is
as follows:


```
$ cd scripts/client/nodeclient
$ node upload.js /tmp/testfile.txt --expire 14 --password abc
$ node download.js --password abc 'https://example.com/filesender/?s=download&token=06....11' 
```

An entire directory can also be uploaded with the `-R` command line flag:

```
$ node upload.js -R /tmp/testdir --password allthefiles

```



The following is a non exhaustive list of issues which might be addressed at some stage.

* Perhaps package this up into a more user friendly installation. The
  javascript for the web site would either be local to that package or
  referenced from the upload.js and download.js files.

* TeraSender upload has not been implemented and tested. This would be the preferred
  upload method for best performance.

* Enable and test upload resume.

* More error checking

* Cleaner file upload progress on the command line

* Perhaps the client should read ~/.filesender/filesender.ini and a
  recommendation made to soft link that to filesender.py.ini.


