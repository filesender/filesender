#!/bin/bash


cp -f vendor/owasp/csrf-protector-php/js/csrfprotector.js ../www/js/

sed -i -e 's/self::STREAM_CHUNK_SIZE/$this->STREAM_CHUNK_SIZE/g' PHPZipStreamer/src/ZipStreamer.php
sed -i -e 's/const STREAM_CHUNK_SIZE = 1048560/public $STREAM_CHUNK_SIZE = 1048560/g' PHPZipStreamer/src/ZipStreamer.php

