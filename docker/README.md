# filesender-phpfpm

- [Introduction](#introduction)
- [Dependencies](#dependencies)
- [Environment Variables](#environment-variables)
- [Deployment](#deployment)
  - [simplesamlphp](#simplesamlphp)

## Introduction
[Docker](https://www.docker.com/what-docker) image of [filesender](https://filesender.org) ran within [php-fpm](https://php-fpm.org/), especially tuned for consumption into Kubernetes. All images are based off of [debian](https://www.debian.org/) stable.

This is a follow-up of [ualibraries](https://github.com/ualibraries/filesender-phpfpm). Automated builds will be available at Docker hub.

This [release](https://github.com/filesender/filesender) of filesender can use [simplesamlphp](https://simplesamlphp.org/) or [shibboleth-sp](https://www.shibboleth.net/products/service-provider) for authentication. 
Questions directly related on using or configuring filesender should get posted to it's [mailinglist](https://sympa.uninett.no/lists/filesender.org/lists).

This docker image is not meant to run on its own, as it has some dependencies missing. Especially, this image only provides PHP-FPM with simplesamlphp and filesender installed.
A seperate web-server is required to run this in production. For Kubernetes, this is embedded in the Ingress Controllers.

## Dependencies
This container image of filesender requires the following dependencies:

###  Environmental dependencies
1. An (external) IP address
2. A (public) DNS entry
3. Sufficient storage capacity to store uploaded files for their lifetime`

### External services
1. An [smtp](https://en.wikipedia.org/wiki/Simple_Mail_Transfer_Protocol) server to send emails. For example a third party SMTP service or a company email server could be used.
2. A [reverse proxy](https://en.wikipedia.org/wiki/Reverse_proxy) that can handle FPM. Haproxy or Nginx would be suitable candidates
3. An [SSL certificate](https://nl.wikipedia.org/wiki/Transport_Layer_Security) to make the service available via https/ssl
4. A [database engine](https://en.wikipedia.org/wiki/Database_engine) to store application data in. Currently supported is mysql/pgsql.

All dependencies are provided using the [helm](https://helm.sh/) chart located in TODO

## Environment Variables

The following environment variables control the container setup:

TODO - list environment variables honored

## Configuration

The container has it's configuration for Filesender, fpm and SimpleSAMLphp in /config. Here you can adjust the default config files, or place your own
