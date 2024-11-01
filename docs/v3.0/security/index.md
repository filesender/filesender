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

As of October 2024 the public key can be found at
https://keys.openpgp.org/search?q=filesender.security%40commonsconservancy.org.

Key ID: 0xAD26FBC14881B8E1
Fingerprint: BC9D CF2B 086D 915A F1D6 D8FE AD26 FBC1 4881 B8E1

## Import the key

On Linux you might find the GPG command is gpg2 for the most recent version.
You should only have to import the key once.

```
gpg2 --import <public-keyfile>

```

## Encrypt the sensitive data and send it

Whenever you want to send sensitive data you can encrypt it with the following command.

```
echo "secret stuff" > data-to-send.txt

gpg2 --output data-to-send-encrypted.gpg --encrypt --armor --recipient BC9DCF2B086D915AF1D6D8FEAD26FBC14881B8E1 data-to-send.txt
```

## How-to reach the team

Please email your findings to this email-address and we'll handle your report carefully:

filesender.security@commonsconservancy.org 
