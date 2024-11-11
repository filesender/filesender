---
title: Reporting security issues
---

# Reporting security issues

We are committed to maintaining the security and integrity of our project. If you discover a bug or issue that may pose a security risk, please report it directly to the project maintainer following the guidelines below.

For enhanced security, you may use FileSender to upload encrypted files. If you choose this method, please send the passphrase separately and securely to filesender.security@commonsconservancy.org, preferably using GPG encryption. Alternatively, you can contact us via email to request another secure channel for transmitting the passphrase, such as Signal.

## Getting the GPG public key

GPG is available on many platforms, see https://www.gnupg.org/download/index.html.

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
