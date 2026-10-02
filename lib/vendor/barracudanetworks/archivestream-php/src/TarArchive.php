<?php
namespace Barracuda\ArchiveStream;

/**
 * Tar-formatted streaming archive.
 */
class TarArchive extends Archive
{
	const REGTYPE = 0;
	const DIRTYPE = 5;
	const XHDTYPE = 'x';

	/**
	 * Array of specified options for the archive.
	 * @var array
	 */
	public $opt = array();

	/**
	 * Create a new TarArchive object.
	 *
	 * @see \Barracuda\ArchiveStream\Archive
	 */
	public function __construct()
	{
		call_user_func_array(array('parent', '__construct'), func_get_args());
		$this->opt['content_type'] = 'application/x-tar';
	}

	/**
	 * Explicitly adds a directory to the tar (necessary for empty directories).
	 *
	 * @param  string $name Name (path) of the directory.
	 * @param  array  $opt  Additional options to set ("type" will be overridden).
	 * @return void
	 */
	public function add_directory($name, array $opt = array())
	{
		// calculate header attributes
		$this->meth_str = 'deflate';
		$meth = 0x08;

		$opt['type'] = self::DIRTYPE;

		// send header
		$this->init_file_stream_transfer($name, $size = 0, $opt, $meth);

		// complete the file stream
		$this->complete_file_stream();
	}

	/**
	 * Initialize a file stream.
	 *
	 * @param string $name File path or just name.
	 * @param int    $size Size in bytes of the file.
	 * @param array  $opt  Array containing time / type (optional).
	 * @param int    $meth Method of compression to use (ignored by TarArchive class).
	 * @return void
	 */
	public function init_file_stream_transfer($name, $size, array $opt = array(), $meth = null)
	{
		// try to detect the type if not provided
		$type = self::REGTYPE;
		if (isset($opt['type']))
		{
			$type = $opt['type'];
		}
		elseif (substr($name, -1) == '/')
		{
			$type = self::DIRTYPE;
		}

		$dirname = dirname($name);
		$name = basename($name);

		// Remove '.' from the current directory
		$dirname = ($dirname == '.') ? '' : $dirname;

		// if we're using a container directory, prepend it to the filename
		if ($this->use_container_dir)
		{
			// container directory will end with a '/' so ensure the lower level directory name doesn't start with one
			$dirname = $this->container_dir_name . preg_replace('/^\/+/', '', $dirname);
		}

		// Remove trailing slash from directory name, because tar implies it.
		if (substr($dirname, -1) == '/')
		{
			$dirname = substr($dirname, 0, -1);
		}

		// handle long file names via PAX
		if (strlen($name) > 99 || strlen($dirname) > 154)
		{
			$pax = $this->__pax_generate(array(
				'path' => $dirname . '/' . $name
			));

			$this->init_file_stream_transfer('', strlen($pax), array(
				'type' => self::XHDTYPE
			));

			$this->stream_file_part($pax, $single_part = true);
			$this->complete_file_stream();
		}

		// stash the file size for later use
		$this->file_size = $size;

		// process optional arguments
		$time = isset($opt['time']) ? $opt['time'] : time();

		// build data descriptor
		$fields = array(
			array('a100', substr($name, 0, 100)),
			array('a8',   str_pad('777', 7, '0', STR_PAD_LEFT)),
			array('a8',   decoct(str_pad('0', 7, '0', STR_PAD_LEFT))),
			array('a8',   decoct(str_pad('0', 7, '0', STR_PAD_LEFT))),
                );
       	        // The $size can be plain binary data in network byte order
                // with a special 4 byte pad to indicate this for large files
                if( $size < ( 2^63 - 1 )) {
                    $fields = array_merge( $fields, array(                
                        array('a12',  decoct(str_pad($size, 11, '0', STR_PAD_LEFT)))
                        ));
                } else {
                    $fields = array_merge( $fields, array(                
                        array('N',  0x80<<24 ),
                        array('J',  $size ),
                        ));
                }
                $fields = array_merge( $fields, array(
			array('a12',  decoct(str_pad($time, 11, '0', STR_PAD_LEFT))),
			array('a8',   ''),
			array('a1',   $type),
			array('a100', ''),
			array('a6',   'ustar'),
			array('a2',   '00'),
			array('a32',  ''),
			array('a32',  ''),
			array('a8',   ''),
			array('a8',   ''),
			array('a155', substr($dirname, 0, 155)),
			array('a12',  ''),
		));

		// pack fields and calculate "total" length
		$header = $this->pack_fields($fields);

		// Compute header checksum
		$checksum = str_pad(decoct($this->__computeUnsignedChecksum($header)), 6, "0", STR_PAD_LEFT);
		for ($i=0; $i<6; $i++)
		{
			$header[(148 + $i)] = substr($checksum, $i, 1);
		}

		$header[154] = chr(0);
		$header[155] = chr(32);

		// print header
		$this->send($header);
	}

	/**
	 * Stream the next part of the current file stream.
	 *
	 * @param string $data        Raw data to send.
	 * @param bool   $single_part Used to determin if we can compress (not used in TarArchive class).
	 * @return void
	 */
	public function stream_file_part($data, $single_part = false)
	{
		// send data
		$this->send($data);

		// flush the data to the output
		flush();
	}

	/**
	 * Complete the current file stream
	 *
	 * @return void
	 */
	public function complete_file_stream()
	{
		// ensure we pad the last block so that it is 512 bytes
		$mod = ($this->file_size % 512);
		if ($mod > 0)
		{
			$this->send(pack('a' . (512 - $mod), ''));
		}

		// flush the data to the output
		flush();
	}

	/**
	 * Finish an archive
	 *
	 * @return void
	 */
	public function finish()
	{
		// adds an error log file if we've been tracking errors
		$this->add_error_log();

		// tar requires the end of the file have two 512 byte null blocks
		$this->send(pack('a1024', ''));

		// flush the data to the output
		flush();
	}

	/*******************
	 * PRIVATE METHODS *
	 *******************/

	/**
	 * Generate unsigned checksum of header
	 *
	 * @param string $header File header.
	 * @return string Unsigned checksum.
	 * @access private
	 */
	private function __computeUnsignedChecksum($header)
	{
		$unsigned_checksum = 0;

		for ($i = 0; $i < 512; $i++)
		{
			$unsigned_checksum += ord($header[$i]);
		}

		for ($i = 0; $i < 8; $i++)
		{
			$unsigned_checksum -= ord($header[148 + $i]);
		}

		$unsigned_checksum += ord(" ") * 8;

		return $unsigned_checksum;
	}

	/**
	 * Generate a PAX string
	 *
	 * @param array $fields Key value mapping.
	 * @return string PAX formated string
	 * @link http://www.freebsd.org/cgi/man.cgi?query=tar&sektion=5&manpath=FreeBSD+8-current Tar / PAX spec
	 */
	private function __pax_generate(array $fields)
	{
		$lines = '';
		foreach ($fields as $name => $value)
		{
			// build the line and the size
			$line = ' ' . $name . '=' . $value . "\n";
			$size = strlen(strlen($line)) + strlen($line);

			// add the line
			$lines .= $size . $line;
		}

		return $lines;
	}
}
