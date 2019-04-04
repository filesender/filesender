<?php
/**
 * Example usage of ArchiveStream
 */

if (!file_exists('../vendor/autoload.php'))
{
	die("Please run `composer install` from the project root before running the example.");
}

require '../vendor/autoload.php';

// add some random files
$files = array(
	'../extras/zip-appnote-6.3.1-20070411.txt',
	'../zipstream.php',
);

// create new zip stream object
$zip = new \Barracuda\ArchiveStream\ZipArchive('test.zip', array(
	'comment' => 'this is a zip file comment.  hello?'
));
var_dump($zip);

// common file options
$file_opt = array(
	// file creation time (2 hours ago)
	'time'    => time() - 2 * 3600,

	// file comment
	'comment' => 'this is a file comment. hi!',
);

// add files under folder 'asdf'
foreach ($files as $file)
{
	// build absolute path and get file data
	$path = ($file[0] == '/') ? $file : "$pwd/$file";

	// add file to archive
	$zip->add_file_from_path('asdf/' . basename($file), $path, $file_opt);
}

// add a long file name
$zip->add_file('/long/' . str_repeat('a', 200) . '.txt', 'test');
$zip->add_directory('/foo');
$zip->add_directory('/foo/bar');

// finish archive
$zip->finish();
