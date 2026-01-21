# filesender-phpfpm

- [Introduction](#introduction)
- [Dependencies](#dependencies)
- [Environment Variables](#environment-variables)
- [Deployment](#deployment)
  - [simplesamlphp](#simplesamlphp)

## Introduction
[Docker](https://www.docker.com/what-docker) example image of [filesender](https://filesender.org) based on [php-fpm](https://php-fpm.org/).

This docker image is meant to be a local demo installation, using docker compose. It is configured to use Filesender 3.3, adding some minor patches.

## Dependencies
This container image of filesender requires the following dependencies:
1. docker
2. docker compose

## Running the image
In the docker folder, run

    ./firstrun.sh

Once the script is done, go to [localhost](http://localhost), and log on with one of the following users:
* employee:employeepass
* student:studentpass

## Things to do/test
Here are some things you might want to do and/or test with this test version:
* Change FILESENDER_VERSION in Dockerfile - If you want to test with another Filesender Release
* Change SSP_VERSION in Dockerfile - If you want to test another version of SimpleSAMLphp
After you changed the configuration you can build and run the new image:

        ./build.sh
        ./run.sh

If you want to start with a clean deployment, you can run cleanall.sh. **Note:** cleanall.sh will remove all local containers, images, and data. After you run cleanall.sh, you can execute firstrun.sh again.

## TODO
* Remove hard coded values from Dockerfile
* Separate database access (user/root)
* Use passwords from environment variables
* https configuration with self-signed certificates
* Update nginx in trixie, currently it uses 1.26, with 1.29 being the most recent at the time of writing
