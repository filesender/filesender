---
title: Reporting security issues
---

# Reporting security issues

If you have found a bug or issue that might be security sensitive you
can report that bug directly to the maintainer. You might like to
encrypt an attached file and either send it directly or send the file
encrypted with filesender and then encrypt and send the passphrase to
the maintainer.

If you want to send something encrypted please use the below GPG key
to encrypt that data. GPG is available on many platforms, see https://www.gnupg.org/download/index.html.


## Getting the GPG public key

As of December 2019 the public key can be found at
```
https://github.com/monkeyiq.gpg
```

## Import the key

On Linux you might find the GPG command is gpg2 for the most recent version.
You should only have to import the key once.

```
gpg2 --import monkeyiq.gpg
gpg2 --fingerprint 903F8814517E7747ED080AD518F3FDAE968ACDEA

   pub   rsa4096 2019-12-17 [SC]
         903F 8814 517E 7747 ED08  0AD5 18F3 FDAE 968A CDEA
   uid           [ultimate] Ben Martin <...>
   sub   rsa4096 2019-12-17 [E]
```

## Encrypt the sensitive data and send it

Whenever you want to send sensitive data you can encrypt it with the following command.

```
echo "secret stuff" > data-to-send.txt

gpg2 --output data-to-send-encrypted.gpg --encrypt --armor --recipient 903F8814517E7747ED080AD518F3FDAE968ACDEA data-to-send.txt
```





