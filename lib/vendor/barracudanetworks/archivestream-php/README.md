# ArchiveStream 1.0.7
[![Code Climate](https://codeclimate.com/github/barracudanetworks/ArchiveStream-php/badges/gpa.svg)](https://codeclimate.com/github/barracudanetworks/ArchiveStream-php)

A library for dynamically streaming dynamic tar or zip files without the need to have the complete file stored on the server. You can specify if you want a tar or a zip; or if you want to have the library figure out the best option based on the user agent string.

## Options

```php
/**
 * Construct Parameters:
 *
 *   $name          - Name of output file (optional).
 *   $opt           - Hash of archive options (optional, see "Archive Options"
 *                    below).
 *   $output_stream - Output stream for archive (optional - defaults to php://output)
 *
 * Archive Options:
 *
 *   comment             - Comment for this archive. (zip only)
 *   content_type        - HTTP Content-Type.  Defaults to 'application/x-zip'.
 *   content_disposition - HTTP Content-Disposition.  Defaults to
 *                         'attachment; filename=\"FILENAME\"', where
 *                         FILENAME is the specified filename.
 *   large_file_size     - Size, in bytes, of the largest file to try
 *                         and load into memory (used by
 *                         add_file_from_path()).  Large files may also
 *                         be compressed differently; see the
 *                         'large_file_method' option.
 *   send_http_headers   - Boolean indicating whether or not to send
 *                         the HTTP headers for this file.
 *   large_files_only    - Boolean indicating whether or not to assume
 *                         that all files we are sending are large.
 *
 * File Options:
 *  time     - Last-modified timestamp (seconds since the epoch) of
 *             this file.  Defaults to the current time.
 *  comment  - Comment related to this file. (zip only)
 *  type     - Type of file object. (tar only)
 *
 *
 * Note that content_type and content_disposition do nothing if you are
 * not sending HTTP headers.
 *
 * Large File Support:
 *
 * By default, the method add_file_from_path() will send send files
 * larger than 20 megabytes along raw rather than attempting to
 * compress them.  You can change both the maximum size and the
 * compression behavior using the large_file_* options above, with the
 * following caveats:
 *
 * * For "small" files (e.g. files smaller than large_file_size), the
 *   memory use can be up to twice that of the actual file.  In other
 *   words, adding a 10 megabyte file to the archive could potentially
 *   occupty 20 megabytes of memory.
 *
 * * For "large" files we use the store method, meaning that the file is
 *   not compressed at all, this is because there is not currenly a good way
 *   to compress a stream within PHP
 *
 * Notes:
 *
 * If you do not set a filename, then this library _DOES NOT_ send HTTP
 * headers by default.  This behavior is to allow software to send its
 * own headers (including the filename), and still use this library.
 */
```

## Usage

### Stream whole file at a time

A fast and simple streaming archive files for PHP.  Here's a simple example:

```php
// Create a new archive stream object (tar or zip depending on user agent)
$zip = \Barracuda\ArchiveStream\Archive::instance_by_useragent('example');

// Create a file named 'hello.txt'
$zip->add_file('hello.txt', 'This is the contents of hello.txt');

// Add a file named 'image.jpg' from a local file 'path/to/image.jpg'
$zip->add_file_from_path('image.jpg', 'path/to/image.jpg');

// Finish the zip stream
$zip->finish();
```

### Stream each file in parts

This method can be used to serve files of any size (GB, TB).

```php
// Create a new archive stream object (tar or zip depending on user agent)
$zip = \Barracuda\ArchiveStream\Archive::instance_by_useragent('example');

// Initiate the stream transfer of some_image.jpg with size 324134
$zip->init_file_stream_transfer('some_image.jpg', 324134);

// Stream part of the contents of some_image.jpg
// This method should be called as many times as needed to send all of its data
$zip->stream_file_part($data);

// Send data descriptor header for file
$zip->complete_file_stream();

// Other files can be added here, simply run the three commands above for each file that is being sent

// Explicitly add a directory to the zip (doesn't recurse - useful for empty
// directories)
$zip->add_directory('foo');
$zip->add_directory('foo/bar');

// Finish the zip stream
$zip->finish();
```

## Installation

Simply run `composer require barracudanetworks/archivestream-php` inside your project.

## Requirements

  * PHP >=5.1.2 (or the [Hash extension](http://php.net/hash)).
  * gmp extension

## Limitations

 * Only the Zip64 (version 4.5 of the Zip specification) format is supported.
 * Files cannot be resumed if a download fails before finishing.

### Other

You can also add comments, modify file timestamps, and customize (or
disable) the HTTP headers.  See the class file for details.

## Contributors
- Paul Duncan - Original author
- Daniel Bergey
- Andy Blyler
- Tony Blyler
- Andrew Borek
- Rafael Corral
- John Maguire
- Zachery Stuart

## License

Original work Copyright 2007-2009 Paul Duncan <pabs@pablotron.org>
Modified work Copyright 2013-2015 Barracuda Networks, Inc.

Licensed under the MIT License
