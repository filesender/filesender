---
title: Downloading Files
---

# Downloading Files

## Through the Web Browser

If a transfer has multiple files you can download each file
individually using the download button to the right of each file.

You might like to download all the files in a transfer by clicking on
"Click here to select all files" and below the file list there are
options to download either a zip or tar archive containing the
selected files.

You can also select just the files you are interested in and download
those as a zip or tar archive.

If files are encrypted you will be prompted for the password when you 
start a download.



## Files without encryption

The download file link from the download page can be copied and used
with normal command line tools for files that are not encrypted.

A download link will look something like the following. Notice both the token and file_ids
in the link. In this case we are downloading a single file.

```
https://server.com/download.php?token=6ae29abc-6cc2-4b07-bdec-f401ce164c3b&files_ids=2936194
```


You might use a command like the following to download this file from
the command line. Note that the file will be saved using a file name
will be supplied by the server.

```
curl -O -J -L -R "https://server.com/download.php?token=6ae29abc-6cc2-4b07-bdec-f401ce164c3b&files_ids=2936194"

wget --content-disposition "https://server.com/download.php?token=6ae29abc-6cc2-4b07-bdec-f401ce164c3b&files_ids=2936194"
```

Many unencrypted files can be downloaded into a zip or tar file from
the command line. To find the link to do this you should start a
download in your browser and then stop that download. If you copy the
link being used to download you can use that on the command line with
the above commands. In Chrome you will have to select the "..." menu
item in the top right of the browser and select Downloads (Control-J).
Then right click the downloading file and copy the link location.

The link will contain many files_ids entries as well as an archive_format
and possibly a transaction_id.



