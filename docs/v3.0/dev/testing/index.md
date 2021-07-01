---
title: Running the test suite
---

# A local Selenium

The simplest way to get a local selenuim installation is using docker.
The following docker invocation is from
https://github.com/SeleniumHQ/docker-selenium

```
docker run -d -p 4444:4444 \
   -p 5902:5900 \
   -v /dev/shm:/dev/shm \
   selenium/standalone-chrome-debug:3.12.0-cobalt
```

This will also let you login over VNC to see the browser run in the test session
which is extremely handy when you are developing new unit tests. The password
to connect to vnc is the word _secret_ by default.

```
vncviewer localhost:5902
```

# Running the tests locally

To run the Selenium tests against your local docker image you will need
to edit your config/config.php file. Note that the test suite makes some
changes to that file so making a backup of the file before you start is a
great idea.

Then edit your config/config.php and set $testing to true. This will enable
fake authentication and set some configuration settings which are used by
the test suite. If you do not already have a $testing in there look at the 
config/config_sample.php from git master and copy the $testingsection into your
config.php. You should make sure to have a copy of all the config settings
in the $testing for your release (or newer) in your config.php.

As mentioned, you need to allow the test suite write access to config.php
for things to work (chmod +w for apache) which is obviously bad in production
but not too bad for a test only instance.

Then you can run individual Selenium tests with the following command, picking
the exact test class to execute.

```
phpunit --debug \
  --configuration unittests/config.xml \
  unittests/selenium_tests/UploadAutoResumeTest.php
```
