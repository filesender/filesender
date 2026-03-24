## Introduction
[Docker](https://www.docker.com/what-docker) example image of [filesender](https://filesender.org) based on [php-fpm](https://php-fpm.org/).

This docker image is meant to be a local demo installation, using docker compose. It can be configured to run different versions of Filesender: 
* Filesender 3.3, adding some minor patches
* Any most recent Filesender branch from github
* The local code with any modification(s)

## Dependencies
This container image of filesender requires the following dependencies:
1. docker
2. docker compose

## Running the image
In the docker folder:
1. copy .env.example .env
2. Change FILESENDER_VERSION and if necessary FILESENDER_BRANCH

    make firstrun

Once the script is done, go to [http://localhost](http://localhost), and log on with one of the following users:
* employee:employeepass
* employee2:employee2pass
* student:studentpass
* guest:guestpass

SimpleSAMLphp administration: [http://localhost/simplesaml/admin](http://localhost/simplesaml/admin)
* admin:456

### Example 1: FileSender 3.3 (default example)

        FILESENDER_VERSION=3.3

### Example 2: Development branch of FileSender 3

        FILESENDER_VERSION=branch
        FILESENDER_BRANCH=development3

### Example 3: Local code (including local modifications)

        FILESENDER_VERSION=localdev

## Rebuild and redeploy
To rebuild and redeploy, just run:
        make rebuild

In some cases it is recommended to start with a clean deployment, for example after changing the Filesender version. In those cases, you should run the following:

        make cleanall
        make firstrun

**Note:** cleanall will remove all local containers, images, and data.

## TODO
* Separate database access (user/root)
* https configuration with self-signed certificates
* Update nginx in trixie, currently it uses 1.26, with 1.29 being the most recent at the time of writing
